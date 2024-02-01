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

namespace App\Http\Controllers\Focus\role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\hrm\ManageHrmRequest;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\Role\Role;
use App\Repositories\Focus\role\PermissionRepository;
use App\Repositories\Focus\role\RoleRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class RoleController.
 */
class RoleController extends Controller
{
    /**
     * @var \App\Repositories\Backend\Access\Role\RoleRepository
     */
    protected $roles;

    /**
     * @var \App\Repositories\Backend\Access\Permission\PermissionRepository
     */
    protected $permissions;

    /**
     * @param \App\Repositories\Backend\Access\Role\RoleRepository $roles
     * @param \App\Repositories\Backend\Access\Permission\PermissionRepository $permissions
     */
    public function __construct(RoleRepository $roles, PermissionRepository $permissions)
    {
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    /**
     * @param \App\Http\Requests\Backend\Access\Role\ManageRoleRequest $request
     *
     * @return mixed
     */
    public function index(ManageHrmRequest $request)
    {
        return new ViewResponse('focus.hrms.roles.index');
    }

    /**
     * @param \App\Http\Requests\Backend\Access\Role\CreateRoleRequest $request
     *
     * @return \App\Http\Responses\Backend\Access\Role\CreateResponse
     */
    public function create(ManageHrmRequest $request)
    {
        return view('focus.hrms.roles.create')
            ->withPermissions($this->permissions->getAll())
            ->withRoleCount($this->roles->getCount());
    }

    /**
     * @param \App\Http\Requests\Backend\Access\Role\StoreRoleRequest $request
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageHrmRequest $request)
    {
        try {
            $this->roles->create($request->except('_token'));
        } catch (\Throwable $th) { 
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Role', $th);
        }

        return new RedirectResponse(route('biller.role.index'), ['flash_success' => trans('alerts.backend.roles.created')]);
    }

    /**
     * @param \App\Models\Access\Role\Role $role
     * @param \App\Http\Requests\Backend\Access\Role\EditRoleRequest $request
     *
     * @return \App\Http\Responses\Backend\Access\Role\EditResponse
     */
    public function edit(Role $role, ManageHrmRequest $request)
    {
        if (auth()->user()->ins == $role->ins) {

            return view('focus.hrms.roles.edit')
                ->withRole($role)
                ->withRolePermissions($role->permissions->pluck('id')->all())
                ->withPermissions($this->permissions->getAll());
        }
    }

    /**
     * @param \App\Models\Access\Role\Role $role
     *
     */
    public function update(Request $request, Role $role)
    {
        try {
            $this->roles->update($role, $request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th; 
            errorHandler('Error Updating Role', $th);
        }

        return new RedirectResponse(route('biller.role.index'), ['flash_success' => trans('alerts.backend.roles.updated')]);
    }

    /**
     * @param \App\Models\Access\Role\Role $role
     * @param \App\Http\Requests\Backend\Access\Role\DeleteRoleRequest $request
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Role $role, ManageHrmRequest $request)
    {
        try {
            $this->roles->delete($role);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th; 
            return errorHandler('Error Deleting Roles', $th);
        }

        return new RedirectResponse(route('biller.role.index'), ['flash_success' => trans('alerts.backend.roles.deleted')]);
    }
}
