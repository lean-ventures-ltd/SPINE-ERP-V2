<?php

namespace App\Models\project;

use App\Models\ModelTrait;
use App\Models\project\Traits\BudgetAttribute;
use App\Models\project\Traits\BudgetRelationship;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use ModelTrait, BudgetAttribute,BudgetRelationship;
    
     /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'budgets';

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
            $builder->where('ins', auth()->user()->ins);
        });
    }    
}