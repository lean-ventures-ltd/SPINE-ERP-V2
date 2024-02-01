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
namespace App\Http\Controllers\Focus\purchase_request;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\purchase_request\PurchaseRequestRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class PurchaseRequestsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRequestRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseRequestRepository $repository ;
     */
    public function __construct(PurchaseRequestRepository $repository)
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
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('tid', function ($purchase_request) {
                return gen4tid('REQ-', $purchase_request->tid);
            })
            ->addColumn('date', function ($purchase_request) {
                return dateFormat($purchase_request->date);
            })
            ->addColumn('employee', function ($purchase_request) {
                if ($purchase_request->employee) 
                return $purchase_request->employee->full_name;
            })
            ->addColumn('expect_date', function ($purchase_request) {
                return dateFormat($purchase_request->expect_date);
            })
            ->addColumn('actions', function ($purchase_request) {
                return $purchase_request->action_buttons;
            })
            ->make(true);
    }
}
