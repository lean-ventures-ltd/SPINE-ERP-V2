<?php

namespace App\Repositories\Focus\invoice;

use App\Http\Controllers\Focus\cuInvoiceNumber\ControlUnitInvoiceNumberController;
use App\Models\items\InvoiceItem;
use App\Models\invoice\Invoice;
use App\Exceptions\GeneralException;
use App\Models\invoice_payment\InvoicePayment;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class InvoiceRepository.
 */
class InvoiceRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = Invoice::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        // date filter
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // project filter (project view)
        $q->when(request('project_id'), function($q) {
            $q->whereHas('quotes', function($q) {
                $q->whereHas('project', function($q) {
                    $q->where('projects.id', request('project_id'));
                });
            });
        });


        // customer and status filter
        $q->when(request('customer_id'), function ($q) {
            $q->where('customer_id', request('customer_id'));
        })->when(request('invoice_status'), function ($q) {
            $status = request('invoice_status');
            switch ($status) {
                case 'not yet due': 
                    $q->where('invoiceduedate', '>', date('Y-m-d'));
                    break;
                case 'due':    
                    $q->where('invoiceduedate', '<=', date('Y-m-d'));
                    break;                 
            }         
        })->when(request('payment_status'), function ($q) {
            $status = request('payment_status');
            switch ($status) {
                case 'unpaid':
                    $q->where('amountpaid', 0);
                    break; 
                case 'partially paid':
                    $q->whereColumn('amountpaid', '<', 'total')->where('amountpaid', '>', 0);
                    break; 
                case 'paid':
                    $q->whereColumn('amountpaid', '>=', 'total');
                    break; 
            }         
        });

        return $q;
    }

    /**
     * Create project invoice
     * @throws \Exception
     */
    public function create_project_invoice(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $bill = $input['bill'];
        $duedate = $bill['invoicedate'] . ' + ' . $bill['validity'] . ' days';
        $bill['invoiceduedate'] = date_for_database($duedate);
        foreach ($bill as $key => $val) {
            if ($key == 'invoicedate') $bill[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax', 'taxable', 'fx_curr_rate'])) {
                $bill[$key] = numberClean($val);
            }
        }


        $cuPrefix = explode('KRAMW', auth()->user()->business->etr_code)[1];
        if (empty($data['cu_invoice_no'])){

            $cuResponse =['isSet' => true,];
        }
        else {

            $setCu = explode($cuPrefix, $input['bill']['cu_invoice_no'])[1];
            $cuResponse = (new ControlUnitInvoiceNumberController())->setCuInvoiceNumber($setCu);
        }

        if (!$cuResponse['isSet']){
            DB::rollBack();
            throw new GeneralException($cuResponse['message']);
        }

        $tid = Invoice::where('ins', auth()->user()->ins)->max('tid');
        if ($bill['tid'] <= $tid) $bill['tid'] = $tid+1;
        //  forex values
        $fx_rate = $bill['fx_curr_rate'];
        if ($fx_rate > 1) {
            $bill = array_replace($bill, [
                'fx_taxable' => round($bill['taxable'] * $fx_rate, 4),
                'fx_subtotal' => round($bill['subtotal'] * $fx_rate, 4),
                'fx_tax' => round($bill['tax'] * $fx_rate, 4),
                'fx_total' => round($bill['total'] * $fx_rate, 4),
            ]);
        }
        
        $result = Invoice::create($bill);
        
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $item = array_replace($item, [
                'invoice_id' => $result->id,
                'tax_rate' => numberClean($item['tax_rate']),
                'product_tax' => floatval(str_replace(',', '', $item['product_tax'])),
                'product_price' => floatval(str_replace(',', '', $item['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $item['product_subtotal'])),
                'product_amount' => floatval(str_replace(',', '', $item['product_amount'])),
            ]);
            // forex values
            $fx_rate = $result->fx_curr_rate;
            if ($fx_rate > 1) {
                $item = array_replace($item, [
                    'fx_curr_rate' => $fx_rate,
                    'fx_product_tax' => round($item['product_tax'] * $fx_rate, 4),
                    'fx_product_price' => round($item['product_price'] * $fx_rate, 4),
                    'fx_product_subtotal' => round($item['product_subtotal'] * $fx_rate, 4),
                    'fx_product_amount' => round($item['product_amount'] * $fx_rate, 4),
                ]);
            }
            $bill_items[$k] = $item;
        }
        InvoiceItem::insert($bill_items);

        // update Quote or PI invoice status
        foreach ($result->products as $key => $item) {
            $quote = $item->quote;
            if ($quote) {
                $quote->update(['invoiced' => 'Yes']);
                if ($key == 0) $result->update(['currency_id' => $quote->currency_id]);
            }
        }
        
        /** accounting */
        $this->post_invoice($result);

        if ($result) {
            DB::commit();
            return $result;
        }
        DB::rollBack();
    }

    /**
     * Update Project Invoice
     */
    public function update_project_invoice($invoice, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $bill = $input['bill'];
        $duedate = $bill['invoicedate'] . ' + ' . $bill['validity'] . ' days';
        $bill['invoiceduedate'] = date_for_database($duedate);
        foreach ($bill as $key => $val) {
            if ($key == 'invoicedate') $bill[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax', 'taxable', 'fx_curr_rate'])) {
                $bill[$key] = numberClean($val);
            }
        }

        //  forex values
        $fx_rate = $bill['fx_curr_rate'];
        if ($fx_rate > 1) {
            $bill = array_replace($bill, [
                'fx_taxable' => round($bill['taxable'] * $fx_rate, 4),
                'fx_subtotal' => round($bill['subtotal'] * $fx_rate, 4),
                'fx_tax' => round($bill['tax'] * $fx_rate, 4),
                'fx_total' => round($bill['total'] * $fx_rate, 4),
            ]);
        }

        $invoice->update($bill);

        // update invoice items
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $item = array_replace($item, [
                'id' => $item['id'],
                'reference' => @$item['reference'] ?: '', 
                'description' => $item['description'],
                'tax_rate' => numberClean($item['tax_rate']),
                'product_tax' => floatval(str_replace(',', '', $item['product_tax'])),
                'product_price' => floatval(str_replace(',', '', $item['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $item['product_subtotal'])),
                'product_amount' => floatval(str_replace(',', '', $item['product_amount'])),
            ]);
            // forex values
            $fx_rate = $invoice->fx_curr_rate;
            if ($fx_rate > 1) {
                $item = array_replace($item, [
                    'fx_curr_rate' => $fx_rate,
                    'fx_product_tax' => round($item['product_tax'] * $fx_rate, 4),
                    'fx_product_price' => round($item['product_price'] * $fx_rate, 4),
                    'fx_product_subtotal' => round($item['product_subtotal'] * $fx_rate, 4),
                    'fx_product_amount' => round($item['product_amount'] * $fx_rate, 4),
                ]);
            }
            $bill_items[$k] = $item;
        }
        Batch::update(new InvoiceItem, $bill_items, 'id');

        // update Quote or PI invoice status
        foreach ($invoice->products as $item) {
            if ($item->quote) $item->quote->update(['invoiced' => 'Yes']);
        }

        /**accounting */
        $invoice->transactions()->delete();
        $this->post_invoice($invoice);

        if ($bill) {
            DB::commit();
            return $invoice;        
        }
        DB::rollBack();
    }

    /**
     * Delete Project Invoice
     *
     * @param Invoice $invoice
     * @return bool
     * @throws GeneralException
     */
    public function delete($invoice)
    {
        if ($invoice->payments()->exists()) {
            foreach ($invoice->payments as $key => $pmt_item) {
                $tids[] = @$pmt_item->paid_invoice->tid ?: '';
            }
            throw ValidationException::withMessages(['Invoice is linked to payments: (' . implode(', ', $tids) . ')']);
        }
            
        DB::beginTransaction();

        // pos invoice
        if ($invoice->product_expense_total > 0) {
            // reverse product qty
            foreach ($invoice->products as $item) {
                $pos_product = $item->product;
                if ($pos_product) $pos_product->decrement('qty', $item->product_qty);
            }
            // delete related payment
            InvoicePayment::whereHas('items', fn($q) => $q->where('invoice_id', $invoice->id))->delete();
        } else {
            // update Quote or PI invoice status
            foreach ($invoice->products as $item) {
                $quote = $item->quote;
                if ($quote) $quote->update(['invoiced' => 'No']);
            }
        }

        $invoice->transactions()->delete();
        $invoice->products()->delete();
        if ($invoice->delete()) {
            DB::commit();
            return true;
        }

        DB::rollBack();
    }
}
