<?php

namespace App\Models\health_and_safety;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\ModelTrait;
use App\Models\project\Project;
use Illuminate\Database\Eloquent\Model;

class HealthAndSafetyTracking extends Model
{
    use ModelTrait;

    protected $table = 'health_and_safety_tracking';

    protected $fillable = [];

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

    public function customer(){
        return $this->belongsTo(Customer::class,  'customer_id');
    }
    public function branch(){
        return $this->belongsTo(Branch::class,  'branch_id');
    }
    public function project(){
        return $this->belongsTo(Project::class, 'project_id');  
    }
    public function employee(){
        return $this->belongsTo(Hrm::class, 'employee');  
    }
    public function res(){
        return $this->belongsTo(Hrm::class, 'responsibility');  
    }
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("create-daily-logs", "biller.health-and-safety.show").'
                '.$this->getEditButtonAttribute("create-daily-logs", "biller.health-and-safety.edit").'
                '.$this->getDeleteButtonAttribute("create-daily-logs", "biller.health-and-safety.destroy").'
                ';
    }

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
