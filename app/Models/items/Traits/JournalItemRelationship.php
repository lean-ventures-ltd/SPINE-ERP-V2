<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;
use App\Models\manualjournal\Journal;
use App\Models\reconciliation\ReconciliationItem;

trait JournalItemRelationship
{
    public function reconciliation_items()
    {
        return $this->hasMany(ReconciliationItem::class, 'journal_item_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
