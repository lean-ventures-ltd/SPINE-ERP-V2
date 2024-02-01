<?php

namespace App\Models\leave\Traits;

use App\Models\Access\User\User;
use App\Models\leave_category\LeaveCategory;

trait LeaveRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function assist_employee()
    {
        return $this->belongsTo(User::class, 'assist_employee_id');
    }

    public function leave_category()
    {
        return $this->belongsTo(LeaveCategory::class);
    }
}
