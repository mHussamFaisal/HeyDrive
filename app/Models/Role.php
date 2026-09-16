<?php

namespace App\Models;

use App\Traits\Roles\Slugable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use Slugable;
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscription_id',
        'name',
        'slug',
        'description',
        'level',
    ];

    /**
     * Typecast for protection.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'name'          => 'string',
        'slug'          => 'string',
        'description'   => 'string',
        'level'         => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    // This code breaks role creation in admin, empty row is being created.
    // function __construct()
    // {
    //     parent::__construct();
    // }

    /**
     * Role belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class)->withTimestamps();
    }

    /**
     * Role belongs to many users.
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class)->withTimestamps();
    }

    /**
     * Attach permission to a role.
     *
     * @param int|Permission $permission
     *
     * @return int|bool
     */
    public function attachPermission($permission)
    {
        return (!$this->permissions()->get()->contains($permission)) ? $this->permissions()->attach($permission) : true;
    }

    /**
     * Detach permission from a role.
     *
     * @param int|Permission $permission
     *
     * @return int
     */
    public function detachPermission($permission)
    {
        return $this->permissions()->detach($permission);
    }

    /**
     * Detach all permissions.
     *
     * @return int
     */
    public function detachAllPermissions()
    {
        return $this->permissions()->detach();
    }

    /**
     * Sync permissions for a role.
     *
     * @param array|Permission[]|Collection $permissions
     *
     * @return array
     */
    public function syncPermissions($permissions)
    {
        return $this->permissions()->sync($permissions);
    }

    /**
     * @return mixed|string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function getName() {
        $name = trans('roles.names.'.$this->slug);
        return $name != 'roles.names.'.$this->slug ? $name : $this->name;
    }

    public function getSlugGroup() {
        $slug = explode(config('roles.separator'), $this->slug);
        return $slug[0];
    }

    public function scopeByPermissions($query) {
        if (!auth()->user()->hasPermission(['admin.users.admin.create', 'admin.users.admin.edit'])) {
            $query->where('slug', 'not like', 'admin.%');
        }
        if (!auth()->user()->hasPermission(['admin.users.driver.create', 'admin.users.driver.edit'])) {
            $query->where('slug', 'not like', 'driver.%');
        }
        if (!auth()->user()->hasPermission(['admin.users.customer.create', 'admin.users.customer.edit'])) {
            $query->where('slug', 'not like', 'customer.%');
        }
    }

    public function scopeGetListing($query)
    {
        $level = auth()->user()->hasRole('admin.root') ? '<=' : '<';
        $query->whereNotIn('slug', config('roles.not_use_roles'))
            ->where(function ($q) {
                $q->whereNull('subscription_id')
                    ->orWhere('subscription_id', request()->system->subscription->id);
            })
            ->where('level', $level, auth()->user()->level());
    }

    public function getPermissions()
    {
        if (!$this->permissions || count($this->permissions) === 0) {
            $permissionList = [];

            if ($this->slug == 'admin.root' && \Storage::disk('root')->exists('app/Helpers/RolePermissions/admin.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/admin.php')));
            } else if ($this->slug == 'customer.root' && \Storage::disk('root')->exists('app/Helpers/RolePermissions/customer.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/customer.php')));
            } else if ($this->slug == 'driver.root' && \Storage::disk('root')->exists('app/Helpers/RolePermissions/driver.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/driver.php')));
            } else if ($this->slug == 'admin.fleet_operator' && \Storage::disk('root')->exists('app/Helpers/RolePermissions/fleet_operator.php')) {
                $permissionList = array_replace_recursive($permissionList, include(parse_path('app/Helpers/RolePermissions/fleet_operator.php', true)));
            } else if ($this->slug == 'admin.manager' && \Storage::disk('root')->exists('app/Helpers/RolePermissions/manager.php')) {
                $permissionList = array_replace_recursive($permissionList, include(base_path('app/Helpers/RolePermissions/manager.php')));
            } else if ($this->slug == 'admin.operator' && \Storage::disk('root')->exists('app/Helpers/RolePermissions/operator.php')) {
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
                $this->permissions;
            }
        }

        return $this->permissions ?: collect([]);
    }
}
