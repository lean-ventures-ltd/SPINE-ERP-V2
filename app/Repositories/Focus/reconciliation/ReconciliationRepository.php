<?php

namespace App\Repositories\Focus\reconciliation;

use App\Exceptions\GeneralException;
use App\Models\reconciliation\Reconciliation;
use App\Models\reconciliation\ReconciliationItem;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

/**
 * Class ProductcategoryRepository.
 */
class ReconciliationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Reconciliation::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->query()->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $input['end_date'] = date_for_database($input['end_date']);
        foreach ($input as $key => $value) {
            if (in_array($key, ['end_balance', 'begin_balance', 'cash_in', 'cash_out', 'cleared_balance', 'balance_diff']))
                $input[$key] = numberClean($value);
        }

        $data_items = Arr::only($input, ['checked', 'man_journal_id', 'journal_item_id', 'payment_id', 'deposit_id']);
        $data = array_diff_key($input, $data_items);
        $recon = Reconciliation::create($data);
        $data_items['reconciliation_id'] = array_fill(0, count($data_items['payment_id']), $recon->id);
        $data_items = modify_array($data_items);
        ReconciliationItem::insert($data_items);
    
        if ($recon) {
            DB::commit();
            return $recon;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Reconciliation $teconcilliation
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Reconciliation $reconciliation, array $input)
    { 
        DB::beginTransaction();
        
        foreach ($input as $key => $value) {
            if (in_array($key, ['end_balance', 'begin_balance', 'cash_in', 'cash_out', 'cleared_balance', 'balance_diff']))
                $input[$key] = numberClean($value);
        }

        $data_items = Arr::only($input, ['checked', 'man_journal_id', 'journal_item_id', 'payment_id', 'deposit_id']);
        $data = array_diff_key($input, $data_items);
        $result = $reconciliation->update($data);
        $data_items['reconciliation_id'] = array_fill(0, count($data_items['checked']), $reconciliation->id);
        $data_items = modify_array($data_items);
        $reconciliation->items()->delete();
        ReconciliationItem::insert($data_items);
    
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Reconciliation $teconcilliation
     * @throws GeneralException
     * @return bool
     */
    public function delete(Reconciliation $reconciliation)
    {
        DB::beginTransaction();

        $reconciliation->items()->delete();    
        if ($reconciliation->delete()) {
            DB::commit();
            return true;
        }
    }
}