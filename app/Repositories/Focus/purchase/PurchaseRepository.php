<?php

namespace App\Repositories\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Exceptions\GeneralException;
use App\Models\Company\Company;
use App\Models\items\PurchaseItem;
use App\Models\items\UtilityBillItem;
use App\Models\product\ProductVariation;
use App\Models\project\ProjectMileStone;
use App\Models\queuerequisition\QueueRequisition;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class PurchaseorderRepository.
 */
class PurchaseRepository extends BaseRepository
{
    use Accounting;
    
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

        $q->when(request('supplier_id'), function($q) {
            $q->where('supplier_id', request('supplier_id'));
        });
        if (!request('supplier_id')) $q->limit(500);

        return $q->latest()->get();
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
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
            ];
            if (in_array($key, $rate_keys)) $data[$key] = numberClean($val);
            if (in_array($key, ['date', 'due_date'])) $data[$key] = date_for_database($val);
        }

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
        if (@$data['tax'] > 0 && @$data['supplier_taxid'] == '')
            throw ValidationException::withMessages(['Tax Pin is Required!!']);
        
        // create walkin supplier if none exists
        if (@$data['supplier_type'] == 'walk-in') {
            $supplier = Supplier::where('name', 'LIKE', '%walk-in%')->orWhere('company', 'LIKE', '%walk-in%')->first();
            if (!$supplier) {
                $company = Company::find(auth()->user()->ins);
                $supplier = Supplier::create([
                    'name' => 'Walk-In',
                    'phone' => 0,
                    'address' => 'N/A',
                    'city' => @$company->city,
                    'region' => @$company->region,
                    'country' => @$company->country,
                    'email' => 'walkin@sample.com',
                    'company' => 'Walk-In',
                    'taxid' => 'N/A',
                    'role_id' => 0,
                ]);
            }
            $data['supplier_id'] = $supplier->id;
        }
        
        $tid = Purchase::where('ins', $data['ins'])->max('tid');
        if ($data['tid'] <= $tid) $data['tid'] = $tid+1; 
        $result = Purchase::create($data);

        $prod_variation_ids = [];
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
                $queuerequisition = QueueRequisition::where('product_code', @$item['product_code'])->where('status', '1')->first();
                if ($queuerequisition) $queuerequisition->update(['status'=> @$order['tid']]);
                $item['type'] = 'Stock';
            }

            // increase stock
            if ($item['type'] == 'Stock' && $item['warehouse_id']) {
                $prod_variation = ProductVariation::find($item['item_id']);
                if ($prod_variation->warehouse_id != $item['warehouse_id']) {
                    $similar_prod_variation = ProductVariation::where(['parent_id' => $prod_variation->parent_id, 'warehouse_id' => $item['warehouse_id']])
                        ->where('name', 'LIKE', '%'. $prod_variation->name .'%')
                        ->first();
                    if (!$similar_prod_variation) {
                        // new warehouse product variation
                        $similar_prod_variation = $prod_variation->replicate();
                        $similar_prod_variation->warehouse_id = $item['warehouse_id'];
                        unset($similar_prod_variation->id, $similar_prod_variation->qty);
                        $similar_prod_variation->save();
                        $prod_variation = $similar_prod_variation;
                    }
                }
                if ($prod_variation) $prod_variation_ids[] = $prod_variation->id;
            }
        }
        PurchaseItem::insert($data_items); 
        
        // check if item totals match parent totals
        $items_amount = $result->items->sum('amount');
        if (round($result->grandttl) != round($items_amount))
            throw ValidationException::withMessages(['Server Error! Please check line item totals']);

        // update stock qty
        updateStockQty($prod_variation_ids);

        $milestoneId = $input['data']['project_milestone'] ?? 0;

        /** Updating Budget Line Balance **/
        $budgetLine = ProjectMileStone::find($milestoneId);
        if (!empty($budgetLine)){
            $budgetLine->balance -= floatval(str_replace(',', '', $input['data']['grandttl']));
            $budgetLine->save();
        }
        
        /** accounting **/
        $bill = $this->generate_bill($result);
        $result->bill_id = $bill->id;
        $this->post_purchase_expense($result);

        if ($result) {
            DB::commit();
            return $result;   
        }
        
        DB::rollBack();
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
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];

        /** Handling milestone changes */
        $budgetLine = ProjectMileStone::find($purchase->project_milestone);

        $newMilestoneId = $input['data']['project_milestone'] ?? 0;

        $newBudgetLine = ProjectMileStone::find($newMilestoneId);


        $milestoneChanged = intval($purchase->project_milestone) !== intval($newMilestoneId);
        $grandTotalChanged = floatval($purchase->grandttl) !== floatval(str_replace(',', '', $input['data']['grandttl']));
        $newMilestoneZero = intval($newMilestoneId) === 0;
        $oldMilestoneZero = intval($purchase->project_milestone) === 0;


        /** If the milestone HAS CHANGED and grand total HAS CHANGED  */
        if($milestoneChanged && $grandTotalChanged){

            if (!$oldMilestoneZero && $budgetLine) {
                $budgetLine->balance = $budgetLine->balance + $purchase->grandttl;
                $budgetLine->save();
            }

            if (!$newMilestoneZero && $newBudgetLine) {

                $newBudgetLine->balance -= floatval(str_replace(',', '', $input['data']['grandttl']));
                $newBudgetLine->save();
            }
        }
        /** If the milestone has NOT changed but grand total HAS CHANGED */
        else if (!$milestoneChanged && $grandTotalChanged){

            if (!$oldMilestoneZero && $budgetLine) {
                $budgetLine->balance = ($budgetLine->balance + $purchase->grandttl) - floatval(str_replace(',', '', $input['data']['grandttl']));
                $budgetLine->save();
            }
        }
        /** If the milestone HAS CHANGED but grand total HAS NOT CHANGED */
        else if($milestoneChanged && !$grandTotalChanged){

            if (!$oldMilestoneZero && $budgetLine) {
                $budgetLine->balance = $budgetLine->balance + $purchase->grandttl;
                $budgetLine->save();
            }

            if (!$newMilestoneZero && $newBudgetLine) {
                $newBudgetLine->balance -= $purchase->grandttl;
                $newBudgetLine->save();
            }
        }

        foreach ($data as $key => $val) {
            $rate_keys = [
                'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
                'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
            ];
            if (in_array($key, ['date', 'due_date'])) $data[$key] = date_for_database($val);
            if (in_array($key, $rate_keys)) $data[$key] = numberClean($val);
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

        // create walkin supplier if none exists
        if (@$data['supplier_type'] == 'walk-in') {
            $supplier = Supplier::where('name', 'LIKE', '%walk-in%')->orWhere('company', 'LIKE', '%walk-in%')->first();
            if (!$supplier) {
                $company = Company::find(auth()->user()->ins);
                $supplier = Supplier::create([
                    'name' => 'Walk-In',
                    'phone' => 0,
                    'address' => 'N/A',
                    'city' => @$company->city,
                    'region' => @$company->region,
                    'country' => @$company->country,
                    'email' => 'walkin@sample.com',
                    'company' => 'Walk-In',
                    'taxid' => 'N/A',
                    'role_id' => 0,
                ]);
            }
            $data['supplier_id'] = $supplier->id;
        }
        $result = $purchase->update($data);

        $prod_variation_ids = [];
        $data_items = $input['data_items']; 
        $purchase->items()->whereNotIn('id', array_map(fn($v) => $v['id'], $data_items))->delete();
        // create or update purchase item
        foreach ($data_items as $item) {  
            if ($item['type'] == 'Expense' && empty($item['uom'])) $item['uom'] = 'Lot';                  
            $purchase_item = PurchaseItem::firstOrNew(['id' => $item['id']]);

            // update product stock
            if ($item['type'] == 'Stock' && $item['warehouse_id']) {
                $prod_variation = $purchase_item->product;
                if (!$prod_variation) $prod_variation = ProductVariation::find($item['item_id']);
                if ($prod_variation->warehouse_id != $item['warehouse_id']) {   
                    $similar_product = ProductVariation::where(['parent_id' => $prod_variation->parent_id, 'warehouse_id' => $item['warehouse_id']])
                        ->where('name', 'LIKE', '%'. $prod_variation->name .'%')->first();
                    if (!$similar_product) {
                        // new product
                        $similar_product = $prod_variation->replicate();
                        $similar_product->warehouse_id = $item['warehouse_id'];
                        unset($similar_product->id, $similar_product->qty);
                        $similar_product->save();
                        $prod_variation = $similar_product;
                    }
                }
                if ($prod_variation) $prod_variation_ids[] = $prod_variation->id;
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
        
        // check if item totals match parent totals
        $items_amount = $purchase->items->sum('amount');
        if (round($purchase->grandttl) != round($items_amount))
            throw ValidationException::withMessages(['Server Error! Please check line item totals']);
        
        // update stock qty
        updateStockQty($prod_variation_ids);

        /** accounting */
        $bill = $this->generate_bill($purchase);
        $purchase->bill_id = $bill->id;
        Transaction::where('bill_id', $bill->id)->delete();
        $this->post_purchase_expense($purchase);

        if ($result) {
            DB::commit();
            return $purchase;
        }

        DB::rollBack();
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
        $bill = $purchase->bill;
        if ($bill && $bill->payments()->exists()) throw ValidationException::withMessages(['Not allowed! Purchase is billed and has related payments']);

        DB::beginTransaction();

        if ($bill) {
            $bill->transactions()->delete();
            $bill->items()->delete();
            $bill->delete();
        }

        $prod_variation_ids = [];
        foreach ($purchase->items as $i => $item) {
            if ($item->type != 'Stock') continue;
            $prod_variation = $item->productvariation;
            if ($prod_variation) $prod_variation_ids = [];
        }
        $purchase->items()->delete();
        updateStockQty($prod_variation_ids);
        if ($purchase->delete()) {
            DB::commit();
            return true;
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
        $purchase_items = $purchase->items->toArray();
        $bill_items_data = array_map(fn($v) => [
            'ref_id' => $v['id'],
            'note' => "({$v['type']}) {$v['description']} {$v['uom']}",
            'qty' => $v['qty'],
            'subtotal' => $v['qty'] * $v['rate'],
            'tax' => $v['taxrate'],
            'total' => $v['amount'], 
        ], $purchase_items);

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
        $bill = UtilityBill::where(['document_type' => 'direct_purchase','ref_id' => $purchase->id])->first();
        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($bill_items_data as $item) {
                $new_item = UtilityBillItem::firstOrNew(['bill_id' => $bill->id,'ref_id' => $item['ref_id']]);
                $new_item->save();
            }
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::max('tid')+1;
            $bill = UtilityBill::create($bill_data);
            $bill_items_data = array_map(function ($v) use($bill) {
                $v['bill_id'] = $bill->id;
                return $v;
            }, $bill_items_data);
            UtilityBillItem::insert($bill_items_data);
        }
        return $bill;
    }
}
