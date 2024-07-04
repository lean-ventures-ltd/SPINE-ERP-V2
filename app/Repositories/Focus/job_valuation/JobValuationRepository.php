<?php

namespace App\Repositories\Focus\job_valuation;

use DB;
use App\Exceptions\GeneralException;
use App\Models\job_valuation\JobValuation;
use App\Models\job_valuation\JobValuationItem;
use App\Models\job_valuation\JobValuationJC;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class JobValuationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = JobValuation::class;
    
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
     * @return JobValuation $job_valuation
     */
    public function create(array $input)
    { 
        DB::beginTransaction();

        $input['valuation_date'] = date_for_database($input['valuation_date']);
        foreach ($input as $key => $val) {
            if (in_array($key, ['date'])) $input[$key] = array_map(fn($v) =>  date_for_database($v), $val);
            if (in_array($key, ['tax_rate', 'product_tax', 'product_price', 'product_subtotal', 'product_amount', 'perc_valuated', 'total_valuated'])) {
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
            }
            if (in_array($key, ['taxable', 'subtotal', 'tax', 'total', 'balance'])) {
                $input[$key] = numberClean($val);
            }
        }

        // create job valuation
        $data = Arr::only($input, ['quote_id', 'customer_id', 'branch_id','tax_id', 'taxable', 'subtotal', 'tax', 'total', 'balance']);
        $data['date'] = $input['valuation_date'];
        $job_valuation = JobValuation::create($data);

        // job valuation items
        $data_items = Arr::only($input, ['verified_item_id', 'numbering', 'row_type', 'row_index', 'product_name', 'unit', 'product_qty', 'tax_rate', 'product_tax', 
            'product_price', 'product_subtotal', 'product_amount', 'productvar_id', 'perc_valuated', 'total_valuated']);
        $data_items['job_valuation_id'] = array_fill(0, count($data_items['perc_valuated']), $job_valuation->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['perc_valuated'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['percentage valuated field is required!']);
        JobValuationItem::insert($data_items);

        // job valuation jobcards
        $data_items = Arr::only($input, ['type', 'reference', 'date', 'technician', 'equipment', 'location', 'fault', 'equipment_id']);
        $data_items['job_valuation_id'] = array_fill(0, count($data_items['technician']), $job_valuation->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['technician']);
        JobValuationJC::insert($data_items);

        if ($job_valuation) {
            DB::commit();
            return $job_valuation;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param JobValuation $job_valuation
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(JobValuation $job_valuation, array $input)
    {   
        // 
    }

    /**
     * For deleting the respective model from storage
     *
     * @param JobValuation $job_valuation
     * @throws GeneralException
     * @return bool
     */
    public function delete(JobValuation $job_valuation)
    { 
        DB::beginTransaction();
        $job_valuation->job_cards()->delete();    
        $job_valuation->items()->delete();
        if ($job_valuation->delete()) {
            DB::commit();
            return true;
        }
    }
}
