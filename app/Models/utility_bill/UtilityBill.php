<?php

namespace App\Models\utility_bill;

use App\Models\ModelTrait;
use App\Models\utility_bill\Traits\UtilityBillAttribute;
use App\Models\utility_bill\Traits\UtilityBillRelationship;
use Illuminate\Database\Eloquent\Model;


class UtilityBill extends Model
{
    use ModelTrait, UtilityBillAttribute, UtilityBillRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'utility_bills';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'tid', 'supplier_id', 'reference', 'reference_type','document_type', 'ref_id', 'date', 'due_date', 'tax_rate', 'subtotal', 
        'tax', 'total', 'note', 'status', 'amount_paid', 'user_id', 'ins'      
    ];

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

    /**
     * model life cycle event listeners
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
