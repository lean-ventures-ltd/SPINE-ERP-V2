<?php

namespace App\Models\documentManager;

use App\User;
use Illuminate\Database\Eloquent\Model;

class DocumentManager extends Model
{


    protected $table = 'document_manager';

    protected $fillable = [
        'name',
        'document_type',
        'description',
        'responsible',
        'co_responsible',
        'issuing_body',
        'issue_date',
        'cost_of_renewal',
        'renewal_date',
        'expiry_date',
        'alert_days_before',
        'status',
        'created_by',
        'updated_by',
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            $instance->created_by = auth()->user()->id;
            $instance->updated_by = auth()->user()->id;
            return $instance;
        });

        static::updating(function ($instance) {

            $instance->updated_by = auth()->user()->id;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('document_manager.ins', '=', auth()->user()->ins);
        });
    }

}
