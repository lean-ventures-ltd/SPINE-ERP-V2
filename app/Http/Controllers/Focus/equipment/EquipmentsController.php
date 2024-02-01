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

namespace App\Http\Controllers\Focus\equipment;

use App\Models\equipment\Equipment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\equipment\CreateResponse;
use App\Http\Responses\Focus\equipment\EditResponse;
use App\Repositories\Focus\equipment\EquipmentRepository;
use App\Http\Requests\Focus\equipment\ManageEquipmentRequest;
use App\Http\Requests\Focus\equipment\StoreEquipmentRequest;
use App\Models\branch\Branch;
use App\Models\customer\Customer;

/**
 * ProductcategoriesController
 */
class EquipmentsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(EquipmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageEquipmentRequest $request)
    {

        $customer_id = auth()->user()->customer_id;
        $customers = Customer::when($customer_id, fn($q) => $q->where('id', $customer_id))
            ->get(['id', 'company']);
       $branches = Branch::where('name', '!=', 'All Branches')->get(['id', 'name', 'customer_id']);

        return new ViewResponse('focus.equipments.index', compact('customers', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create(StoreEquipmentRequest $request)
    {
        return new CreateResponse('focus.equipments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreEquipmentRequest $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Equipment', $th);
        }

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Equipment $equipment)
    {
        return new EditResponse($equipment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreEquipmentRequest $request, Equipment $equipment)
    {
        try {
            $this->repository->update($equipment, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Equipment', $th);
        }

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment  Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Equipment $equipment)
    {

        try {
            $this->repository->delete($equipment);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Equipments, $th');
        }

        return new RedirectResponse(route('biller.equipments.index'), ['flash_success' => 'Equipment Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Equipment $equipment)
    {
        return new ViewResponse('focus.equipments.view', compact('equipment'));
    }

    /**
     * Fetch customer equipments
     */
    public function equipment_search(Request $request)
    {
        $k = $request->post('keyword');
        
        $equipments = Equipment::when(request('branch_id'), function ($q) {
            $q->where('branch_id', request('branch_id'));
        })->when(request('customer_id'), function ($q) {
            $q->where('customer_id', request('customer_id'));
        })->when(request('schedule_id'), function ($q) {
            // unserviced equipments
            $q->whereHas('contract_equipments', function ($q) {
                $q->where('schedule_id', request('schedule_id'));
            })->where(function ($q) {
                $q->doesntHave('contract_service_items', 'or', function ($q) {
                    $q->whereHas('contractservice', function ($q) {
                        $q->where('schedule_id', request('schedule_id'));
                    });
                });
            });
        })
        ->where(function ($q) use($k) {
            $q->where('tid', 'LIKE', '%' . $k . '%')
            ->orWhere('make_type', 'LIKE', '%' . $k . '%')
            ->orWhere('location', 'LIKE', '%' . $k . '%');
        })->limit(10)->get();

        return response()->json($equipments);
    }

    // 
    public function equipment_load()
    {
        $equipments = array();
        if (request('id') != 1) 
            $equipments = Equipment::get();
        
        return response()->json($equipments);
    }
}
