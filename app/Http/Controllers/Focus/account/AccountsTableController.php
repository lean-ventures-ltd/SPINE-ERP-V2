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

namespace App\Http\Controllers\Focus\account;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\account\AccountRepository;
use App\Http\Requests\Focus\account\ManageAccountRequest;
use App\Models\transaction\Transaction;

/**
 * Class AccountsTableController.
 */
class AccountsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $account;
    protected $debit;
    protected $credit;
    protected $balance;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $account ;
     */
    public function __construct(AccountRepository $account)
    {
        $this->account = $account;
    }

    public function set_balances($account, $tr_accounts)
    {
        $this->debit = 0;
        $this->credit = 0;
        $this->balance = 0;
        foreach ($tr_accounts as $key => $tr_account) {
            if ($tr_account->account_id == $account->id) {
                if (in_array($account->account_type, ['Asset', 'Expense'])) {
                    $this->debit = $tr_account->debit;
                    $this->balance = $this->debit - $this->credit;
                } else {
                    $this->credit = $tr_account->credit;
                    $this->balance = $this->credit - $this->debit;
                }
                break;
            }
        }
    }

    /**
     * This method return the data of the model
     * @param ManageAccountRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageAccountRequest $request)
    {
        $tr_accounts = Transaction::selectRaw('account_id, SUM(debit) as debit, SUM(credit) as credit')
        ->groupBy('account_id')
        ->get();
        
        $core = $this->account->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id', 'number', 'holder'])
            ->addIndexColumn()
            ->addColumn('debit', function ($account) use($tr_accounts) {
                $this->set_balances($account, $tr_accounts);
                return numberFormat($this->debit);
            })
            ->addColumn('credit', function ($account) {
                return numberFormat($this->credit);
            })
            ->addColumn('balance', function ($account) {
                return numberFormat($this->balance);
            })
            ->editColumn('account_type', function ($account) {
                return  @$account->accountType->name;
            })
            ->addColumn('system_type', function ($account) {
                $ledger_status = $account->is_parent && $account->is_parent? 'sub-ledger' : 'ledger';
                return $account->system? "default-{$ledger_status}" : "custom-{$ledger_status}";
            })
            ->addColumn('actions', function ($account) {
                return $account->action_buttons;
            })
            ->make(true);
    }
}
