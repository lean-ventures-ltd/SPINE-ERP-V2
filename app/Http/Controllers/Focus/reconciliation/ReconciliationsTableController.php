<?php

namespace App\Http\Controllers\Focus\reconciliation;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\reconciliation\ReconciliationRepository;

class ReconciliationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ReconciliationRepository
     */
    protected $reconciliation;

    /**
     * contructor to initialize repository object
     * @param ReconciliationRepository $reconciliation ;
     */
    public function __construct(ReconciliationRepository $reconciliation)
    {
        $this->reconciliation = $reconciliation;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->reconciliation->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account', function ($recon) {
                return @$recon->account->holder;
            })
            ->editColumn('end_date', function ($recon) {
                return dateFormat($recon->end_date);
            })
            ->editColumn('end_balance', function ($recon) {
                return numberFormat($recon->end_balance);
            })
            ->editColumn('balance_diff', function ($recon) {
                return numberFormat($recon->balance_diff);
            })
            ->editColumn('created_at', function ($recon) {
                return dateFormat($recon->created_at);
            })
            ->addColumn('actions', function ($recon) {
                return $recon->action_buttons;
            })
            ->make(true);
    }
}