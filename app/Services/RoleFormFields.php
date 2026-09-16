<?php

namespace App\Services;

use App\Traits\Roles\RolesAndPermissionsHelpersTrait;

class RoleFormFields
{
    use RolesAndPermissionsHelpersTrait;

    /**
     * List of fields and default value for each field.
     *
     * @var array
     */
    protected $fieldList = [
        'name'              => '',
        'subscription_id'   => 0,
        'slug'              => '',
        'parent_slug'       => '',
        'description'       => '',
        'level'             => '',
        'permissions'       => [],
    ];

    /**
     * Create a new job instance.
     *
     * @param int $id
     *
     * @return void
     */
    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fields = $this->fieldList;
        $rolePermissionsIds = [];
        $rolePermissionsGroups = [];

        if ($this->id) {
            $fields = $this->fieldsFromModel($this->id, $fields);
            $rolePermissionsIds = $this->getRolePermissionsIds($this->id);
            $rolePermissionsGroups = $this->getRolePermissionsGroups($this->id);
        }

        foreach ($fields as $fieldName => $fieldValue) {
            $fields[$fieldName] = old($fieldName, $fieldValue);
        }

        // Get the additional data for the form fields
        $roles = $this->getRoles();
        $permissions = \App\Models\Permission::orderBy('slug', 'asc')->get();


        $groups = [];
        $rolesToJs = [];

        foreach ($roles as $id => $role) {
            $rolesToJs[$id] = $role->toArray();
            $rolesToJs[$id]['permissions'] = $role->permissions;
        }

        foreach ($permissions as $permission) {
            $items = explode('.', $permission->slug);
            $roleRoot = $items[0];
            $action = end($items);
            unset($items[0]);
            unset($items[count($items)]);
            $group = implode('.',$items);
            $permission->name = trans('roles.actions_permission.' . $action, ['permission'=>'']);
            $groups[$roleRoot][$group]['permissions'][] = $permission;
            $groups[$roleRoot][$group]['name'] = trans('roles.permissions.' . $roleRoot . '.' . $group);
            $groups[$roleRoot][$group]['group'] = $group;
            $groups[$roleRoot][$group]['slug'] = $role->slug;
        }


        foreach ($groups as $rid=>$role) {
            usort($groups[$rid], function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });
        }

        return array_merge(
            $fields, [
                'groups'                => $groups,
                'permissions'           => $permissions,
                'rolePermissionsIds'    => $rolePermissionsIds,
                'rolePermissionsGroups' => $rolePermissionsGroups,
                'roles'                 => $roles,
                'rolesToJs'             => $rolesToJs,
            ]
        );
    }

    /**
     * Return the field values from the model.
     *
     * @param int   $id
     * @param array $fields
     *
     * @return array
     */
    protected function fieldsFromModel($id, array $fields)
    {
        $role = \App\Models\Role::findOrFail($id);
        $slugArray = explode(config('roles.separator'), $role->slug);

        if (count($slugArray) > 1) {
            array_pop($slugArray);
        }

        $role->parent_slug = implode(config('roles.separator'), $slugArray);
        $fieldNames = array_keys(array_except($fields, ['permissions']));

        $fields = [
            'id' => $id,
        ];
        foreach ($fieldNames as $field) {
            $fields[$field] = $role->{$field};
        }

        $fields['permissions'] = $role->permissions();

        return $fields;
    }
}
