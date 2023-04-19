<?php

namespace App\Http\Requests;


use Illuminate\Support\Facades\DB;

class DealRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function store()
    {
        return [
            'house_id' => 'nullable|string|max:255',
            'house_flat_number' => 'nullable|string|max:255',
            'doc_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'house_flat_id' => 'required|integer',
            'date_deal' => 'required|date',
            'phone_number' => 'nullable|string|max:255',
            'additional_phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'files' => 'nullable',
            'files.*' => 'mimes:jpeg,jpg,png,pdf,doc,docx,xls,xlsx|max:10240',
            'agreement_number' => 'nullable|string|max:255',
            'price_sell' => 'required|numeric',

            'client_id' => 'integer|nullable',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:255',
            'series_number' => 'required|string|max:20',
            'given_date' => 'required|string|max:500',
            'live_address' => 'required|string|max:500',
            'inn' => 'required|string|max:20',

            'is_installment' => 'nullable',
            'period' => 'integer|nullable',
            'percent' => 'integer|nullable',
            'initial_fee' => 'integer|nullable',
            'installment_date' => 'date|nullable',

            // 'period' => 'required|string|max:255',
            // 'percent' => 'required|string|max:255',
            // 'an_initial_fee' => 'nullable|string|max:255',
            // 'start_date' => 'nullable|date',
            // 'month_pay_first' => 'nullable|string|max:255',
            // 'month_pay_second' => 'nullable|string|max:255',

            // 'pay_date' => 'nullable|date',
            // 'status' => 'nullable|string|max:255',
            // 'pay_start_date' => 'nullable|date',
            // 'pay_end_date' => 'nullable|date',

            // 'booking_id' => 'nullable|integer',
        ];
    }

    public function update()
    {
        return [
            'house_id' => 'nullable|integer|max:255',
            'house_flat_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'house_flat_id' => 'required|integer',
            'date_deal' => 'required|date',
            'phone_number' => 'nullable|string|max:255',
            'additional_phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'files' => 'nullable',
            'files.*' => 'mimes:jpeg,jpg,png,pdf,doc,docx,xls,xlsx|max:10240',
            'agreement_number' => 'nullable|string|max:255',
            'price_sell' => 'required|numeric',

            'client_id' => 'integer|nullable',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:255',
            'series_number' => 'required|string|max:20',
            'given_date' => 'required|string|max:500',
            'live_address' => 'required|string|max:500',
            'inn' => 'required|string|max:20',

            'is_installment' => 'integer|nullable',
            'period' => 'integer|nullable',
            'percent' => 'integer|nullable',
            'initial_fee' => 'numeric|nullable',
            'installment_date' => 'date|nullable',
        ];
    }
}
