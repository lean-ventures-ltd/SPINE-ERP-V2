<?php

namespace App\Models\PurchaseClass;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseClass extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        // Add other fillable fields as needed
    ];

}
