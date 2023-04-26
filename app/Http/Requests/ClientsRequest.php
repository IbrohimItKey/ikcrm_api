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

            // 'gender' => 'nullable|integer',
            // 'referer' => 'nullable|string|max:65500',
            // 'requestid' => 'nullable|string|max:255',
            // 'status' => 'nullable|integer',
            // // 'series_number' => 'required|string|max:255',
            // 'phone_code' => 'required|string',
            // 'interview_date' => 'nullable|date',
            // 'user_id' => 'nullable|integer',
        ];
    }

    public function update()
    {
        return [
            'client_id' => 'nullable|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:25',
            'additional_phone' => 'nullable|string|max:25',
            'email' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'lead_status' => 'required|string|max:25',
            'series_number' => 'nullable|string|max:255|unique:mysql.' . DB::connection('mysql')->getDatabaseName() . '.clients',
            'issued_by' => 'nullable|string|max:255',
            'inn' => 'nullable|string|max:255|unique:mysql.' . DB::connection('mysql')->getDatabaseName() . '.personal_informations',
            'looking_for' => 'nullable|string|max:255',

            // 'name' => 'required|string|max:255',
            // 'surname' => 'required|string|max:255',
            // 'patronymic' => 'nullable|string|max:255',
            // 'email' => 'nullable|string|max:255',
            // 'source' => 'nullable|string|max:255',
            // 'phone' => 'required|string|max:25',
            // 'additional_phone' => 'nullable|string|max:25',
            // // 'series_number' => 'nullable|string|max:255',
            // 'series_number' => 'required|string|max:255|unique:mysql.' . DB::connection('mysql')->getDatabaseName() . '.clients',
            // // 'series_number' => 'nullable|string|max:255',
            // 'issued_by' => 'nullable|string|max:255',
            // 'inn' => 'nullable|string|max:255',
            // 'referer' => 'nullable|string|max:65500',
            // 'requestid' => 'nullable|string|max:255',
            // 'phone_code' => 'required|string',
            // 'lead_status_id' => 'nullable|string|max:25',
            // 'interview_date' => 'nullable|date',
            // 'user_id' => 'nullable|integer',
        ];
    }
}
