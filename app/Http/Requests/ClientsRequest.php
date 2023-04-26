<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ClientsRequest extends BaseFormRequest
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

    public function store()
    {
        return [
            'id' => 'nullable|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:25',
            'additional_phone_number' => 'nullable|string|max:25',
            'email' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'series_number' => 'nullable|string|max:255|unique:mysql.' . DB::connection('mysql')->getDatabaseName() . '.personal_informations',
            'issued_by' => 'nullable|string|max:255',
            'inn' => 'nullable|string|max:255|unique:mysql.' . DB::connection('mysql')->getDatabaseName() . '.personal_informations',
            'looking_for' => 'nullable|string|max:255',
        ];
    }

    public function update()
    {
        return [
            'id' => 'nullable|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:25',
            'additional_phone_number' => 'nullable|string|max:25',
            'email' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
        ];
    }
}
