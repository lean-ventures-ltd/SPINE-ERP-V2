<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;
use App\Models\manualjournal\Journal;

trait JournalItemRelationship
{
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
