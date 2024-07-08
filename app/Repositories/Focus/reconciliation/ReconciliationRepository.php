<?php

namespace App\Repositories\Focus\reconciliation;

use App\Exceptions\GeneralException;
use App\Models\reconciliation\Reconciliation;
use App\Models\reconciliation\ReconciliationItem;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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

        foreach ($input as $key => $value) {
            if (in_array($key, ['end_balance', 'begin_balance', 'cash_in', 'cash_out', 'cleared_balance', 'balance_diff']))
                $input[$key] = numberClean($value);
        }

        $dates = explode('-', $input['end_date']);
        $exists = Reconciliation::whereYear('end_date', end($dates))->whereMonth('end_date', current($dates))->exists();
        if ($exists) throw ValidationException::withMessages(['end_date' => 'Reconciliation For The Same Month already Exists']);

        $data_items = Arr::only($input, ['checked', 'man_journal_id', 'journal_item_id', 'payment_id', 'deposit_id']);
        $data = array_diff_key($input, $data_items);
        $reconciliation = Reconciliation::create($data);
        $data_items['reconciliation_id'] = array_fill(0, count($data_items['payment_id']), $reconciliation->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['checked']);
        if (!$data_items) throw ValidationException::withMessages(['Reconciled line items required!']);
        ReconciliationItem::insert($data_items);
    
        if ($reconciliation) {
            DB::commit();
            return $reconciliation;
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
    public function update(Reconciliation $reconciliationciliation, array $input)
    { 
        DB::beginTransaction();
        
        foreach ($input as $key => $value) {
            if (in_array($key, ['end_balance', 'begin_balance', 'cash_in', 'cash_out', 'cleared_balance', 'balance_diff']))
                $input[$key] = numberClean($value);
        }

        $data_items = Arr::only($input, ['checked', 'man_journal_id', 'journal_item_id', 'payment_id', 'deposit_id']);
        $data = array_diff_key($input, $data_items);
        $result = $reconciliationciliation->update($data);
        $data_items['reconciliation_id'] = array_fill(0, count($data_items['checked']), $reconciliationciliation->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['checked']);
        if (!$data_items) throw ValidationException::withMessages(['Reconciled line items required!']);
        $reconciliationciliation->items()->delete();
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
    public function delete(Reconciliation $reconciliationciliation)
    {
        DB::beginTransaction();

        $reconciliationciliation->items()->delete();    
        if ($reconciliationciliation->delete()) {
            DB::commit();
            return true;
        }
    }
}