<?php

namespace App\Models\creditnote\Traits;

use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\items\TaxReportItem;
use App\Models\transaction\Transaction;

trait CreditNoteRelationship
{
    public function credit_note_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class, 'credit_note_id');
    }

    public function debit_note_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class, 'debit_note_id');
    }

    public function debitnote_transactions()
    {
        return $this->hasMany(Transaction::class, 'dnote_id');
    }

    public function creditnote_transactions()
    {
        return $this->hasMany(Transaction::class, 'cnote_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}