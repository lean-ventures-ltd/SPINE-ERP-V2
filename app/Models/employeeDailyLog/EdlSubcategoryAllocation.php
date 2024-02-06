<?php

namespace App\Models\employeeDailyLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EdlSubcategoryAllocation extends Model
{

    use SoftDeletes;

    protected $table = 'edl_subcategory_allocations';

    protected $primaryKey = 'employee';

    public $incrementing = false;

    protected $fillable = [
        'employee',
        'allocations',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('edl_subcategory_allocations.ins', '=', auth()->user()->ins);
        });
    }


}
