<?php

namespace App\Models\payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollItemV2 extends Model
{

    protected $table = 'payroll_items';

    protected $primaryKey = 'id';

    protected $fillable = [
        "absent_daily_deduction",
        "absent_days",
        "absent_total_deduction",
        "advance",
        "basic_hourly_salary",
        "additional_hourly_salary",
        "basic_plus_allowance",
        "basic_salary",
        "benefits",
        "deduction_narration",
        "employee_id",
        "fixed_salary",
        "house_allowance",
        "housing_levy",
        "income_tax",
        "loan",
        "man_hours",
        "additional_hours",
        "max_hourly_salary",
        "nhif",
        "deduction_exempt",
        "nhif_relief",
        "nssf",
        "other_allowance",
        "other_allowances",
        "other_deductions",
        "pay_per_hr",
        "paye",
        "personal_relief",
        "rate_per_month",
        "taxable_deductions",
        "taxable_pay",
        "total_allowance",
        "transport_allowance",
    ];


}
