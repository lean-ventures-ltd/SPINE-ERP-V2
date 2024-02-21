<?php

namespace App\Repositories\Focus\stock_adj;

use DB;
use App\Exceptions\GeneralException;
use App\Models\stock_adj\StockAdj;
use App\Models\stock_adj\StockAdjItem;
use App\Models\transaction\Transaction;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StockAdjRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = StockAdj::class;
    
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
     * @return StockAdj $stock_adj
     */
    public function create(array $input)
    {  
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['new_qty', 'qty_diff', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create stock adj
        $data = Arr::only($input, ['date', 'adj_type', 'account_id', 'note', 'total']);
        $stock_adj = StockAdj::create($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_adj_id'] = array_fill(0, count($data_items['new_qty']), $stock_adj->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function($v) use($stock_adj) {
            if ($stock_adj->adj_type == 'Qty') return $v['new_qty'] != 0;
            if ($stock_adj->adj_type == 'Cost') return $v['cost'] != 0;
            if ($stock_adj->adj_type == 'Qty-Cost') return ($v['new_qty'] != 0 && $v['cost'] != 0);
            return false;
        });
        if (!$data_items) throw ValidationException::withMessages(['Qty or Cost fields are required!']);
        StockAdjItem::insert($data_items);
        $adj_total = $stock_adj->items()->sum('amount');
        if (round($adj_total) != round($stock_adj->total)){
            $stock_adj->update(['total' => $adj_total]);
        }
        
        // Update Stock Cost
        foreach ($stock_adj->items as $key => $item) {
            if ($item->productvar) $item->productvar->update(['purchase_price', $item->cost]);
        }
        // update stock Qty
        $productvar_ids = $stock_adj->items()->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);

        /** accounting */
        if ($stock_adj->total < 0) $stock_adj->total = $stock_adj->total*-1;
        if (boolval($stock_adj->total)) {
            $this->post_stock_adjustment($stock_adj);
        }

        if ($stock_adj) {
            DB::commit();
            return $stock_adj;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockAdj $stock_adj
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockAdj $stock_adj, array $input)
    {
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['new_qty', 'qty_diff', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create stock adj
        $data = Arr::only($input, ['date', 'adj_type', 'account_id', 'note', 'total']);
        $result = $stock_adj->update($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_adj_id'] = array_fill(0, count($data_items['new_qty']), $stock_adj->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function($v) use($stock_adj) {
            if ($stock_adj->adj_type == 'Qty') return $v['new_qty'] != 0;
            if ($stock_adj->adj_type == 'Cost') return $v['cost'] != 0;
            if ($stock_adj->adj_type == 'Qty-Cost') return ($v['new_qty'] != 0 && $v['cost'] != 0);
            return false;
        });
        if (!$data_items) throw ValidationException::withMessages(['Qty or Cost fields are required!']);
        $stock_adj->items()->delete();
        StockAdjItem::insert($data_items);
        $adj_total = $stock_adj->items()->sum('amount');
        if (round($adj_total) != round($stock_adj->total)){
            $stock_adj->update(['total' => $adj_total]);
        }
        
        // Update Stock Cost
        foreach ($stock_adj->items as $key => $item) {
            if ($item->productvar) $item->productvar->update(['purchase_price', $item->cost]);
        }
        // update stock Qty
        $productvar_ids = $stock_adj->items()->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);

        /** accounting */
        if ($stock_adj->total < 0) $stock_adj->total = $stock_adj->total*-1;
        if (boolval($stock_adj->total)) {
            $stock_adj->transactions()->delete();
            $this->post_stock_adjustment($stock_adj);
        }

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockAdj $stock_adj
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockAdj $stock_adj)
    { 
        DB::beginTransaction();
        $productvar_ids = $stock_adj->items()->pluck('productvar_id')->toArray();

        // Update Cost to reflect opening stock
        foreach ($stock_adj->items as $key => $item) {
            if ($item->productvar) {
                $op_stock_item = $item->productvar->openingstock_item;
                if ($op_stock_item) $item->productvar->update(['purchase_price', $op_stock_item->cost]);
            }
        }

        $stock_adj->transactions()->delete();
        $stock_adj->items()->delete();
        // update Stock Qty 
        updateStockQty($productvar_ids);

        if ($stock_adj->delete()) {
            DB::commit();
            return true;
        }
    }
}
