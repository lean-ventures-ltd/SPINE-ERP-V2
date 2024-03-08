<?php

namespace App\Models\items\Traits;

use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\PurchaseorderItem;
use App\Models\product\ProductVariation;
use App\Models\project\Project;
use App\Models\supplier_product\SupplierProduct;
use App\Models\transaction\Transaction;

trait GoodsreceivenoteItemRelationship
{
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'grn_id');
    }

    public function supplier_product()
    {
        return $this->belongsTo(SupplierProduct::class, 'item_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'itemproject_id');
    }

    public function goodsreceivenote()
    {
        return $this->belongsTo(Goodsreceivenote::class, 'goods_receive_note_id');
    }

    public function purchaseorder_item()
    {
        return $this->belongsTo(PurchaseorderItem::class, 'purchaseorder_item_id');
    }

    public function productvariation()
    {
        return $this->belongsTo(ProductVariation::class, 'item_id');
    }
}
