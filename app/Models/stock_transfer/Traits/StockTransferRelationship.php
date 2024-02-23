<?php

namespace App\Models\stock_transfer\Traits;

use App\Models\items\StockTransferItem;
use App\Models\stock_rcv\StockRcv;
use App\Models\warehouse\Warehouse;

trait StockTransferRelationship
{
    public function stock_rcvs()
    {
        return $this->hasMany(StockRcv::class);
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function source()
    {
        return $this->belongsTo(Warehouse::class, 'source_id');
    }

    public function destination()
    {
        return $this->belongsTo(Warehouse::class, 'dest_id');
    }
}
