<?php

namespace App\Models\quote\Traits;

use App\Models\Access\User\User;
use App\Models\customer\Customer;
use App\Models\branch\Branch;
use App\Models\currency\Currency;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\items\MetaEntry;
use App\Models\items\QuoteItem;
use App\Models\items\VerificationItem;
use App\Models\items\VerifiedItem;
use App\Models\lead\Lead;
use App\Models\lpo\Lpo;
use App\Models\project\Budget;
use App\Models\project\BudgetSkillset;
use App\Models\project\Project;
use App\Models\project\ProjectQuote;
use App\Models\projectstock\Projectstock;
use App\Models\term\Term;
use App\Models\verification\Verification;
use App\Models\verifiedjcs\VerifiedJc;

/**
 * Class QuoteRelationship
 */
trait QuoteRelationship
{
    public function project()
    {
        return $this->hasOneThrough(Project::class, ProjectQuote::class, 'quote_id', 'id', 'id', 'project_id')->withoutGlobalScopes();
    }

    public function projectstock()
    {
        return $this->hasMany(Projectstock::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_quotes', 'quote_id', 'project_id');
    }

    public function skill_items()
    {
        return $this->hasMany(BudgetSkillset::class);
    }

    public function invoice_product()
    {
        return $this->hasOne('App\Models\items\InvoiceItem')->withoutGlobalScopes();
    }

    public function invoice()
    {
        return $this->hasOneThrough(Invoice::class, InvoiceItem::class, 'quote_id', 'id', 'id', 'quote_id')->withoutGlobalScopes();
    }

    public function budget()
    {
        return $this->hasOne(Budget::class);
    }

    public function budget_skillsets()
    {
        return $this->hasManyThrough(BudgetSkillset::class, Budget::class, 'quote_id', 'budget_id', 'id', 'id');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }

    public function project_quote()
    {
        return $this->hasOne(ProjectQuote::class);
    }

    public function verified_jcs()
    {
        return $this->hasMany(VerifiedJc::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function verified_products()
    {
        return $this->hasMany(VerifiedItem::class)->orderBy('row_index', 'ASC');
    }

    public function part_verification_products()
    {
        return $this->hasManyThrough(VerificationItem::class, Verification::class, 'quote_id', 'parent_id', 'id', 'id');
    }

    public function part_verifications()
    {
        return $this->hasMany(Verification::class);
    }

    public function products()
    {
        return $this->hasMany(QuoteItem::class)->orderBy('row_index', 'ASC');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function term()
    {
        return $this->belongsTo(Term::class)->withoutGlobalScopes();
    }

    public function attachment()
    {
        return $this->hasMany(MetaEntry::class, 'rel_id')->where('rel_type', '=', 4)->withoutGlobalScopes();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function client()
    {
        return $this->hasOneThrough(Customer::class, Lead::class, 'id', 'id', 'lead_id', 'client_id')->withoutGlobalScopes();
    }

    public function branch()
    {
        return $this->hasOneThrough(Branch::class, Lead::class, 'id', 'id', 'lead_id', 'branch_id')->withoutGlobalScopes();
    }

    public function lead()
    {
        return $this->hasOne(Lead::class, 'id', 'lead_id')->withoutGlobalScopes();
    }
}