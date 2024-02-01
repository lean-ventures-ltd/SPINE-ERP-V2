<?php

namespace App\Models\invoice_payment;

use App\Models\invoice_payment\Traits\InvoicePaymentAttribute;
use App\Models\invoice_payment\Traits\InvoicePaymentRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use ModelTrait, InvoicePaymentAttribute, InvoicePaymentRelationship;
    
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'paid_invoices';

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
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
