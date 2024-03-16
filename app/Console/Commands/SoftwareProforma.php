<?php

namespace App\Console\Commands;

use App\Models\additional\Additional;
use App\Models\bank\Bank;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\items\QuoteItem;
use App\Models\quote\Quote;
use App\Models\term\Term;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class SoftwareProforma extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'software-proforma:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Software Proforma Invoice';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            $proforma_ids = [];
            $ins = Company::where('is_main', 1)->first(['id'])->id;
            $customers = Customer::withoutGlobalScopes()->where('ins', $ins)->whereHas('tenant_package')->get();
            foreach ($customers as $key => $customer) {
                $tenant_package = $customer->tenant_package;
                if ($tenant_package->status != 'Active') continue;

                $date = Carbon::parse($tenant_package->date);
                $first_proforma = $customer->quotes()->where('bank_id', '>', 0)
                    ->where('total', $tenant_package->total_cost)
                    ->orderBy('id', 'DESC')->first();
                if ($first_proforma) {
                    $last_pay = $customer->deposits()->orderBy('id', 'DESC')->first();
                    if (!$last_pay) continue;
                    $date = Carbon::parse($last_pay->next_date);
                    if ($last_pay && $date->ne(Carbon::today())) continue;
                } 
                
                $bank = Bank::withoutGlobalScopes()->where('ins', $ins)->where('enable', 'yes')->first();
                $tax = Additional::withoutGlobalScopes()->where('ins', $ins)->where('value', 0)->first();
                $term = Term::withoutGlobalScopes()->where('ins', $ins)->where('title', 'LIKE', '%No Terms%')->first();
                $currency = Currency::withoutGlobalScopes()->where('ins', $ins)->where('code', 'KES')->first();
                                
                $tid = Quote::where('ins', $ins)->where('bank_id', '>', 0)->max('tid')+1;
                $amount = $first_proforma? round($tenant_package->maintenance_cost) : round($tenant_package->total_cost);
                $note = $first_proforma? 'Maintenance Service' : 'Software Service Package';

                $quote = Quote::create([
                    'tid' => $tid,
                    'date' => $date->format('Y-m-d'),
                    'taxable' => $amount,
                    'subtotal' => $amount,
                    'total' => $amount,
                    'notes' => $note,
                    'customer_id' => $customer->id,
                    'tax_id' => $tax->value, 
                    'term_id' => $term->id,
                    'currency_id' => $currency->id,
                    'quote_type' => 'standard',
                    'bank_id' => $bank->id,
                    'user_id' => $customer->user_id,
                    'ins' => $customer->ins,
                ]);
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_name' => $note,
                    'product_qty' => 1,
                    'product_price' => $amount,
                    'product_subtotal' => $amount,
                    'product_amount' => $amount,
                    'unit' => 'ITEM',
                    'numbering' => 1,
                    'a_type' => 1,
                    'ins' => $customer->ins,
                ]);
                $proforma_ids[] = $quote->id;
            }

            DB::commit();            
            $this->info(now() .' Success! Proforma Ids generated: ' . implode(',', $proforma_ids));
        } catch (\Throwable $th) {
            $this->error(now() .' '. $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
        }
    }
}
