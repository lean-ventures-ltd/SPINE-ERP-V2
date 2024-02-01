<?php

namespace App\Http\Controllers\Focus\general;

use App\Http\Controllers\Focus\labour_allocation\LabourAllocationController;
use App\Models\invoice\Invoice;
use App\Models\product\ProductVariation;
use App\Models\transaction\Transaction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Project;
use App\Models\purchase\Purchase;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\labour_allocation\LabourAllocationRepository;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;


class CoreDashboard extends Controller
{
    /**
     * Dashboard Index View
     *
     * @throws Exception
     */
    public function index()
    {
        if (!access()->allow('dashboard-owner')) return view('focus.dashboard.common');

        $today = date('Y-m-d');
        $start_date = date_for_database("{$today} - 1 days");

        // invoices
        $data['invoices'] = Invoice::where('invoicedate', $start_date)->with('customer')->latest()->get();
        $data['monthly_invoices'] = Invoice::whereMonth('invoicedate', date('m'))->whereYear('invoicedate', date('Y'))->get();

        // customers
        $data['customers'] = Customer::whereIn('id', $data['invoices']->pluck('customer_id')->toArray())->get();

        // stock alerts
//        $data['stock_alert'] = ProductVariation::whereRaw('qty <= alert')
//            ->whereHas('product', fn($q) => $q->where('stock_type', 'general'))
//            ->get();

        // projects
        $projects = Project::whereHas('misc', fn($q) => $q->where('name', '!=', 'Complete'))->latest()->limit(12)->get();

        // purchases
        $data['purchases'] = Purchase::where('date', $start_date)->latest()->get();
        $data['monthly_purchases'] = Purchase::whereMonth('date', date('m'))->whereYear('date', date('Y'))->get();
        $data['purchase_orders'] = UtilityBill::whereHas('grn')->whereBetween('date', [$start_date, $today])->latest()->get();
        $data['monthly_purchase_orders'] = UtilityBill::whereHas('grn')->whereMonth('date', date('m'))->whereYear('date', date('Y'))->get();


        $keyMetrics = $this->keyDashboardMetrics();
        $dailySalesExpensesData = $this->dailySalesExpensesData();
        $labourAllocationData = (new LabourAllocationController(new LabourAllocationRepository()))->getLabourAllocationData();
        $dailyLabourData = (new LabourAllocationController(new LabourAllocationRepository()))->getDailyLabourHours();
        $sevenDayLabourHours = (new LabourAllocationController(new LabourAllocationRepository()))->get7DaysLabourMetrics();
        $sevenDaySalesExpenses = $this->get7DaysSalesExpensesMetrics();

        return view('focus.dashboard.index', compact('data', 'projects', 'keyMetrics', 'dailySalesExpensesData', 'labourAllocationData', 'sevenDayLabourHours', 'sevenDaySalesExpenses'));
    }

    /**
     * @throws Exception
     */
    public function dailySalesExpensesData(string $date = 'now') {

        $refDate = new DateTime($date);

        $month =  $refDate->format('M');

        $week = $refDate->format('W');

        $daysOfTheWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $dailyTotals = array_fill(0, 7, 0);

        $weekSalesTotals = array_combine($daysOfTheWeek, $dailyTotals);
        $weekExpensesTotals = array_combine($daysOfTheWeek, $dailyTotals);

        //SALES TOTALS
        $userMonthInvoices = Invoice::whereMonth('invoicedate', $refDate->format('m'))
            ->whereYear('invoicedate', $refDate->format('Y'))->get();

        foreach ($userMonthInvoices as $invoice){

            $invoiceWeek = (new DateTime($invoice['invoicedate']))->format('W');

            if ($invoiceWeek === $week){

                $invoiceDay = (new DateTime($invoice['invoicedate']))->format('D');

                $weekSalesTotals[$invoiceDay] += $invoice['total'];

            }

        }

        //EXPENSES TOTALS
        $userMonthPurchases = Purchase::whereMonth('date', $refDate->format('m'))
            ->whereYear('date', $refDate->format('Y'))->get();

        foreach ($userMonthPurchases as $purchase) {

            $purchaseWeek = (new DateTime($purchase['date']))->format('W');

            if ($purchaseWeek === $week){

                $purchaseDay = (new DateTime($purchase['date']))->format('D');
                $weekExpensesTotals[$purchaseDay] += $purchase['grandttl'];

            }


        }


            $chartTitle = "Daily Sales & Expenses for Week " . $week . " of " . $refDate->format('Y');


       return [
           'chartTitle' => $chartTitle,
           'weekSalesTotals' => $weekSalesTotals,
           'weekExpensesTotals' => $weekExpensesTotals,
           'daysOfTheWeek' => $daysOfTheWeek,
       ];
    }

