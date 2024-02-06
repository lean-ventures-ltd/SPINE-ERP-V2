<?php

namespace App\Models\Access\User\Traits;

/**
 * Class UserAccess.
 */
trait UserAccess
{
    /**
     * Checks if the user has a Role by its name or id.
     *
     * @param string $nameOrId Role name or id.
     *
     * @return bool
     */
    public function hasRole($nameOrId)
    {
        foreach ($this->roles as $role) {
            //See if role has all permissions
            if ($role->all) {
                return true;
            }

            //First check to see if it's an ID
            if (is_numeric($nameOrId)) {
                if ($role->id == $nameOrId) {
                    return true;
                }
            }

            //Otherwise check by name
            if ($role->name == $nameOrId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks to see if user has array of roles.
     *
     * All must return true
     *
     * @param  $roles
     * @param  $needsAll
     *
     * @return bool
     */
    public function hasRoles($roles, $needsAll = false)
    {
        //If not an array, make a one item array
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        //User has to possess all of the roles specified
        if ($needsAll) {
            $hasRoles = 0;
            $numRoles = count($roles);

            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    $hasRoles++;
                }
            }

            return $numRoles == $hasRoles;
        }

        //User has to possess one of the roles specified
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its name or id.
     *
     * @param string $nameOrId Permission name or id.
     *
     * @return bool
     */
    public function allow($nameOrId)
    {   
        // Service Provider Account or Alternate Non-Tenant Account
        if (auth()->user()->business->is_main || !auth()->user()->is_tenant) {
            //Check permissions directly tied to user
            foreach ($this->permissions as $perm) {
                if (is_numeric($nameOrId) && $perm->id == $nameOrId) {
                    return true;
                } elseif ($perm->name == $nameOrId) {
                    return true;
                }
            }
            // See if role has all permissions
            foreach ($this->roles as $role) {
                if ($role->all) return true;
            }
            return false;
        } 

        // Permissions depending on service package i.e Basic or Standard
        if (auth()->user()->tenant) {
            $tenant = auth()->user()->tenant;
            $module_ids = @$tenant->package->service->module_id;
            $module_ids = $module_ids? explode(',', $module_ids) : [];
            foreach ($this->permissions as $perm) {
                if (in_array($perm->module_id, $module_ids)) {
                    if (is_numeric($nameOrId) && $perm->id == $nameOrId) {
                        return true;
                    } elseif ($perm->name == $nameOrId) {
                        return true;
                    }
                }
            }
            // check role permssions
            foreach ($this->roles as $role) {
                foreach ($role->permissions as $perm) {
                    if (in_array($perm->module_id, $module_ids)) {
                        if (is_numeric($nameOrId) && $perm->id == $nameOrId) {
                            return true;
                        } elseif ($perm->name == $nameOrId) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Check an array of permissions and whether or not all are required to continue.
     *
     * @param  $permissions
     * @param  $needsAll
     *
     * @return bool
     */
    public function allowMultiple($permissions, $needsAll = false)
    {
        //If not an array, make a one item array
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        //User has to possess all of the permissions specified
        if ($needsAll) {
            $hasPermissions = 0;
            $numPermissions = count($permissions);

            foreach ($permissions as $perm) {
                if ($this->allow($perm)) {
                    $hasPermissions++;
                }
            }

            return $numPermissions == $hasPermissions;
        }

        //User has to possess one of the permissions specified
        foreach ($permissions as $perm) {
            if ($this->allow($perm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  $nameOrId
     *
     * @return bool
     */
    public function hasPermission($nameOrId)
    {
        return $this->allow($nameOrId);
    }

    /**
     * @param  $permissions
     * @param bool $needsAll
     *
     * @return bool
     */
    public function hasPermissions($permissions, $needsAll = false)
    {
        return $this->allowMultiple($permissions, $needsAll);
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     *
     * @return void
     */
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->attach($role);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     *
     * @return void
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->detach($role);
    }

    /**
     * Attach multiple roles to a user.
     *
     * @param mixed $roles
     *
     * @return void
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }
    }

    /**
     * Detach multiple roles from a user.
     *
     * @param mixed $roles
     *
     * @return void
     */
    public function detachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }

    /**
     * Attach multiple Permissions to a user.
     *
     * @param mixed $permissions
     *
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $permission
     *
     * @return void
     */
    public function attachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->permissions()->attach($permission);
    }

    /**
     * Detach multiple permissions from current role.
     *
     * @param mixed $permissions
     *
     * @return void
     */
    public function detachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }
    }

    /**
     * Detach permission form current User.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function detachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->permissions()->detach($permission);
    }
}
