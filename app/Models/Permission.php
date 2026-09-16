<?php

namespace App\Models;

use App\Traits\Roles\Slugable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
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
        'name',
        'slug',
        'description',
        'model',
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
        'model'         => 'string',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    /**
     * Permission belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class)->withTimestamps();
    }

    /**
     * Permission belongs to many users.
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class)->withTimestamps();
    }

    /**
     * @return mixed|string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function getName() {
        $permissionsGroup = $this->getSlugPartial();
        return $permissionsGroup['name'] . ' - ' . $permissionsGroup['action'];
    }

    /**
     * @return mixed|string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function getSlugPartial() {
        $permissionsGroup = [];
        $slugPartial = explode(config('roles.separator'), $this->slug);
        $slugPartialLast = end($slugPartial);
        array_pop($slugPartial);
        $slugPartial = implode(config('roles.separator'), $slugPartial);
        $permissionsGroup['slug'] = $slugPartial;
        $permissionsGroup['action'] = trans('roles.actions.'.$slugPartialLast);
        $permissionsGroup['name'] = trans('roles.permissions.'.$slugPartial);

        return $permissionsGroup;
    }
}
