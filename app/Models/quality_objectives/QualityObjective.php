<?php

namespace App\Models\quality_objectives;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class QualityObjective extends Model
{
    use ModelTrait;

    protected $table = 'quality_objectives';

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

    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("create-daily-logs", "biller.quality-objectives.show").'
                '.$this->getEditButtonAttribute("create-daily-logs", "biller.quality-objectives.edit").'
                '.$this->getDeleteButtonAttribute("create-daily-logs", "biller.quality-objectives.destroy").'
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
