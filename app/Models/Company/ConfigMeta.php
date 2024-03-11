<?php

namespace App\Models\Company;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company\Traits\ConfigMetaRelationship;


class ConfigMeta extends Model
{
    /**
     * The database table used by the model.
     * @var string
     */
    use ModelTrait, ConfigMetaRelationship {
        // InvoiceAttribute::getEditButtonAttribute insteadof ModelTrait;
    }
    protected $table = 'config_meta';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [

    ];

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
    }
}
