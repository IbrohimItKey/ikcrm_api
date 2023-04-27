<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Clients;
use App\Models\Notification_;
use App\Models\PersonalInformations;
use App\Http\Requests\ClientsRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\User;
use App\Models\Booking;
use App\Models\Chat;
use App\Models\Constants;
use App\Models\PayStatus;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    // public function index()
    // {
    //     $models = Clients::orderBy('id', 'desc')->paginate(config('params.pagination'));

    //     return view('forthebuilder::clients.index', [
    //         'models' => $models
    //     ]);
    // }

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
     *     path="/api/clients",
     *     tags={"Clients"},
     *     summary="Get Clients",
     *     description="Get Clients",
     *     operationId="Index",
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
    public function Index()
    {
        $models = Deal::with('house', 'client')
            ->orderBy('type', 'asc')->get(); //->paginate(config('params.pagination'));

        $defaultAction = [
            Constants::FIRST_CONTACT => 'First contact',
            Constants::NEGOTIATION => 'Negotiation',
            Constants::MAKE_DEAL => 'Making a deal',
        ];

        $arr = [];
        if (!empty($models)) {
            $i = 0;
            foreach ($models as $value) {
                if ($value->client) {
                    $arr[$i]['id'] = $value->id;
                    $arr[$i]['last_name'] = $value->client->last_name ?? '';
                    $arr[$i]['first_name'] = $value->client->first_name ?? '';
                    $arr[$i]['middle_name'] = $value->client->middle_name ?? '';
                    $arr[$i]['deal_object'] = $value->house->name ?? '';
                    $arr[$i]['sum'] = $value->price_sell;
                    $arr[$i]['status'] = $value->tasks ? $value->tasks->title : $defaultAction[$value->type];
                    $i++;
                }
            }
        }

        $response = [
            'status' => true,
            'message' => 'success',
            "pagination" => false,
            "pagination_count" => 0,
            'data' => $arr,
        ];
        return response($response);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function allClients()
    {
        $models = Clients::orderBy('id', 'desc')->get(); //->paginate(config('params.pagination'));

        $arr = [];
        if (!empty($models)) {
            $i = 0;
            foreach ($models as $value) {
                $arr[$i]['id'] = $value->id;
                $arr[$i]['last_name'] = $value->last_name ?? '';
                $arr[$i]['first_name'] = $value->first_name ?? '';
                $arr[$i]['middle_name'] = $value->middle_name ?? '';
                $arr[$i]['status'] = ($value->status == Constants::CLIENT_ACTIVE) ? "Aktive" : "Archive";
                $arr[$i]['last_activ'] = date('d.m.Y H:i', strtotime($value->created_at));
                $i++;
            }
        }

        $response = [
            'status' => true,
            'message' => 'success',
            "pagination" => false,
            "pagination_count" => 0,
            'data' => $arr,
        ];
        return response($response);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function insert(ClientsRequest $request)
    {
        $data = $request->validated();

        $user = User::where(['token' => $request->header('token')])->first();
        $auth_user_id = 0;
        if (isset($user))
            $auth_user_id = $user->id;

        $newClient = new Clients();
        $newClient->user_id = $auth_user_id;
        $newClient->first_name = $data['first_name'];
        $newClient->last_name = $data['last_name'];
        $newClient->middle_name = $data['middle_name'];
        $newClient->phone = $data['phone'];
        $newClient->additional_phone = $data['additional_phone_number'];
        $newClient->email = $data['email'];
        $newClient->source = $data['source'];
        $newClient->status = Constants::CLIENT_ACTIVE;
        $newClient->save();

        $client_id = $newClient->id;
        if (isset($data['series_number'])) {
            $newPersonalInfo = new PersonalInformations();
            $newPersonalInfo->client_id = $newClient->id;
            $newPersonalInfo->issued_by = $data['issued_by'];
            $newPersonalInfo->series_number = $data['series_number'];
            $newPersonalInfo->inn = $data['inn'];
            $newPersonalInfo->save();
        }

        $model = new Deal();
        $model->user_id = $auth_user_id;
        $model->client_id = $client_id;
        $model->date_deal = date('Y-m-d');
        $model->status = Constants::ACTIVE;
        $model->type = Constants::FIRST_CONTACT;
        $model->looking_for = $data['looking_for'];
        $model->save();

        $response = ['status' => true, 'message' => 'Success', 'id' => $client_id];
        return response($response);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(ClientsRequest $request)
    {
        $data = $request->validated();

        $model = Clients::find($data['id']);
        $model->first_name = $data['first_name'];
        $model->last_name = $data['last_name'];
        $model->middle_name = $data['middle_name'];
        $model->phone = $data['phone'];
        $model->additional_phone = $data['additional_phone_number'];
        $model->email = $data['email'];
        $model->source = $data['source'];
        $model->save();

        $response = ['status' => true, 'message' => 'Success', 'id' => $model->id];
        return response($response);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function delete(Request $request)
    {
        $id = $request['id'];
        DB::beginTransaction();
        try {
            $response = ['status' => false, 'message' => ''];

            $leads = Clients::where(['id' => $id, 'status' => Constants::CLIENT_ACTIVE])->first();
            if (!isset($leads)) {
                $response['message'] = 'This client is not active';
                return response($response);
            }
            $leads->status = Constants::CLIENT_DELETED;

            $deals = Deal::select('id', 'client_id')->where('client_id', $id);
            $activeDeals = $deals->whereIn('status', [Constants::ACTIVE, Constants::NOT_IMPLEMENTED])->where('type', Constants::MAKE_DEAL)->first();
            if (isset($activeDeals) && !empty($activeDeals)) {
                $response['message'] = 'The client has active deals';
                return response($response);
            }

            $deals = $deals->get();
            $deal_id = [];
            foreach ($deals as $deal) {
                $deal_id[] = $deal->id;
                $deal->deleted_at = date("Y-m-d H:i:s");
                $deal->status = Constants::NOT_IMPLEMENTED;
                $deal->save();
            }

            $payStatus = PayStatus::whereIn('deal_id', $deal_id)->whereIn('status', [Constants::NOT_PAID, Constants::HALF_PAY])->first();
            if (isset($payStatus) && !empty($payStatus)) {
                $response['message'] = 'The client has active installment plan';
                return response($response);
            }

            $tasks = Task::whereIn('deal_id', $deal_id)->get();
            foreach ($tasks as $task) {
                $task->deleted_at = date("Y-m-d H:i:s");
                $task->status = Constants::DID_NOT_DO_IT;
                $task->save();
            }

            $bookings = Booking::whereIn('deal_id', $deal_id);
            $issetBooking = $bookings->where('status', Constants::BOOKING_ACTIVE)->first();
            if (isset($issetBooking)) {
                $response['message'] = 'The client has active bookings';
                return response($response);
            }

            $bookings = $bookings->where('status', Constants::BOOKING_ACTIVE)->get();
            foreach ($bookings as $booking) {
                $booking->deleted_at = date("Y-m-d H:i:s");
                $booking->status = Constants::BOOKING_ARCHIVE;
                $booking->save();
            }

            $chats = Chat::whereIn('deal_id', $deal_id)->get();
            foreach ($chats as $chat) {
                $chat->deleted_at = date("Y-m-d H:i:s");
                $chat->save();
            }
            $leads->save();

            // Log::channel('action_logs2')->info("пользователь удалил " . $leads->name . " Лиды", ['info-data' => $leads]);
            DB::commit();
            $response['status'] = true;
            $response['message'] = 'Data deleted successfuly';
            return response($response);
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    public function show(Request $request)
    {
        $id = $request->id;
        $model = Clients::findOrFail($id);

        $sqlQuery = "
            SELECT
                h.name, h.corpus, hf.number_of_flat, hf.`floor`, hf.entrance, hf.room_count, T.*
            FROM (
                SELECT
                    t.id, d.id AS deal_id, d.status AS deal_status, d.type AS deal_type, d.budget, d.looking_for, d.house_flat_id, d.house_id, c.id AS client_id, t.created_at AS created_at, CAST(t.title AS CHAR) AS text, c.last_name, c.first_name, c.middle_name,
                    '' AS old_type, '' AS new_type, '' as image,
                    'task' AS status
                FROM deals AS d
                INNER JOIN clients AS c ON c.id = d.client_id
                INNER JOIN task AS t ON t.deal_id = d.id
                WHERE d.client_id = " . $id . "

                UNION

                SELECT
                    ch.id, d.id AS deal_id, d.status AS deal_status, d.type AS deal_type, d.budget, d.looking_for, d.house_flat_id, d.house_id, c.id AS client_id, ch.created_at AS created_at, CAST(ch.text AS CHAR) AS text, c.last_name, c.first_name, c.middle_name,
                    '' AS old_type, '' AS new_type, '' as image,
                    'chat' AS status
                FROM deals AS d
                INNER JOIN clients AS c ON c.id = d.client_id
                INNER JOIN chat AS ch ON ch.deal_id = d.id
                WHERE d.client_id = " . $id . "

                UNION

                SELECT
                    d.id, d.id AS deal_id, d.status AS deal_status, d.type AS deal_type, d.budget, d.looking_for, d.house_flat_id, d.house_id, c.id AS client_id,
                    JSON_UNQUOTE(JSON_EXTRACT(d.history, CONCAT('$[', pseudo_rows.row, '].date'))) AS created_at,
                    '' AS text, c.last_name, c.first_name, c.middle_name,
                    JSON_UNQUOTE(JSON_EXTRACT(d.history, CONCAT('$[', pseudo_rows.row, '].old_type'))) AS old_type,
                    JSON_UNQUOTE(JSON_EXTRACT(d.history, CONCAT('$[', pseudo_rows.row, '].new_type'))) AS new_type,
                    JSON_UNQUOTE(JSON_EXTRACT(d.history, CONCAT('$[', pseudo_rows.row, '].user_photo'))) AS image,
                    'history' AS STATUS
                FROM deals AS d
                INNER JOIN clients AS c
                JOIN pseudo_rows
                WHERE c.id = " . $id . "
                HAVING old_type IS NOT NULL
            ) AS T
            LEFT JOIN house_flat AS hf ON hf.id = T.house_flat_id
            LEFT JOIN house AS h ON h.id = T.house_id
            -- LEFT JOIN deals_files AS df ON df.deal_id = T.deal_id
            ORDER BY T.deal_id, T.created_at ASC
            ";

        $results = DB::select($sqlQuery);

        $arr = [];
        if (isset($results) && !empty($results)) {
            $n = -1;
            $h = 0;
            $d = -1;
            $has = [];
            $hasDate = [];
            $default = true;
            foreach ($results as $key => $value) {
                // return $value->deal_id;
                if (!in_array($value->deal_id, $has)) {
                    $has[] = $value->deal_id;
                    $n++;
                    $d = -1;
                    $default = true;
                }

                if (!in_array(date('Y-m-d', strtotime($value->created_at)) . $value->deal_id, $hasDate)) {
                    $hasDate[] = date('Y-m-d', strtotime($value->created_at)) . $value->deal_id;
                    $h = 0;
                    $d++;
                }

                $arr['id'] = $model->id;
                $arr['user_last_name'] = $model->user ? $model->user->last_name : '';
                $arr['user_first_name'] = $model->user ? $model->user->first_name : '';
                $arr['user_middle_name'] = $model->user ? $model->user->middle_name : '';
                $arr['client_last_name'] = $model->last_name;
                $arr['client_first_name'] = $model->first_name;
                $arr['client_middle_name'] = $model->middle_name;
                $arr['phone'] = $model->phone;
                $arr['email'] = $model->email;

                $type = '';
                if ($value->deal_type == Constants::FIRST_CONTACT)
                    $type = 'Первый контакт';
                else if ($value->deal_type == Constants::NEGOTIATION)
                    $type = 'Переговоры';
                else if ($value->deal_type == Constants::MAKE_DEAL)
                    $type = 'Оформление сделки';

                $arr['deals'][$n]['deal_id'] = $value->deal_id;
                $arr['deals'][$n]['status'] = $type;
                $arr['deals'][$n]['budget'] = $value->budget;
                $arr['deals'][$n]['looking_for'] = $value->looking_for;
                $arr['deals'][$n]['interested'] = $value->name . ' ' . $value->corpus . ': Подъезд' . $value->entrance . ': ' . $value->number_of_flat . 'кв';
                $arr['deals'][$n]['images'] = [];

                if ($default) {
                    $default = false;
                    $arr['deals'][$n]['history'][$d]['date'] = date('Y-m-d', strtotime($model->created_at));
                    // return $arr;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['status'] = 'default';
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['id'] = $model->id;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['created_at'] = date('Y-m-d H:i:s', strtotime($model->created_at));
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['client_last_name'] = $model->last_name;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['client_first_name'] = $model->first_name;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['client_middle_name'] = $model->middle_name;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['user_last_name'] = $model->user ? $model->user->last_name : '';
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['user_first_name'] = $model->user ? $model->user->first_name : '';
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['user_middle_name'] = $model->user ? $model->user->middle_name : '';
                    $h++;
                    $d++;
                }

                if (date('Y-m-d', strtotime($model->created_at)) != date('Y-m-d', strtotime($value->created_at)))
                    $arr['deals'][$n]['history'][$d]['date'] = date('Y-m-d', strtotime($value->created_at));
                else
                    $d--;

                if ($value->status == 'chat' || $value->status == 'task') {
                    $arr['deals'][$n]['history'][$d]['list'][$h]['status'] = $value->status;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['id'] = $value->id;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['created_at'] = date('Y-m-d H:i:s', strtotime($value->created_at));
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['text'] = $value->text;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['last_name'] = $value->last_name;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['first_name'] = $value->first_name;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['middle_name'] = $value->middle_name;
                }

                if ($value->status == 'history') {
                    $historyOldType = 'Первый контакт';
                    if ($value->old_type == Constants::FIRST_CONTACT)
                        $historyOldType = 'Первый контакт';
                    else if ($value->old_type == Constants::NEGOTIATION)
                        $historyOldType = 'Переговоры';
                    else if ($value->old_type == Constants::MAKE_DEAL)
                        $historyOldType = 'Оформление сделки';

                    $historyNewType = 'Первый контакт';
                    if ($value->new_type == Constants::FIRST_CONTACT)
                        $historyNewType = 'Первый контакт';
                    else if ($value->new_type == Constants::NEGOTIATION)
                        $historyNewType = 'Переговоры';
                    else if ($value->new_type == Constants::MAKE_DEAL)
                        $historyNewType = 'Оформление сделки';

                    $arr['deals'][$n]['history'][$d]['list'][$h]['status'] = $value->status;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['created_at'] = date('Y-m-d H:i:s', strtotime($value->created_at));
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['last_name'] = $value->last_name;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['first_name'] = $value->first_name;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['middle_name'] = $value->middle_name;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['image'] = $value->image;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['old_type'] = $historyOldType;
                    $arr['deals'][$n]['history'][$d]['list'][$h]['list']['new_type'] = $historyNewType;
                }

                $h++;
            }
        }

        $responce = ['status' => true, 'message' => 'success', 'data' => $arr];
        return response($responce);
    }

    public function calendar()
    {
        $user = Auth::user();
        $models = Task::where('deleted_at', NULL)->get();
        foreach ($models as $model) {
            $tasks[] = [
                'id' => $model->id,
                // 'href' => route('clients.show', [$model->deal->client->id, '0', '0']),
                'client_first_name' => $model->performer->first_name,
                'client_last_name' => $model->performer->last_name,
                'client_middle_name' => $model->performer->middle_name,
                'date' => $model->created_at,
                'email' => $model->performer->email,
                'task_date' => $model->task_date,
                'type' => $model->type,
                'user_id' => $model->user->id ?? '',
                'user_first_name' => $model->user->first_name ?? '',
                'user_last_name' => $model->user->last_name ?? '',
                'user_middle_name' => $model->user->middle_name ?? '',
            ];
        }
        // $this_user_id = $user->id;
        $response = [
            "status" => true,
            "message" => "success",
            "data" => $tasks
        ];
        return response($response);
    }
}
