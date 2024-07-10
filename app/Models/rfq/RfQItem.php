<?php

namespace App\Models\rfq;

use App\Models\account\Account;
use App\Models\product\ProductVariation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfQItem extends Model
{
    protected $table = 'rfq_items';

    protected $fillable = [
        'description',
        'uom',
    ];

    protected $attributes = [];


    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $guarded = [
        'id'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    protected static function boot()
    {
        parent::boot();

    }


    public function account(): BelongsTo {

        return $this->belongsTo(Account::class, 'expense_account_id', 'id');
    }

    public function product(): BelongsTo {

        return $this->belongsTo(ProductVariation::class, 'product_id', 'id');
    }
}
