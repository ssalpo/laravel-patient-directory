<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|min:2|max:255|unique:roles,name,'.$this->route('role'),
            'readable_name' => 'required|min:2|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];
    }
}
