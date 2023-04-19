<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HouseFlatRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function store()
    {
        return [
            'number_of_flat' => 'required|integer|max:1000000',
            'entrance' => 'required|integer|max:1000000',
            'room_count' => 'nullable|integer|max:1000000',
            'floor' => 'nullable|integer|max:1000000',
            'area_housing' => 'required|numeric|between:0,100000000.99',
            'area_basement' => 'nullable|numeric|between:0,100000000.99',
            'area_terraca' => 'nullable|numeric|between:0,100000000.99',
            'area_attic' => 'nullable|numeric|between:0,100000000.99',
            'area_balcony' => 'nullable|numeric|between:0,100000000.99',
            'area_total' => 'required|numeric|between:0,100000000.99',
            'doc_number' => 'required|string|max:255',
            'price' => 'nullable|numeric|between:0,1000000000000.99',
            'price_30' => 'nullable|numeric|between:0,100000000000.99',
            'price_50' => 'nullable|numeric|between:0,100000000000.99',
            'price_basement' => 'nullable|numeric|between:0,100000000000.99',
            'price_basement_30' => 'nullable|numeric|between:0,100000000000.99',
            'price_basement_50' => 'nullable|numeric|between:0,100000000000.99',
            'price_attic' => 'nullable|numeric|between:0,100000000000.99',
            'price_attic_30' => 'nullable|numeric|between:0,100000000000.99',
            'price_attic_50' => 'nullable|numeric|between:0,100000000000.99',
            'price_terrace' => 'nullable|numeric|between:0,100000000000.99',
            'price_terrace_30' => 'nullable|numeric|between:0,100000000000.99',
            'price_terrace_50' => 'nullable|numeric|between:0,100000000000.99',
            'files' => 'nullable',
            'files' => 'mimes:jpeg,jpg,png,pdf,doc,docx,xls,xlsx|max:40960',
        ];
    }

    public function update()
    {
        return [
            'number_of_flat' => 'required|integer|max:1000000',
            'entrance' => 'required|integer|max:1000000',
            'room_count' => 'nullable|integer|max:1000000',
            'floor' => 'nullable|integer|max:1000000',
            'area_housing' => 'required|numeric|between:0,100000000.99',
            'area_basement' => 'nullable|numeric|between:0,100000000.99',
            'area_terraca' => 'nullable|numeric|between:0,100000000.99',
            'area_attic' => 'nullable|numeric|between:0,100000000.99',
            'area_balcony' => 'nullable|numeric|between:0,100000000.99',
            'area_total' => 'required|numeric|between:0,100000000.99',
            'doc_number' => 'required|string|max:255',
            'price' => 'nullable|numeric|between:0,1000000000000.99',
            'price_30' => 'nullable|numeric|between:0,100000000000.99',
            'price_50' => 'nullable|numeric|between:0,100000000000.99',
            'price_basement' => 'nullable|numeric|between:0,100000000000.99',
            'price_basement_30' => 'nullable|numeric|between:0,100000000000.99',
            'price_basement_50' => 'nullable|numeric|between:0,100000000000.99',
            'price_attic' => 'nullable|numeric|between:0,100000000000.99',
            'price_attic_30' => 'nullable|numeric|between:0,100000000000.99',
            'price_attic_50' => 'nullable|numeric|between:0,100000000000.99',
            'price_terrace' => 'nullable|numeric|between:0,100000000000.99',
            'price_terrace_30' => 'nullable|numeric|between:0,100000000000.99',
            'price_terrace_50' => 'nullable|numeric|between:0,100000000000.99',
            'files' => 'nullable',
            'files' => 'mimes:jpeg,jpg,png,pdf,doc,docx,xls,xlsx|max:40960',
        ];
    }
}
