<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallmentPlanRequest extends BaseFormRequest
{

    public function authorize()
    {
        return true;
    }

    public function store()
    {
        return [
            'period' => 'nullable|string|max:255',
            'percent' => 'nullable|string|max:255',
            'an_initial_fee' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
        ];
    }

    public function update()
    {
        return [
            'period' => 'nullable|string|max:255',
            'percent' => 'nullable|string|max:255',
            'an_initial_fee' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'month_pay_first' => 'nullable|string|max:255',
            'month_pay_second' => 'nullable|string|max:255',


        ];
    }
}
