<?php

namespace App\Http\Controllers\Focus\rfq;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\rfq\RfQRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class RfQTableController extends Controller
{
    protected $rfq;
    public function __construct(RfQRepository $rfq)
    {
        $this->rfq = $rfq;
    }
    public function __invoke()
    {
        $core = $this->rfq->getForDataTable();

        $prefixes = prefixesArray(['rfq'], auth()->user()->ins);
        // aggregate
        // $order_total = $core->sum('grandttl');
        $grn_total = 0;
        // foreach ($core as $po) {
        //     $grn_total += $po->grns->sum('total');
        // }
        // $aggregate = [
        //     'order_total' => numberFormat($order_total),
        //     'grn_total' => numberFormat($grn_total),
        //     'due_total' => numberFormat($order_total - $grn_total),
        // ];

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($rfq) use ($prefixes) {
                return '<a class="font-weight-bold" href="' . route('biller.rfq.show', $rfq) . '">' . gen4tid("RFQ-", $rfq->tid) . '</a>';
            })
            // ->addColumn('supplier', function ($po) {
            //     if ($po->supplier)
            //         return ' <a class="font-weight-bold" href="' . route('biller.suppliers.show', $po->supplier) . '">' . $po->supplier->name . '</a>';
            // })
            // ->addColumn('count', function ($po) {
            //     // return $po->items->count();
            //     return 1;
            // })
            ->addColumn('rfq_date', function ($rfq) {
                return dateFormat($rfq->date);
            })
            ->addColumn('due_date', function ($rfq) {
                return dateFormat($rfq->due_date);
            })
            // ->addColumn('amount', function ($po) {
            //     return numberFormat($po->grandttl);
            // })
            // ->addColumn('status', function ($po) {
            //     if ($po->closure_status) return 'Closed';
            //     return $po->status;
            // })
            ->addColumn('actions', function ($rfq) {
                return $rfq->action_buttons;
            })
            // ->addColumn('aggregate', function () use ($aggregate) {
            //     return $aggregate;
            // })
            ->make(true);
    }
}
