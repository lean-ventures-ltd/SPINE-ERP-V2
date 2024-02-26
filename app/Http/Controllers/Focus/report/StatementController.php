<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\report;

use App\Http\Responses\RedirectResponse;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\items\PurchaseItem;
use App\Models\items\Register;
use App\Models\product\ProductMeta;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Models\warehouse\Warehouse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\report\ManageReports;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\OpeningStockItem;
use App\Models\items\StockTransferItem;
use App\Models\stock_adj\StockAdjItem;
use App\Models\stock_issue\StockIssueItem;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class StatementController extends Controller
{
    public function index(ManageReports $reports)
    {
    }

    public function account(ManageReports $reports)
    {
    }

    /**
     * Show the Form for Generating Statement
     */
    public function statement(ManageReports $reports)
    {
        switch ($reports->section) {
            case 'account':
                $lang['title'] = trans('meta.account_statement');
                $lang['module'] = 'account_statement';
                $accounts = Account::all();
                return view('focus.report.general_statement', compact('accounts', 'lang'));
                break;
            case 'income':
                $lang['title'] = trans('meta.income_statement');
                $lang['module'] = 'income_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'expense':
                $lang['title'] = trans('meta.expense_statement');
                $lang['module'] = 'expense_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'customer':
                $lang['title'] = trans('meta.customer_statement');
                $lang['module'] = 'customer_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'supplier':
                $lang['title'] = trans('meta.supplier_statement');
                $lang['module'] = 'supplier_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'tax':
                $lang['title'] = trans('meta.tax_statement');
                $lang['module'] = 'tax_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'stock_transfer':
                $lang['title'] = trans('meta.stock_transfer_statement_warehouse');
                $lang['module'] = 'stock_transfer_statement';
                $warehouses = Warehouse::all();
                return view('focus.report.general_statement', compact('warehouses', 'lang'));
                break;
            case 'stock_transfer_product':
                $lang['title'] = trans('meta.stock_transfer_statement_product');
                $lang['module'] = 'product_transfer_statement';
                $warehouses = Warehouse::all();
                return view('focus.report.general_statement', compact('warehouses', 'lang'));
                break;
            case 'product_statement':
                $lang['title'] = trans('meta.stock_transfer_statement_product');
                $lang['module'] = 'product_statement';
                $warehouses = Warehouse::all();
                return view('focus.report.general_statement', compact('warehouses', 'lang'));
                break;
            case 'product_movement_statement':
                $lang['title'] = 'Product Movement Statement';
                $lang['module'] = 'product_movement_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'product_category_statement':
                $lang['title'] = trans('meta.product_category_statement');
                $lang['module'] = 'product_category_statement';
                $product_categories = Productcategory::all();
                return view('focus.report.general_statement', compact('product_categories', 'lang'));
                break;
            case 'product_warehouse_statement':
                $lang['title'] = trans('meta.product_warehouse_statement');
                $lang['module'] = 'product_warehouse_statement';
                $warehouses = Warehouse::all();
                return view('focus.report.general_statement', compact('warehouses', 'lang'));
                break;
            case 'product_customer_statement':
                $lang['title'] = trans('meta.product_customer_statement');
                $lang['module'] = 'product_customer_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
            case 'product_supplier_statement':
                $lang['title'] = trans('meta.product_supplier_statement');
                $lang['module'] = 'product_supplier_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;

            case 'pos_statement':
                $lang['title'] = trans('meta.pos_statement');
                $lang['module'] = 'pos_statement';
                return view('focus.report.general_statement', compact('lang'));
                break;
        }
    }

    public function generate_statement(ManageReports $reports)
    {

        switch ($reports->section) {
            case 'account':
                if (!$reports->account) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $account_details = Account::where('id', '=', $reports->account)->first();
                $lang['title'] = trans('meta.account_statement');
                $lang['title2'] = trans('accounts.account');
                $lang['module'] = 'account_statement';
                $lang['party'] = $account_details->holder . ' (' . trans('accounts.' . $account_details->account_type) . ')' . '<br>' . $account_details->number . '<br>' . $account_details->type;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $account_details->holder);
                break;

            case 'income':
                $account_details = Account::where('id', '=', $reports->account)->first();
                $lang['title'] = trans('meta.income_statement');
                $lang['title2'] = trans('meta.income_statement');
                $lang['module'] = 'income_statement';
                $default_category = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 8)->first('feature_value');
                $category = Transactioncategory::find($default_category['feature_value']);
                $lang['party'] = $category->name;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $category->name);
                $transactions = Transaction::whereBetween('payment_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->where('trans_category_id', '=', $category->id)->get();

                break;
            case 'expenses':
                $account_details = Account::where('id', '=', $reports->account)->first();
                $lang['title'] = trans('meta.expense_statement');
                $lang['title2'] = trans('meta.expense_statement');
                $lang['module'] = 'expense_statement';
                $default_category = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 10)->first('feature_value');
                $category = Transactioncategory::find($default_category['feature_value']);
                $lang['party'] = $category->name;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $category->name);
                $transactions = Transaction::whereBetween('payment_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->where('trans_category_id', '=', $category->id)->get();

                break;

            case 'customer':
                if (!$reports->account) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $account_details = Customer::where('id', '=', $reports->account)->first();
                $lang['title'] = trans('meta.customer_statement');
                $lang['title2'] = trans('customers.customer');
                $lang['module'] = 'customer_statement';
                $lang['party'] = $account_details->name . '<br>' . $account_details->email . '<br>' . $account_details->phone;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $account_details->name);
                break;
            case 'supplier':
                if (!$reports->account) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $account_details = Supplier::where('id', '=', $reports->account)->first();
                $lang['title'] = trans('meta.supplier_statement');
                $lang['title2'] = trans('suppliers.supplier');
                $lang['module'] = 'supplier_statement';
                $lang['party'] = $account_details->name . '<br>' . $account_details->email . '<br>' . $account_details->phone;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $account_details->name);
                break;
        }

        switch ($reports->trans_type) {
            case 'credit':
                $transactions = $account_details->transactions->whereBetween('payment_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->where('credit', '>', 0);
                break;
            case 'debit':
                $transactions = $account_details->transactions->whereBetween('payment_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->where('debit', '>', 0);
                break;
            case 'all':
                $transactions = $account_details->transactions->whereBetween('payment_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                break;
        }


        switch ($reports->output_format) {

            case 'pdf_print':
                $html = view('focus.report.pdf.account', compact('account_details', 'transactions', 'lang'))->render();
                $headers = array(
                    "Content-type" => "application/pdf",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                break;
            case 'pdf':
                $html = view('focus.report.pdf.account', compact('account_details', 'transactions', 'lang'))->render();
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return $pdf->Output($file_name . '.pdf', 'D');
                break;
            case 'csv':
                $headers = array(
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=$file_name.csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $columns = array(trans('transactions.payment_date'), trans('general.description'), trans('transactions.debit'), trans('transactions.credit'), trans('accounts.balance'));
                $callback = function () use ($transactions, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    $balance = 0;

                    foreach ($transactions as $row) {
                        $balance += $row['credit'] - $row['debit'];
                        fputcsv($file, array(dateFormat($row['payment_date']), $row['note'], amountFormat($row['debit']), amountFormat($row['credit']), amountFormat($balance)));
                    }
                    fclose($file);
                };
                return Response::stream($callback, 200, $headers);
                break;
        }
    }

    public function generate_tax_statement(ManageReports $reports)
    {
        if (!$reports->from_date)
            return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);

        switch ($reports->tax_type) {
            case 'tax_sales':
                $account_details = Transaction::whereHas('account', function ($q) {
                    $q->where('system', 'tax');
                })
                    ->where('tr_type', 'inv')
                    ->where('credit', '>', 0)
                    ->whereBetween('tr_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])
                    ->get();

                $lang['title'] = trans('meta.tax_statement');
                $lang['title2'] = trans('meta.tax_statement');
                $lang['module'] = 'tax_statement';
                $lang['party'] = config('core.cname');
                $lang['party_2'] = trans('customers.customer');
                $lang['type'] = 1;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'tax_purchase':
                $account_details = Transaction::whereHas('account', function ($q) {
                    $q->where('system', 'tax');
                })
                    ->where('tr_type', 'bill')
                    ->where('debit', '>', 0)
                    ->whereBetween('tr_date', [date_for_database($reports->from_date), date_for_database($reports->to_date)])
                    ->get();

                $lang['title'] = trans('meta.tax_statement_purchase');
                $lang['title2'] = trans('meta.tax_statement_purchase');
                $lang['module'] = 'tax_statement';
                $lang['party'] = config('core.cname');
                $lang['party_2'] = trans('suppliers.supplier');
                $lang['type'] = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
        }


        switch ($reports->output_format) {
            case 'pdf_print':
                $html = view('focus.report.pdf.tax', compact('account_details', 'lang'))->render();
                $headers = array(
                    "Content-type" => "application/pdf",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                break;
            case 'pdf':
                $html = view('focus.report.pdf.tax', compact('account_details', 'lang'))->render();
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return $pdf->Output($file_name . '.pdf', 'D');
                break;

            case 'csv':
                $headers = array(
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=$file_name.csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $columns = array(trans('general.date'), trans('orders.order'), trans('customers.customer'), trans('general.total'), trans('general.tax'), trans('accounts.balance'));
                $callback = function () use ($account_details, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    $balance = 0;

                    foreach ($account_details as $row) {
                        $balance += $row['tax'];
                        fputcsv($file, array(dateFormat($row['invoicedate']), $row['tid'], $row->customer->name, amountFormat($row['total']), amountFormat($row['tax']), amountFormat($balance)));
                    }
                    fclose($file);
                };
                return Response::stream($callback, 200, $headers);
                break;
        }
    }

    /**
     * Generate Stock Statement Report Templates
     */
    public function generate_stock_statement(ManageReports $reports)
    {  
        switch ($reports->stock_action) {
            case 'warehouse':
                $reports->validate(['from_warehouse' => 'required']);

                $account_details = StockTransferItem::whereHas('stock_transfer', function($q) use($reports) {
                    $q->whereBetween('date', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)]);
                    $q->when($reports->from_warehouse > 0, fn($q) => $q->where('source_id', $reports->from_warehouse));
                    $q->when($reports->to_warehouse > 0, fn($q) => $q->where('dest_id', $reports->to_warehouse));
                })
                ->with('rcv_items')
                ->get();
                foreach ($account_details as $i => $item) {
                    $item['qty_rcv'] = $item->rcv_items->sum('qty_rcv');
                    $item['qty_onhand'] = $item->qty_transf - $item->qty_rcv;
                    $item['amount'] = $item->qty_onhand * $item->cost;
                    $account_details[$i] = $item;
                }

                $lang['from_date'] = $reports->from_date;
                $lang['to_date'] = $reports->to_date;
                $lang['title'] = trans('meta.stock_transfer_statement');
                $lang['module'] = 'warehouse';
                $transfer = 1;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product':
                if (!$reports->product_name) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $account_details = ProductMeta::where('rel_id', '=', $reports->product_name)->where('rel_type', '=', 1)->when($reports->to_warehouse != 'all', function ($q) use ($reports) {
                    return $q->where('value2', '=', $reports->to_warehouse);
                })->whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->get();
                $lang['title'] = trans('meta.stock_transfer_statement');
                $lang['title2'] = trans('meta.stock_transfer_statement_product');
                $lang['module'] = 'product';
                $lang['party'] = config('core.cname');
                $lang['party_2'] = trans('customers.customer');
                $transfer = 1;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;

            case 'product_statement':
                if (!$reports->product_name) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                if ($reports->type_p == 'sales') {
                    $lang['title2'] = trans('meta.product_statement_sales');

                    $account_details = InvoiceItem::where('product_id', '=', $reports->product_name)->whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->get();
                } elseif ($reports->type_p == 'purchase') {
                    $lang['title2'] = trans('meta.product_statement_purchase');
                    $account_details = PurchaseItem::where('product_id', '=', $reports->product_name)->whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->get();
                }
                $product = ProductVariation::where('id', '=', $reports->product_name)->first();
                $lang['title'] = trans('meta.product_statement');

                $lang['module'] = 'product_statement';
                $lang['party'] = $product->product['name'] . ' ' . $product['name'];
                $lang['party_2'] = trans('products.product');
                $transfer = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product_movement_statement':
                $reports->validate(['from_date' => 'required', 'to_date' => 'required']);

                $account_details = collect();
                $item_id = $reports->product_id;
                if ($item_id > 0) {
                    $item_id = $reports->product_id;
                    $productvar = ProductVariation::where('id', $item_id)->with('warehouse')->first(['id', 'name', 'purchase_price', 'warehouse_id']);
                    $struct = [
                        'date' => '',
                        'location' => @$productvar->warehouse->title,
                        'name' => $productvar->name,
                        'type' => '',
                        'qty' => 0,
                        'avg_cost' => $productvar->purchase_price,
                    ];

                    $op_stock_item = OpeningStockItem::where('productvar_id', $item_id)
                    ->whereHas('opening_stock', function($q) use($reports) {
                        $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                    })->first();
                    if ($op_stock_item) {
                        $struct_rep = array_replace($struct, [
                            'date' => @$op_stock_item->opening_stock->date,
                            'type' => 'opening-stock',
                            'qty' => $op_stock_item->qty,
                        ]);
                        $account_details->add((object) $struct_rep);
                    }

                    $dir_purchase_items = PurchaseItem::where('item_id', $item_id)->where('type', 'Stock')
                    ->whereHas('purchase', function($q) use($reports) {
                        $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                    })->get();
                    foreach ($dir_purchase_items as $item) {
                        $struct_rep = array_replace($struct, [
                            'date' => @$item->purchase->date,
                            'type' => 'direct-purch',
                            'qty' => $item->qty,
                        ]);
                        $account_details->add((object) $struct_rep);
                    }
                    $grn_items = GoodsreceivenoteItem::whereHas('purchaseorder_item', fn($q) => $q->where('item_id', $item_id))
                    ->whereHas('goodsreceivenote', function($q) use($reports) {
                        $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                    })->get();
                    foreach ($grn_items as $item) {
                        $struct_rep = array_replace($struct, [
                            'date' => @$item->goodsreceivenote->date,
                            'type' => 'grn',
                            'qty' => $item->qty,
                        ]);
                        $account_details->add((object) $struct_rep);
                    }
                    $adj_items = StockAdjItem::where('productvar_id', $item_id)
                    ->whereHas('stock_adj', function($q) use($reports) {
                        $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                    })->get();
                    foreach ($adj_items as $item) {
                        $struct_rep = array_replace($struct, [
                            'date' => @$item->stock_adj->date,
                            'type' => $item->qty_diff > 0? '(+)stock-adj' : '(-)stock-adj',
                            'qty' => $item->qty_diff,
                        ]);
                        $account_details->add((object) $struct_rep);
                    }
                    $issue_items = StockIssueItem::where('productvar_id', $item_id)
                    ->whereHas('stock_issue', function($q) use($reports) {
                        $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                    })->get();
                    foreach ($issue_items as $item) {
                        $struct_rep = array_replace($struct, [
                            'date' => @$item->stock_issue->date,
                            'type' => 'stock-issue',
                            'qty' => -$item->issue_qty,
                        ]);
                        $account_details->add((object) $struct_rep);
                    }
                    $account_details = $account_details->sortBy('date');
                    $qty_onhand = 0;
                    foreach ($account_details as $i => $item) {
                        if ($i == 0) {
                            $item->qty_onhand = $item->qty;
                            $qty_onhand = $item->qty;
                        } else {
                            $qty_onhand += $item->qty;
                            $item->qty_onhand = $qty_onhand;
                        }
                        $item->amount = $item->qty_onhand * $item->avg_cost;
                        $account_details[$i] = $item;
                    }
                } else {
                    $productvars = ProductVariation::with('warehouse')
                    ->orderBy('warehouse_id', 'ASC')
                    ->get(['id', 'name', 'purchase_price', 'warehouse_id']);
                    foreach ($productvars as $key => $item) {
                        $productvars[$key]['op_stock_qty'] = OpeningStockItem::where('productvar_id', $item->id)
                        ->whereHas('opening_stock', function($q) use($reports) {
                            $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                        })->sum('qty');
                        
                        // qty in
                        $dir_purchase_qty = PurchaseItem::where('item_id', $item->id)->where('type', 'Stock')
                        ->whereHas('purchase', function($q) use($reports) {
                            $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                        })->sum('qty');
                        
                        $grn_qty = GoodsreceivenoteItem::whereHas('purchaseorder_item', fn($q) => $q->where('item_id', $item->id))
                        ->whereHas('goodsreceivenote', function($q) use($reports) {
                            $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                        })->sum('qty');
                        
                        $pos_adj_qty = StockAdjItem::where('productvar_id', $item->id)->where('qty_diff', '>', 0)
                        ->whereHas('stock_adj', function($q) use($reports) {
                            $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                        })->sum('qty_diff');
                        
                        // qty out
                        $neg_adj_qty = StockAdjItem::where('productvar_id', $item->id)->where('qty_diff', '<', 0)
                        ->whereHas('stock_adj', function($q) use($reports) {
                            $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                        })->sum('qty_diff');
                        
                        $issue_qty = StockIssueItem::where('productvar_id', $item->id)
                        ->whereHas('stock_issue', function($q) use($reports) {
                            $q->whereBetween('date', [date_for_database($reports->from_date), date_for_database($reports->to_date)]);
                        })->sum('issue_qty');
                        
                        $productvars[$key]['qty_in'] = $dir_purchase_qty + $grn_qty + $pos_adj_qty;
                        $productvars[$key]['qty_out'] = $issue_qty + -$neg_adj_qty;
                        $productvars[$key]['qty_onhand'] = $productvars[$key]['op_stock_qty'] + $productvars[$key]['qty_in'] - $productvars[$key]['qty_out'];
                        $productvars[$key]['avg_cost'] = $item['purchase_price'];
                        $productvars[$key]['amount'] = $productvars[$key]['qty_onhand'] * $item['purchase_price'];
                    }
                    $account_details = $productvars;
                }

                $lang['title'] = 'Product Movement Statement';
                $lang['module'] = 'warehouse';
                $lang['from_date'] = $reports->from_date;
                $lang['to_date'] = $reports->to_date;
                $transfer = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product_category_statement':
                if (!$reports->product_category) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $cat_id = $reports->product_category;
                if ($reports->type_p == 'sales') {
                    $lang['title2'] = trans('meta.product_statement_sales');
                    $account_details = InvoiceItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->whereHas('variation', function ($q) use ($cat_id) {
                        return $q->whereHas('product', function ($q) use ($cat_id) {
                            return $q->where('productcategory_id', '=', $cat_id);
                        });
                    })->get();
                } elseif ($reports->type_p == 'purchase') {
                    $lang['title2'] = trans('meta.product_statement_purchase');
                    $account_details = PurchaseItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->whereHas('variation', function ($q) use ($cat_id) {
                        return $q->whereHas('product', function ($q) use ($cat_id) {
                            return $q->where('productcategory_id', '=', $cat_id);
                        });
                    })->get();
                }
                $product = Productcategory::where('id', '=', $reports->product_category)->first();
                $lang['title'] = trans('meta.product_category_statement');
                $lang['module'] = 'product_statement';
                $lang['party'] = $product['title'];
                $lang['party_2'] = trans('products.product');
                $transfer = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;

            case 'product_warehouse_statement':
                if (!$reports->warehouse) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);
                $cat_id = $reports->warehouse;
                if ($reports->type_p == 'sales') {
                    $account_details = InvoiceItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->whereHas('variation', function ($q) use ($cat_id) {
                        return $q->where('warehouse_id', '=', $cat_id);
                    })->get();
                    $lang['title2'] = trans('meta.product_statement_sales');
                } elseif ($reports->type_p == 'purchase') {
                    $account_details = PurchaseItem::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->whereHas('variation', function ($q) use ($cat_id) {
                        return $q->where('warehouse_id', '=', $cat_id);
                    })->get();
                    $lang['title2'] = trans('meta.product_statement_purchase');
                }
                $product = Productcategory::where('id', '=', $reports->product_category)->first();
                $lang['title'] = trans('meta.product_warehouse_statement');
                $lang['module'] = 'product_statement';
                $lang['party'] = $product['title'];
                $lang['party_2'] = trans('products.product');
                $transfer = 2;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product_customer_statement':
                if (!$reports->person) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);

                if ($reports->type_p == 'sales') {
                    $account_details = Invoice::where('customer_id', '=', $reports->person)->whereBetween('invoicedate', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->with('products')->get()->pluck('products');
                    $lang['title2'] = trans('customers.customer');
                    $lang['title'] = trans('meta.product_customer_statement');
                    $customer = Customer::find($reports->person)->first();
                    $lang['party'] = $customer->name;
                }

                $lang['module'] = 'product_statement';
                $lang['party_2'] = trans('products.product');
                $transfer = 3;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
            case 'product_supplier_statement':
                if (!$reports->person) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);


                $account_details = Purchaseorder::whereBetween('created_at', [datetime_for_database($reports->from_date), datetime_for_database($reports->to_date)])->with('products')->get()->pluck('products');
                $lang['title2'] = trans('suppliers.supplier');
                $lang['title'] = trans('meta.product_supplier_statement');
                $supplier = Supplier::find($reports->person)->first();
                $lang['party'] = $supplier->name;


                $lang['module'] = 'product_statement';
                $lang['party_2'] = trans('products.product');
                $transfer = 3;
                $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);
                break;
        }

        // product movement statement
        if ($transfer == 2 && $reports->stock_action == 'product_movement_statement') {
            switch ($reports->output_format) {
                case 'pdf_print':
                    $html = view($item_id > 0? 'focus.report.pdf.product_stock_movement' : 'focus.report.pdf.stock_movement', compact('account_details', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                case 'pdf':
                    $html = view($item_id > 0? 'focus.report.pdf.product_stock_movement' : 'focus.report.pdf.stock_movement', compact('account_details', 'lang'))->render();
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return $pdf->Output($file_name . '.pdf', 'D');
                case 'csv':
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=$file_name.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $callback = function() use ($account_details, $item_id) {
                        $file = fopen('php://output', 'w');
                        if ($item_id > 0) {
                            fputcsv($file, ['Date', 'Type', 'Product', 'Location', 'Qty', 'On-Hand', 'Avg Cost', 'Asset Value']);
                            foreach ($account_details as $item) {
                                fputcsv($file, [
                                    $item->date? dateFormat($item->date) : 'NULL',
                                    $item->type,
                                    $item->name,
                                    $item->location,
                                    $item->qty,
                                    $item->qty_onhand,
                                    numberFormat($item->avg_cost),
                                    numberFormat($item->amount),
                                ]);
                            }
                        } else {
                            fputcsv($file, ['Location', 'Product', 'Opening Qty', 'Qty In', 'Qty Out', 'Qty On-Hand', 'Avg Cost', 'Asset Value']);
                            foreach ($account_details as $item) {
                                fputcsv($file, [
                                    @$item->warehouse->title? $item->warehouse->title : 'NULL',
                                    $item->name,
                                    +$item->op_stock_qty,
                                    +$item->qty_in,
                                    +$item->qty_out,
                                    +$item->qty_onhand,
                                    numberFormat($item->avg_cost),
                                    numberFormat($item->amount),
                                ]);
                            }
                        }
                        fclose($file);
                    };
                    return Response::stream($callback, 200, $headers);
            }
        }

        if ($transfer == 1) {
            switch ($reports->output_format) {
                case 'pdf_print':
                    $html = view('focus.report.pdf.stock_transfer', compact('account_details', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                case 'pdf':
                    $html = view('focus.report.pdf.stock_transfer', compact('account_details', 'lang'))->render();
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return $pdf->Output($file_name . '.pdf', 'D');
                case 'csv':
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=$file_name.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $callback = function() use ($account_details) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, [trans('general.date'), trans('products.product'), 'From Location', 'Qty Out', 'To Destination', 'Qty In', 'On Hand', 'Asset Value']);
                        foreach ($account_details as $item) {
                            fputcsv($file, [
                                $item->stock_transfer? dateFormat($item->stock_transfer->date) : 'NULL',
                                @$item->productvar->name ?: 'NULL',
                                @$item->stock_transfer->source->title ?: 'NULL',
                                +$item->qty_transf,
                                @$item->stock_transfer->destination->title ?: 'NULL',
                                +$item->qty_rcv,
                                +$item->qty_onhand,
                                numberFormat($item->amount),
                            ]);
                        }
                        fclose($file);
                    };
                    return Response::stream($callback, 200, $headers);
            }
        }
        if ($transfer == 2) {
            switch ($reports->output_format) {
                case 'pdf_print':
                    $html = view('focus.report.pdf.product', compact('account_details', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                case 'pdf':
                    $html = view('focus.report.pdf.product', compact('account_details', 'lang'))->render();
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return $pdf->Output($file_name . '.pdf', 'D');
                case 'csv':
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=$file_name.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $columns = array(trans('general.date'), trans('products.product'), trans('products.price'), trans('products.qty'), trans('general.total'));
                    $callback = function () use ($account_details, $columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        $balance = 0;

                        foreach ($account_details as $row) {
                            $balance += $row['product_qty'];
                            fputcsv($file, array(dateFormat($row['created_at']), $row['product_name'], amountFormat($row['product_price']), numberFormat($row['product_qty']) . ' ' . $row['unit'], numberFormat($balance)));
                        }
                        fclose($file);
                    };
                    return Response::stream($callback, 200, $headers);
                    break;
            }
        }
        if ($transfer == 3) {
            switch ($reports->output_format) {
                case 'pdf_print':
                    $html = view('focus.report.pdf.product_person', compact('account_details', 'lang'))->render();
                    $headers = array(
                        "Content-type" => "application/pdf",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                    break;
                case 'pdf':
                    $html = view('focus.report.pdf.product_person', compact('account_details', 'lang'))->render();
                    $pdf = new \Mpdf\Mpdf(config('pdf'));
                    $pdf->WriteHTML($html);
                    return $pdf->Output($reports->section . '.pdf', 'D');
                    break;

                case 'csv':
                    $headers = array(
                        "Content-type" => "text/csv",
                        "Content-Disposition" => "attachment; filename=$file_name.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    );
                    $columns = array(trans('general.date'), trans('products.product'), trans('products.price'), trans('products.qty'), trans('general.total'));
                    $callback = function () use ($account_details, $columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        $balance = 0;
                        foreach ($account_details as $account_detail) {
                            foreach ($account_detail as $row) {
                                $balance += $row['product_qty'];
                                fputcsv($file, array(dateFormat($row['created_at']), @$row['product_name'], amountFormat($row['product_price']), numberFormat($row['product_qty']), numberFormat($balance) . ' ' . $row['unit']));
                            }
                        }
                        fclose($file);
                    };
                    return Response::stream($callback, 200, $headers);
                    break;
            }
        }
    }


    public function pos_statement(ManageReports $reports)
    {
        if (!$reports->from_date) return new RedirectResponse(route('biller.reports.statements', [$reports->section]), ['flash_error' => trans('meta.invalid_entry')]);

        $register_entries = Register::whereBetween('created_at', [date_for_database($reports->from_date), date_for_database($reports->to_date)])->get();
        $lang['title'] = trans('meta.pos_statement');
        $lang['title2'] = trans('meta.pos_statement');
        $lang['module'] = 'pos_statement';
        $lang['party'] = config('core.cname');
        $lang['party_2'] = '';
        $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', $lang['title'] . '_' . $reports->from_date);


        switch ($reports->output_format) {

            case 'pdf_print':

                $html = view('focus.report.pdf.pos_register', compact('register_entries', 'lang'))->render();
                $headers = array(
                    "Content-type" => "application/pdf",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return Response::stream($pdf->Output($file_name . '.pdf', 'I'), 200, $headers);
                break;
            case 'pdf':
                $html = view('focus.report.pdf.pos_register', compact('register_entries', 'lang'))->render();
                $pdf = new \Mpdf\Mpdf(config('pdf'));
                $pdf->WriteHTML($html);
                return $pdf->Output($file_name . '.pdf', 'D');
                break;

            case 'csv':
                $headers = array(
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=$file_name.csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );
                $columns = array(trans('pos.opened_on'), trans('pos.closed_on'), trans('general.employee'), trans('general.description'));
                $callback = function () use ($register_entries, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    foreach ($register_entries as $row) {
                        $bal = '';
                        $balance = json_decode($row->data, true);
                        foreach ($balance as $key => $amount_row) {
                            $bal .= $key . ' : ' . amountFormat($amount_row) . '<br>';
                        }
                        fputcsv($file, array(dateFormat($row['created_at']), dateFormat($row['closed_at']), $row->user->first_name . ' ' . $row->user->last_name, $bal));
                    }
                    fclose($file);
                };
                return Response::stream($callback, 200, $headers);
                break;
        }
    }
}
