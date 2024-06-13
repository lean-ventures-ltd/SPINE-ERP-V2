<?php

namespace App\Repositories\Focus\tenant;

use App\Exceptions\GeneralException;
use App\Models\Access\Role\Role;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\currency\Currency;
use App\Models\department\Department;
use App\Models\hrm\HrmMeta;
use App\Models\items\Prefix;
use App\Models\misc\Misc;
use App\Models\productvariable\Productvariable;
use App\Models\tenant\Tenant;
use App\Models\tenant_package\TenantPackage;
use App\Models\tenant_package\TenantPackageItem;
use App\Models\term\Term;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Artisan;
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
        if (!$user)  return 'Default User must be created under customer module';
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
        $this->setCommonConfig($tenant, $user);

        if ($tenant) {
            DB::commit();
            $this->generateSoftwareProforma($tenant_package);
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
        if ($role) $role->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);            
        
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
            $this->generateSoftwareProforma($tenant_package);
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
    public function setCommonConfig($tenant, $user)
    {
        $results = [];
        $params = ['user_id' => $user->id, 'ins' => $tenant->id];
        $models = [
            'accounts' => Account::query(),
            'tr_categories' => Transactioncategory::query(),
            'prod_units' => Productvariable::query(),
            'currencies' => Currency::query(),
            'terms' => Term::query(),
            'vat_rates' => Additional::query(),
            'miscs' => Misc::query(),
            'prefixes' => Prefix::query(),
            'departments' => Department::query(),
        ];
        foreach ($models as $key => $model) {
            $items = [];
            $collection = $model->get();
            foreach ($collection as $i => $item) {
                $item->fill($params);
                if ($key == 'accounts') {
                    unset($item['opening_balance'],$item['opening_balance_date']); 
                    if (!isset($item['system'])) $item['note'] = null; 
                }
                unset($item['id'], $item['created_at'], $item['updated_at']);
                $items[] = $item->toArray();
            }
            $result = $model->insert($items);
            if ($result) $results[$key] = $items;            
        }

        return $results;
    }    

    /**
     * Generate Sotware Proforma Invoice
     */
    public function generateSoftwareProforma($tenant_package)
    {
        $first_proforma = $tenant_package->customer->quotes()->where('bank_id', '>', 0)
            ->where('total', $tenant_package->total_cost)
            ->orderBy('id', 'DESC')->first();
        if ($first_proforma) return false;

        // generate proforma invoice via artisan call
        $result = Artisan::call('software-proforma:generate');
        $output = Artisan::output();
        $path = storage_path('logs') . DIRECTORY_SEPARATOR . 'cron.log';
        file_put_contents($path, $output, FILE_APPEND | LOCK_EX);
        return $result;
    }
}