    /**
     * @throws Exception
     */
    public function get7DaysSalesExpensesMetrics(){

        //SALES DATA
        $salesTotals = array_fill(0, 7, 0);
        $salesDates = array_fill(0, 7, 'N/A');

        for ($i = 1; $i <= 7; $i++){

            $date = (new DateTime('now'))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');

            $salesValues = Invoice::where('invoicedate', $date)->pluck('total');
            foreach ($salesValues as $sale){
                $salesTotals[$i-1] += $sale;
            }

            $salesDates[$i-1] = (new DateTime('now'))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $salesDates = array_reverse($salesDates);
        $salesTotals = array_reverse($salesTotals);


        //EXPENSES DATA
        $expensesTotals = array_fill(0, 7, 0);
        $expensesDates = array_fill(0, 7, 'N/A');

        for ($i = 1; $i <= 7; $i++){

            $date = (new DateTime('now'))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');

            $expensesValues = Purchase::where('date', $date)->pluck('grandttl');
            foreach ($expensesValues as $expense){
                $expensesTotals[$i-1] += $expense;
            }

            $expensesDates[$i-1] = (new DateTime('now'))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $expensesDates = array_reverse($expensesDates);
        $expensesTotals = array_reverse($expensesTotals);


        $startDate = (new DateTime('now'))->sub(new DateInterval('P7D'))->format('jS F');
        $endDate = (new DateTime('now'))->sub(new DateInterval('P1D'))->format('jS F');

        $chartTitle = 'Daily Sales and Expenses from ' . $startDate . ' to ' . $endDate . ', ' . (new DateTime('now'))->format('Y');

        return compact('salesTotals', 'salesDates', 'expensesTotals', 'expensesDates', 'chartTitle');
    }

    public function keyDashboardMetrics(){

        $today = date('Y-m-d');
        $start_date = date_for_database("{$today} - 1 days");

        // invoices
        $data['invoices'] = Invoice::where('invoicedate', $start_date)->with('customer')->latest()->get();
        $data['monthly_invoices'] = Invoice::whereMonth('invoicedate', date('m'))->whereYear('invoicedate', date('Y'))->get();

        // customers
        $data['customers'] = Customer::whereIn('id', $data['invoices']->pluck('customer_id')->toArray())->get();

        // stock alerts
//        $data['stock_alert'] = ProductVariation::whereRaw('qty <= alert')
//            ->whereHas('product', fn($q) => $q->where('stock_type', 'general'))
//            ->get();

        // projects
        $projects = Project::whereHas('misc', fn($q) => $q->where('name', '!=', 'Complete'))->latest()->limit(25)->get();

        // purchases
        $data['purchases'] = Purchase::where('date', $start_date)->latest()->get();
        $data['monthly_purchases'] = Purchase::whereMonth('date', date('m'))->whereYear('date', date('Y'))->get();
        $data['purchase_orders'] = UtilityBill::whereHas('grn')->whereBetween('date', [$start_date, $today])->latest()->get();
        $data['monthly_purchase_orders'] = UtilityBill::whereHas('grn')->whereMonth('date', date('m'))->whereYear('date', date('Y'))->get();

        $total = 0;
        foreach ($data['monthly_invoices'] as $invoice) {
            if ($invoice->currency) {
                if ($invoice->currency->rate != 1)
                    $total += $invoice->total * $invoice->currency->rate;
                else $total += $invoice->total;
            } else $total += $invoice->total;
        }

        $monthSales = round($total, 4);

        $labourAllocationData = (new LabourAllocationController(new LabourAllocationRepository()))->getLabourAllocationData();


        return [

            'yesterday' => [
                'quantities' => [
                    'invoices' => $data['invoices']->count(),
                    'purchases' => $data['purchases']->count() + $data['purchase_orders']->count(),
                    'labourEntries' => $labourAllocationData['yesterday']['ylaCount'],
                ],
                'totals' => [
                    'sales' => floatval($data['invoices']->sum('total')),
                    'expenses' => floatval($data['purchases']->sum('grandttl') + $data['purchase_orders']->sum('total')),
                    'manHours' => $labourAllocationData['yesterday']['ylaTotalManHours'],
                ]
            ],

            'thisMonth' => [
                'quantities' => [
                    'invoices' => $data['monthly_invoices']->count(),
                    'purchases' => $data['monthly_purchases']->count() + $data['monthly_purchase_orders']->count(),
                    'labourEntries' => $labourAllocationData['thisMonth']['tmlaCount'],
                ],
                'totals' => [
                    'sales' => floatval($monthSales),
                    'expenses' => floatval($data['monthly_purchases']->sum('grandttl') + $data['monthly_purchase_orders']->sum('total')),
                    'manHours' => $labourAllocationData['thisMonth']['tmlaTotalManHours'],
                ]
            ],

        ];

    }

    /** 
     * Dashboard Index Data
     * */
    public function mini_dash()
    {
        $start_date = date('Y-m') . '-01';
        $today = date('Y-m-d');
        // yesterday
        $today = date_for_database($today . ' -1 days');
        
        // invoices
        $today_invoices = Invoice::select(DB::raw('invoicedate, COUNT(*) as items, SUM(total) as total'))
        ->where('invoicedate', $today)
        ->groupBy('invoicedate')->first();
        if (!$today_invoices) $today_invoices = new Invoice;

        $this_month_invoices = Invoice::select(DB::raw('invoicedate, COUNT(*) as items, SUM(total) as total'))
        ->whereMonth('invoicedate', date('m'))->whereYear('invoicedate', date('Y'))
        ->groupBy('invoicedate')->first();
        if (!$this_month_invoices) $this_month_invoices = new Invoice;

        // transaction
        $transactions_today = Transaction::select(DB::raw('SUM(credit) as credit, SUM(debit) as debit'))
        ->whereHas('category', function ($q) {
            $q->whereIn('code', ['inv', 'bill']);
        })
        ->where('tr_date', $today)
        ->groupBy('tr_date')->first();
        if (!$transactions_today) $transactions_today = new Transaction;

        $income_transactions = Transaction::whereBetween('tr_date', ['2023-01-01', $today])
        ->whereHas('category', function ($q) {
            $q->whereIn('code', ['inv']);
        })->get();
        $expense_transactions = Transaction::whereBetween('tr_date', [$start_date, $today])
        ->whereHas('category', function ($q) {
            $q->whereIn('code', ['bill']);
        })->get();
        

        $income_chart = array_map(function ($v) {
            return array('x' => $v['tr_date'], 'y' => (int) $v['credit']);
        }, $income_transactions->toArray());

        $expense_chart = array_map(function ($v) {
            return array('x' => $v['tr_date'], 'y' => (int) $v['debit']);
        }, $expense_transactions->toArray());

        $sales_chart = [];
        $this_month_invoices_array = $this_month_invoices->toArray();
        if ($this_month_invoices_array) {
            $sales_chart = array_map(function ($v) {
                return array('y' => $v['invoicedate'], 'sales' => (int) $v['total'], 'invoices' => (int) $v['items']);
            }, [$this_month_invoices_array]);    
        }

        return response()->json([
            'dash' => array(
                number_format($today_invoices->items, 1),
                amountFormat($today_invoices->total, 0, 1),
                number_format($this_month_invoices->items, 1),
                amountFormat($this_month_invoices->total, 0, 1),
                amountFormat($today_invoices->total, 0, 1),
                amountFormat($transactions_today->credit),
                amountFormat($transactions_today->debit),
                amountFormat($transactions_today->credit - $transactions_today->debit)
            ),
            'income_chart' => $income_chart,
            'expense_chart' => $expense_chart,
            'inv_exp' => ['income' => (int) $income_transactions->sum('credit'), 'expense' => (int) $expense_transactions->sum('debit')],
            'sales' => $sales_chart,
        ]);
    }
    
    /** 
     * Dashboard Tasks 
     * */
    public function todo()
    {
        $mics = Misc::all();
        $employees = Hrm::all();
        $user = auth()->user()->id;
        $project_select = Project::whereHas('users', function ($q) use ($user) {
            return $q->where('rid', $user);
        })->get();

        return new ViewResponse('focus.projects.tasks.index', compact('mics', 'employees', 'project_select'));
    }
}
