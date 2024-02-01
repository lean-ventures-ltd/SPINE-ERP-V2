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
namespace App\Http\Controllers\Focus\utility_bill;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\utility_bill\UtilityBillRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class UtilityBillTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var UtilityBillRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param UtilityBillRepository $repository ;
     */
    public function __construct(UtilityBillRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $prefixes = prefixesArray(['bill'], auth()->user()->ins);

        $core = $this->repository->getForDataTable();
        
        // aggregate
        $q = clone $core;
        $res = $q->selectRaw('SUM(total) as total, SUM(total-amount_paid) as balance')->first();
        $aggregate = [
            'amount_total' => numberFormat(@$res['total']),
            'balance_total' => numberFormat(@$res['balance']),
        ];   
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->editColumn('tid', function ($utility_bill) use($prefixes) {
                $tid = gen4tid("{$prefixes[0]}-", $utility_bill->tid);
                $doc_type = $utility_bill->document_type;
                if ($doc_type == 'direct_purchase') {
                    $purchase = $utility_bill->purchase;
                    $tid = '<a href="'. ($purchase? route('biller.purchases.edit', $purchase) : '#') .'">'. $tid .'</a>';
                } elseif ($doc_type == 'goods_receive_note' && $utility_bill->ref_id) {
                    $grn = $utility_bill->ref_id;
                    $tid = '<a href="'. ($grn? route('biller.goodsreceivenote.edit', $grn) : '#') .'">'. $tid .'</a>';
                } elseif ($doc_type == 'advance_payment') {
                    $adv_pmt = $utility_bill->ref_id;
                    $tid = '<a href="'. ($adv_pmt? route('biller.advance_payments.show', $adv_pmt) : '#') .'">'. $tid .'</a>';
                }
                
                return $tid;
            })
            ->filterColumn('tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[0]) && isset($arr[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->addColumn('supplier', function ($utility_bill) {
                $name = '';
                $doc_type = $utility_bill->document_type;
                if ($doc_type == 'direct_purchase') {
                    $purchase = $utility_bill->purchase;
                    if ($purchase) $name = $purchase->suppliername;
                } elseif ($doc_type == 'advance_payment') {
                    $payment = $utility_bill->advance_payment;
                    if ($payment) $name = $payment->employee->full_name;
                }

                if ($utility_bill->supplier && !$name) 
                    return $utility_bill->supplier->name;
                    
                return $name;
            })        
            ->addColumn('note', function ($utility_bill) {
                $note = $utility_bill->note;
                $ref_type = $utility_bill->reference_type;
                $ref = $utility_bill->reference;
                $reference = $ref && $ref_type? "{$ref_type}:{$ref}" : '';

                $prefix = '';
                $doc_type = $utility_bill->document_type;
                if ($doc_type == 'direct_purchase') {
                    $purchase = $utility_bill->purchase;
                    if ($purchase) {
                        $tid = gen4tid('DP-', $purchase->tid);
                        $prefix = "({$tid})";
                    }
                } elseif ($doc_type == 'goods_receive_note' && $utility_bill->ref_id) {
                    $grn = $utility_bill->grn;
                    if ($grn) {
                        $tid = gen4tid('GRN-', $grn->tid);
                        $prefix = "({$tid})"; 
                    }
                } elseif ($doc_type == 'goods_receive_note') {
                    $tids = [];
                    foreach ($utility_bill->grn_items as $grn_item) {
                        $grn = $grn_item->goodsreceivenote;
                        if ($grn) $tids[] = $grn->tid;
                    }
                    $tids = array_map(fn($v) => gen4tid('GRN-', $v), array_unique($tids));
                    $prefix = '(' . implode(', ', $tids) . ')';
                } elseif ($doc_type == 'kra_bill') {
                    $prefix = '(KRA)';
                } elseif ($doc_type == 'advance_payment') {
                    $prefix = '(Advance PMT)';
                }   
                          
                return "{$prefix} {$reference} - {$note}";
            })
            ->addColumn('total', function ($utility_bill) {
                return numberFormat($utility_bill->total);
            })
            ->addColumn('balance', function ($utility_bill) {
                return numberFormat($utility_bill->total - $utility_bill->amount_paid);
            })
            ->addColumn('date', function ($utility_bill) {
                return dateFormat($utility_bill->date);
            })
            ->addColumn('due_date', function ($utility_bill) {
                return dateFormat($utility_bill->due_date);
            })
            ->addColumn('actions', function ($utility_bill) {
                return $utility_bill->action_buttons;
            })
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->make(true);
    }
}
