<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadStatusRequest extends BaseFormRequest
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
            'order' => 'required|integer|max:10000000'
        ];
    }

    public function update()
    {
        return [
            'name' => 'required|string|max:255',
            'order' => 'required|integer|max:10000000'
        ];
    }
}
