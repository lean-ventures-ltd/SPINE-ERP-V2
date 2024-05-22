<?php

namespace App\Models\stock_issue\Traits;

use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\project\Project;
use App\Models\quote\Quote;
use App\Models\stock_issue\StockIssueItem;
use App\Models\transaction\Transaction;

trait StockIssueRelationship
{    
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'stock_issue_id');
    }

    public function employee()
    {
        return $this->belongsTo(Hrm::class, 'employee_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(StockIssueItem::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'id');
    }

}
