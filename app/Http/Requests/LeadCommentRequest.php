<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadCommentRequest extends BaseFormRequest
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
            'lead_id' => 'required|integer',
            'comment'  => 'required|string|max:255',
        ];
    }

    public function update()
    {
        return [
            'comment'  => 'required|string|max:255',
        ];
    }


}
