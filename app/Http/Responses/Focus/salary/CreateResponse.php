<?php

namespace App\Http\Responses\Focus\salary;

use App\Models\Access\User\User;
use App\Models\employeeDailyLog\EmployeeDailyLog;
use App\Models\hrm\Hrm;
use App\Models\salary\Salary;
use Illuminate\Contracts\Support\Responsable;
use App\Models\department\Department;
use App\Models\workshift\Workshift;
use Illuminate\Support\Facades\DB;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $workshifts = Workshift::all(['id','name']);

        $salariedEmployees = Salary::all()->pluck('employee_id');

        $employees = User::whereNotIn('id', $salariedEmployees)
            ->select(
                'id',
                DB::raw('CONCAT(first_name, " ", last_name) as full_name')
            )
            ->get()->toArray();

        return view('focus.salary.create', compact('workshifts', 'employees'));
    }
}