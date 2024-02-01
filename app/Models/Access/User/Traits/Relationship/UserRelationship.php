<?php

namespace App\Models\Access\User\Traits\Relationship;

use App\Models\Access\User\SocialLogin;
use App\Models\Company\Company;
use App\Models\leave\Leave;
use App\Models\System\Session;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Permission\Permission;
use App\Models\client_user\ClientUser;
use App\Models\client_vendor\ClientVendor;
use App\Models\customer\Customer;
use App\Models\tenant\Tenant;

/**
 * Class UserRelationship.
 */
trait UserRelationship
{
    public function client_user()
    {
        return $this->belongsTo(ClientUser::class);
    }

    public function client_vendor()
    {
        return $this->belongsTo(ClientVendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'id', 'ins');
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class, 'employee_id');
    }

    public function roles()
    {
        return $this->belongsToMany(config('access.role'), config('access.role_user_table'), 'user_id', 'role_id');
    }

    public function business()
    {
        return $this->hasOne(Company::class, 'id', 'ins');
    }

    public function providers()
    {
        return $this->hasMany(SocialLogin::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Many-to-Many relations with Permission.
     * ONLY GETS PERMISSIONS ARE NOT ASSOCIATED WITH A ROLE.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(config('access.permission'), config('access.permission_user_table'), 'user_id', 'permission_id');
    }

    public function user_associated_permission()
    {
        //user current permission
        //  return $this->hasManyThrough(Permission::class, PermissionUser::class, 'permission_id','id', 'id','user_id')->withoutGlobalScopes();

        return $this->belongsToMany(Permission::class, PermissionUser::class, 'user_id', 'permission_id', 'id', 'id')->withoutGlobalScopes();
    }
}
