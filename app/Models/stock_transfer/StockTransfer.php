<?php

namespace App\Models\stock_transfer;

use App\Models\ModelTrait;
use App\Models\stock_transfer\Traits\StockTransferAttribute;
use App\Models\stock_transfer\Traits\StockTransferRelationship;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use ModelTrait, StockTransferAttribute, StockTransferRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'stock_transfers';

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
            $instance->fill( [
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
                'tid' => StockTransfer::getTid()+1,
            ]);
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', auth()->user()->ins);
        });
    }

    static function getTid()
    {
        $ins = auth()->user()->ins;
        return StockTransfer::where('ins', $ins)->max('tid');
    }
}
