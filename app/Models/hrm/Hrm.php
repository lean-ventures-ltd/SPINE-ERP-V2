<?php

namespace App\Models\hrm;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\hrm\Traits\HrmAttribute;
use App\Models\hrm\Traits\HrmRelationship;

class Hrm extends Model
{
    use ModelTrait,
        HrmAttribute,
        HrmRelationship {
        // HrmAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'users';

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
            $builder->where('users.ins', '=', auth()->user()->ins);
        });
    }
    /**
     * Set password attribute.
     *
     * @param [string] $password
     */
    public function setPasswordAttribute($password)
    {
        if (!empty($password)) {
            $this->attributes['password'] = bcrypt($password);
        }
    }

    /**
     * Concatenate first_name and last_name column
     * 
     * @return string
     */
    public function getFullnameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
