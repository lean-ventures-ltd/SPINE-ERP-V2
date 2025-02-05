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
namespace App\Http\Controllers\Focus\transactioncategory;

use App\Http\Requests\Focus\general\ManageCompanyRequest;
use App\Models\transactioncategory\Transactioncategory;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\transactioncategory\CreateResponse;
use App\Http\Responses\Focus\transactioncategory\EditResponse;
use App\Repositories\Focus\transactioncategory\TransactioncategoryRepository;


/**
 * TransactioncategoriesController
 */
class TransactioncategoriesController extends Controller
{
    /**
     * variable to store the repository object
     * @var TransactioncategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TransactioncategoryRepository $repository ;
     */
    public function __construct(TransactioncategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\transactioncategory\ManageTransactioncategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageCompanyRequest $request)
    {
        return new ViewResponse('focus.transactioncategories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateTransactioncategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\transactioncategory\CreateResponse
     */
    public function create(ManageCompanyRequest $request)
    {
        return new CreateResponse('focus.transactioncategories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTransactioncategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageCompanyRequest $request)
    {
        try {
            $this->repository->create( $request->except(['_token', 'ins']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Transaction Category', $th);
        }
        return new RedirectResponse(route('biller.transactioncategories.index'), ['flash_success' => trans('alerts.backend.transactioncategories.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\transactioncategory\Transactioncategory $transactioncategory
     * @param EditTransactioncategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\transactioncategory\EditResponse
     */
    public function edit(Transactioncategory $transactioncategory, ManageCompanyRequest $request)
    {
        return new EditResponse($transactioncategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTransactioncategoryRequestNamespace $request
     * @param Transactioncategory $transactioncategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(ManageCompanyRequest $request, Transactioncategory $transactioncategory)
    {
        try {
            $this->repository->update($transactioncategory, $request->except(['_token', 'ins']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Transaction Categories', $th);
        }
        return new RedirectResponse(route('biller.transactioncategories.index'), ['flash_success' => trans('alerts.backend.transactioncategories.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteTransactioncategoryRequestNamespace $request
     * @param Transactioncategory $transactioncategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Transactioncategory $transactioncategory, ManageCompanyRequest $request)
    {
        try {
            $result = $this->repository->delete($transactioncategory);
            if ($result) return new RedirectResponse(route('biller.transactioncategories.index'), ['flash_success' => trans('alerts.backend.transactioncategories.deleted')]);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Transaction Categories', $th);
        }
        return new RedirectResponse(route('biller.transactioncategories.index'), ['flash_error' => trans('meta.delete_error')]);
    }
}
