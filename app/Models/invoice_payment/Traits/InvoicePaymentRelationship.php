<?php

namespace App\Models\invoice_payment\Traits;

use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\items\InvoicePaymentItem;
use App\Models\reconciliation\ReconciliationItem;
use App\Models\transaction\Transaction;

trait InvoicePaymentRelationship
{
    public function reconciliation_items()
    {
        return $this->hasMany(ReconciliationItem::class, 'deposit_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'deposit_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoicePaymentItem::class, 'paidinvoice_id');
    }
}
