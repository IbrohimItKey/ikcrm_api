<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Modules\ForTheBuilder\Entities\Clients;
use Modules\ForTheBuilder\Entities\Constants;
use Modules\ForTheBuilder\Entities\PayStatus;
use Modules\ForTheBuilder\Entities\Task;
use Modules\ForTheBuilder\Entities\Notification_;
use Modules\ForTheBuilder\Events\RealTimeMessage;
use Modules\ForTheBuilder\Http\Requests\TaskRequest;
use Modules\ForTheBuilder\Notifications\TaskNotification;

class TaskController extends Controller
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
        $users = User::all();
        $listLeads = Clients::select('id', 'last_name', 'first_name', 'middle_name')->get();
        $models = Task::where('task_date', '<', date('Y-m-d 00:00:00', strtotime('+8 days')))->orderBy('task_date', 'asc')->paginate(config('params.pagination'));

        $arr = [
            translate('Overdue') => [],
            translate('Tasks for today') => [],
            translate('Tasks for tomorrow') => [],
            translate('Tasks for next week') => [],
        ];
        $i = 0;
        if (!empty($models)) {
            foreach ($models as $val) {
                // pre($val->deal->client);
                $keyArr = '';
                if (strtotime($val->task_date) < strtotime(date('Y-m-d 00:00:00')))
                    $keyArr = translate('Overdue');
                else if (strtotime($val->task_date) == strtotime(date('Y-m-d 00:00:00')))
                    $keyArr = translate('Tasks for today');
                else if (strtotime($val->task_date) == strtotime(date('Y-m-d 00:00:00', strtotime('tomorrow'))))
                    $keyArr = translate('Tasks for tomorrow');
                else if (strtotime($val->task_date) > strtotime(date('Y-m-d 00:00:00', strtotime('tomorrow'))) && strtotime($val->task_date) <= strtotime(date('Y-m-d 00:00:00', strtotime('+8 days'))))
                    $keyArr = translate('Tasks for next week');

                if ($val->deal && $val->deal->client) {
                    $arr[$keyArr][$i]['id'] = $val->id;
                    $arr[$keyArr][$i]['responsible'] = (isset($val->performer)) ? $val->performer->last_name . ' ' . $val->performer->first_name : '';
                    $arr[$keyArr][$i]['client'] = (isset($val->deal->client)) ? $val->deal->client->last_name . ' ' . $val->deal->client->first_name : '';
                    $arr[$keyArr][$i]['client_middle_name'] = (isset($val->deal->client)) ? $val->deal->client->middle_name : '';
                    $arr[$keyArr][$i]['client_id'] = $val->deal->client->id ?? 0;
                    $arr[$keyArr][$i]['day'] = date('d.m.Y', strtotime($val->task_date));
                    $arr[$keyArr][$i]['time'] = date('H:i:s', strtotime($val->task_date));
                    $i++;
                }
            }
        }
        // pre($arr);

        return view('forthebuilder::task.index', [
            'arr' => $arr,
            'models' => $models,
            'users' => $users,
            'listLeads' => $listLeads,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function filterIndex()
    {
        $users = User::all();
        $listLeads = Clients::select('id', 'last_name', 'first_name', 'middle_name')->get();
        // $models = Task::where('task_date', '<', date('Y-m-d 00:00:00', strtotime('+8 days')))->orderBy('task_date', 'asc')->paginate(config('params.pagination'));
        $models = Task::where('task_date', '<', date('Y-m-d 00:00:00', strtotime('+8 days')))->orderBy('task_date', 'asc')->where('performer_id', Auth::user()->id)->get();

        $arr = [
            translate('Overdue') => [],
            translate('Tasks for today') => [],
            translate('Tasks for tomorrow') => [],
            translate('Tasks for next week') => [],
        ];
        $i = 0;
        if (!empty($models)) {
            foreach ($models as $val) {
                // pre($val->deal->client);
                $keyArr = '';
                if (strtotime($val->task_date) < strtotime(date('Y-m-d 00:00:00')))
                    $keyArr = translate('Overdue');
                else if (strtotime($val->task_date) == strtotime(date('Y-m-d 00:00:00')))
                    $keyArr = translate('Tasks for today');
                else if (strtotime($val->task_date) == strtotime(date('Y-m-d 00:00:00', strtotime('tomorrow'))))
                    $keyArr = translate('Tasks for tomorrow');
                else if (strtotime($val->task_date) > strtotime(date('Y-m-d 00:00:00', strtotime('tomorrow'))) && strtotime($val->task_date) <= strtotime(date('Y-m-d 00:00:00', strtotime('+8 days'))))
                    $keyArr = translate('Tasks for next week');

                $arr[$keyArr][$i]['id'] = $val->id;
                $arr[$keyArr][$i]['responsible'] = (isset($val->performer)) ? $val->performer->last_name . ' ' . $val->performer->first_name : '';
                $arr[$keyArr][$i]['client'] = (isset($val->deal->client)) ? $val->deal->client->last_name . ' ' . $val->deal->client->first_name : '';
                $arr[$keyArr][$i]['client_middle_name'] = (isset($val->deal->client)) ? $val->deal->client->middle_name : '';
                $arr[$keyArr][$i]['client_id'] = $val->deal->client->id ?? 0;
                $arr[$keyArr][$i]['day'] = date('d.m.Y', strtotime($val->task_date));
                $arr[$keyArr][$i]['time'] = date('H:i:s', strtotime($val->task_date));
                $i++;
            }
        }

        return view('forthebuilder::task.index', [
            'arr' => $arr,
            'users' => $users,
            'listLeads' => $listLeads,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $models = Task::all();
        $users = User::all();

        return view('forthebuilder::task.create', [
            'models' => $models,
            'users' => $users,
            'all_notifications' => $this->getNotification(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function calendar_store(Request $request)
    {
        $model = new Task();
        $model->user_id = Auth::user()->id;
        $model->performer_id = $request->performer_id;
        $model->status = Constants::DID_NOT_DO_IT;
        if (isset($request->is_task)) {
            $model->deal_id = $request->deal_id;
            $model->title = $request->task_title ?? '';
            $title = $request->task_title ?? '';
        } else {
            $array_deal_id = explode(" ", $request->deal_id);
            $model->deal_id = (int)end($array_deal_id);
            $title = 'Task added by ';
            $model->title = $title;
        }
        $model->task_date = $request->task_date;
        $model->type = $request->type;
        $model->save();
        Log::channel('action_logs2')->info("пользователь создал новую Task : " . $model->title . "", ['info-data' => $model]);

        $userIdTask = User::findOrFail($request->performer_id);
        event(new RealTimeMessage($title, $userIdTask));
        $notification = new Notification_();
        $notify_array = [
            'id' => $model->id,
            'title' => $model->title,
            'performer_id' => $model->performer->id,
            'user_fio' => $model->user->first_name.' '.$model->user->last_name,
            'performer_fio' => $model->performer->first_name.' '.$model->performer->last_name,
            'performer_middle_name' => $model->performer->middle_name,
            'performer_avatar' => $model->performer->avatar,
            'client_id' => $model->deal->client->id,
            'client_fio' => $model->deal->client->first_name.' '.$model->deal->client->last_name,
            'type' => $model->type,
            'task_date' => $model->task_date
        ];
        $notification->data = json_encode($notify_array);
        $notification->user_id = $userIdTask->id;
        $notification->notifiable_id = $model->id;
        $notification->type = 'Task';
        $notification->save();
        if (isset($request->is_task)) {
            return redirect()->route('forthebuilder.clients.show', [$request->client_id, "0", '0'])->with('success', __('locale.successfully'));
        } else {
            return redirect()->route('forthebuilder.clients.calendar')->with('success', __('locale.successfully'));
        }
    }

    public function taskAnswer(Request $request)
    {
        $model = Task::find($request->task_id);
        $model->answer = $request->answer;
        $model->save();
        return redirect()->back();
    }

    public function store(TaskRequest $request)
    {
        $data = $request->validated();
        $data['status'] = 'Новый';
        $data['user_id'] = Auth::user()->id;
        $title = $data['title'] ?? '';

        $model = Task::create($data);
        Log::channel('action_logs2')->info("пользователь создал новую Task : " . $model->title . "", ['info-data' => $model]);

        $userIdTask = User::findOrFail($data['perform_id']);

        event(new RealTimeMessage($title, $userIdTask));
//        Notification::send($userIdTask, new TaskNotification($model));
        $notification = new Notification_();
        $notify_array = [
            'id' => $model->id,
            'title' => $model->title,
            'performer_id' => $model->performer->id,
            'user_fio' => $model->user->first_name.' '.$model->user->last_name,
            'performer_fio' => $model->performer->first_name.' '.$model->performer->last_name,
            'performer_middle_name' => $model->performer->middle_name,
            'performer_avatar' => $model->performer->avatar,
            'client_id' => $model->deal->client->id,
            'client_fio' => $model->deal->client->first_name.' '.$model->deal->client->last_name,
            'type' => $model->type,
            'task_date' => $model->task_date
        ];
        $notification->data = json_encode($notify_array);
        $notification->user_id = $userIdTask->id;
        $notification->notifiable_id = $model->id;
        $notification->type = 'Task';
        $notification->save();

        return redirect()->route('forthebuilder.task.index')->with('success', __('locale.successfully'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $model = Task::findOrFail($id);
        return view('forthebuilder::task.show', [
            'model' => $model,
            'all_notifications' => $this->getNotification(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $this->getNotification();
        $model = Task::findOrFail($id);
        $users = User::all();
        return view('forthebuilder::task.edit', [
            'model' => $model,
            'users' => $users,
            'all_notifications' => $this->getNotification(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function update(TaskRequest $request, $id)
    {
        $data = $request->validated();
        $model = Task::findOrFail($id);
        $model->title = $data['title'];
        $model->user_task_id = $data['user_task_id'];
        $model->task_date = $data['task_date'];
        $model->task_type = $data['task_type'];
        $model->status = $data['status'];
        $model->prioritet = $data['prioritet'];

        $title = $data['title'];
        $userIdTask = User::findOrFail($data['user_task_id']);
        event(new RealTimeMessage($title, $userIdTask));
        $model->save();

        Log::channel('action_logs2')->info("пользователь обновил " . $model->title . " Task", ['info-data' => $model]);

        return redirect()->route('forthebuilder.task.index')->with('success', __('locale.successfully'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $model = Task::findOrFail($id);
        $model->delete();

        Log::channel('action_logs2')->info("пользователь удалил" . $model->title . " Task", ['info-data' => $model]);

        return back()->with('success', __('locale.deleted'));
    }

    public function read(DatabaseNotification $notification)
    {
        $this->getNotification();
        $notification->markAsRead();
        // dd($notification->data['id']);
        $model = Task::findOrFail($notification->data['id']);

        return view('forthebuilder::task.show', [
            'model' => $model,
            'all_notifications' => $this->getNotification(),
        ]);
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

            $model = Task::findOrFail($id);

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
