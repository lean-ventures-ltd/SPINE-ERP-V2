<?php

namespace App\Models\job_valuation\Traits;

use App\Models\items\VerifiedItem;
use App\Models\product\ProductVariation;

trait JobValuationItemRelationship
{    
    public function verified_item()
    {
        return $this->belongsTo(VerifiedItem::class, 'verified_item_id');
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }
}
