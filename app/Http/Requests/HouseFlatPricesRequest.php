<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HouseFlatPricesRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function store()
    {
        return [
            'house_id' => 'required|integer|max:255',
            'price_type'  => 'required|integer|max:255',
            'payment_type'  => 'required|integer|max:255',
            'amount' => 'required|string|max:1000',
            'house_flats' => 'required|string|max:10000',
        ];
    }

    public function update()
    {
        return [
            'house_id' => 'required|integer|max:255',
            'price_type'  => 'required|integer|max:255',
            'payment_type'  => 'required|integer|max:255',
            'amount' => 'required|string|max:1000',
            'house_flats' => 'required|string|max:10000',
        ];
    }
}
