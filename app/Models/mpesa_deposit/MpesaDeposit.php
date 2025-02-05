<?php

namespace App\Models\mpesa_deposit;

use App\Models\ModelTrait;
use App\Models\mpesa_deposit\Traits\DarajaAttribute;
use App\Models\mpesa_deposit\Traits\MpesaDepositAttribute;
use App\Models\mpesa_deposit\Traits\MpesaDepositRelationship;
use Illuminate\Database\Eloquent\Model;

class MpesaDeposit extends Model
{
    use ModelTrait, MpesaDepositAttribute, MpesaDepositRelationship, DarajaAttribute;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'mpesa_deposits';

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
}
