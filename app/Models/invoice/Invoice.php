<?php

namespace App\Models\invoice;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\invoice\Traits\InvoiceAttribute;
use App\Models\invoice\Traits\InvoiceRelationship;

class Invoice extends Model
{
    use ModelTrait,
        InvoiceAttribute,
        InvoiceRelationship {
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'invoices';

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
            $builder->where('ins', auth()->user()->ins);
        });

        static::creating(function ($instance) {
            $instance->fill([
                'user_id' => auth()->user()->id,
                'ins' => auth()->user()->ins,
                'tid' => Invoice::getTid() + 1,
            ]);
            return $instance;
        });
    }

    static function getTid()
    {
        return Invoice::where('ins', auth()->user()->ins)->max('tid');
    }
}
