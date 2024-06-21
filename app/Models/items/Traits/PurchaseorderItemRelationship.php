<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;
use App\Models\equipment\Assetequipment;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\product\ProductVariation;
use App\Models\project\Project;
use App\Models\purchaseorder\Purchaseorder;

trait PurchaseorderItemRelationship
{
    public function purchaseorder()
    {
        return $this->belongsTo(Purchaseorder::class, 'purchaseorder_id');
    }

    public function grn_items()
    {
        return $this->hasMany(GoodsreceivenoteItem::class, 'poitem_id');
    }

    public function asset()
    {
        return $this->belongsTo(Assetequipment::class, 'item_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'item_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'itemproject_id');
    }

    public function productvariation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'product_id')->withoutGlobalScopes();
    }
}
