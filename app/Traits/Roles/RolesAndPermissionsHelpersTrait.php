<?php

namespace App\Traits\Roles;

use Illuminate\Support\Facades\DB;

trait RolesAndPermissionsHelpersTrait
{
    /**
     * Gets the roles.
     *
     * @return \App\Models\Role[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getRoles()
    {
        $level = auth()->user()->hasRole('admin.root') ? '<=' : '<';
        $roles = \App\Models\Role::whereNotIn('slug', config('roles.not_use_roles'))
            ->where(function ($q) {
                $q->whereNull('subscription_id')
                    ->orWhere('subscription_id', request()->system->subscription->id);
            })
            ->where('level', $level, auth()->user()->level())
            ->orderBy('slug', 'asc')
            ->orderBy('level', 'asc')
            ->get();

        foreach ($roles as $id=>$role) {
            $roles[$id]->getPermissions();
        }

        return $roles;
    }

    /**
     * Gets the role.
     *
     * @param int $id The identifier
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function getRole($id)
    {
        $item =  \App\Models\Role::withTrashed()->findOrFail($id);

        $item->getPermissions();

        $slugArr = explode('.', $item->slug);
        array_pop($slugArr);
        $item->parent_slug = implode('.', $slugArr);

        $permissionsGroup = [];
        $permissionIds = [];

        foreach ($item->permissions as $permission) {
            $items = explode('.', $permission->slug);
            $roleRoot = $items[0];
            $action = end($items);
            unset($items[0]);
            unset($items[count($items)]);
            $group = implode('.',$items);
            $permission->action = $action;
            $permissionsGroup[$roleRoot][] = $group;
            $permissionIds[] = $permission->id;
        }

        $item->permissions_group = $permissionsGroup;
        $item->permissions_ids = $permissionIds;
        return $item;
    }

    /**
     * Gets the permissions.
     *
     * @return \App\Models\Permission[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions()
    {
        $permissions = \App\Models\Permission::all();
        return $permissions;
    }

    /**
     * Gets the users.
     *
     * @return \App\Models\User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getUsers()
    {
        $users = \App\Models\User::all();
        return $users;
    }

    /**
     * Gets the user.
     *
     * @param int $id The user id
     *
     * @return mixed
     */
    public function getUser($id)
    {
        return \App\Models\User::findOrFail($id);
    }

    /**
     * Gets the deleted permissions.
     *
     * @return mixed
     */
    public function getDeletedPermissions()
    {
        return \App\Models\Permission::onlyTrashed();
    }

    /**
     * Gets the permissions with roles.
     *
     * @param null $roleId
     * @return mixed
     */
    public function getPermissionsWithRoles($roleId = null)
    {
        $query = DB::table('permission_role');
        if ($roleId) {
            $query->where('role_id', '=', $roleId);
        }

        return $query->get();
    }

    /**
     * Gets the permission users.
     *
     * @param null $permissionId
     * @return mixed
     */
    public function getPermissionUsers($permissionId = null)
    {
        $query = DB::table('permission_user');
        if ($permissionId) {
            $query->where('permission_id', '=', $permissionId);
        }

        return $query->get();
    }

    /**
     * Gets the permission models.
     *
     * @return mixed
     */
    public function getPermissionModels()
    {
        return DB::table('permissions')->pluck('model')->merge(collect(class_basename(new \App\Models\Permission())))->unique();
    }

    /**
     * Gets the role permissions.
     *
     * @param $id
     * @return \Illuminate\Support\Collection
     */
    public function getRolePermissions($id)
    {
        $permissionPivots = $this->getPermissionsWithRoles($id);
        $permissions = [];

        if (count($permissionPivots) != 0) {
            foreach ($permissionPivots as $permissionPivot) {
                $permissions[] = \App\Models\Permission::findOrFail($permissionPivot->permission_id);
            }
        }

        return collect($permissions);
    }

    /**
     * Gets the role permissions identifiers.
     *
     * @param int $id The Role Id
     *
     * @return array The role permissions Ids.
     */
    public function getRolePermissionsIds($id)
    {
        $permissionPivots = $this->getPermissionsWithRoles($id);
        $permissionIds = [];

        if (count($permissionPivots) != 0) {
            foreach ($permissionPivots as $permissionPivot) {
                $permissionIds[] = $permissionPivot->permission_id;
            }
        }

        return $permissionIds;
    }

    /**
     * Gets the role permissions identifiers.
     *
     * @param int $id The Role Id
     *
     * @return array The role permissions Ids.
     */
    public function getRolePermissionsGroups($id)
    {
        $role = \App\Models\Role::find($id);
        $groups = [];

        if ($role->id) {
            if (!empty($role->permissions)) {
                foreach ($role->permissions as $permission) {
                    $items = explode('.', $permission->slug);
                    $roleRoot = $items[0];
                    unset($items[0]);
                    unset($items[count($items)]);
                    $group = implode('.',$items);
                    if (empty($groups[$roleRoot]) || (!empty($groups[$roleRoot]) && !in_array($group, $groups[$roleRoot]))) {
                        $groups[$roleRoot][] = $group;
                    }
                }
            }
        }

        return $groups;
    }

