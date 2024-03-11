<?php

namespace App\Repositories\Focus\tenant;

use App\Exceptions\GeneralException;
use App\Models\Access\Role\Role;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\currency\Currency;
use App\Models\hrm\HrmMeta;
use App\Models\misc\Misc;
use App\Models\productvariable\Productvariable;
use App\Models\tenant\Tenant;
use App\Models\tenant_package\TenantPackage;
use App\Models\tenant_package\TenantPackageItem;
use App\Models\term\Term;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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
        // id 1 and 2 are reserved for administrative accounts
        $q = $this->query()->where('id', '>', 2)->whereNull('deleted_at');

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
        if (!$user) throw ValidationException::withMessages(['Default User must be created under customer module']);
        $user->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);
        $role = Role::where('created_by', $user->id)->first();
        $role->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);            
        HrmMeta::create([
            'user_id' => $user->id,
            'employee_no' => 0,
            'id_number' => 'None',
            'primary_contact' => 'None',
            'secondary_contact' => 'None',
            'gender' => 'None',
            'marital_status' => 'None',
            'id_front' => 'None',
            'id_back' => 'None',
            'home_county' => 'None',
            'home_address' => 'None',
            'residential_address' => 'None',
            'award' => 'None',
            'position' => 'None',
            'specify' => 'None',
        ]);
        
        // set tenant common configuration
        $this->set_common_config($tenant, $user);

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
        if (!$user) throw ValidationException::withMessages(['Default User must be created under customer module']);
        $user->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);
        $role = Role::where('created_by', $user->id)->first();
        $role->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);            
        $user_meta = HrmMeta::where('user_id', $user->id)->first();
        if (!$user_meta) {
            HrmMeta::create([
                'user_id' => $user->id,
                'employee_no' => 0,
                'id_number' => 'None',
                'primary_contact' => 'None',
                'secondary_contact' => 'None',
                'gender' => 'None',
                'marital_status' => 'None',
                'id_front' => 'None',
                'id_back' => 'None',
                'home_county' => 'None',
                'home_address' => 'None',
                'residential_address' => 'None',
                'award' => 'None',
                'position' => 'None',
                'specify' => 'None',
            ]);
        }

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
     * Replicate Common Configuration
     */
    function set_common_config($tenant, $user)
    {
        $models = [
            'accounts' => Account::all(),
            'tr_categories' => Transactioncategory::all(),
            'prod_units' => Productvariable::all(),
            'currencies' => Currency::all(),
            'terms' => Term::all(),
            'vat_rates' => Additional::all(),
            'miscs' => Misc::all(),
        ];
        foreach ($models as $key => $collection) {
            foreach ($collection as $i => $item) {
                $item2 = $item->replicate();
                $item2->fill(['user_id' => $user->id, 'ins' => $tenant->id]);
                if ($key == 'accounts') unset($item2['opening_balance'],$item2['opening_balance_date'],$item2['note']); 
                $item2->save();
            }
        }
    }
}
