<?php

namespace App\Models\product\Traits;

use App\Models\items\GoodsreceivenoteItem;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\productvariable\Productvariable;

/**
 * Class ProductRelationship
 */
trait ProductRelationship
{
    public function grn_item()
    {
        return $this->hasOne(GoodsreceivenoteItem::class, 'item_id');
    }

    public function units()
    {
        return $this->belongsToMany(Productvariable::class, 'product_unit', 'product_id', 'product_variable_id');
    }

    public function unit()
    {
        return $this->belongsTo(Productvariable::class, 'unit_id');
    }

    public function standard()
    {
        return $this->hasOne(ProductVariation::class, 'parent_id')->withoutGlobalScopes();
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'parent_id')->withoutGlobalScopes();
    }

    public function category()
    {
        return $this->hasOne(Productcategory::class, 'id', 'productcategory_id')->withoutGlobalScopes();
    }

    public function subcategory()
    {
        return $this->hasOne(Productcategory::class, 'id', 'sub_cat_id');
    }

    public function record()
    {
        return $this->hasMany(ProductVariation::class);
    }
    
    public function record_one()
    {
        return $this->hasOne(ProductVariation::class);
    }
}