    /**
     * Gets the role users.
     *
     * @param int $roleId The role identifier
     *
     * @return array The role users.
     */
    public function getRoleUsers($roleId)
    {
        $queryRolesPivot = DB::table('role_user');
        $users = [];

        if ($roleId) {
            $queryRolesPivot->where('role_id', '=', $roleId);
        }

        $pivots = $queryRolesPivot->get();

        if ($pivots->count() > 0) {
            foreach ($pivots as $pivot) {
                $users[] = $this->getUser($pivot->user_id);
            }
        }

        return $users;
    }

    /**
     * Gets all users for permission.
     *
     * @param $permission
     * @return \Illuminate\Support\Collection
     */
    public function getAllUsersForPermission($permission)
    {
        $roles = $permission->roles()->get();
        $users = [];
        foreach ($roles as $role) {
            $users[] = $this->getRoleUsers($role->id);
        }
        $users = array_shift($users);
        $permissionUserPivots = $this->getPermissionUsers($permission->id);
        if ($permissionUserPivots->count() > 0) {
            foreach ($permissionUserPivots as $permissionUserPivot) {
                $users[] = $this->getUser($permissionUserPivot->user_id);
            }
        }

        return collect($users)->unique();
    }

    /**
     * Retrieves permission roles.
     *
     * @param $permission
     * @param $permissionsAndRolesPivot
     * @param $sortedRolesWithUsers
     * @return \Illuminate\Support\Collection
     */
    public function retrievePermissionRoles($permission, $permissionsAndRolesPivot, $sortedRolesWithUsers)
    {
        $roles = [];
        foreach ($permissionsAndRolesPivot as $permissionAndRoleKey => $permissionAndRoleValue) {
            if ($permission->id === $permissionAndRoleValue->permission_id) {
                foreach ($sortedRolesWithUsers as $sortedRolesWithUsersItemKey => $sortedRolesWithUsersItemValue) {
                    if ($sortedRolesWithUsersItemValue['role']->id === $permissionAndRoleValue->role_id) {
                        $roles[] = $sortedRolesWithUsersItemValue['role'];
                    }
                }
            }
        }

        return collect($roles);
    }

    /**
     * Retrieves permission users.
     *
     * @param $permission
     * @param $permissionsAndRolesPivot
     * @param $sortedRolesWithUsers
     * @param $permissionUsersPivot
     * @param $appUsers
     * @return \Illuminate\Support\Collection
     */
    public function retrievePermissionUsers($permission, $permissionsAndRolesPivot, $sortedRolesWithUsers, $permissionUsersPivot, $appUsers)
    {
        $users = [];
        $userIds = [];

        // Get Users from permissions associated with roles
        foreach ($permissionsAndRolesPivot as $permissionsAndRolesPivotItemKey => $permissionsAndRolesPivotItemValue) {
            if ($permission->id === $permissionsAndRolesPivotItemValue->permission_id) {
                foreach ($sortedRolesWithUsers as $sortedRolesWithUsersItemKey => $sortedRolesWithUsersItemValue) {
                    if ($permissionsAndRolesPivotItemValue->role_id === $sortedRolesWithUsersItemValue['role']->id) {
                        foreach ($sortedRolesWithUsersItemValue['users'] as $sortedRolesWithUsersItemValueUser) {
                            $users[] = $sortedRolesWithUsersItemValueUser;
                        }
                    }
                }
            }
        }

        // Setup Users IDs from permissions associated with roles
        foreach ($users as $userKey => $userValue) {
            $userIds[] = $userValue->id;
        }

        // Get Users from permissions pivot table that are not already in users from permissions associated with roles
        foreach ($permissionUsersPivot as $permissionUsersPivotKey => $permissionUsersPivotItem) {
            if (!in_array($permissionUsersPivotItem->user_id, $userIds) && $permission->id === $permissionUsersPivotItem->permission_id) {
                foreach ($appUsers as $appUser) {
                    if ($appUser->id === $permissionUsersPivotItem->user_id) {
                        $users[] = $appUser;
                    }
                }
            }
        }

        return collect($users);
    }

    /**
     * Gets the sorted users with roles.
     *
     * @param $roles
     * @param $users
     * @return \Illuminate\Support\Collection
     */
    public function getSortedUsersWithRoles($roles, $users)
    {
        $sortedUsersWithRoles = [];

        foreach ($roles as $rolekey => $roleValue) {
            $sortedUsersWithRoles[] = [
                'role'   => $roleValue,
                'users'  => [],
            ];
            foreach ($users as $user) {
                foreach ($user->roles as $userRole) {
                    if ($userRole->id === $sortedUsersWithRoles[$rolekey]['role']['id']) {
                        $sortedUsersWithRoles[$rolekey]['users'][] = $user;
                    }
                }
            }
        }

        return collect($sortedUsersWithRoles);
    }

