<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\Deal;
use App\Models\DealsFile;
use App\Models\HouseFlat;
use App\Models\LeadComment;
use App\Models\Leads;
use App\Models\Notification_;
use App\Http\Requests\LeadCommentRequest;
use App\Http\Requests\LeadsRequest;

class LeadCommentController extends Controller
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
        return view('forthebuilder::index', ['all_notifications' => $this->getNotification()]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('forthebuilder::create', ['all_notifications' => $this->getNotification()]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    public function store(LeadCommentRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::user()->id;

        $lead = LeadComment::create($data);

        Log::channel('action_logs2')->info("пользователь создал новую lead : ",['info-data'=>$lead]);

        return back()->with('success',__('locale.successfully'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('forthebuilder::show', ['all_notifications' => $this->getNotification()]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $model = LeadComment::findOrFail($id);

        return view('forthebuilder::lead-comment.edit',[
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
    public function update(LeadCommentRequest $request, $id)
    {
        $data = $request->validated();
        $model = LeadComment::findOrFail($id);

        $model->comment = $data['comment'];

        $model->save();

        Log::channel('action_logs2')->info("пользователь обновил ".$model->comment." Lead-comment",['info-data'=>$model]);

        return redirect()->route('forthebuilder.leads.show',$model->lead_id)->with('success',__('locale.edited'));

    }
    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $dealModel = LeadComment::findOrFail($id);

        $dealModel->delete();

        Log::channel('action_logs2')->info("пользователь удалил lead-comment",['info-data'=>$dealModel]);
        return back()->with('success', __('locale.deleted'));
    }
}
