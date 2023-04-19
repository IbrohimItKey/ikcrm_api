<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function store()
    {
        return [
            'name' => 'required|string|max:255',
            'percent'  => 'required|integer',
        ];
    }

    public function update()
    {
        return [
            'name' => 'required|string|max:255',
            'percent'  => 'required|integer',
        ];
    }
}
