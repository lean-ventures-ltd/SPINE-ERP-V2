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
namespace App\Http\Controllers\Focus\labour_allocation;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\labour_allocation\LabourAllocationRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class LabourAllocationTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LabourAllocationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LabourAllocationRepository $repository ;
     */
    public function __construct(LabourAllocationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();
        $prefixes = prefixesArray(['quote', 'proforma_invoice'], auth()->user()->ins);
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($labour_allocation) use($prefixes) {
                $project_tid = $labour_allocation->project;
                if($project_tid)return gen4tid('PRJ-',$project_tid->tid);
            })
            ->editColumn('project_id', function ($labour_allocation) {
                
                return;
            })
            ->addColumn('employee_name', function ($labour_allocation) {
               $names = [];
                foreach ($labour_allocation->items as $item) {
                    if ($item->employee) $names[] = $item->employee->full_name;
                }
                return $names;
            })
            ->addColumn('project_name', function ($labour_allocation) {
                return @$labour_allocation->project->name;
            })
            ->addColumn('customer_branch', function ($labour_allocation) {
                $project = $labour_allocation->project;
                if($project){
                    if($project->quote){
                        $customer = @$project->customer->company;
                        $branch = @$project->branch->name;
                        if ($branch) $customer  .= " - {$branch}";
                        return $customer;
                    }
                }
                
            })
            ->addColumn('quote_tid', function ($labour_allocation) {
                $quote_tids = [];
                if($labour_allocation->project){
                    $project = $labour_allocation->project;
                    foreach ($project->quotes as $quote) {
                        $tid = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                        $quote_tids[] = '<a href="'. route('biller.quotes.show', $quote) .'">'. $tid .'</a>';
                    }
                }
                return implode(', ', $quote_tids);
            })
            ->addColumn('job_card', function ($labour_allocation) {
                return $labour_allocation->job_card;
            })
            ->addColumn('note', function ($labour_allocation) {
                return $labour_allocation->note;
            })
            ->addColumn('date', function ($labour_allocation) {
                return dateFormat($labour_allocation->date);
            })
            ->addColumn('hrs', function ($labour_allocation) {
                return $labour_allocation->hrs;
            })
            ->addColumn('type', function ($labour_allocation) {
                return strtoupper($labour_allocation->type);
            })
            
            ->addColumn('actions', function ($labour_allocation) {
                return $labour_allocation->action_buttons;
            })
            ->make(true);
    }
}
