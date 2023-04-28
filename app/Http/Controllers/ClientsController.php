<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Currency;
use App\Models\House;
use App\Models\HouseFlat;
use App\Models\InstallmentPlan;
use App\Models\LeadComment;
use App\Models\Clients;
use App\Models\LeadStatus;
use App\Models\Notification_;
use App\Models\PersonalInformations;
use App\Models\StatusColors;
use App\Exports\LeadsExport;
use App\Http\Requests\ClientsRequest;
use App\Imports\LeadsImport;
use App\Http\Requests\TaskRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Events\RealTimeMessage;
use App\Notifications\TaskNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Booking;
use App\Models\Chat;
use App\Models\Constants;
use App\Models\PayStatus;
use PHPUnit\TextUI\XmlConfiguration\Constant;

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
        // pre($defaultAction);
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
        // return view('forthebuilder::clients.all-clients', [
        //     'models' => $models,
        //     'active' => Constants::CLIENT_ACTIVE,
        //     'archive' => Constants::CLIENT_DELETED,
        //     'all_notifications' => $this->getNotification()
        //     // 'defaultAction' => $defaultAction,
        // ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function insert(ClientsRequest $request)
    {
        return $request->header('token');
        $data = $request->validated();

        $auth_user_id = User::where(['token' => $request->header('token')])->get();
        return $auth_user_id;
        $client_id = $data['client_id'];
        if (isset($data['client_id']) && $data['client_id'] != null && $data['client_id'] != 'null') {
            $existPersonalInfo = PersonalInformations::where(['client_id' => $data['client_id'], 'series_number' => $data['series_number']])->first();
            if (isset($existPersonalInfo)) {
                $existClient = Clients::find($data['client_id']);
                $existClient->first_name = $data['first_name'];
                $existClient->last_name = $data['last_name'];
                $existClient->middle_name = $data['middle_name'];
                $existClient->phone = $data['phone'];
                $existClient->additional_phone = $data['additional_phone'];
                $existClient->email = $data['email'];
                $existClient->source = $data['source'];
                $existClient->lead_status = $data['lead_status'];
                $existClient->save();
                $client_id = $existClient->id;
            }
        } else {
            $newClient = new Clients();
            $newClient->user_id = $auth_user_id;
            $newClient->first_name = $data['first_name'];
            $newClient->last_name = $data['last_name'];
            $newClient->middle_name = $data['middle_name'];
            $newClient->phone = $data['phone'];
            $newClient->additional_phone = $data['additional_phone'];
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
        }

        $model = new Deal();
        $model->user_id = $auth_user_id;
        $model->client_id = $client_id;
        $model->date_deal = date('Y-m-d');
        $model->status = Constants::ACTIVE;
        $model->type = $data['lead_status'];
        $model->looking_for = $data['looking_for'];
        if ($model->save()) {
            return redirect()->route('forthebuilder.clients.index')->with('success', translate('successfully'));
        }

        $leadStatuses = LeadStatus::all();
        return view('forthebuilder::clients.create', [
            'leadStatuses' => $leadStatuses,
            'data' => $data,
        ])->with('warning', translate('This lead is exist'));
    }

    public function calendar()
    {
        $user = Auth::user();
        $models = Task::where('deleted_at', NULL)->get();
        foreach ($models as $model) {
            $tasks[] = [
                'id' => $model->id,
                'href' => route('clients.show', [$model->deal->client->id, '0', '0']),
                'first_name' => $model->performer->first_name,
                'last_name' => $model->performer->last_name,
                'middle_name' => $model->performer->middle_name,
                'created_at' => $model->created_at,
                'email' => $model->performer->email,
                'task_date' => $model->task_date,
                'type' => $model->type,
                'user_first_name' => $model->user->first_name ?? '',
                'user_last_name' => $model->user->last_name ?? '',
            ];
        }
        //        $my_models = Task::where('performer_id', $user->id)->where('deleted_at', NULL)->get();
        //        foreach ($my_models as $my_model){
        //            $my_tasks[] = [
        //                'id' => $my_model->id,
        //                'href' => route('clients.show', [$my_model->deal->client->id, '0', '0']),
        //                'first_name' => $my_model->performer->first_name,
        //                'last_name' => $my_model->performer->last_name,
        //                'middle_name' => $my_model->performer->middle_name,
        //                'created_at' => $my_model->created_at,
        //                'email' => $my_model->performer->email,
        //                'task_date' => $my_model->task_date,
        //                'type' => $my_model->type,
        //                'user_first_name' => $my_model->user->first_name??'',
        //                'user_last_name' => $my_model->user->last_name??'',
        //            ];
        //        }
        $this_user_id = $user->id;
        $deals = Deal::where('status', 1)->get();
        foreach ($deals as $deal) {
            $deal_[] = [
                'id' => $deal->id,
                'first_name' => $deal->client->first_name,
                'last_name' => $deal->client->last_name,
                'middle_name' => $deal->client->middle_name,
            ];
        }
        $response = [
            "status" => true,
            "message" => "success",
            "data" => $tasks
        ];
        return response($response);
    }

    public function getUsers(){
        $users = User::select('id', 'first_name')->get();
        $response = [
            "status" => true,
            "message" => "success",
            'data' => $users,
        ];
        return $response;
    }

    public function getDeals(){
        $deals = Deal::where('status', 1)->get();
        foreach ($deals as $deal) {
            $deal_[] = [
                'id' => $deal->id,
                'first_name' => $deal->client->first_name,
                'last_name' => $deal->client->last_name,
                'middle_name' => $deal->client->middle_name,
            ];
        }
        $response = [
            "status" => true,
            "message" => "success",
            'data' => $deal_,
        ];
        return $response;
    }

    public function indexLeadListNew()
    {

        $leadStatuses = LeadStatus::all();

        return view('forthebuilder::clients.index-lead-list-new', [
            'leadStatuses' => $leadStatuses,
            'all_notifications' => $this->getNotification()
        ]);
    }

    // public function indexClientList()
    // {
    //     $model = Deal::where('status', Constants::ACTIVE)->orderBy('date_deal', 'desc')->get();

    //     $arr = [
    //         translate('First contact') => [],
    //         translate('Negotiation') => [],
    //         translate('Making a deal') => [],
    //     ];

    //     if (!empty($model)) {
    //         $n = 0;
    //         foreach ($model as $key => $value) {
    //             switch ($value->type) {
    //                 case Constants::FIRST_CONTACT:
    //                     $key = translate('First contact');
    //                     break;
    //                 case Constants::NEGOTIATION:
    //                     $key = translate('Negotiation');
    //                     break;
    //                 case Constants::MAKE_DEAL:
    //                     $key = translate('Making a deal');
    //                     break;
    //                 default:
    //                     $key = translate('First contact');
    //                     break;
    //             }

    //             $arr[$key]['type'] = $value->type;
    //             $arr[$key]['list'][$n]['id'] = $value->id;
    //             $arr[$key]['list'][$n]['responsible'] = (isset($value->user)) ? $value->user->last_name . ' ' . $value->user->first_name . ' ' . $value->user->middle_name : '';
    //             $arr[$key]['list'][$n]['client'] = (isset($value->client)) ? $value->client->last_name . ' ' . $value->client->first_name . ' ' . $value->client->middle_name : '';
    //             $arr[$key]['list'][$n]['client_id'] = $value->client->id ?? 0;
    //             $arr[$key]['list'][$n]['day'] = date('d.m.Y', strtotime($value->date_deal));
    //             $arr[$key]['list'][$n]['time'] = date('H:i:s', strtotime($value->date_deal));
    //             $n++;
    //         }
    //     }

    //     return view('forthebuilder::clients.index-client-list', [
    //         'model' => $arr,
    //     ]);
    // }

    public function indexNewClients()
    {

        $lead_status_id = LeadStatus::where('name', 'новый')->first();

        $models = [];
        if (isset($lead_status_id))
            $models = Clients::where('lead_status_id', $lead_status_id->id)->paginate(config('params.pagination'));

        return view('forthebuilder::clients.index-new-leads', [
            'models' => $models,
            'all_notifications' => $this->getNotification()
        ]);
    }

    public function getLeadList(Request $request)
    {

        $lead_status_id = LeadStatus::where('name', 'Пустой')->first();

        if ($request->ajax()) {
            $models = Clients::where('lead_status_id', $lead_status_id)->paginate(20);
        }

        return response()->json($models);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {

        // $models = Clients::all();
        // $leadStatuses = LeadStatus::all();
        $request_status = LeadStatus::NEW_STATUS;
        return view('forthebuilder::clients.create', [
            // 'models' => $models,
            'request_status' => $request_status,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function store(ClientsRequest $request)
    {
        $data = $request->validated();
        $auth_user_id = Auth::user()->id;
        $client_id = $data['client_id'];
        if (isset($data['client_id']) && $data['client_id'] != null && $data['client_id'] != 'null') {
            $existPersonalInfo = PersonalInformations::where(['client_id' => $data['client_id'], 'series_number' => $data['series_number']])->first();
            if (isset($existPersonalInfo)) {
                $existClient = Clients::find($data['client_id']);
                $existClient->first_name = $data['first_name'];
                $existClient->last_name = $data['last_name'];
                $existClient->middle_name = $data['middle_name'];
                $existClient->phone = $data['phone'];
                $existClient->additional_phone = $data['additional_phone'];
                $existClient->email = $data['email'];
                $existClient->source = $data['source'];
                $existClient->lead_status = $data['lead_status'];
                $existClient->save();
                $client_id = $existClient->id;
            }
        } else {
            $newClient = new Clients();
            $newClient->user_id = $auth_user_id;
            $newClient->first_name = $data['first_name'];
            $newClient->last_name = $data['last_name'];
            $newClient->middle_name = $data['middle_name'];
            $newClient->phone = $data['phone'];
            $newClient->additional_phone = $data['additional_phone'];
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
        }

        $model = new Deal();
        $model->user_id = $auth_user_id;
        $model->client_id = $client_id;
        $model->date_deal = date('Y-m-d');
        $model->status = Constants::ACTIVE;
        $model->type = $data['lead_status'];
        $model->looking_for = $data['looking_for'];
        if ($model->save()) {
            return redirect()->route('forthebuilder.clients.index')->with('success', translate('successfully'));
        }



        // // $leads = Clients::all();
        // // $leads = Clients::where('series_number', str_replace(' ','', $data['series_number']))->first();
        // // foreach ($leads as $lead){
        // //     $series_number[] = str_replace(' ','', $lead->series_number);
        // // }
        // // if(!isset($leads)){
        // $model = new Clients();
        // $model->first_name = $data['first_name'];
        // $model->last_name = $data['last_name'];
        // $model->middle_name = $data['middle_name'];
        // $model->phone = $data['phone'];
        // $model->additional_phone = $data['additional_phone'];
        // $model->email = $data['email'];
        // $model->source = $data['source'];
        // $model->lead_status = $data['lead_status'];
        // $model->issued_by = $data['issued_by'];
        // $model->inn = str_replace(' ', '', $data['inn']);
        // // $model->referer = $data['referer'];
        // // $model->created_at = $data['created_at'];
        // // $model->requestid = $data['requestid'];
        // // $model->lead_status_id = $data['lead_status_id'];
        // $model->user_id = Auth::user()->id;
        // if ($model->save()) {
        //     Log::channel('action_logs2')->info("пользователь создал новую Лиды : " . $model->name . "", ['info-data' => $model]);

        //     return redirect()->route('forthebuilder.clients.index')->with('success', translate('successfully'));
        // }
        // }else{

        $leadStatuses = LeadStatus::all();
        return view('forthebuilder::clients.create', [
            'leadStatuses' => $leadStatuses,
            'data' => $data,
        ])->with('warning', translate('This lead is exist'));

        // $model = Clients::where('series_number', str_replace(' ','', $data['series_number']))->first();
        // return redirect()->route('forthebuilder.clients.create', $leads->id)->with('warning', translate('This lead is exist'));
        // }
    }
    public function clientHouse($client_id)
    {

        $models = House::orderBy('id', 'desc')->paginate(config('params.pagination'));
        return view('forthebuilder::house.index', [
            'models' => $models,
            'status' => 'client',
            'client_id' => $client_id,
            'all_notifications' => $this->getNotification()
        ]);
    }
    public function clientHouseFlat($id, $client_id)
    {

        $model = House::findOrFail($id);
        $flats = HouseFlat::select('id', 'floor', 'entrance', 'status', 'number_of_flat', 'price', 'areas', 'room_count')->where('house_id', $model->id)->orderBy('entrance', 'asc')->orderBy('floor', 'desc')->orderBy('created_at', 'asc')->get();
        $statusColors = StatusColors::select('id', 'color', 'status')->get();
        $arr = [];
        $i_default = ($model->has_basement) ? 0 : 1;
        $j_default = ($model->has_attic) ? $model->floor_count + 1 : $model->floor_count;
        // dd($j_default);
        for ($i = 1; $i <= $model->entrance_count; $i++) {
            for ($j = $j_default; $j >= $i_default; $j--) {
                $f_j = $j;
                // echo $j . '>' . $model->floor_count . '<br>';
                if ($j > $model->floor_count)
                    $f_j = translate('attic');

                if ($j == 0)
                    $f_j = translate('basement');

                $arr['list'][$i]['list'][$f_j] = [];
                $arr['entrance_count'][$f_j] = $f_j;
            }
        }

        // for ($i = 1; $i <= $model->entrance_count; $i++)
        //     for ($j = $model->floor_count; $j >= 1; $j--)
        //         $arr['list'][$i]['list'][$j] = [];

        $count_all = 0;
        $count_bookings = 0;
        $count_free = 0;
        $count_solds = 0;

        $entrance_all = 0;
        $entrance_bookings = 0;
        $entrance_free = 0;
        $entrance_solds = 0;

        $entranceArr = [];
        $floorArr = [];
        $n = 0;

        $model->entrance_count;
        $model->floor_count;
        // $entrance_count = 0;
        // $floor_count = 0;
        // pre($flats);
        foreach ($flats as $val) {
            $count_all++;
            if ($val->status == HouseFlat::STATUS_BOOKING)
                $count_bookings++;
            else if ($val->status == HouseFlat::STATUS_FREE)
                $count_free++;
            else if ($val->status == HouseFlat::STATUS_SOLD)
                $count_solds++;

            if (!in_array($val->entrance, $entranceArr)) {
                $entranceArr[] = $val->entrance;
                $entrance_all = 0;
                $entrance_bookings = 0;
                $entrance_free = 0;
                $entrance_solds = 0;
            }

            $entrance_all++;
            if ($val->status == HouseFlat::STATUS_BOOKING)
                $entrance_bookings++;
            else if ($val->status == HouseFlat::STATUS_FREE)
                $entrance_free++;
            else if ($val->status == HouseFlat::STATUS_SOLD)
                $entrance_solds++;

            if (!in_array($val->floor, $floorArr)) {
                $floorArr[] = $val->floor;
                $n = 0;
            }

            $f_j = $val->floor;
            if ($val->floor > $model->floor_count)
                $f_j = translate('attic');

            if ($val->floor == 0)
                $f_j = translate('basement');

            $arr['list'][$val->entrance]['entrance_all'] = $entrance_all;
            $arr['list'][$val->entrance]['entrance_bookings'] = $entrance_bookings;
            $arr['list'][$val->entrance]['entrance_free'] = $entrance_free;
            $arr['list'][$val->entrance]['entrance_solds'] = $entrance_solds;
            $arr['list'][$val->entrance]['entrance'] = $val->entrance;
            $arr['list'][$val->entrance]['list'][$f_j][$n]['id'] = $val->id;
            $arr['list'][$val->entrance]['list'][$f_j][$n]['color_status'] = $val->status;
            $arr['list'][$val->entrance]['list'][$f_j][$n]['number_of_flat'] = $val->number_of_flat;
            $arr['list'][$val->entrance]['list'][$f_j][$n]['areas'] = $val->areas;
            $arr['list'][$val->entrance]['list'][$f_j][$n]['price'] = $val->price;
            $arr['list'][$val->entrance]['list'][$f_j][$n]['contract_number'] = $val->contract_number;
            $arr['list'][$val->entrance]['list'][$f_j][$n]['room_count'] = $val->room_count;

            $n++;
        }

        $arr['count_all'] = $count_all;
        $arr['count_bookings'] = $count_bookings;
        $arr['count_free'] = $count_free;
        $arr['count_solds'] = $count_solds;
        // pre($flats);

        $colors = [];
        if (!empty($statusColors)) {
            foreach ($statusColors as $value) {
                $colors[$value->status] = $value->color;
            }
        }
        // pre($arr);
        // return view('forthebuilder::house.show-more-second', [
        return view('forthebuilder::house.show-more', [
            'model' => $model,
            'flats' => $flats,
            'arr' => $arr,
            'colors' => $colors,
            'status' => 'client',
            'client_id' => $client_id,
            'all_notifications' => $this->getNotification()
        ]);
    }
    public function showDetails($house_id, $entrance, $flat_id, $client_id)
    {

        $model = House::findOrFail($house_id);
        $flats = HouseFlat::select('id', 'floor', 'entrance', 'status', 'number_of_flat', 'price', 'areas', 'room_count', 'house_id', 'doc_number')->where(['house_id' => $model->id, 'entrance' => $entrance])->orderBy('entrance', 'asc')->orderBy('floor', 'desc')->get();
        $statusColors = StatusColors::select('id', 'color', 'status')->get();
        $arr = [];
        // for ($i = 1; $i <= $model->entrance_count; $i++)
        //     for ($j = $model->floor_count; $j >= 1; $j--)
        //         $arr['list'][$i]['list'][$j] = [];

        $count_all = 0;
        $count_bookings = 0;
        $count_free = 0;
        $count_solds = 0;

        $entrance_all = 0;
        $entrance_bookings = 0;
        $entrance_free = 0;
        $entrance_solds = 0;

        $entranceArr = [];
        $floorArr = [];
        $n = 0;

        $model->entrance_count;
        $model->floor_count;
        // $entrance_count = 0;
        // $floor_count = 0;
        // pre($flats);
        foreach ($flats as $val) {
            $count_all++;
            if ($val->status == HouseFlat::STATUS_BOOKING)
                $count_bookings++;
            else if ($val->status == HouseFlat::STATUS_FREE)
                $count_free++;
            else if ($val->status == HouseFlat::STATUS_SOLD)
                $count_solds++;

            if (!in_array($val->entrance, $entranceArr)) {
                $entranceArr[] = $val->entrance;
                $entrance_all = 0;
                $entrance_bookings = 0;
                $entrance_free = 0;
                $entrance_solds = 0;
            }

            $entrance_all++;
            if ($val->status == HouseFlat::STATUS_BOOKING)
                $entrance_bookings++;
            else if ($val->status == HouseFlat::STATUS_FREE)
                $entrance_free++;
            else if ($val->status == HouseFlat::STATUS_SOLD)
                $entrance_solds++;

            if (!in_array($val->floor, $floorArr)) {
                $floorArr[] = $val->floor;
                $n = 0;
            }

            $f_j = $val->floor;
            if ($val->floor > $model->floor_count)
                $f_j = translate('attic');

            if ($val->floor == 0)
                $f_j = translate('basement');

            $areas = json_decode($val->areas);
            // pre($areas->total);
            $arr['entrance_all'] = $entrance_all;
            $arr['entrance_bookings'] = $entrance_bookings;
            $arr['entrance_free'] = $entrance_free;
            $arr['entrance_solds'] = $entrance_solds;
            $arr['entrance'] = $val->entrance;
            $arr['list'][$f_j][$n]['id'] = $val->id;
            $arr['list'][$f_j][$n]['house_id'] = $val->house_id;
            $arr['list'][$f_j][$n]['house_house_name'] = $model->name;
            $arr['list'][$f_j][$n]['doc_number'] = $val->doc_number;
            $arr['list'][$f_j][$n]['color_status'] = $val->status;
            $arr['list'][$f_j][$n]['number_of_flat'] = $val->number_of_flat;
            $arr['list'][$f_j][$n]['areas'] = $areas->total;
            $arr['list'][$f_j][$n]['price'] = $val->price;
            $arr['list'][$f_j][$n]['contract_number'] = $val->contract_number;
            $arr['list'][$f_j][$n]['room_count'] = $val->room_count;
            $arr['list'][$f_j][$n]['client'] = '';
            if ($val->status == Constants::STATUS_BOOKING) {
                $arr['list'][$f_j][$n]['client'] = (isset($val->booking->clients)) ? $val->booking->clients->last_name . ' ' . $val->booking->clients->first_name . ' ' . $val->booking->clients->middle_name : '';
            } else if ($val->status == Constants::STATUS_SOLD) {
                $arr['list'][$f_j][$n]['client'] = (isset($val->deal->client)) ? $val->deal->client->last_name . ' ' . $val->deal->client->first_name . ' ' . $val->deal->client->middle_name : '';
            }
            $arr['list'][$f_j][$n]['floor'] = $val->floor;

            // [$val->entrance]

            $n++;
        }

        $arr['count_all'] = $count_all;
        $arr['count_bookings'] = $count_bookings;
        $arr['count_free'] = $count_free;
        $arr['count_solds'] = $count_solds;

        $colors = [];
        if (!empty($statusColors))
            foreach ($statusColors as $value)
                $colors[$value->status] = $value->color;

        // pre($arr);
        return view('forthebuilder::house.show-details', [
            'model' => $model,
            'flats' => $flats,
            'arr' => $arr,
            'colors' => $colors,
            'status' => 'client',
            'client_id' => $client_id,
            'all_notifications' => $this->getNotification()
        ]);
    }
    public function storeBudget(Request $request, $client_id)
    {
        $user = Auth::user();
        $model = Deal::find($request->deal_id);
        date_default_timezone_set("Asia/Tashkent");
        if (isset($request->budget)) {
            $model->budget = (float)$request->budget;
        }
        if (isset($request->looking_for)) {
            $model->looking_for = $request->looking_for;
        }
        if (isset($request->house_id)) {
            $model->house_id = $request->house_id;
        }
        if (isset($request->house_flat_id)) {
            $model->house_flat_id = $request->house_flat_id;
        }
        if ($model->history == NULL) {
            $model->history = json_encode([['date' => date('Y-m-d H:i:s'), 'user' => $user->first_name, 'user_id' => $user->id, 'user_photo' => $user->avatar, 'new_type' => $request->type, 'old_type' => $model->type]]);
        } else {
            $old_history = json_decode($model->history);
            $old_history[] = ['date' => date('Y-m-d H:i:s'), 'user' => $user->first_name, 'user_id' => $user->id,  'user_photo' => $user->avatar, 'new_type' => $request->type, 'old_type' => $model->type];
            $model->history = json_encode($old_history);
        }
        $model->type = $request->type;
        if (isset($request->series_number) || isset($request->issued_by) || isset($request->inn)) {
            if (isset($request->personal_id)) {
                $personal = PersonalInformations::find($request->personal_id);
                $personal->series_number = $request->series_number;
                $personal->issued_by = $request->issued_by;
                $personal->inn = $request->inn;
            } else {
                $personal = new PersonalInformations();
                $personal->series_number = $request->series_number;
                $personal->issued_by = $request->issued_by;
                $personal->inn = $request->inn;
                $personal->client_id = $client_id;
            }
            $personal->save();
        }
        $model->save();
        return redirect()->route('forthebuilder.clients.show', [$model->client_id, "0", "0"])->with('status', translate('successfully'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function taskAnswer()
    {
        $model = Task::find();
    }

    public function show($id, $house_flat_id = 0, $task_id = 0)
    {

        // $model = Clients::findOrFail($id);
        // $modelDeals = Deal::where('client_id', $model->id)->get();

        $data = DB::table('forthebuilder.clients as c')
            ->leftJoin('forthebuilder.deals as d', 'd.client_id', '=', 'c.id')
            //            ->leftJoin('forthebuilder.task as t', 't.deal_id', '=', 'd.id')
            ->leftJoin('forthebuilder.house as h', 'd.house_id', '=', 'h.id')
            ->leftJoin('forthebuilder.house_flat as h_f', 'd.house_flat_id', '=', 'h_f.id')
            ->leftJoin('forthebuilder.personal_informations as pi', 'c.id', '=', 'pi.client_id')
            ->leftJoin('newhouse.users as nu', 'nu.id', '=', 'd.user_id')
            ->where('c.id', $id)
            ->select(
                'c.id AS client_id',
                'c.first_name',
                'c.last_name',
                'c.middle_name',
                'c.phone',
                'c.additional_phone',
                'c.email',
                'c.gender',
                'c.status',
                'd.type',
                'd.status AS deal_status',
                'd.id AS deal_id',
                'd.budget AS budget',
                'd.looking_for AS looking_for',
                'd.history AS history',
                'h.name AS house_name',
                'h_f.number_of_flat AS flat_number',
                'h_f.entrance AS flat_entrance',
                'h_f.areas AS flat_area',
                'nu.id AS user_id',
                'nu.first_name AS userFirstName',
                'nu.last_name AS userLastName',
                'nu.middle_name AS userMiddleName',
                'nu.email AS userEmail',
                'pi.id AS personal_id',
                'pi.series_number',
                'pi.inn',
                'pi.issued_by',
                'pi.given_date',
                'pi.address'
            )->get();
        // pre($data);
        //        dd($data);
        foreach ($data as $dat) {
            $task_array[] = $dat->deal_id;
        }
        $tasks = Task::select('id', 'performer_id', 'title', 'deal_id', 'type', 'task_date', 'answer', 'created_at')->whereIn('deal_id', $task_array)->get();
        $chats = Chat::whereIn('deal_id', $task_array)->get();
        if ($house_flat_id != '0') {
            $house_flat = HouseFlat::findOrFail($house_flat_id);
        } else {
            $house_flat = "";
        }
        $time = date('Y-m-d');

        if ($task_id != 0) {
            $task = Notification_::where('notifiable_id', $task_id)->first();
            $task->read_at = $time;
            $task->save();
        }
        $users = User::all();
        $client = Clients::find($id);
        // $comments = LeadComment::where('lead_id', $id)->get();
        // $leadStatuses = LeadStatus::all();
        // $deals = Deal::where('series_number', $model->series_number)->get();
        // $all_deal_id = [];
        // foreach ($deals as $deal) {
        //     $all_deal_id[] = $deal->id;
        // }
        // $personalinfo = PersonalInformations::where('series_number', $model->series_number)->first();
        // if (count($all_deal_id) > 0) {
        //     $installmentplans = InstallmentPlan::whereIn('deal_id', $all_deal_id)->get();
        // }
        // $users = User::all();

        // $listTasks = Task::where('lead_id', $id)->orderBy('id', 'desc')->paginate(config('params.pagination'));

        return view('forthebuilder::clients.show', [
            // 'model' => $model,
            // 'modelDeals' => $modelDeals,
            'data' => $data,
            'house_flat' => $house_flat,
            'users' => $users,
            'tasks' => $tasks,
            'client' => $client,
            'chats' => $chats,
            'all_notifications' => $this->getNotification()
            // 'leadStatuses' => $leadStatuses,
            // 'personalinfo' => $personalinfo,
            // 'installmentplans' => $installmentplans ?? [],
            // 'deals' => $deals ?? [],
            // 'users' => $users,
            // 'listTasks' => $listTasks,
        ]);
    }

    // /**
    //  * Show the specified resource.
    //  * @param int $id
    //  * @return Renderable
    //  */
    // public function show($id)
    // {
    //     $model = Clients::findOrFail($id);
    //     $comments = LeadComment::where('lead_id', $id)->get();
    //     $leadStatuses = LeadStatus::all();
    //     $deals = Deal::where('series_number', $model->series_number)->get();
    //     $all_deal_id = [];
    //     foreach ($deals as $deal) {
    //         $all_deal_id[] = $deal->id;
    //     }
    //     $personalinfo = PersonalInformations::where('series_number', $model->series_number)->first();
    //     if (count($all_deal_id) > 0) {
    //         $installmentplans = InstallmentPlan::whereIn('deal_id', $all_deal_id)->get();
    //     }
    //     $users = User::all();

    //     $listTasks = Task::where('lead_id', $id)->orderBy('id', 'desc')->paginate(config('params.pagination'));

    //     return view('forthebuilder::clients.show', [
    //         'model' => $model,
    //         'comments' => $comments,
    //         'leadStatuses' => $leadStatuses,
    //         'personalinfo' => $personalinfo,
    //         'installmentplans' => $installmentplans ?? [],
    //         'deals' => $deals ?? [],
    //         'users' => $users,
    //         'listTasks' => $listTasks,
    //     ]);
    // }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {

        $model = Clients::findOrFail($id);
        $leadStatuses = LeadStatus::all();
        return view('forthebuilder::clients.edit', [
            'model' => $model,
            'leadStatuses' => $leadStatuses,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(ClientsRequest $request, $id)
    {
        $data = $request->validated();

        $model = Clients::find($id);
        $model->first_name = $data['first_name'];
        $model->last_name = $data['last_name'];
        $model->middle_name = $data['middle_name'];
        $model->phone = $data['phone'];
        $model->additional_phone = $data['additional_phone'];
        $model->email = $data['email'];
        $model->source = $data['source'];
        $model->lead_status = $data['lead_status'];
        $model->save();

        Log::channel('action_logs2')->info("пользователь обновил " . $model->first_name . " Клиент", ['info-data' => $model]);

        return redirect()->route('forthebuilder.clients.index')->with('success', translate('successfully'));
    }

    // public function updateType(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'type' => 'required|integer',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->route('forthebuilder.client-lists.indexClientList')->withErrors($validator);
    //     }

    //     if ($request->ajax()) {
    //         $model = Deal::findOrFail($id);
    //         $model->type = $request->type;
    //         $model->save();

    //         $mStatus = Deal::select('type', DB::raw('count(*) as total'))
    //             ->groupBy('type')
    //             ->pluck('total', 'type')
    //             ->toArray();
    //         //            $leadstatus = LeadStatus::all();
    //         //            $mStatus = $leadstatus->leads->count();
    //         return response()->json([
    //             'mStatus' => $mStatus,
    //             'success' => 'Статус измeнён'
    //         ]);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $leads = Clients::findOrFail($id);
            $leads->status = Constants::CLIENT_DELETED;

            $deals = Deal::select('id', 'client_id')->where('client_id', $id);
            $activeDeals = $deals->whereIn('status', [Constants::ACTIVE, Constants::NOT_IMPLEMENTED])->where('type', Constants::MAKE_DEAL)->first();
            if (isset($activeDeals) && !empty($activeDeals))
                return redirect()->route('forthebuilder.clients.index')->with('delete_warning', translate('The client has active deals'));

            $deals = $deals->get();
            $deal_id = [];
            foreach ($deals as $deal) {
                $deal_id[] = $deal->id;
                $deal->deleted_at = date("Y-m-d H:i:s");
                $deal->status = Constants::NOT_IMPLEMENTED;
                $deal->save();
            }

            $payStatus = PayStatus::whereIn('deal_id', $deal_id)->whereIn('status', [Constants::NOT_PAID, Constants::HALF_PAY])->first();
            if (isset($payStatus) && !empty($payStatus))
                return redirect()->route('forthebuilder.clients.index')->with('delete_warning', translate('The client has active installment plan'));

            $tasks = Task::whereIn('deal_id', $deal_id)->get();
            foreach ($tasks as $task) {
                $task->deleted_at = date("Y-m-d H:i:s");
                $task->status = Constants::DID_NOT_DO_IT;
                $task->save();
            }

            $bookings = Booking::whereIn('deal_id', $deal_id);
            $issetBooking = $bookings->where('status', Constants::BOOKING_ACTIVE)->first();
            if (isset($issetBooking))
                return redirect()->route('forthebuilder.clients.index')->with('delete_warning', translate('The client has active bookings'));

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

            Log::channel('action_logs2')->info("пользователь удалил " . $leads->name . " Лиды", ['info-data' => $leads]);
            DB::commit();
            return redirect()->route('forthebuilder.clients.index')->with('deleted', translate('Data deleted successfuly'));
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    public function import(Request $request)
    {
        $request->validate(
            [
                'lead_file' => 'required'
            ],
            // ['lead_file.required' => 'this is my custom error message for required']
        );
        // dd($request->file('lead_file'));
        Excel::import(new LeadsImport, $request->file('lead_file'));

        return back()->with('success', 'Success!');
    }

    public function addTask(TaskRequest $request)
    {
        $data = $request->validated();
        $data['status'] = 'Новый';
        $data['user_id'] = Auth::user()->id;
        $title = $data['title'];

        $model = Task::create($data);
        Log::channel('action_logs2')->info("пользователь создал новую Task : " . $model->title . "", ['info-data' => $model]);

        $userIdTask = User::findOrFail($data['user_task_id']);

        event(new RealTimeMessage($title, $userIdTask));
        Notification::send($userIdTask, new TaskNotification($model));

        return redirect()->route('forthebuilder.clients.show', ['id' => $data['lead_id'], "0", "0"]);

        // return redirect()->route('forthebuilder.clients.show')->with('success', translate('successfully'));
    }
}
