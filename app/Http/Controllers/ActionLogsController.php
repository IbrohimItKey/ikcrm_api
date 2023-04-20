<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ActionLogs;
use App\Models\Notification_;

class ActionLogsController extends Controller
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
        $models = ActionLogs::orderBy('id','desc')->paginate(config('params.pagination'));
        return view('forthebuilder::action-logs.index',[
            'models' => $models,
            'all_notifications'=>$this->getNotification()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = ActionLogs::findOrfail($id);

        return view('forthebuilder::action-logs.show',[
            'model' => $model,
            'all_notifications'=>$this->getNotification()
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ActionLogs::findOrFail($id)->delete();
        return back()->with('success', __('locale.deleted'));
    }

    public function destroyMultiple(Request $request)
    {
        $data_ids = $request->ids;
        if ($data_ids) {
            DB::table("action_logs")->whereIn('id',explode(",",$data_ids))->delete();
            return response()->json(['success'=>"успешно удален"]);
        }
    }

    public function destroyAll(Request $request)
    {
        ActionLogs::truncate();
        return back()->with('success', __('locale.deleted'));
    }
}
