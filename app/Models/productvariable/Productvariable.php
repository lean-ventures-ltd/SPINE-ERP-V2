<?php

namespace App\Models\productvariable;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\productvariable\Traits\ProductvariableAttribute;
use App\Models\productvariable\Traits\ProductvariableRelationship;

class Productvariable extends Model
{
    use ModelTrait,  ProductvariableAttribute, ProductvariableRelationship;
       
    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'product_variables';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'title', 'code', 'unit_type', 'base_unit_id', 'base_ratio', 'count_type', 'ins'
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
            $instance->ins = $instance->ins ?: auth()->user()->ins;
            $instance->user_id = $instance->user_id ?: auth()->user()->id;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', auth()->user()->ins);
        });
    }
}
