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
namespace App\Http\Controllers\Focus\salary;

use App\Models\salary\Salary;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\salary\CreateResponse;
use App\Http\Responses\Focus\salary\EditResponse;
use App\Repositories\Focus\salary\SalaryRepository;
use App\Models\hrm\Hrm;
use App\models\workshift\Workshift;


/**
 * salarysController
 */
class SalaryController extends Controller
{
    /**
     * variable to store the repository object
     * @var SalaryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SalaryRepository $repository ;
     */
    public function __construct(SalaryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\salary\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.salary.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatesalaryRequestNamespace $request
     * @return \App\Http\Responses\Focus\salary\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.salary.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoresalaryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $input = $request->except(['_token','allowance_id','amount']);
       // dd($request->all());
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;
        $employee_allowance = $request->only(['allowance_id','amount']);
       
        $employee_allowance = modify_array($employee_allowance);
        $employee_allowance = array_filter($employee_allowance, function ($v) { return $v['allowance_id']; });
        
        //Create the model using repository create method
        $this->repository->create(compact('input', 'employee_allowance'));
        //return with successfull messagetrans
        return new RedirectResponse(route('biller.salary.index'), ['flash_success' => 'Salary Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\salary\salary $salary
     * @param EditsalaryRequestNamespace $request
     * @return \App\Http\Responses\Focus\salary\EditResponse
     */
    public function edit(Salary $salary, Request $request)
    {
        return new EditResponse($salary);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatesalaryRequestNamespace $request
     * @param App\Models\salary\salary $salary
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Salary $salary)
    {
        //Input received from the request
       // $input = $request->except(['_token', 'ins']);
        $data = $request->only(['employee_id', 'employee_name', 'basic_pay', 'contract_type','workshift_id','start_date','duration', 'pay_per_hr']);
        //dd($input);
        $data_items = $request->only([
            'allowance_id','amount','id'
        ]);
        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($val) { return $val['allowance_id']; });
        try {
            //Update the model using repository update method
            $this->repository->update($salary, compact('data','data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Contract', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.salary.index'), ['flash_success' => 'Contract Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletesalaryRequestNamespace $request
     * @param App\Models\salary\salary $salary
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Salary $salary, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($salary);
        //returning with successfull message
        return new RedirectResponse(route('biller.salary.index'), ['flash_success' => trans('alerts.backend.salary.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletesalaryRequestNamespace $request
     * @param App\Models\salary\salary $salary
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Salary $salary, Request $request)
    {
        $workshifts = Workshift::all(['id','name']);
        //returning with successfull message
        return new ViewResponse('focus.salary.view', compact('salary','workshifts'));
    }
    
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Hrm::where('first_name', 'LIKE', '%'.$q.'%')
            ->orWhere('email', 'LIKE', '%'.$q.'')
            ->limit(6)->get()
            ->map(function($v) {
                // ['id', 'first_name', 'last_name', 'email']
                $v1 = new Hrm;
                $v1->fill([
                    'id' => $v->id,
                    'employee_no' => $v->meta->employee_no ?: '',
                    'first_name' => $v->first_name,
                    'last_name' => $v->last_name,
                    'email' => $v->email,
                ]);
                return $v1;
            });
            
        return response()->json($users);
    }

    public function renew_contract(Request $request)
    {
        
        $old_contract = Salary::find($request->id);
        if($old_contract->status == 'ongoing'){
            $old_contract->status = 'terminated';
            $old_contract->update();
        }
        $new_contract = new Salary();
        $new_contract->employee_name = $request->employee_name;
        $new_contract->employee_id = $request->employee_id;
        $new_contract->basic_pay = $request->basic_pay;
        $new_contract->house_allowance = $request->house_allowance;
        $new_contract->transport_allowance = $request->transport_allowance;
        $new_contract->directors_fee = $request->directors_fee;
        $new_contract->contract_type = $request->contract_type;
        $start_date = date_for_database($request->start_date);
        $new_contract->start_date = $start_date;
        $new_contract->duration = $request->duration;
        $new_contract->status = 'ongoing';
        $new_contract['ins'] = auth()->user()->ins;
        $new_contract['user_id'] = auth()->user()->id;
        $new_contract->save();
        return new RedirectResponse(route('biller.salary.index'), ['flash_success' => 'Contract Renewed Successfully!!']);
    }
    public function terminate_contract(Request $request)
    {
        //dd($request->all());
        $terminate_contract = Salary::find($request->id);
        $terminate_date = date_for_database($request->terminate_date);
        if($terminate_contract->status == 'ongoing'){
            $terminate_contract->status = $request->status;
            $terminate_contract->update();
            return new RedirectResponse(route('biller.salary.index'), ['flash_success' => 'Contract Terminated Successfully!!']);
        }
        
        return new RedirectResponse(route('biller.salary.index'), ['flash_error' => 'Contract Cannot be Terminated!!']);
    }
}
