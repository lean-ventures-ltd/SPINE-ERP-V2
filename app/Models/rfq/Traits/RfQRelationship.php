<?php

namespace App\Models\rfq\Traits;

use App\Models\rfq\RfQItem;

trait RfQRelationship
{
    public function products()
    {
        return $this->hasMany(RfQItem::class, 'rfq_id')->withoutGlobalScopes();
    }
}
