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

namespace App\Http\Controllers\Focus\tenant_service;

use App\Http\Controllers\Focus\employeeDailyLog\EmployeeDailyLogController;
use App\Models\Access\Permission\Permission;
use App\SubscriptionTier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\tenant_service\PackageExtra;
use App\Models\tenant_service\TenantService;
use App\Repositories\Focus\tenant_service\TenantServiceRepository;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\Translation\t;

/**
 * ProductcategoriesController
 */
class TenantServicesController extends Controller
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
    public function __construct(TenantServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return view('focus.tenant_services.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        $package_extras = PackageExtra::where('active', 0)->get();

        $permissions = Permission::orderBy('display_name')->get();
        $packagePermissions = [];

        $subscriptionTiers = SubscriptionTier::all();

        return view('focus.tenant_services.create', compact('package_extras', 'permissions', 'packagePermissions', 'subscriptionTiers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => ['required', 'string'],
            'cost' => ['required', 'numeric'],
            'maintenance_cost' => ['required', 'numeric'],
            'subscription_packs' => ['required', 'array'],
            'subscription_packs.*' => ['required', 'string'],
        ]);

        try {
            DB::beginTransaction();


            $tenantService = new TenantService();
            $tenantService->fill($validated);
            $tenantService->total_cost = $validated['cost'] + $validated['maintenance_cost'];
            $tenantService->subscription_packs = json_encode($validated['subscription_packs']);
            $tenantService->save();

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            return "Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . " | & Line " . $e->getLine();
        }

        return new RedirectResponse(route('biller.tenant_services.index'), ['flash_success' => 'Tenant Service  Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(TenantService $tenant_service, Request $request)
    {

        $subscriptionTiers = SubscriptionTier::all();
        $tenant_service->subscription_packs = json_decode($tenant_service->subscription_packs, true);

        return view('focus.tenant_services.edit', compact('tenant_service', 'subscriptionTiers'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, TenantService $tenantService)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => ['required', 'string'],
                'cost' => ['required', 'numeric'],
                'maintenance_cost' => ['required', 'numeric'],
                'subscription_packs' => ['required', 'array'],
                'subscription_packs.*' => ['required', 'string'],
            ]);

            $tenantService->fill($validated);
            $tenantService->total_cost = $validated['cost'] + $validated['maintenance_cost'];
            $tenantService->subscription_packs = json_encode($validated['subscription_packs']);
            $tenantService->save();

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            return "Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . " | & Line " . $e->getLine();
        }

        return new RedirectResponse(route('biller.tenant_services.index'), ['flash_success' => 'Tenant Service Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(TenantService $tenant_service)
    {
        try {
            $this->repository->delete($tenant_service);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Tenant Service!', $th);
        }

        return new RedirectResponse(route('biller.tenant_services.index'), ['flash_success' => 'Tenant Service successfully deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(TenantService $tenant_service, Request $request)
    {
        return view('focus.tenant_services.view', compact('tenant_service'));
    }
}
