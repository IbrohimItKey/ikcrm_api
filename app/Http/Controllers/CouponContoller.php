<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Coupon;
use App\Models\Notification_;
use App\Http\Requests\CouponRequest;
use App\Models\Constants;


class CouponContoller extends Controller
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

    public function index(Request $request)
    {
    //  dd('fefsefsfs');




        $coupons = Coupon::get()->toArray();
        // dd($coupons);



        $page = $request->page;
        $pagination = Constants::PAGINATION; 
        $offset = ($page - 1) * $pagination;
        $endCount = $offset + $pagination;
        $count = count($coupons);
        // dd($count);
        $paginated_results=array_slice($coupons, $offset, $pagination);
        $paginatin_count=ceil($count/$pagination);
        return response([
            'status' => true,
            'message' => 'success',
            'data' => $paginated_results,
            "pagination"=>true,
            "pagination_count"=>$paginatin_count
        ]);



    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('forthebuilder::coupon.create', [
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(CouponRequest $request)
    {

        $data = $request->validated();

        $model = new Coupon();
        $model->name = $data['name'];
        $model->percent = $data['percent'];
        if ($model->save())
        return response([
            'status' => true,
            'message' => 'success'
        ]);

        // return redirect()->route('forthebuilder.coupon.index')->with('success', __('locale.successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $model = Coupon::find($id);
        return view('forthebuilder::coupon.edit')->with([
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
    public function update(CouponRequest $request)
    {
        $data = $request->validated();

        if ($model=Coupon::find($request->id)) {
            // dd($model);
            $model->name = $data['name'];
            $model->percent = $data['percent'];
            if ($model->save())
            return response([
                'status' => true,
                'message' => 'success'
            ]);
        }
        return response([
            'status' => false,
            'message' => 'error',

        ]);
      

        // return redirect()->route('forthebuilder.coupon.index')->with('success', __('locale.successfully'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Request $request)
    {
        dd($request->all());
        if ($model = Coupon::find($request->id)) {
            $model = Coupon::find($request->id);
            // dd($model);
            $model->delete();
            return response([
                'status' => true,
                'message' => 'success'
            ]);
        }
        return response([
            'status' => false,
            'message' => 'error'
        ]);
        
        // return redirect()->route('forthebuilder.coupon.index')->with('deleted', translate('Data deleted successfuly'));
    }
}
