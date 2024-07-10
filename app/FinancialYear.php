<?php

namespace App;

use App\Models\PurchaseClass\PurchaseClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialYear extends Model
{

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'ins'
    ];

    public function purchaseClasses() : HasMany {

        return $this->hasMany(PurchaseClass::class, 'financial_year_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('financial_years.ins', '=', auth()->user()->ins);
        });
    }


}
