<?php

namespace App\Models\PurchaseClass;

use App\Models\purchase\Purchase;
use App\Models\purchaseorder\Purchaseorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function purchases(): HasMany {

        return $this->hasMany(Purchase::class, 'purchase_class', 'id');
    }

    public function purchaseOrders(): HasMany {

        return $this->hasMany(Purchaseorder::class, 'purchase_class', 'id');
    }


}
