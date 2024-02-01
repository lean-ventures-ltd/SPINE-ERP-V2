<?php

namespace App\Models\manualjournal;

use App\Models\manualjournal\Traits\JournalAttribute;
use App\Models\manualjournal\Traits\JournalRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use ModelTrait,
        JournalAttribute,
        JournalRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'journals';

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

        static::creating(function ($instance) {
            $instance->ins = auth()->user()->ins;
            $instance->user_id = auth()->user()->id;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', auth()->user()->ins);
        });
    }
}
