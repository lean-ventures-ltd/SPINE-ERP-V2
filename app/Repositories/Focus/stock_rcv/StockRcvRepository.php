<?php

namespace App\Repositories\Focus\stock_rcv;

use App\Exceptions\GeneralException;
use App\Models\stock_rcv\StockRcv;
use App\Models\stock_rcv\StockRcvItem;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class StockRcvRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = StockRcv::class;

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
     * @return StockRcv $stock_rcv
     */
    public function create(array $input)
    {  
        DB::beginTransaction();
        
        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['qty_rcv', 'qty_rem', 'qty_transf', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create stock receiving
        $data = Arr::only($input, ['stock_transfer_id', 'date', 'ref_no', 'receiver_id', 'note', 'total']);
        $stock_rcv = StockRcv::create($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_rcv_id'] = array_fill(0, count($data_items['qty_rcv']), $stock_rcv->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['qty_rcv'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Qty Received fields are required!']);
        StockRcvItem::insert($data_items);

        // update status
        $this->updateTransferStatus($stock_rcv->stock_transfer);
        
        // update Stock Qty
        $productvar_ids = $stock_rcv->items->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);
        
        if ($stock_rcv) {
            DB::commit();
            return $stock_rcv;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param StockRcv $stock_rcv
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(StockRcv $stock_rcv, array $input)
    {   
        DB::beginTransaction();

        $input['date'] = date_for_database($input['date']);
        $input['total'] = numberClean($input['total']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['qty_rcv', 'qty_rem', 'qty_transf', 'cost', 'amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // update stock transfer
        $data = Arr::only($input, ['stock_transfer_id', 'date', 'ref_no', 'receiver_id', 'note', 'total']);
        $result =  $stock_rcv->update($data);

        $data_items = array_diff_key($input, $data);
        $data_items['stock_rcv_id'] = array_fill(0, count($data_items['qty_rcv']), $stock_rcv->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['qty_rcv'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Qty Received fields are required!']);
        $stock_rcv->items()->delete();
        StockRcvItem::insert($data_items);

        // update transfer status
        $this->updateTransferStatus($stock_rcv->stock_transfer);

        // update Stock Qty
        $productvar_ids = $stock_rcv->items->pluck('productvar_id')->toArray();
        updateStockQty($productvar_ids);
        
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param StockRcv $stock_rcv
     * @throws GeneralException
     * @return bool
     */
    public function delete(StockRcv $stock_rcv)
    { 
        DB::beginTransaction();

        $productvar_ids = $stock_rcv->items->pluck('productvar_id')->toArray();
        $stock_rcv->items()->delete();
        // update transfer status
        $this->updateTransferStatus($stock_rcv->stock_transfer);
        // Update Stock Qty 
        updateStockQty($productvar_ids);

        if ($stock_rcv->delete()) {
            DB::commit();
            return true;
        }
    }

    /**
     * Update Stock Transfer Status
     */
    public function updateTransferStatus($stock_transfer)
    {
        if (!$stock_transfer) return false;
        foreach ($stock_transfer->items as $key => $item) {
            $qty_transf = round($item->qty_transf);
            $qty_rcv_total = round($item->rcv_items()->sum('qty_rcv'));
            if ($qty_rcv_total == 0) $stock_transfer->update(['status' => 'Pending']);
            elseif ($qty_transf > $qty_rcv_total) $stock_transfer->update(['status' => 'Partial']);
            else $stock_transfer->update(['status' => 'Complete']);
        }
        return true;
    }
}
