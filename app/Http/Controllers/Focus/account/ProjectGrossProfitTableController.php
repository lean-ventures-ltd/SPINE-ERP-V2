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

namespace App\Http\Controllers\Focus\account;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\account\AccountRepository;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ProjectGrossProfitTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    // income, expense, profit
    protected $income = 0;
    protected $expense = 0;
    protected $profit = 0;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->repository->getForProjectGrossProfit();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function($project) {
                $customer = '';
                if ($project->customer_project) {
                    $customer = $project->customer_project->company;
                    if ($project->branch) $customer .= " - {$project->branch->name}";
                }
                return $customer;
            })
            ->addColumn('tid', function($project) {
                return '<a href="'. route('biller.projects.show', $project) .'">'. gen4tid('Prj-', $project->tid) .'</a>';
            })
            ->addColumn('status', function($project) {
                return 'Active';
            })
            ->addColumn('quote_amount', function($project) {
                $quotes = '';
                foreach ($project->quotes as $quote) {
                    $tid = gen4tid($quote->bank_id? 'PI-': 'QT-', $quote->tid);
                    $quotes .= '<a href="'. route('biller.quotes.show', $quote->id) .'">'. $tid .'</a>' . ' : ' . numberFormat($quote->subtotal) . '<br>';
                }
                return $quotes;
            })
            ->addColumn('verify_date', function($project) {
                $verification_dates = '';
                foreach ($project->quotes as $quote) {
                    if ($quote->verified_amount > 0) {
                        $verification_dates .= dateFormat($quote->verification_date) . '<br>';
                    }
                }
                return $verification_dates;
            })
            ->addColumn('income', function($project) {
                $income = 0;
                foreach ($project->quotes as $quote) {
                    $inv_product = $quote->invoice_product;
                    if ($inv_product) $income += $quote->subtotal;                        
                }
                $this->income = $income;
                return numberFormat($income);
            })
            ->addColumn('expense', function($project) {
                $total_estimate = 0;
                foreach ($project->quotes as $quote) {
                    $dir_purchase_amount = $project->purchase_items->sum('amount') / $project->quotes->count();
                    $proj_grn_amount = $project->grn_items()->sum(DB::raw('round(rate*qty)')) / $project->quotes->count();
                    $labour_amount = $project->labour_allocations()->sum(DB::raw('hrs * 500')) / $project->quotes->count();
                    $expense_amount = $dir_purchase_amount + $proj_grn_amount + $labour_amount;
                    if ($quote->projectstock) $expense_amount += $quote->projectstock->sum('total');
                    $total_estimate += $expense_amount;
                }
                $this->expense = $total_estimate;
                return numberFormat($total_estimate);
            })
            ->addColumn('gross_profit', function($project) {
                $profit = 0;
                if ($this->income > 0) {
                    $profit = $this->income  - $this->expense;
                }
                $this->profit = $profit;
                return numberFormat($profit);
            })
            ->addColumn('percent_profit', function($project) {                
                return round(div_num($this->profit, $this->income) * 100);
            })
            ->make(true);
    }
}