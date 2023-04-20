<?php

namespace App\Exports;

use App\Models\Leads;
use App\Models\InstallmentPlan;
use App\Models\PayStatus;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Currency;

class RassrochkaExport implements FromView
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        $model = InstallmentPlan::findOrFail($this->id);
        $statuses = PayStatus::where('installment_plan_id', $this->id)->get();
        $currency = Currency::select('USD', 'SUM')->orderBy('created_at', 'desc')->first();

        return view('forthebuilder::exports.rassrochka', [
            'model' => $model,
            'statuses' => $statuses,
            'currency' => $currency,
        ]);
    }

}