    /**
     * Gets the sorted roles with permissions.
     *
     * @param $sortedRolesWithUsers
     * @param $permissions
     * @return \Illuminate\Support\Collection
     */
    public function getSortedRolesWithPermissionsAndUsers($sortedRolesWithUsers, $permissions)
    {
        $sortedRolesWithPermissions = [];
        $permissionsAndRoles = $this->getPermissionsWithRoles();

        foreach ($sortedRolesWithUsers as $sortedRolekey => $sortedRoleValue) {
            $role = $sortedRoleValue['role'];
            $users = $sortedRoleValue['users'];
            $sortedRolesWithPermissions[] = [
                'role'          => $role,
                'permissions'   => collect([]),
                'users'         => collect([]),
            ];

            // Add Permission with Role
            foreach ($permissionsAndRoles as $permissionAndRole) {
                if ($permissionAndRole->role_id == $role->id) {
                    foreach ($permissions as $permissionKey => $permissionValue) {
                        if ($permissionValue->id == $permissionAndRole->permission_id) {
                            $sortedRolesWithPermissions[$sortedRolekey]['permissions'][] = $permissionValue;
                        }
                    }
                }
            }

            // Add Users with Role
            foreach ($users as $user) {
                foreach ($user->roles as $userRole) {
                    if ($userRole->id === $sortedRolesWithPermissions[$sortedRolekey]['role']['id']) {
                        $sortedRolesWithPermissions[$sortedRolekey]['users'][] = $user;
                    }
                }
            }
        }

        return collect($sortedRolesWithPermissions);
    }

    /**
     * Gets the sorted permissons with roles and users.
     *
     * @param $sortedRolesWithUsers
     * @param $permissions
     * @param $users
     * @return \Illuminate\Support\Collection
     */
    public function getSortedPermissonsWithRolesAndUsers($sortedRolesWithUsers, $permissions, $users)
    {
        $sortedPermissionsWithRoles = [];
        $permissionsAndRolesPivot = $this->getPermissionsWithRoles();
        $permissionUsersPivot = $this->getPermissionUsers();

        foreach ($permissions as $permissionKey => $permissionValue) {
            $sortedPermissionsWithRoles[] = [
                'permission'    => $permissionValue,
                'roles'         => $this->retrievePermissionRoles($permissionValue, $permissionsAndRolesPivot, $sortedRolesWithUsers),
                'users'         => $this->retrievePermissionUsers($permissionValue, $permissionsAndRolesPivot, $sortedRolesWithUsers, $permissionUsersPivot, $users),
            ];
        }

        return collect($sortedPermissionsWithRoles);
    }

    /**
     * Removes an users and permissions from role.
     *
     * @param $role
     */
    public function removeUsersAndPermissionsFromRole($role)
    {
        $users = $this->getUsers();
        $roles = $this->getRoles();
        $sortedRolesWithUsers = $this->getSortedUsersWithRoles($roles, $users);
        $roleUsers = [];

        // Remove Users Attached to Role
        foreach ($sortedRolesWithUsers as $sortedRolesWithUsersKey => $sortedRolesWithUsersValue) {
            if ($sortedRolesWithUsersValue['role'] == $role) {
                $roleUsers[] = $sortedRolesWithUsersValue['users'];
            }
        }
        foreach ($roleUsers as $roleUserKey => $roleUserValue) {
            if (!empty($roleUserValue)) {
                $roleUserValue[$roleUserKey]->detachRole($role);
            }
        }

        // Remove Permissions from Role
        $role->detachAllPermissions();
    }

    /**
     * Removes an users and permissions from permission.
     *
     * @param $permission
     */
    public function removeUsersAndRolesFromPermissions($permission)
    {
        $users = $this->getUsers();
        $roles = $this->getRoles();
        $permissions = $this->getPermissions();
        $sortedRolesWithUsers = $this->getSortedUsersWithRoles($roles, $users);
        $sortedPermissionsRolesUsers = $this->getSortedPermissonsWithRolesAndUsers($sortedRolesWithUsers, $permissions, $users);

        foreach ($sortedPermissionsRolesUsers as $sortedPermissionsRolesUsersKey => $sortedPermissionsRolesUsersItem) {
            if ($sortedPermissionsRolesUsersItem['permission']->id === $permission->id) {

                // Remove Permission from roles
                foreach ($sortedPermissionsRolesUsersItem['roles'] as $permissionRoleKey => $permissionRoleItem) {
                    $permissionRoleItem->detachPermission($permission);
                }

                // Permission Permission from Users
                foreach ($sortedPermissionsRolesUsersItem['users'] as $permissionUserKey => $permissionUserItem) {
                    $permissionUserItem->detachPermission($permission);
                }
            }
        }
    }
}
