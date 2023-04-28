<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Constants;
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
use App\Models\Notification_;
use App\Models\Task;
use App\Http\Requests\ForTheBuilderUserRequest;

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
    /**
     * @OA\Get(
     *     path="/api/user/index?page=1",
     *     tags={"User"},
     *     summary="Get User",
     *     description="Get User",
     *     operationId="user_index",
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
    public function user_index(Request $request)
    {
        if(Gate::allows('isAdmin')){
            $models = User::select('id', 'first_name', 'last_name', 'middle_name', 'email', 'avatar')
                ->where('status',2)->where('id', '!=', Auth::user()->id)->orderBy('id','desc')->get();
        }else{
            $models = User::select('id', 'first_name', 'last_name', 'middle_name', 'email', 'avatar')
                ->where('status',2)->where('id', '!=', Auth::user()->id)->where('role_id', 2)->orderBy('id','desc')->get();
        }
        $response = [];
        foreach ($models as $model){
            $response[] = [
                'id' => $model->id,
                'first_name' => $model->first_name,
                'last_name' => $model->last_name,
                'middle_name' => $model->middle_name,
                'email' => $model->email,
                'image' => asset('/uploads/user/' . $model->id . '/' . $model->avatar),
            ];
        }
        $page = $request->page;
        $pagination = Constants::PAGINATION;
        $offset = ($page - 1) * $pagination;
        $endCount = $offset + $pagination;
        $count = count($response);
        $paginated_results = array_slice($response, $offset, $pagination);
        $paginatin_count = ceil($count/$pagination);
        return response([
            'status' => true,
            'message' => 'success',
            'data' => $paginated_results,
            "pagination"=>true,
            "pagination_count"=>$paginatin_count
        ]);
    }

    public function settings(){
        return view('forthebuilder::settings.index', ['all_notifications' => $this->getNotification()]);
    }
    /**
     * @OA\Post(
     *     path="/api/user/insert",
     *     tags={"User"},
     *     summary="create a task with form data",
     *     operationId="user_store",
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
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="first_name",
     *                     description="firstname",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     description="lastname",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="middle_name",
     *                     description="middle name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="email",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="role_id",
     *                     description="role id",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     description="image",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="password",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     description="password confirmation",
     *                     type="string",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function user_store(ForTheBuilderUserRequest $request)
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
        }
        Log::channel('action_logs2')->info("пользователь создал новую Пользователь : " . $model->first_name."",['info-data'=>$model]);
        $response = [
            "status" => true,
            "message" => "success",
            "id" => $model->id
        ];
        return response($response);
    }

    /**
     * @OA\Get(
     *     path="/api/user/show?page=1",
     *     tags={"User"},
     *     summary="User show",
     *     description="User show",
     *     operationId="user_show",
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
    public function user_show(Request $request)
    {
        // \Artisan::call('websocket:start');

        $model = User::select('id', 'first_name', 'last_name', 'middle_name', 'email', 'role_id', 'status', 'avatar')->findOrfail($request->id);
        $user = User::select('id')->find(Auth::user()->id);
        if(Gate::allows('isAdmin')){
            $users = User::select('first_name', 'last_name', 'middle_name', 'email', 'role_id', 'status', 'avatar')->where('status',2)->where('id', '!=', $user->id)->orderBy('id','desc')->paginate(config('params.pagination'));
        }else{
            $users = User::select('first_name', 'last_name', 'middle_name', 'email', 'role_id', 'status', 'avatar')->where('status',2)->where('id', '!=', $user->id)->where('role_id', 2)->orderBy('id','desc')->paginate(config('params.pagination'));
        }
        $my_tasks = Task::where('performer_id', $request->id)->get();
        $tasks = count(Task::where('performer_id', $request->id)->get());
        $tasks_ended = count(Task::where('performer_id', $request->id)->where('status', 1)->get());
        $tasks_not_ended = count(Task::where('performer_id', $request->id)->where('status', NULL)->get());
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
        $response = [
            "status" => true,
            "message" => "success",
            "data" => [
                'user' => $model,
                'user_is_me' => $user->id,
                'tasks_ended' => $tasks_ended,
                'tasks_not_ended' => $tasks_not_ended,
                'monthly_count' => $monthly_count,
                'task_count' => $task_count,
            ],
        ];
        return response($response);
    }

    /**
     * @OA\Get(
     *     path="/api/user/edit/15",
     *     tags={"User"},
     *     summary="User edit",
     *     description="User edit",
     *     operationId="user_edit",
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
    public function user_edit($id)
    {
        $model = User::select('id', 'first_name', 'last_name', 'middle_name', 'email', 'role_id', 'role_id', 'avatar as image')
            ->findOrfail($id);
        $model->image = asset('/uploads/user/'.$model->id.'/'.$model->avatar);
        return response($model);
    }

    /**
     * @OA\Post(
     *     path="/api/user/update",
     *     tags={"User"},
     *     summary="create a task with form data",
     *     operationId="user_update",
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
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="first_name",
     *                     description="firstname",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     description="lastname",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="middle_name",
     *                     description="middle name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     description="status",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="email",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="role_id",
     *                     description="role id",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     description="image",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="current_password",
     *                     description="current password",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="password",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     description="password confirmation",
     *                     type="string",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function user_update(ForTheBuilderUserRequest $request)
    {
        $data = $request->validated();
        $model = User::findOrFail($request->id);
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

            if (!empty($image_old)) {
                File::delete(public_path('uploads/user/'.$model->id.'/'.$image_old));
            }
            $model->avatar = $imageName;
            $model->save();
        }

        Log::channel('action_logs2')->info("пользователь обновил ".$model->first_name." Пользователь",['info-data'=>$model]);
        $response = [
            "status" => true,
            "message" => "success",
            "id" => $model->id
        ];
        return response($response);
    }

    /**
     * @OA\Post(
     *     path="/api/user/delete",
     *     tags={"User"},
     *     summary="Delete a user with form data",
     *     operationId="user_destroy",
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
     *                     description="User id",
     *                     type="integer",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function user_destroy(Request $request)
    {
        $user = User::findOrFail($request->id);
        if($user->id != Auth::user()->id) $user->delete();
        Log::channel('action_logs2')->info("пользователь удалил ".$user->first_name." Пользователь",['info-data'=>$user]);

        $response = [
            "status" => true,
            "message" => "success",
            "id" => $user->id
        ];
        return response($response);
    }

}
