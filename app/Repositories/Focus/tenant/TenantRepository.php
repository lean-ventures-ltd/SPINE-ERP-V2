<?php

namespace App\Repositories\Focus\tenant;

use App\Exceptions\GeneralException;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\productvariable\Productvariable;
use App\Models\tenant\Tenant;
use App\Models\tenant_package\TenantPackage;
use App\Models\tenant_package\TenantPackageItem;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;

class TenantRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Tenant::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q = $this->query()->whereNull('deleted_at')->where('id', '>', 1);

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {   
        DB::beginTransaction();

        $package_data = Arr::only($input, ['customer_id', 'subscr_term', 'date', 'package_id', 'cost', 'maintenance_cost', 'extras_cost', 'total_cost', 'package_item_id']);
        $tenant_data = array_diff_key($input, $package_data);

        $tenant = Tenant::create($tenant_data);

        $package_data = array_replace($package_data, [
            'company_id' => $tenant->id,
            'date' => date_for_database($package_data['date']),
            'cost' => numberClean($package_data['cost']),
            'maintenance_cost' => numberClean($package_data['maintenance_cost']),
            'extras_cost' => numberClean($package_data['extras_cost']),
            'total_cost' => numberClean($package_data['total_cost']),
            'due_date' => (new Carbon(date('Y-m-d')))->addMonths(@$package_data['subscr_term'])->format('Y-m-d'),
        ]);
        unset($package_data['package_item_id']);
        $tenant_package = TenantPackage::create($package_data);

        $input['package_item_id'] = @$input['package_item_id'] ?: [];
        foreach ($input['package_item_id'] as $key => $value) {
            $input['package_item_id'][$key] = [
                'tenant_package_id' => $tenant_package->id,
                'package_item_id' => $value,
            ];
        }
        TenantPackageItem::insert($input['package_item_id']);

        // update tenant user
        $user = User::where('customer_id', $tenant_package->customer_id)->first();
        if ($user) $user->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);

        // set tenant account properties
        $this->account_setup($tenant, $user);

        if ($tenant) {
            DB::commit();
            return $tenant;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Tenant $tenant, array $input)
    {
        DB::beginTransaction();

        $package_data = Arr::only($input, ['customer_id', 'subscr_term', 'date', 'package_id', 'cost', 'maintenance_cost', 'extras_cost', 'total_cost', 'package_item_id']);
        $tenant_data = array_diff_key($input, $package_data);
        
        $result = $tenant->update($tenant_data);

        $tenant_package = $tenant->package;
        if ($tenant_package) {
            $package_data = array_replace($package_data, [
                'date' => date_for_database($package_data['date']),
                'cost' => numberClean($package_data['cost']),
                'maintenance_cost' => numberClean($package_data['maintenance_cost']),
                'extras_cost' => numberClean($package_data['extras_cost']),
                'total_cost' => numberClean($package_data['total_cost']),
                'due_date' => (new Carbon(date('Y-m-d')))->addMonths(@$package_data['subscr_term'])->format('Y-m-d'),
            ]);
            unset($package_data['package_item_id']);
            $tenant_package->update($package_data);
            $tenant_package->items()->delete();
            $input['package_item_id'] = @$input['package_item_id'] ?: [];
            foreach ($input['package_item_id'] as $key => $value) {
                $input['package_item_id'][$key] = [
                    'tenant_package_id' => $tenant_package->id,
                    'package_item_id' => $value,
                ];
            }
            TenantPackageItem::insert($input['package_item_id']);
        }

        // update tenant user
        $user = User::where('customer_id', $tenant_package->customer_id)->first();
        if ($user) $user->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Tenant $tenant)
    {
        return $tenant->update(['deleted_at' => now()]);
    }

    /**
     * Replicate default account records
     * 
     */
    function account_setup($tenant, $user)
    {
        // set ledger accounts
        $accounts = Account::all();
        foreach ($accounts as $key => $account) {
            $account2 = $account->replicate();
            $account2->ins = $tenant->id;
            $account2->user_id = @$user->id ?: null;
            $account2->save();
        }
        // set transaction categories
        $tr_categories = Transactioncategory::all();
        foreach ($tr_categories as $key => $tr_category) {
            $tr_category2 = $tr_category->replicate();
            $tr_category2->ins = $tenant->id;
            $tr_category2->user_id = @$user->id ?: null;
            $tr_category2->save();
        }
        // set product units of measure
        $units = Productvariable::all();
        foreach ($units as $key => $unit) {
            $unit2 = $unit->replicate();
            $unit2->ins = $tenant->id;
            $unit2->user_id = @$user->id ?: null;
            $unit2->save();
        }
    }
}
