<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Deal;
use App\Models\HouseFlat;
use App\Models\InstallmentPlan;
// use App\Models\Leads;
use App\Models\Notification_;
use App\Models\PayStatus;
use App\Models\Constants;
use App\Http\Requests\InstallmentPlanRequest;

class InstallmentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function getNotification()
    {
        $notification = ['Booking', 'BookingPrepayment'];
        $all_task = Notification_::where('type', 'Task')->where(['read_at' => NULL,  'user_id' => Auth::user()->id])->orderBy('created_at', 'desc')->get();
        $all_booking = Notification_::whereIn('type', $notification)->where('read_at', NULL)->orderBy('created_at', 'desc')->get();
        return ['all_task' => $all_task, 'all_booking' => $all_booking];
    }

    public function index()
    {
        // $models = Deal::where('installment_plan_id', '!=', NULL);
        $models = Deal::with('house_flat', 'user', 'client')->where('installment_plan_id', '!=', NULL)
//            ->paginate(config('params.pagination'));
        ->get();
        // pre($models[0]->plan);
        $installment_plans = [];
        foreach ($models as $model){
            $installment_plans[] = [
                'id'=>$model->id,
                'client_first_name'=>$model->client->first_name,
                'client_last_name'=>$model->client->last_name,
                'client_middle_name'=>$model->client->middle_name,
                'agreement_number'=>$model->agreement_number,
                'price_sell'=>number_format($model->price_sell, 2),
                'period'=>$model->installmentPlan->period ?? 0,
            ];
        }
        $response = [
            "status" => true,
            "message" => "success",
            "data" => $installment_plans
        ];
        return response($response);

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('forthebuilder::installment-plan.create', ['all_notifications' => $this->getNotification()]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request)
    {
        $model = Deal::findOrFail($request->id);
        $statuses = PayStatus::where('deal_id', $request->id)->get();
        //        $statuses = $model->status();
        $client_first_name = $model->client->first_name ?? '';
        $client_last_name = $model->client->last_name ?? '';
        $client_middle_name = $model->client->middle_name ?? '';
        foreach ($statuses as $status){
            switch($status->status){
                case 0:
                    $status_name = 'Не оплачен';
                    break;
                case 1:
                    $status_name = 'Оплачен';
                    break;
                case 2:
                    $status_name = 'Частичная оплата';
                    break;
                default:
                    $status_name = 'Не оплачен';
            }
            $installment_plan[] = [
                'id' => $status->id,
                'pay_date' => $status->must_pay_date,
                'price_to_pay' => $status->price_to_pay,
                'status' => $status_name,
            ];
        }

        $response = [
            "status" => true,
            "message" => "success",
            'data' => [
              'client_full_name'=> $client_first_name.' '.$client_last_name.' '.$client_middle_name,
              'client_email'=> $model->client->email ?? '',
              'client_phone'=>$model->phone ?? '',
              'client_series_number'=>$model->client->informations->series_number ?? '' ,
              'initial_fee_date'=> date('d.m.Y', strtotime($model->initial_fee_date)),
              'agreement_number'=> $model->agreement_number ?? '',
              'price_sell'=> number_format($model->price_sell, 2, ',', '.'),
              'initial_fee'=> number_format($model->initial_fee, 2, ',', '.'),
              'period'=> $model->installmentPlan->period ,
              'user_full_name' => $model && $model->user ? $model->user->first_name . ' ' . $model->user->last_name . ' ' . $model->user->middle_name : '',
              'house_flat_image' =>  asset('/uploads/house-flat/' . $model->house_id . '/m_' . $model->house_flat->main_image->guid),
              'installment-plan' => $installment_plan,
            ],
        ];
        return response($response);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $model = InstallmentPlan::findOrFail($id);
        return view('forthebuilder::installment-plan.edit', [
            'model' => $model,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(InstallmentPlanRequest $request, $id)
    {
        $data = $request->validated();
        $model = InstallmentPlan::findOrFail($id);

        $model->period = $data['period'];
        $model->percent = $data['percent'];
        $model->an_initial_fee = $data['an_initial_fee'];
        $model->start_date = $data['start_date'];
        $model->month_pay_first = $data['month_pay_first'];
        $model->month_pay_second = $data['month_pay_second'];
        $model->save();

        Log::channel('action_logs2')->info("пользователь обновил Installment plan", ['info-data' => $model]);
        return redirect()->route('forthebuilder.installment-plan.index')->with('success', __('locale.successfully'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $plans = InstallmentPlan::findOrFail($id);
        $plans->delete();

        Log::channel('action_logs2')->info("пользователь удалил " . $plans->period . " Plans", ['info-data' => $plans]);

        return back()->with('success', __('locale.deleted'));
    }

    public function getStatus($id)
    {

        $statuses = PayStatus::where(['installment_plan_id' => $id])->orderBy('pay_start_date', 'asc')->get();

        return response()->json([
            'statuses' => $statuses,
        ]);
    }

    public function paySum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'string|max:25',
        ]);
        if ($validator->fails()) {
            return response()->json($validator);
        }
        $model_paystatus = PayStatus::where(['installment_plan_id' => $request->id, 'deal_id' => $request->deal_id])
            ->WhereIn('status', [Constants::HALF_PAY, Constants::NOT_PAID])->orderBy('id', 'asc')->get();
        // pre($model_paystatus);
        $paystatus_id = [];
        if (!empty($model_paystatus)) {
            $payingSum = $request->sum;
            foreach ($model_paystatus as $key => $value) {
                $value->pay_date = date('Y-m-d');
                $arr = $value->price_history ? json_decode($value->price_history) : [];
                $oldPrice = $value->price_to_pay;
                if ($value->price_to_pay == $payingSum) {
                    $paystatus_id[] = $value->id;
                    $value->price_to_pay = 0;
                    $value->status = Constants::PAID;
                    $arr[] = ['date' => date('Y-m-d H:i:s'), 'price' => $oldPrice - $value->price_to_pay];
                    $value->price_history = json_encode($arr);
                    $value->save();
                    $payingSum = 0;
                    break;
                } else if ($value->price_to_pay > $payingSum && $payingSum != 0) {
                    $paystatus_id[] = $value->id;
                    $value->price_to_pay = $value->price_to_pay - $payingSum;
                    $value->status = Constants::HALF_PAY;
                    $arr[] = ['date' => date('Y-m-d H:i:s'), 'price' => $oldPrice - $value->price_to_pay];
                    $value->price_history = json_encode($arr);
                    $value->save();
                    $payingSum = 0;
                    break;
                } else if ($value->price_to_pay < $payingSum) {
                    $paystatus_id[] = $value->id;
                    $value->price_to_pay = 0;
                    $value->status = Constants::PAID;
                    $arr[] = ['date' => date('Y-m-d H:i:s'), 'price' => $oldPrice - $value->price_to_pay];
                    $value->price_history = json_encode($arr);
                    $value->save();
                    $payingSum = $payingSum - $oldPrice;
                    if ($payingSum <= 0)
                        break;
                }
            }
        }
        $response = [
            "status" => true,
            "message" => "success",
            'id' => $paystatus_id
        ];
        return response($response);
    }

    // public function paySum(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'status' => 'string|max:25',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator);
    //     }
    //     $paystatus = PayStatus::findOrFail($request->id);
    //     $model_paystatus = PayStatus::where(['installment_plan_id' => $paystatus->installment_plan_id])
    //         ->WhereIn('status', ["Част. оплата", "Не оплачен"])->first();
    //     $monthly_sum = 0;
    //     if ($paystatus->plan->period == '12 месяц') {
    //         $monthly_sum = ($paystatus->plan->all_sum - $paystatus->plan->an_initial_fee) / 12;
    //     } else {
    //         $monthly_sum = ($paystatus->plan->all_sum - $paystatus->plan->an_initial_fee) / 18;
    //     }
    //     $sum = $model_paystatus->sum ?? 0;
    //     $j = floor(($request->sum + $sum) / $monthly_sum);
    //     $the_rest_sum = $request->sum + $sum - $monthly_sum * $j;
    //     for ($i = 0; $i < $j; $i++) {
    //         $model = PayStatus::findOrFail($model_paystatus->id + $i);
    //         $model->status = 'Оплачен';
    //         $model->sum = $monthly_sum;
    //         $model->save();
    //     }
    //     $model = PayStatus::findOrFail($model_paystatus->id + $j);
    //     $model->status = 'Част. оплата';
    //     $model->sum = round($the_rest_sum, 2);
    //     $model->save();

    //     return redirect()->back()->with('success', 'Статус измeнён');
    // }

    public function reduceSum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'string|max:25',
        ]);
        if ($validator->fails()) {
            return response()->json($validator);
        }
        if (Auth::user()->status == 1000) {
            $model = PayStatus::findOrFail($request->id);
            $model->status = Constants::NOT_PAID;
            $model->price_to_pay = $model->price;
            $model->pay_date = NULL;
            $model->save();
        } else {
            $response = [
                "status" => true,
                "message" => "not found",
            ];
            return response($response);
        }
        $response = [
            "status" => true,
            "message" => "success",
            'id' => $model->id
        ];
        return response($response);
    }

    public function updateStatus(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'status' => 'string|max:25',
        ]);

        if ($validator->fails()) {
            return response()->json($validator);
        }
        if ($request->ajax()) {

            $model = PayStatus::findOrFail($id);

            $model->status = $request->status;
            $model->save();

            return response()->json([
                'id' => $id,
                'status' => $request->status,
                'success' => 'Статус измeнён'
            ]);
        }
    }
}
