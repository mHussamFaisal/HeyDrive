<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (config('roles.rolesGuiCreateNewPermissionMiddlewareType') == 'role') {
            return $this->user()->hasRole(config('roles.rolesGuiCreateNewPermissionsMiddleware'));
        }
        if (config('roles.rolesGuiCreateNewPermissionMiddlewareType') == 'permissions') {
            return $this->user()->hasPermission(config('roles.rolesGuiCreateNewPermissionsMiddleware'));
        }

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
            'name'          => 'required|max:60|unique:permissions,name,'.$this->id.',id',
            'slug'          => 'required|max:60|unique:permissions,slug,'.$this->id.',id',
            'description'   => 'nullable|string|max:255',
            'model'         => 'required|string|max:60',
        ];
    }
}
