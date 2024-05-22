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
namespace App\Http\Controllers\Focus\stock_issue;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\stock_issue\StockIssueRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class StockIssuesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockIssueRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockIssueRepository $repository ;
     */
    public function __construct(StockIssueRepository $repository)
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

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->editColumn('date', function ($stock_issue) {
                return dateFormat($stock_issue->date);
            })
            ->editColumn('issue_to', function ($stock_issue) {
                $issue_to = '';
                if ($stock_issue->employee_id) $issue_to = $stock_issue->employee->full_name;
                if ($stock_issue->customer_id) $issue_to = @$stock_issue->customer->company ?: @$stock_issue->customer->name;
                if ($stock_issue->project_id) $issue_to = $stock_issue->project->name;
                return $issue_to;
            })
            ->addColumn('quote', function ($stock_issue) {

                $quote = $stock_issue->quote;

                if (!empty($quote)) {
                    return   '<a href="' . route('biller.stock_issues.show', $stock_issue->id) . '">' . '<i>' . gen4tid('QT-', $quote->tid) . '</i> ' . '</a> <b> | </b>' . $quote->notes;
                } else return '<b> QUOTE NOT FOUND </b>';
            })
            ->editColumn('total', function ($stock_issue) {
                return numberFormat($stock_issue->total);
            })
            ->addColumn('actions', function ($stock_issue) {
                return $stock_issue->action_buttons;
            })
            ->make(true);
    }
}
