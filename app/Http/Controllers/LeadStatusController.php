<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\LeadStatus;
use App\Models\Notification_;
use App\Http\Requests\LeadStatusRequest;

class LeadStatusController extends Controller
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
        $models = LeadStatus::orderBy('id','desc')->paginate(config('params.pagination'));
        return view('forthebuilder::lead-status.index',[
            'models' => $models,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $models = LeadStatus::all();

        return view('forthebuilder::lead-status.create',[
            'models' => $models,
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(LeadStatusRequest $request)
    {
        $data = $request->validated();

        $leads = LeadStatus::create($data);

        Log::channel('action_logs2')->info("пользователь создал новую LeadStatus : " . $leads->name."",['info-data'=>$leads]);

        return redirect()->route('forthebuilder.lead-status.index')->with('success', __('locale.successfully'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $model = LeadStatus::findOrFail($id);
        return view('forthebuilder::lead-status.show',[
            'model' => $model,
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
        $model = LeadStatus::findOrFail($id);

        return view('forthebuilder::lead-status.edit',[
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
    public function update(LeadStatusRequest $request, $id)
    {
        $data = $request->validated();
        $model = LeadStatus::findOrFail($id);
        $model->name = $data['name'];
        $model->order = $data['order'];

        $model->save();

        Log::channel('action_logs2')->info("пользователь обновил ".$model->name." LeadStatus",['info-data'=>$model]);

        return redirect()->route('forthebuilder.lead-status.index')->with('success', __('locale.successfully'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $leads = LeadStatus::findOrFail($id);
        $leads->delete();

        Log::channel('action_logs2')->info("пользователь удалил ".$leads->name." LeadStatus",['info-data'=>$leads]);

        return back()->with('success', __('locale.deleted'));
    }
}
