<?php

namespace App\Models\stock_adj\Traits;

use App\Models\product\ProductVariation;

trait StockAdjItemRelationship
{    
    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }
}
