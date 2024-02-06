<?php

namespace App\Models\PurchaseClass;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseClass extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        // Add other fillable fields as needed
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('purchase_classes.ins', '=', auth()->user()->ins);
        });
    }


}
