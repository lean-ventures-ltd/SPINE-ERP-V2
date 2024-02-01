<?php

namespace App\Repositories\Focus\purchase;

use App\Models\project\ProjectMileStone;
use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\assetequipment\Assetequipment;
use App\Models\Company\Company;
use App\Models\items\PurchaseItem;
use App\Models\items\UtilityBillItem;
use App\Models\product\ProductVariation;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use Error;
use Illuminate\Support\Facades\DB;
use App\Models\queuerequisition\QueueRequisition;
use Illuminate\Validation\ValidationException;

/**
 * Class PurchaseorderRepository.
 */
class PurchaseRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Purchase::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        
        if (request('supplier_id')) {
            $q->where('supplier_id', request('supplier_id'));
            // imported records
            $q->whereNotBetween('tid', [3558, 7216]);
        } else $q->limit(500);
        
        return $q->latest()->get();
    }

    /**
     * Import Expenses from external array data
     */
    function expense_import_data($file_name = '') {
        try {
            $expense_data = [];

            $file = base_path() . '/main_creditors/' . $file_name;
            if (!file_exists($file)) return $expense_data;
            // dd($file);

            // convert csv to array
            $export = [];
            $csv_file = fopen($file, 'r');
            while ($row = fgetcsv($csv_file)) $export[] = $row;
            fclose($csv_file);
            // dd($export);

            // compatible database array
            $import = [];
            $headers = current($export);
            $data_rows = array_slice($export, 1, count($export));
            foreach ($data_rows as $i => $row) {
                if (count($headers) != count($row)) {
                    throw ValidationException::withMessages([
                        'Unequal column count on line '. strval($i+1). ' on file '.$file_name
                    ]);
                }

                $new_row = [];
                foreach ($row as $key => $val) {
                    if (stripos($val, 'null') !== false) $val = null;
                    $new_row[$headers[$key]] = $val; 
                }
                $import[] = $new_row;
            }
            // dd($import);

            // expense and expense_items
            foreach ($import as $key => $data) {
                // sanitize data
                $supplier = Supplier::find($data['supplier_id'], ['id', 'taxid']);
                if ($supplier) $data['supplier_taxid'] = $supplier->taxid;
                if ($data['grandtax'] == 0) $data['tax'] = 0;
                
                foreach ($data as $key => $value) {
                    if (in_array($key, ['date', 'due_date'])) {
                        $data[$key] = date_for_database($value);
                    }
                    $data[$key] = trim($value);
                }
                

                if (stripos($data['status'], 'paid') !== false) $data['status'] = 'paid';
                elseif (stripos($data['status'], 'partly paid') !== false) $data['status'] = 'partial';
                else $data['status'] = 'pending';

                // skip payments
                if (stripos($data['status'], 'pmt') !== false) continue;

                // expense items
                $data_items = array_map(fn($v) => [
                    'item_id' => @$data['ledger_id']? $data['ledger_id'] : 103, // cog account
                    'description' => $v['note'],
                    'itemproject_id' => $v['project_id'],
                    'qty' => 1,
                    'rate' => $v['paidttl'],
                    'taxrate' => $v['grandtax'],
                    'itemtax' => $v['tax'],
                    'amount' => $v['grandttl'],
                    'type' => 'Expense', 
                    'warehouse_id' => null, 
                    'uom' => 'Lot',
                ], [$data]);

                unset($data['id'], $data['po_id'], $data['created_at'], $data['updated_at'], $data['ledger_id']);
                $data_keys = array_filter(array_keys($data));
                $data = array_intersect_key($data, array_flip($data_keys));
                // dd(compact('data', 'data_items'));
                $expense_data[] = compact('data', 'data_items');
            }
            return $expense_data;
        } catch (\Throwable $th) {
            $err = $th->getMessage();
            throw new Error("{$err} on file {$file_name}");
        }
    }

    
    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return \App\Models\purchase\Purchase $purchase
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
            ];
            if (in_array($key, ['date', 'due_date'], 1)) 
                $data[$key] = date_for_database($val);
            if (in_array($key, $rate_keys, 1)) 
                $data[$key] = numberClean($val);
        }

        // restrict special characters to only "/" and "-"
        if (@$data['doc_ref_type'] == 'Invoice') {
            if (strlen($data['doc_ref']) != 19 && $data['tax'] > 1) 
                throw ValidationException::withMessages(['invoice_no' => 'Reference No. should contain 19 characters']);
            // restrict special characters to only "/" and "-"
            $pattern = "/^[a-zA-Z0-9-\/]+$/i";
            if (!preg_match($pattern, $data['doc_ref']))
                throw ValidationException::withMessages(['Purchase invoice contains invalid characters']);
            $inv_exists = Purchase::where('doc_ref_type', 'Invoice')
                ->where('doc_ref', $data['doc_ref'])->where('tax', $data['tax'])->exists();
            if ($inv_exists) throw ValidationException::withMessages(['Purchase with similar invoice exists']);
        }

        if (@$data['supplier_taxid']) {
            $taxid_exists = Supplier::where('taxid', $data['supplier_taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists && $data['supplier_type'] != 'supplier') throw ValidationException::withMessages(['Duplicate Tax Pin']);

            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['supplier_taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);
            if (strlen($data['supplier_taxid']) != 11)
                throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters']);
            if (!in_array($data['supplier_taxid'][0], ['P', 'A'])) 
                throw ValidationException::withMessages(['First character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($data['supplier_taxid'],1,9))) 
                throw ValidationException::withMessages(['Characters between 2nd and 10th letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $data['supplier_taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
        }
         if(@$data['tax'] > 0){
            if(@$data['supplier_taxid'] == '')  throw ValidationException::withMessages(['Tax Pin is Required!!']);
        }

        $tid = Purchase::where('ins', $data['ins'])->max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1;
        $result = Purchase::create($data);

        $data_items = $input['data_items'];
        foreach ($data_items as $i => $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'taxrate', 'amount'], 1))
                    $item[$key] = numberClean($val);
                if (isset($item['itemproject_id'])) $item['warehouse_id'] = null;
                if (isset($item['warehouse_id'])) $item['itemproject_id'] = null;
                if ($item['type'] == 'Expense' && empty($input['uom'])) $input['uom'] = 'Lot';
                
            }

            // append modified data_items
            $data_items[$i] = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'bill_id' => $result->id
            ]);
            if ($item['type'] == 'Requisit') {
                $queuerequisition = QueueRequisition::where('product_code', $order_item['product_code'])->where('status', '1')->update(['status'=>$order['tid']]);
                $item['type'] = 'Stock';
            }
            // increase product stock
            if ($item['type'] == 'Stock' && $item['warehouse_id']) {
                $prod_variation = ProductVariation::find($item['item_id']);
                if ($prod_variation->warehouse_id != $item['warehouse_id']) {
                    $is_similar = false;
                    $similar_products = ProductVariation::where('id', '!=', $prod_variation->id)
                        ->where('name', 'LIKE', '%'. $prod_variation->name .'%')->get();
                    foreach ($similar_products as $s_product) {
                        if ($prod_variation->warehouse_id == $item['warehouse_id']) {
                            $is_similar = true;
                            $prod_variation = $s_product;
                            break;
                        }
                    }
                    if (!$is_similar) {
                        // new warehouse product variation
                        $new_wh_product = clone $prod_variation;
                        $new_wh_product->warehouse_id = $item['warehouse_id'];
                        unset($new_wh_product->id, $new_wh_product->qty);
                        $new_wh_product->save();
                        $prod_variation = $new_wh_product;
                    }
                }

                // apply unit conversion
                if (isset($prod_variation->product->units)) {
                    $units = $prod_variation->product->units;
                    foreach ($units as $unit) {
                        if ($unit->code == $item['uom']) {
                            if ($unit->unit_type == 'base') {
                                $prod_variation->increment('qty', $item['qty']);
                            } else {
                                $converted_qty = $item['qty'] * $unit->base_ratio;
                                $prod_variation->increment('qty', $converted_qty);
                            }
                        }
                    }    
                } else throw ValidationException::withMessages(['Please attach units to stock items']);
            }
        }
        PurchaseItem::insert($data_items);

        // direct purchase bill
        $this->generate_bill($result);

        /** accounting **/
        $this->post_transaction($result);

        /** Updating Budget Line Balance **/
        $budgetLine = ProjectMileStone::find($input['data']['project_milestone']);
        if (!empty($budgetLine)){
            $budgetLine->balance -= floatval(str_replace(',', '', $input['data']['grandttl']));
            $budgetLine->save();
        }

        if ($result) {
            DB::commit();
            return $result;   
        }
        
        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.purchaseorders.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Purchaseorder $purchaseorder
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($purchase, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];


        /** Handling milestone changes */
        $budgetLine = ProjectMileStone::find($purchase->project_milestone);
        $newBudgetLine = ProjectMileStone::find($input['data']['project_milestone']);


        $milestoneChanged = intval($purchase->project_milestone) !== intval($input['data']['project_milestone']);
        $grandTotalChanged = floatval($purchase->grandttl) !== floatval(str_replace(',', '', $input['data']['grandttl']));
        $newMilestoneZero = intval($input['data']['project_milestone']) === 0;
        $oldMilestoneZero = intval($purchase->project_milestone) === 0;


        /** If the milestone HAS CHANGED and grand total HAS CHANGED  */
        if($milestoneChanged && $grandTotalChanged){

            if (!$oldMilestoneZero) {
                $budgetLine->balance = $budgetLine->balance + $purchase->grandttl;
                $budgetLine->save();
            }

            if (!$newMilestoneZero) {

                $newBudgetLine->balance -= floatval(str_replace(',', '', $input['data']['grandttl']));
                $newBudgetLine->save();
            }
        }
        /** If the milestone has NOT changed but grand total HAS CHANGED */
        else if (!$milestoneChanged && $grandTotalChanged){

            if (!$oldMilestoneZero) {
                $budgetLine->balance = ($budgetLine->balance + $purchase->grandttl) - floatval(str_replace(',', '', $input['data']['grandttl']));
                $budgetLine->save();
            }
        }
        /** If the milestone HAS CHANGED but grand total HAS NOT CHANGED */
        else if($milestoneChanged && !$grandTotalChanged){

            if (!$oldMilestoneZero) {
                $budgetLine->balance = $budgetLine->balance + $purchase->grandttl;
                $budgetLine->save();
            }

            if (!$newMilestoneZero) {

                $newBudgetLine->balance -= $purchase->grandttl;
                $newBudgetLine->save();
            }
        }



        foreach ($data as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
            ];
            if (in_array($key, ['date', 'due_date'])) 
                $data[$key] = date_for_database($val);
            if (in_array($key, $rate_keys)) 
                $data[$key] = numberClean($val);
        }

        if (@$data['doc_ref_type'] == 'Invoice') {
            if (strlen($data['doc_ref']) != 19 && $data['tax'] > 1) 
                throw ValidationException::withMessages(['invoice_no' => 'Reference No. should contain 19 characters']);
            // restrict special characters to only "/" and "-"
            $pattern = "/^[a-zA-Z0-9-\/]+$/i";
            if (!preg_match($pattern, $data['doc_ref']))
                throw ValidationException::withMessages(['Purchase invoice contains invalid characters!']);
            $inv_exists = Purchase::where('id', '!=', $purchase->id)->where('doc_ref_type', 'Invoice')
                ->where('doc_ref', $data['doc_ref'])->where('tax', $data['tax'])->exists();
            if ($inv_exists) throw ValidationException::withMessages(['Purchase with similar invoice exists!']);
        }
        
        if (@$data['supplier_taxid']) {
            $taxid_exists = Supplier::where('taxid', $data['supplier_taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists && $data['supplier_type'] != 'supplier') throw ValidationException::withMessages(['Duplicate Tax Pin']);

            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['supplier_taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);
            if (strlen($data['supplier_taxid']) != 11)
                throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters']);
            if (!in_array($data['supplier_taxid'][0], ['P', 'A'])) 
                throw ValidationException::withMessages(['Initial character of Tax Pin must be letter "P" or "A"']);
            $pattern = "/^[0-9]+$/i";
            if (!preg_match($pattern, substr($data['supplier_taxid'],1,9))) 
                throw ValidationException::withMessages(['Characters between 2nd and 10th letters must be numbers']);
            $letter_pattern = "/^[a-zA-Z]+$/i";
            if (!preg_match($letter_pattern, $data['supplier_taxid'][-1])) 
                throw ValidationException::withMessages(['Last character of Tax Pin must be a letter']);
        }

        $prev_note = $purchase->note;
        $result = $purchase->update($data);

        $data_items = $input['data_items'];
        $purchase->items()->whereNotIn('id', array_map(fn($v) => $v['item_id'], $data_items))->delete();
        // create or update purchase item
        foreach ($data_items as $item) {  
            if ($item['type'] == 'Expense' && empty($item['uom'])) 
                $item['uom'] = 'Lot';      
                
            $purchase_item = PurchaseItem::firstOrNew(['id' => $item['item_id']]);

            // update product stock
            if ($item['type'] == 'Stock' && $item['warehouse_id']) {
                $prod_variation = $purchase_item->product;
                if ($prod_variation) $prod_variation->decrement('qty', $purchase_item->qty);
                else $prod_variation = ProductVariation::find($item['item_id']);
            
                if ($prod_variation->warehouse_id != $item['warehouse_id']) {   
                    $is_similar = false;
                    $similar_products = ProductVariation::where('id', '!=', $prod_variation->id)
                        ->where('name', 'LIKE', '%'. $prod_variation->name .'%')->get();
                    foreach ($similar_products as $s_product) {
                        if ($prod_variation->warehouse_id == $item['warehouse_id']) {
                            $is_similar = true;
                            $prod_variation = $s_product;
                            break;
                        }
                    }
                    if (!$is_similar) {
                        $new_product = clone $prod_variation;
                        $new_product->warehouse_id = $item['warehouse_id'];
                        unset($new_product->id, $new_product->qty);
                        $new_product->save();
                        $prod_variation = $new_product;
                    }
                }

                // apply unit conversion
                if (isset($prod_variation->product->units)) {
                    $units = $prod_variation->product->units;
                    foreach ($units as $unit) {
                        if ($unit->code == $item['uom']) {
                            if ($unit->unit_type == 'base') {
                                $prod_variation->increment('qty', $item['qty']);
                            } else {
                                $converted_qty = $item['qty'] * $unit->base_ratio;
                                $prod_variation->increment('qty', $converted_qty);
                            }
                        }
                    }   
                } else throw ValidationException::withMessages(['Please attach units to stock items']);
            }    

            $item = array_replace($item, [
                'ins' => $purchase->ins,
                'user_id' => $purchase->user_id,
                'bill_id' => $purchase->id,
                'rate' => numberClean($item['rate']),
                'taxrate' => numberClean($item['taxrate']),
                'amount' => numberClean($item['amount']),
            ]);   
            $purchase_item->fill($item);
            if (!$purchase_item->id) unset($purchase_item->id);
            if ($purchase_item->warehouse_id) unset($purchase_item->itemproject_id);
            elseif ($purchase_item->itemproject_id) unset($purchase_item->warehouse_id);
            $purchase_item->save();
        }

        // direct purchase bill 
        $this->generate_bill($purchase);

        /** accounting */
        Transaction::where(['tr_type' => 'bill', 'tr_ref' => $purchase->id])->where('note', 'LIKE', "%{$prev_note}%")->delete();
        $this->post_transaction($purchase);

        if ($result) {
            DB::commit();
            return $purchase;
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.purchaseorders.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Purchaseorder $purchaseorder
     * @throws GeneralException
     * @return bool
     */
    public function delete($purchase)
    {
        if ($purchase->bill()->whereHas('payments')->exists()) 
            throw ValidationException::withMessages(['Not allowed! Purchase is billed and has related payments']);
        
        DB::beginTransaction();

        try {
            // reduce stock
            foreach ($purchase->items as $i => $item) {
                if ($item->type != 'Stock') continue;
                $prod_variation = $item->productvariation;
                // apply unit conversion
                if (isset($prod_variation->product->units)) {
                    $units = $prod_variation->product->units;
                    foreach ($units as $unit) {
                        if ($unit->code == $item['uom']) {
                            if ($unit->unit_type == 'base') {
                                $prod_variation->decrement('qty', $item['qty']);
                            } else {
                                $converted_qty = $item['qty'] * $unit->base_ratio;
                                $prod_variation->decrement('qty', $converted_qty);
                            }
                        }
                    }     
                } else if ($prod_variation) $prod_variation->decrement('qty', $item['qty']);      
                else throw ValidationException::withMessages(['Product on line ' . strval($i+1) . ' may not exist! Please update it from the Inventory']); 
            }

            // delete bill
            UtilityBill::where(['document_type' => 'direct_purchase', 'ref_id' => $purchase->id])->delete();

            // delete transactions
            Transaction::where(['tr_type' => 'bill', 'tr_ref' => $purchase->id])->where('note', 'LIKE', "%{$purchase->note}%")->delete();
            aggregate_account_transactions();

            if ($purchase->delete()) {
                DB::commit();
                return true;
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($th instanceof ValidationException) throw $th;
            throw new GeneralException(trans('exceptions.backend.purchaseorders.delete_error'));
        }
    }

    /**
     * Generate Purchase Bill
     * 
     * @param Purchase $purchase
     * @return $bill
     */
    public function generate_bill($purchase)
    {
        $bill_data = [
            'supplier_id' => $purchase->supplier_id,
            'reference' => $purchase->doc_ref,
            'reference_type' => strtolower($purchase->doc_ref_type),
            'document_type' => 'direct_purchase',
            'ref_id' => $purchase->id,
            'date' => $purchase->date,
            'due_date' => $purchase->due_date,
            'tax_rate' => $purchase->tax,
            'subtotal' => $purchase->paidttl,
            'tax' => $purchase->grandtax,
            'total' => $purchase->grandttl,
            'note' => $purchase->note,
        ];

        $purchase_items = $purchase->items->toArray();
        $bill_items_data = array_map(fn($v) => [
            'ref_id' => $v['id'],
            'note' => "({$v['type']}) {$v['description']} {$v['uom']}",
            'qty' => $v['qty'],
            'subtotal' => $v['qty'] * $v['rate'],
            'tax' => $v['taxrate'],
            'total' => $v['amount'], 
        ], $purchase_items);

        $bill = UtilityBill::where([
            'document_type' => $bill_data['document_type'], 
            'ref_id' => $bill_data['ref_id']
        ])->first();
        
        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($bill_items_data as $item) {
                $new_item = UtilityBillItem::firstOrNew([
                    'bill_id' => $bill->id,
                    'ref_id' => $item['ref_id']
                ]);
                $new_item->save();
            }
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::where('ins', auth()->user()->ins)->max('tid') + 1;
            $bill = UtilityBill::create($bill_data);

            $bill_items_data = array_map(function ($v) use($bill) {
                $v['bill_id'] = $bill->id;
                return $v;
            }, $bill_items_data);
            UtilityBillItem::insert($bill_items_data);
        }

        return $bill;
    }

    /**
     * Direct Purchase Transaction
     * 
     * @param Purchase $purchase
     * @return void
     */
    public function post_transaction($purchase) 
    {
        // credit Accounts Payable (Creditors)
        $account = Account::where('system', 'payable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $purchase->grandttl,
            'tr_date' => $purchase->date,
            'due_date' => $purchase->due_date,
            'user_id' => $purchase->user_id,
            'note' => $purchase->note,
            'ins' => $purchase->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $purchase->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
        ];
        Transaction::create($cr_data);

        $dr_data = array();
        unset($cr_data['credit'], $cr_data['is_primary']);

        // debit Stock
        $wip_account = Account::where('system', 'wip')->first(['id']);
        $stock_exists = $purchase->items()->where('type', 'Stock')->count();
        if ($stock_exists) {
            // if project stock, WIP account else Stock account
            $is_project_stock = $purchase->items()->where('type', 'Stock')->where('itemproject_id', '>', 0)->count();
            if ($is_project_stock) {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $wip_account->id,
                    'debit' => $purchase['stock_subttl'],
                ]);    
            } else {
                $account = Account::where('system', 'stock')->first(['id']);
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account->id,
                    'debit' => $purchase['stock_subttl'],
                ]);    
            }
        }

        // debit Expense and Asset account
        foreach ($purchase->items as $item) {
            $subttl = $item['amount'] - $item['taxrate'];
            // debit Expense 
            if ($item['type'] == 'Expense') {
                $account_id = $item['item_id'];
                // if project expense, use WIP account
                if ($item['itemproject_id']) 
                    $account_id = $wip_account->id;
                    
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account_id,
                    'debit' => $subttl,
                ]);
            }
            //  debit Asset 
            if ($item['type'] == 'Asset') {
                $account_id = Assetequipment::find($item['item_id'])->account_id;
                // if project asset, use WIP account
                if ($item['itemproject_id']) 
                    $account_id = $wip_account->id;
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $account_id,
                    'debit' => $subttl,
                ]);
            }
        }

        // debit tax (VAT)
        if ($purchase['grandtax'] > 0) {
            $account = Account::where('system', 'tax')->first(['id']);
            $dr_data[] = array_replace($cr_data, [
                'account_id' => $account->id, 
                'debit' => $purchase['grandtax'],
            ]);
        }
        Transaction::insert($dr_data); 
        aggregate_account_transactions();
    }
}
