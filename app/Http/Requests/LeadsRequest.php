<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class LeadsRequest extends BaseFormRequest
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
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'phone' => 'required|string|max:25',
            'additional_phone' => 'nullable|string|max:25',
            'series_number'=>'required|string|max:255|unique:mysql2.'.DB::connection('mysql2')->getDatabaseName().'.leads',
//            'series_number'=>'required|string|max:255',
            'issued_by' => 'nullable|string|max:255',
            'inn' => 'nullable|string|max:255',
            'referer' => 'nullable|string|max:65500',
            'requestid' => 'nullable|string|max:255',
            'phone_code' => 'required|string',
            'lead_status_id' => 'required|string|max:25',
            'interview_date' => 'nullable|date',
            'user_id' => 'nullable|integer',
        ];
    }

    public function update()
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'phone' => 'required|string|max:25',
            'additional_phone' => 'nullable|string|max:25',
            // 'series_number' => 'nullable|string|max:255',
//            'series_number'=>'required|string|max:255|unique:mysql2.'.DB::connection('mysql2')->getDatabaseName().'.leads',
            'series_number' => 'nullable|string|max:255',
            'issued_by' => 'nullable|string|max:255',
            'inn' => 'nullable|string|max:255',
            'referer' => 'nullable|string|max:65500',
            'requestid' => 'nullable|string|max:255',
            'phone_code' => 'required|string',
            'lead_status_id' => 'nullable|string|max:25',
            'interview_date' => 'nullable|date',
            'user_id' => 'nullable|integer',
        ];
    }

}
