<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends BaseFormRequest
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
//            'user_id' => 'required|integer|max:10000000',
            'user_task_id' => 'required|integer|max:10000000',
            'task_date' => 'required|date',
            'status' => 'nullable|string|max:255',
            'prioritet' => 'required|string|max:255',
            'task_type' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'lead_id' => 'nullable|integer|max:10000000'
        ];
    }

    public function update()
    {
        return [
//            'user_id' => 'required|integer|max:10000000',
            'user_task_id' => 'required|integer|max:10000000',
            'task_date' => 'required|date',
            'status' => 'nullable|string|max:255',
            'prioritet' => 'required|string|max:255',
            'task_type' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'lead_id' => 'nullable|integer|max:10000000'
        ];
    }
}
