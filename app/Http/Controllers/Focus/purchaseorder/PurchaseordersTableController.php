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
namespace App\Http\Controllers\Focus\purchaseorder;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\purchaseorder\PurchaseorderRepository;
use App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest;

/**
 * Class PurchaseordersTableController.
 */
class PurchaseordersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseorderRepository
     */
    protected $purchaseorder;

    /**
     * contructor to initialize repository object
     * @param PurchaseorderRepository $purchaseorder ;
     */
    public function __construct(PurchaseorderRepository $purchaseorder)
    {
        $this->purchaseorder = $purchaseorder;
    }

    /**
     * This method return the data of the model
     * @param ManagePurchaseorderRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        $query = $this->purchaseorder->getForDataTable();
        $query_1 = clone $query;
        $prefixes = prefixesArray(['purchase_order'], auth()->user()->ins);
        // aggregate
        $order_total = $query_1->sum('grandttl');
        $grn_total = 0;
        foreach ($query_1 as $po) {
            $grn_total += $po->grns->sum('total');
        }
        $aggregate = [
            'order_total' => numberFormat($order_total),
            'grn_total' => numberFormat($grn_total),
            'due_total' => numberFormat($order_total - $grn_total),
        ];   

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($po) use($prefixes) {
                return '<a class="font-weight-bold" href="' . route('biller.purchaseorders.show', $po) . '">' . gen4tid("{$prefixes[0]}-", $po->tid) . '</a>';
            })
            ->filterColumn('tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[0]) && isset($arr[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->addColumn('supplier', function ($po) {
                if ($po->supplier)
                return ' <a class="font-weight-bold" href="' . route('biller.suppliers.show', $po->supplier) . '">'. $po->supplier->name . '</a>';
            })
            ->filterColumn('supplier', function($query, $supplier) {
                $query->whereHas('supplier', fn($q) => $q->where('name', 'LIKE', "%{$supplier}%"));
            })
            ->addColumn('count', function ($po) {
                return $po->items->count();
            })
            ->addColumn('date', function ($po) {
                return dateFormat($po->date);
            })
            ->orderColumn('date', '-date $1')
            ->addColumn('amount', function ($po) {
                return numberFormat($po->grandttl);
            })
            ->orderColumn('amount', function ($query, $order) {
                $query->orderBy('grandttl', $order);
            })
            ->editColumn('status', function ($po) {
                if ($po->closure_status) return 'Closed';
                return $po->status;
            })
            ->addColumn('actions', function ($po) {
                return $po->action_buttons;
            })
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->make(true);
    }
}
