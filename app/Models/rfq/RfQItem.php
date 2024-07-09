<?php

namespace App\Models\rfq;

use Illuminate\Database\Eloquent\Model;

class RfQItem extends Model
{
    protected $table = 'rfq_items';

    protected $fillable = [
        'description',
        'uom',
    ];

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
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('rfq_items.ins', '=', auth()->user()->ins);
        });
    }
}
