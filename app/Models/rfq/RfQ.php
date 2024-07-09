<?php

namespace App\Models\rfq;

use App\Models\ModelTrait;
use App\Models\rfq\Traits\RfQAttribute;
use App\Models\rfq\Traits\RfQRelationship;
use Illuminate\Database\Eloquent\Model;

class RfQ extends Model
{
    use ModelTrait, RfQAttribute, RfQRelationship;
    protected $table = 'rfqs';

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
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {
            $builder->where('rfqs.ins', '=', auth()->user()->ins);
        });
    }
}
