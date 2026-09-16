<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // if (config('roles.rolesGuiCreateNewRolesMiddlewareType') == 'role') {
        //    return $this->user()->hasRole(config('roles.rolesGuiCreateNewRolesMiddleware'));
        // }
        // if (config('roles.rolesGuiCreateNewRolesMiddlewareType') == 'permissions') {
        //    return $this->user()->hasPermission(config('roles.rolesGuiCreateNewRolesMiddleware'));
        // }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:roles,name,'.$this->id.',id',
            // 'slug' => 'required|unique:roles,slug,'.$this->id.',id',
            'parent_slug' => Rule::unique('roles', 'slug')->where(function ($query) {
                if (empty($this->slug)) {
                    $this->slug = $this->parent_slug . '.' . snake_case($this->name);
                }

                $query->where('slug', $this->slug)->where('subscription_id', request()->system->subscription->id);
            }),
            'description' => 'nullable|string|max:255',
            // 'level' => 'required|integer',
        ];
    }

    /**
     * Return the fields and values to create a new role.
     *
     * @return array
     */
    public function roleFillData()
    {
        return [
            'subscription_id'   => request()->system->subscription->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'parent_slug'       => $this->parent_slug,
            'description'       => $this->description,
            'level'             => $this->level,
        ];
    }
}
