<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\lead;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\lead\LeadRepository;
use Carbon\Carbon;

/**
 * Class BranchTableController.
 */
class LeadsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $lead;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(LeadRepository $lead)
    {

        $this->lead = $lead;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->lead->getForDataTable();

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['lead'], $ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('reference', function ($lead) use($prefixes) {
                return gen4tid("{$prefixes[0]}-", $lead->reference);
            })
            ->addColumn('client_name', function ($lead) {
                $client_name = $lead->client_name;
                if ($lead->customer) $client_name = $lead->customer->company;
                if ($client_name && $lead->branch) $client_name .= " - {$lead->branch->name}";
                return $client_name;
            })
            ->addColumn('exact_date', function ($lead) {
                $days = '';
                if ($lead->exact_date) {
                    $exact = Carbon::parse($lead->exact_date);
                    $difference = $exact->diff(Carbon::now());
                    $days = $difference->days;
                    return $days;
                }
                return $days;
            })
            ->addColumn('created_at', function ($lead) {
                return dateFormat($lead->created_at);
            })
            ->addColumn('actions', function ($lead) {
                return $lead->action_buttons;
            })
            ->make(true);
    }
}
