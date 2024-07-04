<?php

namespace App\Repositories\Focus\stock_issue;

use App\Models\product\ProductVariation;
use DB;
use App\Exceptions\GeneralException;
use App\Models\stock_issue\StockIssue;
use App\Models\stock_issue\StockIssueItem;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StockIssueRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = StockIssue::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return StockIssue $stock_issue
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['issue_qty', 'qty_onhand', 'qty_rem', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }
        if (@$input['employee_id'] && !isset($input['account_id']))
            throw ValidationException::withMessages(['Expense account required!']);

        // create stock issue
        $data = Arr::only($input, ['date', 'ref_no', 'issue_to', 'employee_id', 'customer_id', 'project_id', 'note', 'quote_id', 'budget_line', 'total']);
        $stock_issue = StockIssue::create($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_issue_id'] = array_fill(0, count($data_items['issue_qty']), $stock_issue->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['warehouse_id'] && $v['issue_qty'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Fields required! issue-qty, location']);
        StockIssueItem::insert($data_items);

        // update stock Qty
        $productvar_ids = $stock_issue->items->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);

            $product = ProductVariation::where('id', $productId)->first();

            foreach ($issuedProducts as $isp){

                if (intval($isp['productvar_id']) === $productId && intval($isp['issue_qty']) > 0) {
                    $product->qty -= intval($isp['issue_qty']);
                    $product->save();
                }
            }
        }

//        $productsAfter = ProductVariation::whereIn('id', $productvarIds)->get()->toArray();
//        return StockIssue::where('id', $stock_issue->id)->with('items')->first();//->with('items')->get();
        /** accounting */
        $this->post_stock_issue($stock_issue);

        if ($stock_issue) {
            DB::commit();
            return $stock_issue;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockIssue $stock_issue
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockIssue $stock_issue, array $input)
    {
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['issue_qty', 'qty_onhand', 'qty_rem', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }
        if (@$input['employee_id'] && !isset($input['account_id']))
            throw ValidationException::withMessages(['Expense account required!']);

        // create stock issue
        $data = Arr::only($input, ['date', 'ref_no', 'issue_to', 'employee_id', 'customer_id', 'project_id', 'note', 'quote_id', 'budget_line', 'total']);
        $result = $stock_issue->update($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_issue_id'] = array_fill(0, count($data_items['issue_qty']), $stock_issue->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['warehouse_id'] && $v['issue_qty'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Fields required! issue-qty, location']);
        $stock_issue->items()->delete();
        StockIssueItem::insert($data_items);

        // update stock Qty
        $productvar_ids = $stock_issue->items->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);

        /** accounting */
        $stock_issue->transactions()->delete();
        $this->post_stock_issue($stock_issue);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockIssue $stock_issue
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockIssue $stock_issue)
    {
        DB::beginTransaction();

        $productvar_ids = $stock_issue->items->pluck('productvar_id')->toArray();
        $stock_issue->items()->delete();

        // update stock Qty
        updateStockQty($productvar_ids);
        
        $stock_issue->transactions()->delete();
        if ($stock_issue->delete()) {
            DB::commit();
            return true;
        }
    }
}
