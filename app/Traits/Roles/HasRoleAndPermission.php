<?php

namespace App\Traits\Roles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use InvalidArgumentException;
use App\Models\Permission;
use App\Models\Role;

trait HasRoleAndPermission
{
    /**
     * Property for caching roles.
     *
     * @var Collection|null
     */
    protected $roles_list;

    /**
     * Property for caching permissions.
     *
     * @var Collection|null
     */
    protected $permissions;

    /**
     * User belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Get all roles as collection.
     *
     * @return Collection
     */
    public function getRoles()
    {
        return (!$this->roles_list) ? $this->roles_list = $this->roles : $this->roles_list;
        // return (!$this->roles) ? $this->roles = $this->roles()->orderBy('level', 'desc')->get() : $this->roles;
    }

    /**
     * @param $role
     * @return mixed|null
     */
    public function getRole($role)
    {
        foreach($this->roles_list as $roleCollection) {
            if ($role == $roleCollection->slug || $role == $roleCollection->id) {
                return $roleCollection;
            }
        }
        return null;
    }

    /**
     * @param $role
     * @return mixed|null
     */
    public function getRoleName($role)
    {
        return getRole($role) ? getRole($role)->getName() : $role;
    }

    /**
     * @return mixed
     */
    public function getMaxRoleGroup()
    {
        $role = $this->roles()->orderBy('level', 'desc')->first();
        $slug = explode('.', $role->slug);
        return $slug[0];
    }

    /**
     * Check if the user has a role or roles.
     *
     * @param int|string|array $role
     * @param bool             $all
     *
     * @return bool
     */
    public function hasRole($role, $all = false)
    {
        if ($this->isPretendEnabled()) {
            return $this->pretend('hasRole');
        }

        if (!$all) {
            return $this->hasOneRole($role);
        }

        return $this->hasAllRoles($role);
    }

