<?php

namespace App\Http\Responses\Focus\salary;

use App\Models\Access\User\User;
use Illuminate\Contracts\Support\Responsable;
use App\Models\workshift\Workshift;
use Illuminate\Support\Facades\DB;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\salary\salary
     */
    protected $salary;

    /**
     * @param App\Models\salary\salary $salary
     */
    public function __construct($salary)
    {
        $this->salary = $salary;
    }

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

        $employees = User::where('ins', auth()->user()->ins)
        ->select(
            'id',
            DB::raw('CONCAT(first_name, " ", last_name) as full_name')
        )
            ->get()->toArray();


        return view('focus.salary.edit')->with([
            'employees' => $employees,
            'salary' => $this->salary,
            'workshifts' => $workshifts
        ]);
    }
}