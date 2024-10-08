<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
     * @return array<string, mixed>3
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3|max:255',
            'print_name' => 'nullable|min:3|max:255',
            'username' => 'required|min:3|max:255|unique:users,username,'.$this->route('user'),
            'password' => 'min:2|max:255'.($this->isMethod('PUT') ? '|nullable' : '|required'),
            'role' => 'required|exists:roles,name',
            'doctor_id' => 'nullable|exists:doctors,id',
        ];
    }
}
