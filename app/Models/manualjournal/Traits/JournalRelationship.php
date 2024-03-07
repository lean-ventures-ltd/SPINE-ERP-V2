<?php

namespace App\Models\manualjournal\Traits;

use App\Models\account\Account;
use App\Models\items\JournalItem;
use App\Models\reconciliation\ReconciliationItem;
use App\Models\transaction\Transaction;

trait JournalRelationship
{
    public function reconciliation_items()
    {
        return $this->hasMany(ReconciliationItem::class, 'man_journal_id');
    }
    
    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_id');
    }

    public function ledger_account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'man_journal_id');
    }
}
