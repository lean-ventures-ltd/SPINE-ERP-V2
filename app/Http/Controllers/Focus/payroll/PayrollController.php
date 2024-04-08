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
namespace App\Http\Controllers\Focus\payroll;

use App\Exceptions\GeneralException;
use App\Models\Access\Permission\PermissionRole;
use App\Models\Access\Permission\PermissionUser;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\payroll\Payroll;
use App\Models\payroll\PayrollItemV2;
use App\Models\product\Product;
use App\Models\product\ProductVariation;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\payroll\CreateResponse;
use App\Http\Responses\Focus\payroll\EditResponse;
use App\Repositories\Focus\payroll\PayrollRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Models\hrm\Hrm;
use App\Models\deduction\Deduction;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendPayslipEmail;
use Illuminate\Support\Facades\View;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Jobs\SendEmailJob;
use App\Models\salary\Salary;
use App\Models\account\Account;
use App\Models\payroll\PayrollItem;
use Carbon\Carbon;


use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

/**
 * payrollsController
 */
class PayrollController extends Controller
{
    /**
     * variable to store the repository object
     * @var payrollRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param payrollRepository $repository ;
     */
    public function __construct(PayrollRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\payroll\ManagepayrollRequest $request
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.payroll.index');
    }

    private function revokePermissions(){

        $permissionNames = [
            'manage-pricelist',
            'create-pricelist',
            'edit-pricelist',
            'delete-pricelist',
        ];

        try {
            DB::beginTransaction();


            foreach ($permissionNames as $pName){

                $pId = \App\Models\Access\Permission\Permission::where('name', $pName)->first()->id;

                $permissionRoles = PermissionRole::where('permission_id', $pId)->get();
                foreach ($permissionRoles as $pR){
                    $pR->delete();
                }

                $permissionUsers = PermissionUser::where('permission_id', $pId)->get();
                foreach ($permissionUsers as $pU){
                    $pU->delete();
                }

            }


            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage() . " On File: " .  $e->getFile() . " On Line: " . $e->getLine());
        }


