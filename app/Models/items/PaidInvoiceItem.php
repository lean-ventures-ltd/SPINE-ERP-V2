<?php

namespace App\Models\items;

use App\Models\items\Traits\PaidInvoiceItemRelationship;
use Illuminate\Database\Eloquent\Model;

class PaidInvoiceItem extends Model
{
    use PaidInvoiceItemRelationship;

    protected $table = 'paid_invoice_items';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [];

    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Constructor of Model
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    protected static function boot()
    {
        parent::boot();
    }
}
