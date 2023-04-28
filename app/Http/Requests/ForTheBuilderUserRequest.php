<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ForTheBuilderUserRequest extends BaseFormRequest
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
     * @return array
     */
    public function store()
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'role_id' => ['required', 'integer', 'max:200'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'avatar' =>'nullable|mimes:jpeg,jpg,png|max:10240',
            'status' =>'nullable|integer',
        ];
    }
    public function update()
    {
        return [
            'id' => 'required|integer|max:200',
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'role_id' => ['required', 'integer', 'max:200'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => 'nullable|string|min:8|confirmed',
            'current_password' => 'nullable|string|min:8',
            'avatar' =>'nullable|mimes:jpeg,jpg,png|max:10240',
            'status' =>'nullable|integer',
        ];
    }
}
