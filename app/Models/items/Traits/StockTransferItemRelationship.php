<?php

namespace App\Models\items\Traits;

use App\Models\product\ProductVariation;
use App\Models\stock_transfer\StockTransfer;

/**
 * Class CustomerRelationship
 */
trait StockTransferItemRelationship
{
    public function stock_transfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }
}
