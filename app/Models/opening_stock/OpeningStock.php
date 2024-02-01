<?php

namespace App\Models\opening_stock;

use App\Models\ModelTrait;
use App\Models\opening_stock\Traits\OpeningStockAttribute;
use App\Models\opening_stock\Traits\OpeningStockRelationship;
use Illuminate\Database\Eloquent\Model;

class OpeningStock extends Model
{
    use ModelTrait, OpeningStockAttribute, OpeningStockRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'opening_stock';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'tid', 'date', 'note', 'warehouse_id', 'total', 'user_id', 'ins'
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
