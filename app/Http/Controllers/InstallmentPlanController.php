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
    /**
     * @OA\Get(
     *     path="/api/installment-plan/index?page=1",
     *     tags={"Installment-plan"},
     *     summary="Get Installment plans",
     *     description="Get Installment plans",
     *     operationId="plan_index",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status values that needed to be considered for filter",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="available",
     *             type="string",
     *             enum={"available", "pending", "sold"},
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status value"
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     },
     * )
     */
    public function plan_index(Request $request)
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
        $page = $request->page;
        $pagination = Constants::PAGINATION;
        $offset = ($page - 1) * $pagination;
        $endCount = $offset + $pagination;
        $count = count($installment_plans);
        $paginated_results = array_slice($installment_plans, $offset, $pagination);
        $paginatin_count = ceil($count/$pagination);
        return response([
            'status' => true,
            'message' => 'success',
            'data' => $paginated_results,
            "pagination"=>true,
            "pagination_count"=>$paginatin_count
        ]);

    }
    /**
     * @OA\Get(
     *     path="/api/installment-plan/show?id=10",
     *     tags={"Installment-plan"},
     *     summary="Get Installment plan",
     *     description="Get Installment plan",
     *     operationId="plan_show",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",

     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status value"
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     },
     * )
     */
    public function plan_show(Request $request)
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
              'period'=> $model->installmentPlan->period ?? "",
              'user_full_name' => $model && $model->user ? $model->user->first_name . ' ' . $model->user->last_name . ' ' . $model->user->middle_name : '',
              'house_flat_image' =>  asset('/uploads/house-flat/' . $model->house_id . '/m_' . $model->house_flat->main_image->guid) ?? "",
              'installment-plan' => $installment_plan??[],
            ],
        ];
        return response($response);
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
    /**
     * @OA\Post(
     *     path="/api/installment-plan/pay-sum",
     *     tags={"Installment-plan"},
     *     summary="create a task with form data",
     *     operationId="plan_paySum",
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     },
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     description="Installment plan id",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="deal_id",
     *                     description="Deal id",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="sum",
     *                     description="Sum",
     *                     type="integer",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function plan_paySum(Request $request)
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

    /**
     * @OA\Post(
     *     path="/api/installment-plan/remove-payment",
     *     tags={"Installment-plan"},
     *     summary="Make pay status not paid with form data",
     *     operationId="plan_reduceSum",
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     },
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     description="Pay sttus id",
     *                     type="integer",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function plan_reduceSum(Request $request)
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
