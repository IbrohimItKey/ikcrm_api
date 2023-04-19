<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\components\ImageResize;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\ForTheBuilder\Entities\Notification_;
use Modules\ForTheBuilder\Entities\Task;
use Modules\ForTheBuilder\Http\Requests\ForTheBuilderUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getNotification(){
        $notification = ['Booking', 'BookingPrepayment'];
        $all_task = Notification_::where('type', 'Task')->where(['read_at' => NULL,  'user_id' => Auth::user()->id])->orderBy('created_at', 'desc')->get();
        $all_booking = Notification_::whereIn('type', $notification)->where('read_at', NULL)->orderBy('created_at', 'desc')->get();
        return ['all_task'=>$all_task, 'all_booking'=>$all_booking];
    }

    public function index()
    {
        if(Gate::allows('isAdmin')){
            $models = User::where('status',2)->where('id', '!=', Auth::user()->id)->orderBy('id','desc')->paginate(config('params.pagination'));
        }else{
            $models = User::where('status',2)->where('id', '!=', Auth::user()->id)->where('role_id', 2)->orderBy('id','desc')->paginate(config('params.pagination'));
        }

        return view('forthebuilder::user.index',[
            'models' => $models,
            'all_notifications' => $this->getNotification()
        ]);
    }

    public function settings(){
        return view('forthebuilder::settings.index', ['all_notifications' => $this->getNotification()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
//        dd(Storage::class);
        $roles = Role::all();

        return view('forthebuilder::user.create',[
            'roles' => $roles,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ForTheBuilderUserRequest $request)
    {
        $data = $request->validated();
        $data['status'] = 2;
        $data['password'] = Hash::make($data['password']);
        $image = $data['avatar'] ?? '';
        if (!empty($image)) {
            $imageName = md5(time().$image).'.'.$image->getClientOriginalExtension();
            $data['avatar'] = $imageName;
        }

        $model = User::create($data);
        if (!empty($image)) {
            //bu yerda orginal rasm yuklanyapti ochilgan papkaga
            $image->move(public_path('uploads/user/'.$model->id),$imageName);

            //bu yerda orginal rasm  app/components/imageresize.php fayliga kesiladigan rasm manzili ko'rsatilyapti
            $imageR = new ImageResize( public_path('uploads/user/'.$model->id . '/' . $imageName));

            //bu yerda orginal rasm  app/components/imageresize.php fayli orqali kesilyapti
            $imageR->resizeToBestFit(config('params.medium_image.width'), config('params.medium_image.width'))->save(public_path('uploads/user/'.$model->id . '/s_' . $imageName));
            //bu yerda orginal rasm  o'chirilyapti.chunki endi bizga kerakmas orginali biz o'zimizga kerkligicha kesib oldik
            File::delete(public_path('uploads/user/'.$model->id.'/'.$imageName));

        }
        Log::channel('action_logs2')->info("пользователь создал новую Пользователь : " . $model->first_name."",['info-data'=>$model]);

        return redirect()->route('forthebuilder.user.index')->with('success', __('locale.successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {


        /* php artisan websocket:start */

        // \Artisan::call('websocket:start');


        $model = User::findOrfail($id);
        $user = Auth::user();
        if(Gate::allows('isAdmin')){
            $users = User::where('status',2)->where('id', '!=', $user->id)->orderBy('id','desc')->paginate(config('params.pagination'));
        }else{
            $users = User::where('status',2)->where('id', '!=', $user->id)->where('role_id', 2)->orderBy('id','desc')->paginate(config('params.pagination'));
        }
        $my_tasks = Task::where('performer_id', $id)->get();
        $tasks = count(Task::where('performer_id', $id)->get());
        $tasks_ended = count(Task::where('performer_id', $id)->where('status', 1)->get());
        $tasks_not_ended = count(Task::where('performer_id', $id)->where('status', NULL)->get());
        $task_count = [];
        $task_count['count'] = [];
        $task_count['task_date'] = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $monthly_count = [];
        if(!empty($my_tasks)){
            foreach($my_tasks as $task){
                $task_array = explode('-',$task->task_date);
                if($task_array[0] == date('Y')){
                    $task_count['task_date'][] = $task_array[1];
                    $taskcount = count(Task::where('task_date', $task->task_date)->get());
                    $task_count['count'][] = $taskcount;
                }
            }
            if(!empty($task_count['task_date'])){
                $monthly_count = array_count_values($task_count['task_date']);
            }
        }

        return view('forthebuilder::user.show',[
            'model' => $model,
            'users' => $users,
            'user' => $user,
            'tasks' => $tasks,
            'tasks_ended' => $tasks_ended,
            'tasks_not_ended' => $tasks_not_ended,
            'monthly_count' => $monthly_count,
            'my_tasks' => $my_tasks,
            'task_count' => $task_count,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = User::findOrfail($id);
        $roles = Role::all();

        return view('forthebuilder::user.edit',[
            'model' => $model,
            'roles' => $roles,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(ForTheBuilderUserRequest $request, $id)
    {
        $data = $request->validated();
        $model = User::findOrFail($id);
        $model->first_name = $data['first_name'];
        $model->last_name = $data['last_name'];
        $model->middle_name = $data['middle_name'];
        if ($model->status == 10) $model->status = 10;
        else $model->status = $data['status'];
        $model->email = $data['email'];
        $model->role_id = $data['role_id'];
        $model->save();

        if(!empty($request->input('current_password'))) {
            if(!Hash::check($request->input('current_password'), $model->password)){
                return back()->with('current_password', 'Current password does not match!');
            }else{
                $model->fill(['password' => Hash::make($request->input('password'))])->save();
            }
        }

        if (!empty($data['avatar']))
        {
            $image = $data['avatar'];
            $image_old = $model->avatar;
            $imageName = md5(time().$image).'.'.$image->extension();

            //bu yerda orginal rasm yuklanyapti ochilgan papkaga
            $image->move(public_path('uploads/user/'.$model->id),$imageName);

            //bu yerda orginal rasm  app/components/imageresize.php fayliga kesiladigan rasm manzili ko'rsatilyapti
            $imageR = new ImageResize( public_path('uploads/user/'.$model->id . '/' . $imageName));

            //bu yerda orginal rasm  app/components/imageresize.php fayli orqali kesilyapti
            $imageR->resizeToBestFit(config('params.medium_image.width'), config('params.medium_image.width'))->save(public_path('uploads/user/'.$model->id . '/s_' . $imageName));
            //bu yerda orginal rasm  o'chirilyapti.chunki endi bizga kerakmas orginali biz o'zimizga kerkligicha kesib oldik
            File::delete(public_path('uploads/user/'.$model->id.'/'.$imageName));

            if (!empty($image_old)) {
                File::delete(public_path('uploads/user/'.$model->id.'/s_'.$image_old));
            }
            $model->avatar = $imageName;
            $model->save();
        }

        Log::channel('action_logs2')->info("пользователь обновил ".$model->first_name." Пользователь",['info-data'=>$model]);

        return redirect()->route('forthebuilder.user.index')->with('success', __('locale.successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if($user->id != Auth::user()->id) $user->delete();

        Log::channel('action_logs2')->info("пользователь удалил ".$user->first_name." Пользователь",['info-data'=>$user]);

        return redirect()->route('forthebuilder.user.index')->with('success', __('locale.deleted'));
    }

//    public function delete()
//    {
//        if (Gate::allows('isAdmin')) {
//            dd('Admin allowed');
//        } else {
//            dd('You are not Admin');
//        }
//    }

}
