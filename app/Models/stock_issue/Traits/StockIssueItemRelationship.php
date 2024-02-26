<?php

namespace App\Models\stock_issue\Traits;

use App\Models\hrm\Hrm;
use App\Models\product\ProductVariation;
use App\Models\stock_issue\StockIssue;
use App\Models\warehouse\Warehouse;

trait StockIssueItemRelationship
{    
    public function stock_issue()
    {
        return $this->belongsTo(StockIssue::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Hrm::class, 'assignee_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }
}