    /**
     * Check if the user has at least one of the given roles.
     *
     * @param int|string|array $role
     *
     * @return bool
     */
    public function hasOneRole($role)
    {
        foreach ($this->getArrayFrom($role) as $role) {
            if ($this->checkRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has all roles.
     *
     * @param int|string|array $role
     *
     * @return bool
     */
    public function hasAllRoles($role)
    {
        foreach ($this->getArrayFrom($role) as $role) {
            if (!$this->checkRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the user has role.
     *
     * @param int|string $role
     *
     * @return bool
     */
    public function checkRole($role)
    {
        return $this->getRoles()->contains(function ($value) use ($role) {
            return $role == $value->id || Str::is($role, $value->slug);
        });
    }

    /**
     * Attach role to a user.
     *
     * @param int|Role $role
     *
     * @return null|bool
     */
    public function attachRole($role)
    {
        if ($this->getRoles()->contains($role)) {
            return true;
        }
        $this->roles_list = null;

        return $this->roles()->attach($role);
    }

    public function attachRoleSlug($slug)
    {
        $roles = \App\Models\Role::select('id')->where('slug', $slug)->first();
        return $this->attachRole($roles->id ?: null);
    }

    /**
     * Detach role from a user.
     *
     * @param int|Role $role
     *
     * @return int
     */
    public function detachRole($role)
    {
        $this->roles_list = null;

        return $this->roles()->detach($role);
    }

    /**
     * Detach all roles from a user.
     *
     * @return int
     */
    public function detachAllRoles()
    {
        $this->roles_list = null;

        return $this->roles()->detach();
    }

    /**
     * Sync roles for a user.
     *
     * @param array|\jeremykenedy\LaravelRoles\Models\Role[]|\Illuminate\Database\Eloquent\Collection $roles
     *
     * @return array
     */
    public function syncRoles($roles)
    {
        $this->roles_list = null;

        return $this->roles()->sync($roles);
    }

    /**
     * Get role level of a user.
     *
     * @return int
     */
    public function level()
    {
        return ($role = $this->getRoles()->sortByDesc('level')->first()) ? $role->level : 0;
    }

    /**
     * Get all permissions from roles.
     *
     * @return Builder
     */
    public function rolePermissions()
    {
        $permissionModel = app(\App\Models\Permission::class);

        if (!$permissionModel instanceof Model) {
            throw new InvalidArgumentException('[roles.models.permission] must be an instance of \Illuminate\Database\Eloquent\Model');
        }

        $data = $permissionModel::select('permissions.*');

        if (!$this->hasRole(config('roles.role_has_all_permissions'))) {
            $data->addSelect(['permission_role.created_at as pivot_created_at', 'permission_role.updated_at as pivot_updated_at'])
                ->join('permission_role', 'permission_role.permission_id', '=', 'permissions.id')
                ->join('roles', 'roles.id', '=', 'permission_role.role_id')
                ->whereIn('roles.id', $this->getRoles()->pluck('id')->toArray())
                ->groupBy(['permissions.id', 'permissions.name', 'permissions.slug', 'permissions.description', 'permissions.model', 'permissions.created_at', 'permissions.updated_at', 'permissions.deleted_at', 'pivot_created_at', 'pivot_updated_at']);
        }
        else {
            $data->where('slug', 'not like', 'service.%');
        }

        return $data;
    }

    /**
     * User belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function userPermissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class)->withTimestamps();
    }

    /**
     * Get all permissions as collection.
     *
     * @return Collection
     */
    public function getPermissions()
    {
        if (!$this->permissions || count($this->permissions) === 0) {
            $permissionList = [];

            if ($this->hasRole('admin.root') && \Storage::disk('root')->exists('app/Helpers/RolePermissions/admin.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/admin.php')));
            } else if ($this->hasRole('customer.root') && \Storage::disk('root')->exists('app/Helpers/RolePermissions/customer.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/customer.php')));
            } else if ($this->hasRole('driver.root') && \Storage::disk('root')->exists('app/Helpers/RolePermissions/driver.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/driver.php')));
            }
             else if ($this->hasRole('supplier.root') && \Storage::disk('root')->exists('app/Helpers/RolePermissions/driver.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/driver.php')));
            }
            else if ($this->hasRole('admin.fleet_operator') && \Storage::disk('root')->exists('app/Helpers/RolePermissions/fleet_operator.php')) {
                $permissionList = array_replace_recursive($permissionList, include(parse_path('app/Helpers/RolePermissions/fleet_operator.php', true)));
            } else if ($this->hasRole('admin.manager') && \Storage::disk('root')->exists('app/Helpers/RolePermissions/manager.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/manager.php')));
            } else if ($this->hasRole('admin.operator') && \Storage::disk('root')->exists('app/Helpers/RolePermissions/operator.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/operator.php')));
            }

            if (count($permissionList) > 0 && !is_subclass_of($permissionList[0], 'Illuminate\Database\Eloquent\Model')) {
                try {
                    foreach ($permissionList as $id => $per) {
                        if (is_string($per)) {
                            $permissionObject = new \stdClass();
                            $permissionObject->slug = $per;
                            $permissionObject->id = null;
                            $per = $permissionObject;
                        }

                        $permissionList[$id] = \App\Models\Permission::hydrate([(array)$per])[0];
                    }

                    $this->permissions = collect($permissionList);
                }
                catch (\Exception $e) {}
            } else {
                $this->permissions = $this->rolePermissions()->get()->merge($this->userPermissions()->get());
            }
        }

        return $this->permissions;
    }

    /**
     * Check if the user has a permission or permissions.
     *
     * @param int|string|array $permission
     * @param bool             $all
     *
     * @return bool
     */
    public function hasPermission($permission, $all = false)
    {
        if ($this->isPretendEnabled()) {
            return $this->pretend('hasPermission');
        }

        if (!$all) {
            return $this->hasOnePermission($permission);
        }

        return $this->hasAllPermissions($permission);
    }

    /**
     * Check if the user has at least one of the given permissions.
     *
     * @param int|string|array $permission
     *
     * @return bool
     */
    public function hasOnePermission($permission)
    {
        foreach ($this->getArrayFrom($permission) as $permission) {
            if ($this->checkPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has all permissions.
     *
     * @param int|string|array $permission
     *
     * @return bool
     */
    public function hasAllPermissions($permission)
    {
        foreach ($this->getArrayFrom($permission) as $permission) {
            if (!$this->checkPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the user has a permission.
     *
     * @param int|string $permission
     *
     * @return bool
     */
    public function checkPermission($permission)
    {
        $permissionList = $this->getPermissions();

        return count($permissionList) > 0 && $permissionList->contains(function ($value) use ($permission) {
            return $permission == $value->id || (string)$value->slug === (string)$permission || Str::is($permission, $value->slug) || Str::is($value->slug, $permission);
        });
    }

    /**
     * Check if the user is allowed to manipulate with entity.
     *
     * @param string $providedPermission
     * @param Model  $entity
     * @param bool   $owner
     * @param string $ownerColumn
     *
     * @return bool
     */
    public function allowed($providedPermission, Model $entity, $owner = true, $ownerColumn = 'user_id')
    {
        if ($this->isPretendEnabled()) {
            return $this->pretend('allowed');
        }

        if ($owner === true && $entity->{$ownerColumn} == $this->id) {
            return true;
        }

        return $this->isAllowed($providedPermission, $entity);
    }

    /**
     * Check if the user is allowed to manipulate with provided entity.
     *
     * @param string $providedPermission
     * @param Model  $entity
     *
     * @return bool
     */
    protected function isAllowed($providedPermission, Model $entity)
    {
        foreach ($this->getPermissions() as $permission) {
            if ($permission->model != '' && get_class($entity) == $permission->model
                && ($permission->id == $providedPermission || $permission->slug === $providedPermission)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Attach permission to a user.
     *
     * @param int|Permission $permission
     *
     * @return null|bool
     */
    public function attachPermission($permission)
    {
        if ($this->getPermissions()->contains($permission)) {
            return true;
        }
        $this->permissions = null;

        return $this->userPermissions()->attach($permission);
    }

    /**
     * Detach permission from a user.
     *
     * @param int|Permission $permission
     *
     * @return int
     */
    public function detachPermission($permission)
    {
        $this->permissions = null;

        return $this->userPermissions()->detach($permission);
    }

    /**
     * Detach all permissions from a user.
     *
     * @return int
     */
    public function detachAllPermissions()
    {
        $this->permissions = null;

        return $this->userPermissions()->detach();
    }

    /**
     * Sync permissions for a user.
     *
     * @param array|\jeremykenedy\LaravelRoles\Models\Permission[]|\Illuminate\Database\Eloquent\Collection $permissions
     *
     * @return array
     */
    public function syncPermissions($permissions)
    {
        $this->permissions = null;

        return $this->userPermissions()->sync($permissions);
    }

    /**
     * Check if pretend option is enabled.
     *
     * @return bool
     */
    private function isPretendEnabled()
    {
        return (bool) config('roles.pretend.enabled');
    }

    /**
     * Allows to pretend or simulate package behavior.
     *
     * @param string $option
     *
     * @return bool
     */
    private function pretend($option)
    {
        return (bool) config('roles.pretend.options.'.$option);
    }

    /**
     * Get an array from argument.
     *
     * @param int|string|array $argument
     *
     * @return array
     */
    private function getArrayFrom($argument)
    {
        return (!is_array($argument)) ? preg_split('/ ?[,|] ?/', $argument) : $argument;
    }

    public function callMagic($method, $parameters)
    {
        if (starts_with($method, 'is')) {
            return $this->hasRole(snake_case(substr($method, 2), config('roles.separator')));
        }
        elseif (starts_with($method, 'can')) {
            return $this->hasPermission(snake_case(substr($method, 3), config('roles.separator')));
        }
        elseif (starts_with($method, 'allowed')) {
            return $this->allowed(snake_case(substr($method, 7), config('roles.separator')), $parameters[0], (isset($parameters[1])) ? $parameters[1] : true, (isset($parameters[2])) ? $parameters[2] : 'user_id');
        }

        return parent::__call($method, $parameters);
    }

    public function __call($method, $parameters)
    {
        return $this->callMagic($method, $parameters);
    }
}