        return "HOORAH!!!";
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatepayrollRequestNamespace $request
     * @return \App\Http\Responses\Focus\payroll\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.payroll.create');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param StorepayrollRequestNamespace $request
     * @throws GeneralException
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $result = $this->repository->create($input);
        //dd($result);
        //return with successfull message
        return new RedirectResponse(route('biller.payroll.page', $result), ['flash_success' => 'Payroll Processed Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\payroll\payroll $payroll
     * @param EditpayrollRequestNamespace $request
     * @return \App\Http\Responses\Focus\payroll\EditResponse
     */
    public function edit(Payroll $payroll, Request $request)
    {
        $payroll->processing_month = Carbon::parse($payroll->processing_month)->format('Y-m');
        return new EditResponse($payroll);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatepayrollRequestNamespace $request
     * @param App\Models\payroll\payroll $payroll
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Payroll $payroll)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($payroll, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.payroll.page', $payroll->id), ['flash_success' => 'Payroll Processing Updating Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletepayrollRequestNamespace $request
     * @param App\Models\payroll\payroll $payroll
     * @return \App\Http\Responses\RedirectResponse
     */
    public function deletePayroll($payrollId, Request $request)
    {
        //Calling the delete method on repository
//        $this->repository->delete($payroll);
//        return $payrollId;
        $payroll = Payroll::find($payrollId);
        $payrollItems = PayrollItemV2::where('payroll_id', $payroll->id)->get();

        foreach ($payrollItems as $pI){
            $pI->delete();
        }

        $payroll->delete();



        //returning with successfull message
        return new RedirectResponse(route('biller.payroll.index'), ['flash_success' => 'Payroll Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletepayrollRequestNamespace $request
     * @param App\Models\payroll\payroll $payroll
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show($payrollId, Request $request)
    {
        $accounts = Account::whereNull('system')
            ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
            ->get(['id', 'holder']);

        $expired_contracts = Salary::where('status', 'expired')->count();
        $payroll = Payroll::find($payrollId);
        $payroll->reference = gen4tid('PYRL-',$payroll->tid);

//        return
        $payrollItems = Payroll::where('payroll_id', $payrollId)
            ->join('payroll_items', 'payroll.id', 'payroll_items.payroll_id')
            ->join('users', 'payroll_items.employee_id', 'users.id')
            ->select(
                'payroll_items.*',
//                'payroll_items.basic_salary as employee_basic_salary'.
                'payroll.*',
                DB::raw('CONCAT(first_name, " ", last_name) as name'),
                DB::raw('SUM(basic_hourly_salary) as hourly_pay_tally'),
                DB::raw('SUM(absent_days) as absent_days_tally'),
//                DB::raw('SUM(employee_basic_salary) as basic_salary_tally'),
                DB::raw('SUM(total_allowance + other_allowances) as allowances_tally'),
                DB::raw('SUM(taxable_gross) as taxable_gross_tally'),
                DB::raw('SUM(nssf) as nssf_tally'),
                DB::raw('SUM(taxable_deductions) as taxable_deductions_tally'),
                DB::raw('SUM(housing_levy) as housing_levy_tally'),
                DB::raw('SUM(nhif) as nhif_tally'),
                DB::raw('SUM(netpay) as netpay_tally'),
                DB::raw('SUM(absent_total_deduction + total_nhif + total_nssf + housing_levy + loan + other_deductions) as deductions_tally'),
            )
            ->get();


        //returning with successfull message
        return new ViewResponse('focus.payroll.view', compact('payrollItems', 'payroll','accounts'));
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function get_employee(Request $request)
    {
        
        $payroll = Payroll::find($request->payroll_id);
        $payroll_items = $payroll->payroll_items;
        return Datatables::of($payroll_items)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_id', function ($payroll_items) {
                $employee_id = gen4tid('EMP-', $payroll_items->employee_id);
                return $employee_id;
             })
            ->addColumn('employee_name', function ($payroll_items) {
                $employee_name = $payroll_items->employee ? $payroll_items->employee->first_name : '';
               return $employee_name;
            })
            ->addColumn('basic_pay', function ($payroll_items) {
                return amountFormat($payroll_items->basic_pay);
            })
            ->addColumn('absent_days', function ($payroll_items) {
                return $payroll_items->absent_days;
            })
            ->addColumn('house_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->house_allowance);
            })
            ->addColumn('transport_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->transport_allowance);
            })
            ->addColumn('other_allowance', function ($payroll_items) {
                return amountFormat($payroll_items->other_allowance);
            })
            ->addColumn('gross_pay', function ($payroll_items) {
                return amountFormat($payroll_items->gross_pay -$payroll_items->tx_deductions);
            })
            ->addColumn('nssf', function ($payroll_items) {
                return amountFormat($payroll_items->nssf);
            })
            ->addColumn('tx_deductions', function ($payroll_items) {
                return amountFormat($payroll_items->tx_deductions);
            })
            ->addColumn('paye', function ($payroll_items) {
                return amountFormat($payroll_items->paye);
            })
            ->addColumn('taxable_gross', function ($payroll_items) {
                return amountFormat($payroll_items->taxable_gross);
            })
            ->addColumn('total_other_allowances', function ($payroll_items) {
                return amountFormat($payroll_items->total_other_allowances);
            })
            ->addColumn('total_benefits', function ($payroll_items) {
                return amountFormat($payroll_items->total_benefits);
            })
            ->addColumn('loan', function ($payroll_items) {
                return amountFormat($payroll_items->loan);
            })
            ->addColumn('advance', function ($payroll_items) {
                return amountFormat($payroll_items->advance);
            })
            ->addColumn('total_other_deductions', function ($payroll_items) {
                return amountFormat($payroll_items->total_other_deductions);
            })
            ->addColumn('netpay', function ($payroll_items) {
                return amountFormat($payroll_items->netpay);
            })
            ->make(true);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function get_deductions()
    {
        return Datatables::of($employees)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_name', function ($payroll) {
               return $payroll->employees_salary ? $payroll->employees_salary->employee_name : '';
            })
            ->addColumn('basic_pay', function ($payroll) {
                return $payroll->employees_salary ? amountFormat($payroll->employees_salary->basic_pay) : '';
            })
            ->addColumn('total_allowances', function ($payroll) {
                return $payroll->employees_salary ? amountFormat($payroll->employees_salary->house_allowance + $payroll->employees_salary->transport_allowance) : '';
            })
            ->addColumn('gross_pay', function ($payroll) {
                return $payroll->employees_salary ? amountFormat($payroll->employees_salary->basic_pay + $payroll->employees_salary->house_allowance + $payroll->employees_salary->transport_allowance) : '';
            })
            ->addColumn('actions', function ($payroll) {
                return $payroll->actions_buttons;
            })
            ->make(true);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws Exception
     */
    public function processPayroll($id)
    {
//        return  $this->calculatePAYE(98920.00, true, 1700);
//        return $allColumns = Schema::getColumnListing('payroll_items');

        $expired_contracts = Salary::where('status', 'expired')->count();
        $payroll = Payroll::find($id);
        $payroll->reference = gen4tid('PYRL-',$payroll->tid);

//        return
        $payrollItems = PayrollItemV2::where('payroll_id', $id)
            ->join('users', 'payroll_items.employee_id', 'users.id')
            ->join('salary', 'payroll_items.employee_id', 'salary.employee_id')
            ->select(
                'payroll_items.*',
                'salary.nhif as nhif_status',
                'deduction_exempt',
                DB::raw('CONCAT(first_name, " ", last_name) as name') ,
            )
            ->get();

        $payDetails = Hrm::join('salary', 'users.id', 'salary.employee_id')
//            ->join('labour_allocation_items', 'users.id', 'labour_allocation_items.employee_id')
            ->select(
                'users.id as employee_id',
                DB::raw('CONCAT(first_name, " ", last_name) as name') ,
                'basic_salary',
                'hourly_salary',
                'pay_per_hr',
                'house_allowance as man_hours',
            )
            ->get();

        foreach ($payDetails as $sd){

            $firstDayOfMonth = (new DateTime($payroll->payroll_month))->format('Y-m-01');
            $lastDayOfMonth = (new DateTime($payroll->payroll_month))->format('Y-m-t');

            $laItems = LabourAllocationItem::where('employee_id', $sd['employee_id'])
                ->whereBetween('date', [$firstDayOfMonth, $lastDayOfMonth])->get();

            $totalManHours = 0;
            foreach ($laItems as $item){

                $totalManHours += $item->hrs;
            }

            $sd['man_hours'] = $totalManHours;
        }


        $employees = Hrm::with(['employees_salary' => function ($q){
            $q->where('contract_type', 'permanent')->where('status', 'ongoing');
        }])->get();
        $total_gross = 0;
        $total_paye = 0;
        $total_nhif = 0;
        $total_housing_levy = 0;
        $total_nssf = 0;
        $total_tx_deduction = 0;

        foreach ($payrollItems as $item) {
//            $item->employee_name = $item->employee ? $item->employee->first_name : '';
            if($item->basic_plus_allowance){

                $deductionExemptStatus = boolval($item->deduction_exempt);

                $item->nssf = $deductionExemptStatus ? 0.00 : $this->calculate_nssf($item->basic_plus_allowance);
                $item->nhif = boolval($item->nhif_status) ? $this->calculate_nhif($item->basic_plus_allowance) : 0.00;
                $item->housing_levy = $deductionExemptStatus ? 0.00 : $this->calculateHousingLevy($item->basic_plus_allowance);

                $total_gross += $item->gross_pay;
                $nhif_relief = 15/100 * $item->nhif;

                $total_nhif += $item->nhif;
                $total_nssf += $item->nssf;
                $total_housing_levy += $item->housing_levy;
                $total_tx_deduction += $item->taxable_deductions;

            }
        }


        return view('focus.payroll.pages.create', compact('payroll', 'payrollItems', 'payDetails', 'employees','total_gross','total_paye','total_nhif','total_nssf', 'total_housing_levy','total_tx_deduction','expired_contracts'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \App\Exceptions\GeneralException
     */
    public function approve_payroll(Request $request)
    {
//        return $request;
        //dd($request->all());
        $payroll = Payroll::find($request->id);
//        $payroll->approval_note = $request->approval_note;
//        $payroll->approval_date = date_for_database($request->approval_date);
//        $payroll->status = $request->status;
       // $payroll['account'] = $request->account_id;
       // $payroll->update();
        return $this->repository->approve_payroll(compact('payroll'));
//        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send_mail(Request $request)
    {
        //dd($request->all());
        $payroll = Payroll::find($request->id);
        $users = $payroll->payroll_items()->get();
        $input=array();
        $input['text']='Payslip Received';
        $input['subject']='Payslip';
        $input['customer_name']='Your name';
        SendEmailJob::dispatch($users, $input);
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_basic(Request $request)
    {
        //dd($request->all());


        $data = $request->only([
            'payroll_id','salary_total','processing_date'
        ]);

        try {
            DB::beginTransaction();

            $payroll = Payroll::find($data['payroll_id']);
            $payroll->salary_total = $data['salary_total'];
            $payroll->processing_date = date_for_database($data['processing_date']);
            $payroll->ins = auth()->user()->ins;
            $payroll->user_id = auth()->user()->id;

            $payroll->update();

            $payrollData = $request->except(['_token', 'payroll_id', 'processing_date', 'salary_total']);

            $individualPayrolls = $this->splitPayrollRequest($payrollData, 'employee_id');;
            foreach ($individualPayrolls as $ip){

                $payrollItem = new PayrollItemV2();
                $payrollItem->payroll_id = $request['payroll_id'];
                $payrollItem->fill($ip);
                $payrollItem->save();

            }


            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage() . " On File: " .  $e->getFile() . " On Line: " . $e->getLine());
        }


//        $data_items = $request->only([
//            'absent_rate', 'absent_days','rate_per_day','rate_per_month','basic_pay', 'employee_id','basic_salary'
//        ]);
//
//        $data['ins'] = auth()->user()->ins;
//        $data['user_id'] = auth()->user()->id;
//        // modify and filter items without item_id
//        return $data_items = modify_array($data_items);
//        $data_items = array_filter($data_items, function ($v) { return $v['employee_id']; });
//
//
//        try {
//            $result = $this->repository->create_basic(compact('data', 'data_items'));
//        } catch (\Throwable $th) {
//            return errorHandler('Error creating Basic Salary', $th);
//        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_basic(Request $request)
    {
        //dd($request->all());
        $payroll_items = PayrollItemV2::find($request->id);
        $new_house_allowance = 0;
        $new_transport_allowance = 0;
        $new_other_allowance = 0;
        if ($request->absent_days > 0 && $payroll_items->absent_days > 0) {
            //New House allowance
            $house_allowance = ($request->month * $payroll_items->house_allowance) / $payroll_items->absent_days;
            $new_house_allowance = $house_allowance - ($request->absent_days / $request->month);
            //New Transport allowance
            $transport_allowance = ($request->month * $payroll_items->transport_allowance) / $payroll_items->absent_days;
            $new_transport_allowance = $transport_allowance - ($request->absent_days / $request->month);
            //New other allowance
            $other_allowance = ($request->month * $payroll_items->other_allowance) / $payroll_items->absent_days;
            $new_other_allowance = $other_allowance - ($request->absent_days / $request->month);
           // dd($new_house_allowance, 1);
        }
        else if ($request->absent_days > 0 && $payroll_items->absent_days == 0) {
            //New House allowance
            $house_allowance = ($payroll_items->house_allowance * $request->absent_days) / $request->month;
            $new_house_allowance = $payroll_items->house_allowance - $house_allowance;
            //New Transport allowance
            $transport_allowance = ($payroll_items->transport_allowance * $request->absent_days) / $request->month;
            $new_transport_allowance = $payroll_items->transport_allowance - $transport_allowance;
            //New other allowance
            $other_allowance = ($payroll_items->other_allowance * $request->absent_days) / $request->month;
            $new_other_allowance = $payroll_items->other_allowance - $other_allowance;
            //dd($new_house_allowance, 2);
        }
        else if ($request->absent_days == 0 && $payroll_items->absent_days > 0) {
            //New House allowance
            $house_allowance = ($payroll_items->house_allowance * $request->month) / ($request->month - $payroll_items->absent_days);
            $new_house_allowance = $house_allowance;
            //New Transport allowance
            $transport_allowance = ($payroll_items->transport_allowance * $request->month) / ($request->month - $payroll_items->absent_days);
            $new_transport_allowance = $transport_allowance;
            //New other allowance
            $other_allowance = ($payroll_items->other_allowance * $request->month) / ($request->month - $payroll_items->absent_days);
            $new_other_allowance = $other_allowance;
            //dd($other_allowance);
           // dd($new_house_allowance, 3);
        }
        else if ($request->absent_days == 0 && $payroll_items->absent_days == 0) {
            //New House allowance
            $new_house_allowance = $payroll_items->house_allowance;
            //New Transport allowance
            $new_transport_allowance = $payroll_items->transport_allowance;
            //New other allowance
            $new_other_allowance = $payroll_items->other_allowance;
            //dd($other_allowance);
           // dd($new_house_allowance, 4);
        }
        //dd($new_house_allowance);
        //Updating Payroll Items
        $payroll_items->absent_days = $request->absent_days;
        $payroll_items->absent_rate = $request->absent_rate;
        $payroll_items->basic_pay = $request->basic_pay;
        $payroll_items->house_allowance = $new_house_allowance;
        $payroll_items->transport_allowance = $new_transport_allowance;
        $payroll_items->other_allowance = $new_other_allowance;
        $allowance = $new_other_allowance + $new_transport_allowance +$new_house_allowance;
        $total_basic_allowance = $request->basic_pay + $allowance;
        $payroll_items->total_allowance = $allowance;
        $payroll_items->total_basic_allowance = $total_basic_allowance;
        if($payroll_items->total_basic_allowance){
            $payroll_items->nssf = $this->calculate_nssf($payroll_items->total_basic_allowance);
            $payroll_items->gross_pay = $payroll_items->total_basic_allowance - ($payroll_items->nssf + $payroll_items->tx_deductions);
            // $total_gross += $payroll_items->gross_pay;
            $payroll_items->nhif = $this->calculate_nhif($payroll_items->gross_pay);
            $nhif_relief = 15/100 * $payroll_items->nhif;
            $payroll_items->paye = $this->calculate_paye($payroll_items->gross_pay) - $nhif_relief;

            if($payroll_items->paye < 0){
                $payroll_items->paye = 0;
            }
            $advance_loan = $payroll_items->advance + $payroll_items->loan;
            $payroll_items->netpay = $payroll_items->gross_pay -($payroll_items->paye + $payroll_items->nhif) + $payroll_items->total_other_allowances + $payroll_items->total_benefits 
            - ($advance_loan + $payroll_items->total_other_deduction);
           
        }
        $payroll_items->update();
        //Get payroll and update 
        $payroll = $payroll_items->payroll->first();
        $total_allowance = $payroll->payroll_items()->sum('total_allowance');
        $basic_pay = $payroll->payroll_items()->sum('basic_pay');
        $nhif = $payroll->payroll_items()->sum('nhif');
        $nssf = $payroll->payroll_items()->sum('nssf');
        $paye = $payroll->payroll_items()->sum('paye');
        $netpay = $payroll->payroll_items()->sum('netpay');
        $payroll->allowance_total = $total_allowance;
        $payroll->salary_total = $basic_pay;
        $payroll->paye_total = $paye;
        $payroll->total_nhif = $nhif;
        $payroll->total_nssf = $nssf;
        $payroll->total_netpay = $netpay;
        $payroll->update();
        // foreach ($payroll->payroll_items as $item) {
        //     $total_allowance += $item->total_allowance;
        // }
        //dd($payroll->payroll_items()->sum('total_allowance'));
        return redirect()->back()->with('flash_success', 'Basic Pay Updated Successfully');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_allowance(Request $request)
    {
        //dd($request->all());
        $payroll_items = PayrollItemV2::find($request->id);
        //Total tx allowance

        $total_allowance = $request->house_allowance + $request->transport_allowance + $request->other_allowance;
        $payroll_items->house_allowance = $request->house_allowance;
        $payroll_items->transport_allowance = $request->transport_allowance;
        $payroll_items->other_allowance = $request->other_allowance;
        $payroll_items->total_allowance = $total_allowance;
        $total_basic_allowance = $payroll_items->basic_pay + $total_allowance;
        $payroll_items->total_basic_allowance = $total_basic_allowance;
        if($payroll_items->total_basic_allowance){
            $payroll_items->nssf = $this->calculate_nssf($total_basic_allowance);
            $payroll_items->gross_pay = $total_basic_allowance - ($payroll_items->nssf + $payroll_items->tx_deductions);
            // $total_gross += $payroll_items->gross_pay;
            $payroll_items->nhif = $this->calculate_nhif($payroll_items->gross_pay);
            $nhif_relief = 15/100 * $payroll_items->nhif;
            $payroll_items->paye = $this->calculate_paye($payroll_items->gross_pay) - $nhif_relief;

            if($payroll_items->paye < 0){
                $payroll_items->paye = 0;
            }
            $advance_loan = $payroll_items->advance + $payroll_items->loan;
            $payroll_items->netpay = $payroll_items->gross_pay -($payroll_items->paye + $payroll_items->nhif) + $payroll_items->total_other_allowances + $payroll_items->total_benefits 
            - ($advance_loan + $payroll_items->total_other_deduction);
           
        }
        $payroll_items->update();
        //Get payroll and update 
        $payroll = $payroll_items->payroll->first();
        $total_allowances = $payroll->payroll_items()->sum('total_allowance');
        $nhif = $payroll->payroll_items()->sum('nhif');
        $nssf = $payroll->payroll_items()->sum('nssf');
        $paye = $payroll->payroll_items()->sum('paye');
        $netpay = $payroll->payroll_items()->sum('netpay');
        $payroll->allowance_total = $total_allowances;
        $payroll->paye_total = $paye;
        $payroll->total_nhif = $nhif;
        $payroll->total_nssf = $nssf;
        $payroll->total_netpay = $netpay;
        $payroll->update();
        return redirect()->back()->with('flash_success', 'Taxable Allowances Updated Successfully');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_deduction(Request $request)
    {
        //dd($request->all());
        $payroll_items = PayrollItemV2::find($request->id);
        $payroll_items->tx_deductions = $request->tx_deductions;
        if($payroll_items->total_basic_allowance){
            $payroll_items->nssf = $this->calculate_nssf($payroll_items->total_basic_allowance);
            $payroll_items->gross_pay = $payroll_items->total_basic_allowance - ($payroll_items->nssf + $request->tx_deductions);
            // $total_gross += $payroll_items->gross_pay;
            $payroll_items->nhif = $this->calculate_nhif($payroll_items->gross_pay);
            $nhif_relief = 15/100 * $payroll_items->nhif;
            $payroll_items->paye = $this->calculate_paye($payroll_items->gross_pay) - $nhif_relief;

            if($payroll_items->paye < 0){
                $payroll_items->paye = 0;
            }
            $advance_loan = $payroll_items->advance + $payroll_items->loan;
            $payroll_items->netpay = $payroll_items->gross_pay -($payroll_items->paye + $payroll_items->nhif) + $payroll_items->total_other_allowances + $payroll_items->total_benefits 
            - ($advance_loan + $payroll_items->total_other_deduction);
           
        }
        $payroll_items->update();
        //Get payroll and update 
        $payroll = $payroll_items->payroll->first();
        $nhif = $payroll->payroll_items()->sum('nhif');
        $nssf = $payroll->payroll_items()->sum('nssf');
        $paye = $payroll->payroll_items()->sum('paye');
        $netpay = $payroll->payroll_items()->sum('netpay');
        $payroll->paye_total = $paye;
        $payroll->total_nhif = $nhif;
        $payroll->total_nssf = $nssf;
        $payroll->total_netpay = $netpay;
        $payroll->update();
        return redirect()->back()->with('flash_success', 'Taxable Deductions Updated Successfully');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_other(Request $request)
    {
        //dd($request->all());
        $payroll_items = PayrollItemV2::find($request->id);
        $payroll_items->total_other_allowances = $request->total_other_allowances;
        $payroll_items->total_benefits = $request->total_benefits;
        $payroll_items->loan = $request->loan;
        $payroll_items->advance = $request->advance;
        $payroll_items->total_other_deduction = $request->total_other_deduction;
        $advance_loan = $request->advance + $request->loan;
        $payroll_items->netpay = $payroll_items->gross_pay -($payroll_items->paye + $payroll_items->nhif) + $request->total_other_allowances + $request->total_benefits 
            - ($advance_loan + $request->total_other_deduction);
        $payroll_items->update();
        //Get payroll and update 
        $payroll = $payroll_items->payroll->first();
        $total_other_allowances = $payroll->payroll_items()->sum('total_other_allowances');
        $total_benefits = $payroll->payroll_items()->sum('total_benefits');
        $total_other_deduction = $payroll->payroll_items()->sum('total_other_deduction');
        $netpay = $payroll->payroll_items()->sum('netpay');
        $payroll->other_allowances_total = $total_other_allowances;
        $payroll->other_deductions_total = $total_other_deduction;
        $payroll->other_benefits_total = $total_benefits;
        $payroll->total_netpay = $netpay;
        $payroll->update();
        return redirect()->back()->with('flash_success', 'Other Benefits and Deductions Updated Successfully');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_nhif(Request $request)
    {
         $data = $request->only([
            'payroll_id','total_nhif'
        ]);
        

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        
        try {
            $result = $this->repository->create_nhif(compact('data'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Deductions', $th);
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_allowance(Request $request)
    {

        $payrollItemsData = $request->except(['_token', 'payroll_id', 'allowance_total']);

        $individualPayrolls = $this->splitPayrollRequest($payrollItemsData);

        try {
            DB::beginTransaction();


            foreach ($individualPayrolls as $ip){

            $payrollItemNew = PayrollItemV2::find($ip['id']);
            $payrollItemNew->fill($ip);
            $payrollItemNew->save();
            }

            $payroll = Payroll::find($request->payroll_id);
            $payroll->allowance_total = $request->allowance_total;

            $targetKey = 'other_allowance';
            $payroll->extra_allowances_total = collect($individualPayrolls)->sum(function ($item) use ($targetKey) {
                return $item[$targetKey];
            });

            $payroll->gross_post_allowances = $payroll->salary_total + $payroll->allowance_total;

            $payroll->save();

            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage() . " On File: " .  $e->getFile() . " On Line: " . $e->getLine());
        }


//        $data = $request->only([
//            'payroll_id','allowance_total'
//        ]);
//        $data_items = $request->only([
//            'id', 'house_allowance','transport_allowance','other_allowance','total_allowance','total_basic_allowance'
//        ]);
//
//        $data['ins'] = auth()->user()->ins;
//        $data['user_id'] = auth()->user()->id;
//        // modify and filter items without item_id
//        $data_items = modify_array($data_items);
//        $data_items = array_filter($data_items, function ($v) { return $v['id']; });
//
//
//        try {
//            $result = $this->repository->create_allowance(compact('data', 'data_items'));
//        } catch (\Throwable $th) {
//            return errorHandler('Error creating Taxable Allowances', $th);
//        }
        return redirect()->back();
    }

    /**
     * @param $payrollItemsData
     * @param string $key
     * @return array
     */
    private function splitPayrollRequest($payrollItemsData, string $key = 'id'){

        $numberOfEntries = count($payrollItemsData[$key]);
        $individualPayrolls = [];
        for ($i = 0; $i < $numberOfEntries; $i++) {
            foreach ($payrollItemsData as $key => $values) {
                $individualPayrolls[$i][$key] = $values[$i];
            }

            ksort($individualPayrolls[$i]);
        }

        return $individualPayrolls;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_deduction(Request $request)
    {

        $payrollItemsData = $request->except(['_token', 'payroll_id', 'deduction_total', 'total_nssf']);
        $individualPayrolls = $this->splitPayrollRequest($payrollItemsData);

        try {
            DB::beginTransaction();

            $payeTotal = 0.00;
            $housingLevyTotal = 0.00;
            foreach ($individualPayrolls as $ip){

                $payrollItemNew = PayrollItemV2::find($ip['id']);
                $payrollItemNew->fill($ip);

                $payrollItemNew->taxable_gross = $payrollItemNew->basic_plus_allowance - $ip['nssf'] - $ip['taxable_deductions'];

                $deductionExempt = boolval(Salary::where('employee_id', $payrollItemNew->employee_id)->first()->deduction_exempt);

                $taxArray = $this->calculatePAYE($payrollItemNew->taxable_gross, $deductionExempt, !empty($payrollItemNew->nhif), $payrollItemNew->nhif);
                $payrollItemNew->fill($taxArray);

                $payeTotal += $payrollItemNew->paye;
                $housingLevyTotal += $payrollItemNew->housing_levy;

                $payrollItemNew->netpay = $payrollItemNew->taxable_gross - $payrollItemNew->nhif - $payrollItemNew->housing_levy - $payrollItemNew->paye;

                $payrollItemNew->save();
            }

            $payroll = Payroll::find($request->payroll_id);
            $payroll->deduction_total = $request->deduction_total;
            $payroll->paye_total = $payeTotal;

            $targetKey = 'nhif';
            $payroll->total_nhif= collect($individualPayrolls)->sum(function ($item) use ($targetKey) {
                return $item[$targetKey];
            });
            $payroll->total_nssf = floatval($request->total_nssf);
            $payroll->total_housing_levy = bcmul(floatval($housingLevyTotal), 2, 2);

            $payroll->save();

            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage() . " On File: " .  $e->getFile() . " On Line: " . $e->getLine());
        }

        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_otherdeduction(Request $request)
    {

        $payrollItemsData = $request->except(['_token', 'payroll_id', 'other_benefits_total', 'other_deductions_total', 'other_allowances_total']);
        $individualPayrolls = $this->splitPayrollRequest($payrollItemsData);

        try {
            DB::beginTransaction();

            $totalAfterBnd = 0.00;

            foreach ($individualPayrolls as $ip){

                $payrollItemNew = PayrollItemV2::find($ip['id']);
                $payrollItemNew->fill($ip);

                $payrollItemNew->net_after_bnd = $payrollItemNew->netpay + floatval($ip['benefits']) + floatval($ip['other_allowances']) - floatval($ip['loan']) - floatval($ip['advance']) - floatval($ip['other_deductions']);

                $totalAfterBnd += $payrollItemNew->net_after_bnd;

                $payrollItemNew->save();
            }

            $payroll = Payroll::find($request->payroll_id);
            $payroll->fill($request->only(['other_benefits_total', 'other_deductions_total', 'other_allowances_total']));

            $payroll->total_salary_after_bnd = $totalAfterBnd;


            $payroll->save();

            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage() . " On File: " .  $e->getFile() . " On Line: " . $e->getLine());
        }


        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_summary(Request $request)
    {

        return $request;

        $data = $request->only([
            'payroll_id','total_netpay'
        ]);
        $data_items = $request->only([
            'id', 'netpay'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['id']; });
        
       
       
        try {
            $result = $this->repository->create_summary(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Deductions', $th);
        }
        return redirect()->back();
    }


    /**
     * Calculates PAYE
     * @param $income
     * @param bool $nhif
     * @param float $nhifContribution
     * @return float
     */
    public function calculatePAYE($income, bool $deductionExempt = false, bool $nhif = false, float $nhifContribution)
    {

        if($deductionExempt){

            $income_tax = 0.00;
            $nhif_relief = 0.00;
            $personal_relief = 0.00;
            $paye = 0.00;

            return compact('income_tax', 'nhif_relief', 'personal_relief', 'paye');
        }


        // Define tax brackets and rates
        $taxBandLimits = [24000, 32333, 500000, 800000];
        $rates = [0.10, 0.25, 0.30, 0.325];



        // Personal Relief and Minimum Taxable Income
        $personal_relief = 2400;
        $minTaxableIncome = 24001;

        //Calculate PAYE
        $nhif_relief = 0;
        if ($nhif) $nhif_relief = bcmul($nhifContribution, 0.15, 2);

        $totalRelief = round($nhif_relief + $personal_relief, 2);

        if($income < 24001){

            $income_tax = 0.00;
            $nhif_relief = 0.00;
            $personal_relief = 0.00;
            $paye = 0.00;

            return compact('income_tax', 'nhif_relief', 'personal_relief', 'paye');
        }


        $paye = 0;//bcsub(0, 255, 4);
        $income_tax = 0;
        $taxableBalance = $income;

        $baseTax = bcmul(24000, 0.1, 4);
        $income_tax = bcadd($income_tax, $baseTax, 4);

        $taxableBalance = bcsub($taxableBalance, 24000, 4);

        if($taxableBalance >= 8333){

            $tax = bcmul(8333, 0.25, 4);
            $income_tax = bcadd($income_tax, $tax, 4);
            $taxableBalance = bcsub($taxableBalance, 8333, 4);
        }
        else {

            $tax = bcmul($taxableBalance, 0.25, 4);
            $income_tax = bcadd($income_tax, $tax, 4);
            $paye = (float) bcsub(round($income_tax, 2, PHP_ROUND_HALF_UP), $totalRelief, 2);

            if ($paye < 0){

                $income_tax = 0;
                $nhif_relief = 0;
                $personal_relief = 0;
                $paye = 0;
            }

            return compact('income_tax', 'nhif_relief', 'personal_relief', 'paye');
        }

        if($taxableBalance >= 467667){

            $tax = bcmul(467667, 0.30, 4);
            $income_tax = bcadd($income_tax, $tax, 4);
            $taxableBalance = bcsub($taxableBalance, 467667, 4);
        }
        else {

            $tax = bcmul($taxableBalance, 0.30, 4);
            $income_tax = bcadd($income_tax, $tax, 4);
            $paye = (float) bcsub(round($income_tax, 2, PHP_ROUND_HALF_UP), $totalRelief, 2);

            if ($paye < 0){

                $income_tax = 0;
                $nhif_relief = 0;
                $personal_relief = 0;
                $paye = 0;
            }

            return compact('income_tax', 'nhif_relief', 'personal_relief', 'paye');
        }

        if($taxableBalance >= 300000){

            $tax = bcmul(300000, 0.325, 4);
            $income_tax = bcadd($income_tax, $tax, 4);
            $taxableBalance = bcsub($taxableBalance, 300000, 4);
        }
        else {

            $tax = bcmul($taxableBalance, 0.325, 4);
            $income_tax = bcadd($income_tax, $tax, 4);
            $paye = (float) bcsub(round($income_tax, 2, PHP_ROUND_HALF_UP), $totalRelief, 2);

            if ($paye < 0){

                $income_tax = 0;
                $nhif_relief = 0;
                $personal_relief = 0;
                $paye = 0;
            }

            return compact('income_tax', 'nhif_relief', 'personal_relief', 'paye');
        }

        if($taxableBalance > 0){

            $tax = bcmul($taxableBalance, 0.35, 4);
            $income_tax = bcadd($income_tax, $tax, 4);
        }

        if ($paye < 0){

            $income_tax = 0;
            $nhif_relief = 0;
            $personal_relief = 0;
            $paye = 0;
        }

        return compact('income_tax', 'nhif_relief', 'personal_relief', 'paye');

        return (float) bcsub(round($paye, 2, PHP_ROUND_HALF_UP), $totalRelief, 2);
    }

    /**
     * @param Request $request
     * @return Request
     */
    public function store_paye(Request $request)
    {

        $payrollItemsData = $request->except(['_token', 'payroll_id', 'paye_total',]);
        $individualPayrolls = $this->splitPayrollRequest($payrollItemsData);

        try {
            DB::beginTransaction();


            foreach ($individualPayrolls as $ip){

                $payrollItemNew = PayrollItemV2::find($ip['id']);
                $payrollItemNew->fill($ip);

                $payrollItemNew->taxable_gross = $payrollItemNew->basic_plus_allowance - $ip['nssf'];

                $deductionExempt = boolval(Salary::where('employee_id', $payrollItemNew->employee_id)->first()->deduction_exempt);

                $taxArray = $this->calculatePAYE($payrollItemNew->taxable_gross, $deductionExempt, !empty($payrollItemNew->nhif), $payrollItemNew->nhif);
                $payrollItemNew->fill($taxArray);

                $payrollItemNew->netpay = $payrollItemNew->taxable_gross - $payrollItemNew->taxable_deductions - $payrollItemNew->nhif - $payrollItemNew->nssf - $payrollItemNew->paye;

                $payrollItemNew->save();

            }

            $payroll = Payroll::find($request->payroll_id);
            $payroll->deduction_total = $request->deduction_total;

            $targetKey = 'nhif';
            $payroll->total_nhif= collect($individualPayrolls)->sum(function ($item) use ($targetKey) {
                return $item[$targetKey];
            });
            $payroll->total_nssf = $request->total_nssf;

            $payroll->save();

            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage() . " On File: " .  $e->getFile() . " On Line: " . $e->getLine());
        }




        return $individualPayrolls;
        
        $data = $request->only([
            'payroll_id','paye_total'
        ]);
        $data_items = $request->only([
            'id', 'paye','taxable_gross'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        
        // modify and filter items without item_id
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['id']; });

        
        try {
            $result = $this->repository->create_paye(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error creating Taxable Deductions', $th);
        }
        return redirect()->back();
    }

    /**
     * @param $gross_pay
     * @return float|int
     */
    public function calculate_nssf($gross_pay)
    {
        $nssf_brackets = Deduction::where('deduction_id','2')->get();
        $nssf = 0;
        foreach ($nssf_brackets as $i => $bracket) {
            if($i > 0){
                if($gross_pay > $bracket->amount_from){
                    $nssf = $bracket->rate;
                }
            }else{
                $nssf = $bracket->rate/100 * $gross_pay;
            }
        }
        return $nssf;
    }

    /**
     * @param $gross_pay
     * @return int
     */
    public function calculate_nhif($gross_pay)
    {
        $nhif_brackets = Deduction::where('deduction_id','1')->get();
        $nhif = 0;

        foreach ($nhif_brackets as $bracket) {
            if($gross_pay >= floatval($bracket->amount_from) && $gross_pay <= floatval($bracket->amount_to)){
                $nhif = floatval($bracket->rate);
            }
        }
        return $nhif;
    }

    public function calculateHousingLevy($grossPay): float {

        $housingLevy = bcmul(floatval($grossPay), 0.015, 2);

        return floatval($housingLevy);
    }

    /**
     * @param $gross_pay
     * @return float|int
     */
    public function calculate_paye($gross_pay)
    {
         //Get PAYE brackets
         $tax = 0;
         $paye_brackets = Deduction::where('deduction_id','3')->get();
         $first_bracket = Deduction::where('deduction_id','3')->first();
         $personal_relief = $first_bracket->rate/100 * $first_bracket->amount_to;
         $count = count($paye_brackets);
         //dd($count);
            foreach ($paye_brackets as $i => $bracket) {
                if ($i == $count-1) {
                    
                    if ($gross_pay > $bracket->amount_from) {
                        $tax += $bracket->rate / 100 * ($gross_pay - $bracket->amount_from);
                       
                    }
                    
                }
                else {
                    
                    if($i == 0){
                        
                        if($gross_pay > $bracket->amount_from){
                            $tax += $bracket->rate/100 * $bracket->amount_to;
                        }
                       
                    }else{
                        
                        if($gross_pay >= $bracket->amount_from && $gross_pay < $bracket->amount_to){
                            $tax += $bracket->rate/100 * ($gross_pay - $bracket->amount_from);
                           // dd($tax);
                        }
                        elseif($gross_pay >= $bracket->amount_from && $gross_pay > $bracket->amount_to){
                            $tax += $bracket->rate/100 * ($bracket->amount_to - $bracket->amount_from);
                        }
                        
                    }
                    
                }
             }
             if($gross_pay > $first_bracket->amount_to){
                $tax = $tax - $personal_relief;
             }else{
                $tax = $tax - ($first_bracket->rate/100 * $first_bracket->amount_to);
             }
         
        return $tax;
    }

    /**
     * @param $payroll
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reports($payroll){

        $payrollTallies = Payroll::where('payroll_id', $payroll)
            ->join('payroll_items', 'payroll.id', 'payroll_items.payroll_id')
            ->join('users', 'payroll_items.employee_id', 'users.id')
            ->select(
                'payroll_items.*',
                'payroll.*',
                DB::raw('CONCAT(first_name, " ", last_name) as name'),
                DB::raw('SUM(basic_hourly_salary) as hourly_pay_tally'),
                DB::raw('SUM(absent_days) as absent_days_tally'),
                DB::raw('SUM(total_allowance + other_allowances) as allowances_tally'),
                DB::raw('SUM(taxable_gross) as taxable_gross_tally'),
                DB::raw('SUM(nssf) as nssf_tally'),
                DB::raw('SUM(taxable_deductions) as taxable_deductions_tally'),
                DB::raw('SUM(housing_levy) as housing_levy_tally'),
                DB::raw('SUM(nhif) as nhif_tally'),
                DB::raw('SUM(paye) as paye_tally'),
                DB::raw('SUM(netpay) as netpay_tally'),
                DB::raw('SUM(net_after_bnd) as final_pay_tally'),
                DB::raw('SUM(absent_total_deduction + total_nhif + total_nssf + housing_levy + loan + other_deductions) as deductions_tally'),
            )
            ->get();

        // aggregate
        $nssf_total = amountFormat($payrollTallies->sum('nssf_tally'));
        $nhif_total = amountFormat($payrollTallies->sum('nhif_tally'));
        $housing_levy_total = amountFormat($payrollTallies->sum('housing_levy_tally'));
        $paye_total = amountFormat($payrollTallies->sum('paye_tally'));
        $netpay_total = amountFormat($payrollTallies->sum('netpay_tally'));
        $final_pay_total = amountFormat($payrollTallies->sum('final_pay_tally'));
        $tallies = compact('nssf_total', 'nhif_total', 'housing_levy_total', 'paye_total', 'netpay_total', 'final_pay_total');


        return view('focus.payroll.pages.reports',compact('payroll', 'tallies', 'payrollTallies'));
    }

    public function getNhifReport($payrollId){

        $query = Payroll::where('payroll_id', $payrollId)
            ->join('payroll_items', 'payroll.id', 'payroll_items.payroll_id')
            ->where('payroll_items.nhif', '!=', 0.00);

        return $this->getReportsV2($payrollId, $query);
    }

    public function getNssfReport($payrollId){

        $query = Payroll::where('payroll_id', $payrollId)
            ->join('payroll_items', 'payroll.id', 'payroll_items.payroll_id')
            ->where('payroll_items.nssf', '!=', 0.00);

        return $this->getReportsV2($payrollId, $query);
    }

    public function getPayeReport($payrollId){

        $query = Payroll::where('payroll_id', $payrollId)
            ->join('payroll_items', 'payroll.id', 'payroll_items.payroll_id')
            ->where('payroll_items.paye', '!=', 0.00);

        return $this->getReportsV2($payrollId, $query);
    }

    public function getHousingLevyReport($payrollId){

        $query = Payroll::where('payroll_id', $payrollId)
            ->join('payroll_items', 'payroll.id', 'payroll_items.payroll_id')
            ->where('payroll_items.housing_levy', '!=', 0.00);

        return $this->getReportsV2($payrollId, $query);
    }

    public function getReportsV2($payrollId, $query)
    {
        $payroll = Payroll::find($payrollId);


        $payrollItems = $query
            ->join('users', 'payroll_items.employee_id', 'users.id')
            ->join('hrm_metas', 'users.id', 'hrm_metas.user_id')
            ->select(
                'payroll_items.*',
//                'payroll_items.basic_salary as employee_basic_salary'.
                'payroll.*',
                'hrm_metas.nssf as nssf_number',
                'hrm_metas.nhif as nhif_number',
                'kra_pin',
                'first_name',
                'last_name',
                'id_number',
                'primary_contact',
                DB::raw('CONCAT(first_name, " ", last_name) as name'),
            )
            ->get();

        return Datatables::of($payrollItems)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_id', function ($payrollItems) {
                return $payrollItems->employee_id;
            })
            ->addColumn('payroll_id', function ($payrollItems) {
                $payroll_id = gen4tid('PYRLL-', $payrollItems->payroll_id);
                return $payrollItems->payroll_id;
            })
            ->addColumn('name', function ($payrollItems) {
                return $payrollItems->name;
            })
            ->addColumn('surname', function ($payrollItems) {

                $parts = explode(" ", $payrollItems->last_name);
                $surname = end($parts);

                return $surname;
            })
            ->addColumn('other_names', function ($payrollItems) {

                $parts = explode(" ", $payrollItems->last_name);

                if (count($parts) >= 2) {
                    // Remove the last part
                    array_pop($parts);

                    $otherNames = implode(" ", $parts);
                } else {
                    $otherNames = '';
                }

                return $payrollItems->first_name . " " . $otherNames;
            })
            ->addColumn('id_number', function ($payrollItems) {
                return $payrollItems->id_number;
            })
            ->addColumn('nssf_number', function ($payrollItems) {

                if ($payrollItems->nssf_number == 00 || $payrollItems->nssf_number == 0) return '0';

                return $payrollItems->nssf_number;
            })
            ->addColumn('kra_pin', function ($payrollItems) {
                return $payrollItems->kra_pin;
            })
            ->addColumn('nhif_number', function ($payrollItems) {

                if ($payrollItems->nhif_number == 00 || $payrollItems->nhif_number == 0) return '0';
                return $payrollItems->nhif_number;
            })
            ->addColumn('primary_contact', function ($payrollItems) {
                return $payrollItems->primary_contact;
            })
            ->addColumn('fixed_salary', function ($payrollItem) {
                return number_format($payrollItem->fixed_salary, 2, '.', ',');
            })
            ->addColumn('max_hourly_salary', function ($payrollItem) {
                return number_format($payrollItem->max_hourly_salary, 2, '.', ',');
            })
            ->addColumn('pay_per_hr', function ($payrollItem) {
                return number_format($payrollItem->pay_per_hr, 2, '.', ',');
            })
            ->addColumn('man_hours', function ($payrollItem) {
                return number_format($payrollItem->man_hours, 2, '.', ',');
            })
            ->addColumn('basic_hourly_salary', function ($payrollItem) {
                return number_format($payrollItem->basic_hourly_salary, 2, '.', ',');
            })
            ->addColumn('absent_days', function ($payrollItem) {
                return number_format($payrollItem->absent_days, 2, '.', ',');
            })
            ->addColumn('absent_daily_deduction', function ($payrollItem) {
                return number_format($payrollItem->absent_daily_deduction, 2, '.', ',');
            })
            ->addColumn('absent_total_deduction', function ($payrollItem) {
                return number_format($payrollItem->absent_total_deduction, 2, '.', ',');
            })
            ->addColumn('basic_salary', function ($payrollItem) {
                return number_format($payrollItem->basic_salary, 2, '.', ',');
            })
            ->addColumn('house_allowance', function ($payrollItem) {
                return number_format($payrollItem->house_allowance, 2, '.', ',');
            })
            ->addColumn('transport_allowance', function ($payrollItem) {
                return number_format($payrollItem->transport_allowance, 2, '.', ',');
            })
            ->addColumn('other_allowance', function ($payrollItem) {
                return number_format($payrollItem->other_allowance, 2, '.', ',');
            })
            ->addColumn('total_allowance', function ($payrollItem) {
                return number_format($payrollItem->total_allowance, 2, '.', ',');
            })
            ->addColumn('basic_plus_allowance', function ($payrollItem) {
                return number_format($payrollItem->basic_plus_allowance, 2, '.', ',');
            })
            ->addColumn('taxable_deductions', function ($payrollItem) {
                return number_format($payrollItem->taxable_deductions, 2, '.', ',');
            })
            ->addColumn('deduction_narration', function ($payrollItem) {
                return $payrollItem->deduction_narration;
            })
            ->addColumn('nssf', function ($payrollItem) {
                return number_format($payrollItem->nssf, 2, '.', ',');
            })
            ->addColumn('taxable_gross', function ($payrollItem) {
                return number_format($payrollItem->taxable_gross, 2, '.', ',');
            })
            ->addColumn('nhif', function ($payrollItem) {
                return number_format($payrollItem->nhif, 2, '.', ',');
            })
            ->addColumn('housing_levy', function ($payrollItem) {
                return number_format($payrollItem->housing_levy, 2, '.', ',');
            })
            ->addColumn('income_tax', function ($payrollItem) {
                return number_format($payrollItem->income_tax, 2, '.', ',');
            })
            ->addColumn('nhif_relief', function ($payrollItem) {
                return number_format($payrollItem->nhif_relief, 2, '.', ',');
            })
            ->addColumn('personal_relief', function ($payrollItem) {
                return number_format($payrollItem->personal_relief, 2, '.', ',');
            })
            ->addColumn('paye', function ($payrollItem) {
                return number_format($payrollItem->paye, 2, '.', ',');
            })
            ->addColumn('netpay', function ($payrollItem) {
                return number_format($payrollItem->netpay, 2, '.', ',');
            })
            ->addColumn('loan', function ($payrollItem) {
                return number_format($payrollItem->loan, 2, '.', ',');
            })
            ->addColumn('advance', function ($payrollItem) {
                return number_format($payrollItem->advance, 2, '.', ',');
            })
            ->addColumn('benefits', function ($payrollItem) {
                return number_format($payrollItem->benefits, 2, '.', ',');
            })
            ->addColumn('other_deductions', function ($payrollItem) {
                return number_format($payrollItem->other_deductions, 2, '.', ',');
            })
            ->addColumn('other_allowances', function ($payrollItem) {
                return number_format($payrollItem->other_allowances, 2, '.', ',');
            })
            ->addColumn('net_after_bnd', function ($payrollItem) {
                return number_format($payrollItem->net_after_bnd, 2, '.', ',');
            })
            ->addColumn('tax_obligation', function ($payrollItem) {
                return 'Resident';
            })
            ->addColumn('employee_type', function ($payrollItem) {
                return 'Primary Employee';
            })
            ->addColumn('benefit_not_given', function ($payrollItem) {
                return 'Benefit not given';
            })
            ->addColumn('blank_col', function ($payrollItem) {
                return '';
            })
            ->addColumn('zero_col', function ($payrollItem) {
                return 0;
            })

            ->make(true);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function get_reports($payrollId)
    {
        $payroll = Payroll::find($payrollId);

        $payrollItems = Payroll::where('payroll_id', $payrollId)
            ->join('payroll_items', 'payroll.id', 'payroll_items.payroll_id')
            ->join('users', 'payroll_items.employee_id', 'users.id')
            ->join('hrm_metas', 'users.id', 'hrm_metas.user_id')
            ->select(
                'payroll_items.*',
//                'payroll_items.basic_salary as employee_basic_salary'.
                'payroll.*',
                'hrm_metas.nssf as nssf_number',
                'hrm_metas.nhif as nhif_number',
                'kra_pin',
                'first_name',
                'last_name',
                'id_number',
                'primary_contact',
                DB::raw('CONCAT(first_name, " ", last_name) as name'),
            )
            ->get();

        return Datatables::of($payrollItems)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_id', function ($payrollItems) {
                return $payrollItems->employee_id;
             })
             ->addColumn('payroll_id', function ($payrollItems) {
                $payroll_id = gen4tid('PYRLL-', $payrollItems->payroll_id);
                return $payrollItems->payroll_id;
             })
            ->addColumn('name', function ($payrollItems) {
               return $payrollItems->name;
            })
            ->addColumn('surname', function ($payrollItems) {

                $parts = explode(" ", $payrollItems->last_name);
                $surname = end($parts);

               return $surname;
            })
            ->addColumn('other_names', function ($payrollItems) {

                $parts = explode(" ", $payrollItems->last_name);

                if (count($parts) >= 2) {
                    // Remove the last part
                    array_pop($parts);

                    $otherNames = implode(" ", $parts);
                } else {
                    $otherNames = '';
                }

                return $payrollItems->first_name . " " . $otherNames;
            })
            ->addColumn('id_number', function ($payrollItems) {
                return $payrollItems->id_number;
            })
            ->addColumn('nssf_number', function ($payrollItems) {

                if ($payrollItems->nssf_number == 00 || $payrollItems->nssf_number == 0) return '0';

               return $payrollItems->nssf_number;
            })
            ->addColumn('kra_pin', function ($payrollItems) {
                return $payrollItems->kra_pin;
            })
            ->addColumn('nhif_number', function ($payrollItems) {
                if ($payrollItems->nhif_number == 00 || $payrollItems->nhif_number == 0) return '0';
                return $payrollItems->nhif_number;
            })
            ->addColumn('primary_contact', function ($payrollItems) {
                $contact = $payrollItems->primary_contact;
                if (substr($contact, 0, 1) == 0) {
                    $contact = substr_replace($contact, "254", 0, 1);
                }
                return $contact;
            })
            ->addColumn('fixed_salary', function ($payrollItem) {
                return number_format($payrollItem->fixed_salary, 2, '.', ',');
            })
            ->addColumn('max_hourly_salary', function ($payrollItem) {
                return number_format($payrollItem->max_hourly_salary, 2, '.', ',');
            })
            ->addColumn('pay_per_hr', function ($payrollItem) {
                return number_format($payrollItem->pay_per_hr, 2, '.', ',');
            })
            ->addColumn('man_hours', function ($payrollItem) {
                return number_format($payrollItem->man_hours, 2, '.', ',');
            })
            ->addColumn('basic_hourly_salary', function ($payrollItem) {
                return number_format($payrollItem->basic_hourly_salary, 2, '.', ',');
            })
            ->addColumn('absent_days', function ($payrollItem) {
                return number_format($payrollItem->absent_days, 2, '.', ',');
            })
            ->addColumn('absent_daily_deduction', function ($payrollItem) {
                return number_format($payrollItem->absent_daily_deduction, 2, '.', ',');
            })
            ->addColumn('absent_total_deduction', function ($payrollItem) {
                return number_format($payrollItem->absent_total_deduction, 2, '.', ',');
            })
            ->addColumn('basic_salary', function ($payrollItem) {
                return number_format($payrollItem->basic_salary, 2, '.', ',');
            })
            ->addColumn('house_allowance', function ($payrollItem) {
                return number_format($payrollItem->house_allowance, 2, '.', ',');
            })
            ->addColumn('transport_allowance', function ($payrollItem) {
                return number_format($payrollItem->transport_allowance, 2, '.', ',');
            })
            ->addColumn('other_allowance', function ($payrollItem) {
                return number_format($payrollItem->other_allowance, 2, '.', ',');
            })
            ->addColumn('total_allowance', function ($payrollItem) {
                return number_format($payrollItem->total_allowance, 2, '.', ',');
            })
            ->addColumn('basic_plus_allowance', function ($payrollItem) {
                return number_format($payrollItem->basic_plus_allowance, 2, '.', ',');
            })
            ->addColumn('taxable_deductions', function ($payrollItem) {
                return number_format($payrollItem->taxable_deductions, 2, '.', ',');
            })
            ->addColumn('deduction_narration', function ($payrollItem) {
                return $payrollItem->deduction_narration;
            })
            ->addColumn('nssf', function ($payrollItem) {
                return number_format($payrollItem->nssf, 2, '.', ',');
            })
            ->addColumn('taxable_gross', function ($payrollItem) {
                return number_format($payrollItem->taxable_gross, 2, '.', ',');
            })
            ->addColumn('nhif', function ($payrollItem) {
                return number_format($payrollItem->nhif, 2, '.', ',');
            })
            ->addColumn('housing_levy', function ($payrollItem) {
                return number_format($payrollItem->housing_levy, 2, '.', ',');
            })
            ->addColumn('income_tax', function ($payrollItem) {
                return number_format($payrollItem->income_tax, 2, '.', ',');
            })
            ->addColumn('nhif_relief', function ($payrollItem) {
                return number_format($payrollItem->nhif_relief, 2, '.', ',');
            })
            ->addColumn('personal_relief', function ($payrollItem) {
                return number_format($payrollItem->personal_relief, 2, '.', ',');
            })
            ->addColumn('paye', function ($payrollItem) {
                return number_format($payrollItem->paye, 2, '.', ',');
            })
            ->addColumn('netpay', function ($payrollItem) {
                return number_format($payrollItem->netpay, 2, '.', ',');
            })
            ->addColumn('loan', function ($payrollItem) {
                return number_format($payrollItem->loan, 2, '.', ',');
            })
            ->addColumn('advance', function ($payrollItem) {
                return number_format($payrollItem->advance, 2, '.', ',');
            })
            ->addColumn('benefits', function ($payrollItem) {
                return number_format($payrollItem->benefits, 2, '.', ',');
            })
            ->addColumn('other_deductions', function ($payrollItem) {
                return number_format($payrollItem->other_deductions, 2, '.', ',');
            })
            ->addColumn('other_allowances', function ($payrollItem) {
                return number_format($payrollItem->other_allowances, 2, '.', ',');
            })
            ->addColumn('net_after_bnd', function ($payrollItem) {
                return number_format($payrollItem->net_after_bnd, 2, '.', ',');
            })
            ->addColumn('tax_obligation', function ($payrollItem) {
                return 'Resident';
            })
            ->addColumn('employee_type', function ($payrollItem) {
                return 'Primary Employee';
            })
            ->addColumn('benefit_not_given', function ($payrollItem) {
                return 'Benefit not given';
            })
            ->addColumn('blank_col', function ($payrollItem) {
                return '';
            })
            ->addColumn('zero_col', function ($payrollItem) {
                return 0;
            })

            ->make(true);
    }





    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit_basic(int $payrollId)
    {

        $payrollDefaultValues = [
            'salary_total' => '',
            'extra_allowances_total' => '',
            'allowance_total' => '',
            'gross_post_allowances' => '0.00',
            'deduction_total' => '',
            'paye_total' => '',
            'other_deductions_total' => '',
            'other_benefits_total' => '',
            'other_allowances_total' => '0.00',
            'total_netpay' => '',
            'total_nhif' => '',
            'total_nssf' => '',
            'total_housing_levy' => '0.00',
            'total_salary_after_bnd' => '0.00',
        ];


        try {
            DB::beginTransaction();

            $payroll = Payroll::find($payrollId);
            $payroll->fill($payrollDefaultValues);
            $payroll->save();

            $payrollItems = PayrollItemV2::where('payroll_id', $payrollId)->get();

            foreach ($payrollItems as $item){

                $item->delete();
            }

//            $excludedColumns = [
//                'id',
//                'payroll_id',
//                'employee_id',
//                'fixed_salary',
//                'max_hourly_salary',
//                'pay_per_hr',
//                'man_hours',
//                'user_id',
//                'ins',
//                'created_at',
//                'updated_at',
//            ];
//            $allColumns = Schema::getColumnListing('payroll_items');
//            foreach ($payrollItems as $item){
//
//                $defaultValues = Arr::except($this->getPayrollItemDefaults(), $excludedColumns);
//
//                $item->fill($defaultValues);
//                $item->save();
//            }


            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage() . " On File: " .  $e->getFile() . " On Line: " . $e->getLine());
        }


        return redirect()->route('biller.payroll.page', $payrollId);
    }



    /**
     * Get default values for Payroll Items Table
     * @return array
     */
    public function getPayrollItemDefaults(){

        return $defaultValues = [
            'fixed_salary' => 0.00,
            'max_hourly_salary' => 0.00,
            'pay_per_hr' => 0.00,
            'man_hours' => 0.00,
            'basic_hourly_salary' => 0.00,
            'absent_days' => 0,
            'absent_daily_deduction' => 0.00,
            'absent_total_deduction' => 0.00,
            'basic_salary' => 0.00,
            'house_allowance' => 0.00,
            'transport_allowance' => 0.00,
            'other_allowance' => 0.00,
            'total_allowance' => 0.00,
            'basic_plus_allowance' => 0.00,
            'taxable_deductions' => 0.00,
            'deduction_narration' => null,
            'nssf' => 0.00,
            'taxable_gross' => 0.00,
            'nhif' => 0.00,
            'housing_levy' => 0.00,
            'income_tax' => 0.00,
            'nhif_relief' => 0.00,
            'personal_relief' => 0.00,
            'paye' => 0.00,
            'netpay' => 0.00,
            'loan' => 0.00,
            'advance' => 0.00,
            'benefits' => 0.00,
            'other_deductions' => 0.00,
            'other_allowances' => 0.00,
            'net_after_bnd' => 0.00,
            'nssf_employer_contribution' => 0.00,
            'user_id' => 0, // Replace with the default user_id value
            'ins' => 0, // Replace with the default ins value
            'created_at' => now(), // Assuming you have a function to get the current timestamp
            'updated_at' => now(), // Assuming you have a function to get the current timestamp
        ];


    }




}
