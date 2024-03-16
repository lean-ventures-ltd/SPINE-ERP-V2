<?php

namespace App\Models\customer\Traits;

use App\Models\branch\Branch;
use App\Models\client_product\ClientProduct;
use App\Models\Company\Company;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\lead\Lead;
use App\Models\manualjournal\Journal;
use App\Models\project\Project;
use App\Models\quote\Quote;
use App\Models\tenant_package\TenantPackage;

/**
 * Class CustomerRelationship
 */
trait CustomerRelationship
{
    function tenant_package() {
        return $this->hasOne(TenantPackage::class);
    }

    function journal() {
        return $this->hasOne(Journal::class);
    }

    function quotes() {
        return $this->hasMany(Quote::class);
    }

    public function products()
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function client_products()
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function group()
    {
        return $this->hasMany('App\Models\customergroup\CustomerGroupEntry');
    }

    public function primary_group()
    {
        return $this->hasOne('App\Models\customergroup\CustomerGroupEntry')->oldest();
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\invoice\Invoice')->orderBy('id', 'DESC');
    }

    public function deposits()
    {
        return $this->hasMany(InvoicePayment::class);
    }
    
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
