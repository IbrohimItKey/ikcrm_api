<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Deal;
use App\Models\InstallmentPlan;
use App\Models\LeadComment;
use App\Models\Leads;
use App\Models\LeadStatus;
use App\Models\Notification_;
use App\Models\PersonalInformations;
use App\Exports\LeadsExport;
use App\Http\Requests\LeadsRequest;
use App\Imports\LeadsImport;
use App\Http\Requests\TaskRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Events\RealTimeMessage;
use App\Notifications\TaskNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class LeadsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function getNotification(){
        $notification = ['Booking', 'BookingPrepayment'];
        $all_task = Notification_::where('type', 'Task')->where(['read_at' => NULL,  'user_id' => Auth::user()->id])->orderBy('created_at', 'desc')->get();
        $all_booking = Notification_::whereIn('type', $notification)->where('read_at', NULL)->orderBy('created_at', 'desc')->get();
        return ['all_task'=>$all_task, 'all_booking'=>$all_booking];
    }

    public function index()
    {
        $models = Leads::orderBy('id', 'desc')->paginate(config('params.pagination'));

        return view('forthebuilder::leads.index', [
            'models' => $models,
            'all_notifications' => $this->getNotification()
        ]);
    }

    public function calendar()
    {
        $user = Auth::user();
        $models = Task::all();
        $my_models = Task::where('user_task_id', $user->id)->get();
        $users = User::all();
        return view('forthebuilder::leads.calendar', [
            'models' => $models,
            'users' => $users,
            'my_models' => $my_models,
            'all_notifications' => $this->getNotification()
        ]);
    }

    public function indexLeadListNew()
    {
        $leadStatuses = LeadStatus::all();

        return view('forthebuilder::leads.index-lead-list-new', [
            'leadStatuses' => $leadStatuses,
            'all_notifications' => $this->getNotification()
        ]);
    }

    public function indexLeadList()
    {
        $leadStatuses = LeadStatus::all();

        return view('forthebuilder::leads.index-lead-list', [
            'leadStatuses' => $leadStatuses,
            'all_notifications' => $this->getNotification()
        ]);
    }

    public function indexNewLeads()
    {
        $lead_status_id = LeadStatus::where('name', 'новый')->first();
        $models = [];
        if (isset($lead_status_id))
            $models = Leads::where('lead_status_id', $lead_status_id->id)->paginate(config('params.pagination'));

        return view('forthebuilder::leads.index-new-leads', [
            'models' => $models,
            'all_notifications' => $this->getNotification()
        ]);
    }

    public function getLeadList(Request $request)
    {
        $lead_status_id = LeadStatus::where('name', 'Пустой')->first();

        if ($request->ajax()) {
            $models = Leads::where('lead_status_id', $lead_status_id)->paginate(20);
        }

        return response()->json($models);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        // $models = Leads::all();
        // $leadStatuses = LeadStatus::all();
        $request_status = LeadStatus::NEW_STATUS;
        return view('forthebuilder::leads.create', [
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

    public function store(LeadsRequest $request)
    {
        $data = $request->validated();
        $model = new Leads();
        $model->name = $data['name'];
        $model->surname = $data['surname'];
        $model->phone = $data['phone_code'] . $data['phone'];
        $model->email = $data['email'];
        $model->source = $data['source'];
        $model->patronymic = $data['patronymic'];
        $model->additional_phone = $data['phone_code'] . $data['additional_phone'];
        $model->issued_by = $data['issued_by'];
        $model->interview_date = $data['interview_date'];
        $model->inn = str_replace(' ', '', $data['inn']);
        $model->series_number = str_replace(' ', '', $data['series_number']);
        $model->lead_status_id = $data['lead_status_id'];
        $model->user_id = Auth::user()->id;
        if ($model->save()) {
            Log::channel('action_logs2')->info("пользователь создал новую Лиды : " . $model->name . "", ['info-data' => $model]);

            return redirect()->route('forthebuilder.leads.index')->with('success', __('locale.successfully'));
        }

        $leadStatuses = LeadStatus::all();
        return view('forthebuilder::leads.create', [
            'leadStatuses' => $leadStatuses,
            'data' => $data,
            'all_notifications' => $this->getNotification()
        ])->with('warning', __('locale.This lead is exist'));

        // $model = Leads::where('series_number', str_replace(' ','', $data['series_number']))->first();
        // return redirect()->route('forthebuilder.leads.create', $leads->id)->with('warning', __('locale.This lead is exist'));
        // }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $model = Leads::findOrFail($id);
        $comments = LeadComment::where('lead_id', $id)->get();
        $leadStatuses = LeadStatus::all();
        $deals = Deal::where('series_number', $model->series_number)->get();
        $all_deal_id = [];
        foreach ($deals as $deal) {
            $all_deal_id[] = $deal->id;
        }
        $personalinfo = PersonalInformations::where('series_number', $model->series_number)->first();
        if (count($all_deal_id) > 0) {
            $installmentplans = InstallmentPlan::whereIn('deal_id', $all_deal_id)->get();
        }
        $users = User::all();

        $listTasks = Task::where('lead_id', $id)->orderBy('id', 'desc')->paginate(config('params.pagination'));

        return view('forthebuilder::leads.show', [
            'model' => $model,
            'comments' => $comments,
            'leadStatuses' => $leadStatuses,
            'personalinfo' => $personalinfo,
            'installmentplans' => $installmentplans ?? [],
            'deals' => $deals ?? [],
            'users' => $users,
            'listTasks' => $listTasks,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $model = Leads::findOrFail($id);
        $leadStatuses = LeadStatus::all();
        return view('forthebuilder::leads.edit', [
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
    public function update(LeadsRequest $request, $id)
    {
        $data = $request->validated();
        $leads = Leads::all();
        foreach ($leads as $lead) {
            $series_number[] = str_replace(' ', '', $lead->series_number);
        }
        $model = Leads::findOrFail($id);
        if (!in_array(str_replace(' ', '', $data['series_number']), $series_number) || str_replace(' ', '', $model->series_number) == str_replace(' ', '', $data['series_number'])||$data['series_number'] == NULL) {
            $model->name = $data['name'];
            $model->surname = $data['surname'];
            $model->phone = $data['phone_code'] . $data['phone'];
            $model->email = $data['email'];
            $model->source = $data['source'];
            $model->patronymic = $data['patronymic'];
            $model->additional_phone = $data['phone_code'] . $data['additional_phone'];
            $model->series_number = str_replace(' ', '', $data['series_number']);
            $model->issued_by = $data['issued_by'];
            $model->inn = str_replace(' ', '', $data['inn']);
            //        $model->referer = $data['referer'];
            //        $model->created_at = $data['created_at'];
            //        $model->requestid = $data['requestid'];
            $model->lead_status_id = $data['lead_status_id'];
            $model->interview_date = $data['interview_date'];
            $model->save();
        } else {
            return redirect()->route('forthebuilder.leads.edit', $model->id)->with('warning', __('locale.This series of passport belongs to another'));
        }

        Log::channel('action_logs2')->info("пользователь обновил " . $model->name . " Лиды", ['info-data' => $model]);

        return redirect()->route('forthebuilder.leads.index')->with('success', __('locale.successfully'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->route('forthebuilder.lead-lists.indexLeadList')->withErrors($validator);
        }
        if ($request->ajax()) {
            $model = Leads::findOrFail($id);
            $model->lead_status_id = $request->status;
            $model->save();

            $mStatus = Leads::select('lead_status_id', DB::raw('count(*) as total'))
                ->groupBy('lead_status_id')
                ->pluck('total', 'lead_status_id')
                ->toArray();
            //            $leadstatus = LeadStatus::all();
            //            $mStatus = $leadstatus->leads->count();
            return response()->json([
                'mStatus' => $mStatus,
                'success' => 'Статус измeнён',
                'all_notifications' => $this->getNotification()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $leads = Leads::findOrFail($id);
        $leads->delete();

        Log::channel('action_logs2')->info("пользователь удалил " . $leads->name . " Лиды", ['info-data' => $leads]);

        return redirect()->route('forthebuilder.leads.index');
        // return back()->with('success', __('locale.deleted'));
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

        return redirect()->route('forthebuilder.leads.show', ['id' => $data['lead_id']]);

        // return redirect()->route('forthebuilder.leads.show')->with('success', __('locale.successfully'));
    }
}
