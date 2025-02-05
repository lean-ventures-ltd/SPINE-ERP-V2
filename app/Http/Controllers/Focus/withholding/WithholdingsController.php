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
namespace App\Http\Controllers\Focus\withholding;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\withholding\CreateResponse;
use App\Http\Responses\Focus\withholding\EditResponse;
use App\Repositories\Focus\withholding\WithholdingRepository;
use App\Http\Requests\Focus\withholding\ManageWithholdingRequest;
use App\Http\Requests\Focus\withholding\StoreWithholdingRequest;
use App\Models\withholding\Withholding;
use Illuminate\Validation\ValidationException;

/**
 * BanksController
 */
class WithholdingsController extends Controller
{
    /**
     * variable to store the repository object
     * @var WithholdingRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param WithholdingRepository $repository ;
     */
    public function __construct(WithholdingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\bank\ManageBankRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageWithholdingRequest $request)
    {
        return new ViewResponse('focus.withholdings.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\CreateResponse
     */
    public function create(StoreWithholdingRequest $request)
    {
        return new CreateResponse('focus.withholdings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreWithholdingRequest $request)
    {
        // extract request fields
        $data = $request->only([
            'customer_id', 'tid', 'certificate', 'cert_date', 'tr_date', 'amount', 'reference', 'allocate_ttl', 
            'note', 'withholding_tax_id'
        ]);
        $data_items = $request->only(['invoice_id', 'paid']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($v) { return $v['paid']; });

        try {
            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Withholding Certificate', $th);
        }

       return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => 'Withholding Certificate Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\bank\Bank $bank
     * @param EditBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\EditResponse
     */
    public function edit(Withholding $withholding)
    {
        return redirect(route('biller.withholdings.index'));

        return new EditResponse($withholding);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreWithholdingRequest $request, Withholding $withholding)
    {
        try {
            $this->repository->update($withholding, $request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Withholding Certificate', $th);
        }

        return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => 'Withholding Certificate Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Withholding $withholding)
    {
        try {
            $this->repository->delete($withholding);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Withholding Certificate', $th);
        }

        return new RedirectResponse(route('biller.withholdings.index'), ['flash_success' => 'Withholding Certificate Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Withholding $withholding)
    {
        return new ViewResponse('focus.withholdings.view', compact('withholding'));
    }
}
