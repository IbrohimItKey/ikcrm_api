<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Models\House;
use App\Models\HouseFlat;
use App\Models\Leads;
use App\Models\LeadStatus;
use Illuminate\Support\Facades\DB;
use App\Models\Constants;
use Carbon\Carbon;
use App\Models\Deal;
use App\Models\Notification_;


class ForTheBuilderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    /**
     * Class constructor.
     */

    // public function getNotification(){
    //     $notification = ['Booking', 'BookingPrepayment'];
    //     $all_task = Notification_::where('type', 'Task')->where(['read_at' => NULL,  'user_id' => Auth::user()->id])->orderBy('created_at', 'desc')->get();
    //     $all_booking = Notification_::whereIn('type', $notification)->where('read_at', NULL)->orderBy('created_at', 'desc')->get();
    //     return ['all_task'=>$all_task, 'all_booking'=>$all_booking];
    // }

    public function index()
    {

          // Deal
        $new_users=DB::table('deals as dt1')
        ->join('clients as dt2', 'dt2.id', '=', 'dt1.client_id')
        ->where('dt1.type',Constants::FIRST_CONTACT)
        // ->distinct('dt1.client_id')
        ->count();
        // dd($new_user);
        $in_negotiations=DB::table('deals as dt1')
        ->where('dt1.type',Constants::NEGOTIATION)
        // ->distinct('dt1.client_id')
        ->count();
        $make_deal=DB::table('deals as dt1')
        ->where('dt1.type',Constants::MAKE_DEAL)
        // ->distinct('dt1.client_id')
        ->count();

         // Task
         // today
         $date=Carbon::now()->format('Y-m-d');
         $today=DB::table('task as task')
        //  ->join('clients as dt2', 'dt2.id', '=', 'dt1.client_id')
         ->where('task.task_date','=',$date)
         ->where('task.status',0)
         ->count();
        //  dd($today);
        //  toomorrow
         $datetime = date("Y-m-d", strtotime('tomorrow'));
        //  dd($datetime);
         $tomorrow=DB::table('task as task')
        //  ->join('clients as dt2', 'dt2.id', '=', 'dt1.client_id')
         ->where('task.task_date','=',$datetime)
         ->where('task.status',0)
         ->count();
        //  dd($tomorrow);
         // week
        $day_after_a_week=Carbon::now()->addDay(7)->format('Y-m-d');
        // dd($day_after_a_week);
        $week=DB::table('task as task')
        // ->join('clients as dt2', 'dt2.id', '=', 'dt1.client_id')
        ->where('task.task_date','<',$day_after_a_week)
        ->where('task.task_date','>=',$date)
        ->where('task.status',0)
        ->count();

        $full_task=DB::table('task as task')
        // ->join('clients as dt2', 'dt2.id', '=', 'dt1.client_id')
        ->where('task.task_date','>=',$date)
        ->where('task.status',0)
        ->count();
        // dd($date);
        // $tomorrow_date=Carbon::now()->addDay(1)->format('Y-m-d');
        $overdue_tasks=DB::table('task as task')
        // ->join('clients as dt2', 'dt2.id', '=', 'dt1.client_id')
        ->where('task.task_date','<',$date)
        ->where('task.status',0)
        ->count();
        // dd($overdue_tasks);

        $house_count=DB::table('house as house')
        ->where('house.deleted_at',null)
        ->count();

        $house_flat_status_free=DB::table('house_flat as house_flat')
        ->where('house_flat.deleted_at',null)
        ->where('house_flat.status',Constants::STATUS_FREE)
        ->count();

        $house_flat_status_booking=DB::table('house_flat as house_flat')
        ->where('house_flat.deleted_at',null)
        ->where('house_flat.status',Constants::STATUS_BOOKING)
        ->count();

        $house_flat_status_sold=DB::table('house_flat as house_flat')
        ->where('house_flat.deleted_at',null)
        ->where('house_flat.status',Constants::STATUS_SOLD)
        ->count();
        // dd($house_flat_status_sold);

        // $installment_count=DB::table('installment_plan as installment_plan')
        // ->count();
        $installment_count = Deal::where('installment_plan_id', '!=', NULL)
        ->count();



        $month_prices = DB::table('deals as dt1')
            ->join('house_flat as dt2', 'dt2.id', '=', 'dt1.house_flat_id')
            ->join('users as dt3', 'dt3.id', '=', 'dt1.user_id')
            ->where('dt2.status',Constants::STATUS_SOLD)
            // ->where('dt3.role_id',2)
            ->select('dt1.price_sell','dt1.date_deal','dt3.first_name','dt3.last_name')
            ->get();
            // dd($month_prices);


        $price=0;
        $priceArr=[];
        for ($j=0; $j <= 11; $j++) {
            $priceArr[$j] = 0;
        }

        $year=Carbon::now()->format('y');
        $month=Carbon::now()->format('m');
        $last_date = cal_days_in_month(CAL_GREGORIAN, $month,$year);

        $price_day_array = [];
        $month_day = [];
        for ($i=0; $i <= $last_date; $i++) {
            $price_day_array[$i] = 0;
            $month_day[$i] = $i;
        }
            $core_chart="";
        foreach ($month_prices as  $value) {
            $myDate=$value->date_deal;
            $date_table = Carbon::createFromFormat('Y-m-d', $myDate);
            $month_code = $date_table->format('n');
            $date_day=Carbon::now()->format('m');
            $month_code_day = $date_table->format('j');
            if ($month_code==$date_day) {
                $core_chart.="['".$value->first_name."',     ".$value->price_sell."],";
            }
            // dd($month_prices);
            // dd($month_code);

            if ($month_code==$date_day) {
                $price_day_array[$month_code_day-1] +=$value->price_sell;
            }

            // $mont_code = $date_table->format('m');
            $priceArr[$month_code-1] += $value->price_sell;
            $price +=$value->price_sell;
        }


        $data=[
           'date_today'=>$date,
           'new_clients'=>$new_users,
           'in_negotiations'=>$in_negotiations,
           'make_deal'=>$make_deal,
           'today'=>$today,
           'tomorrow'=>$tomorrow,
           'week'=>$week,
           'full_task'=>$full_task,
           'overdue_tasks'=>$overdue_tasks,
           'house_count'=>$house_count,
           'house_flat_status_free'=>$house_flat_status_free,
           'house_flat_status_booking'=>$house_flat_status_booking,
           'house_flat_status_sold'=>$house_flat_status_sold,
           'installment_count'=>$installment_count,
           'price'=>$price,
           'month_sales_price'=>$priceArr,
           'month_day'=>$month_day,
           'price_day_array'=>$price_day_array,
           'core_chart'=>$core_chart
        //    'list'=>$list


        ];
        // dd($data);


        $response = [
            'status' => true,
            'message' => 'success',
            'data' => $data
        ];
        return  response($response);

        // $boughFlatsCount = HouseFlat::where('status',2)->count();
        // $newFlatsCount = HouseFlat::where('status',0)->count();
        // $busyFlatsCount = HouseFlat::where('status',1)->count();

        // $newLeadsCount = LeadStatus::where('name','Новый')->first();

        // if(!empty($newLeadsCount)){
        //     $newLeadsCount = $newLeadsCount->leads->count();
        // }

        // return view('forthebuilder::index',[
        //     'newLeadsCount' => $newLeadsCount,
        //     'boughFlatsCount' => $boughFlatsCount,
        //     'newFlatsCount' => $newFlatsCount,
        //     'busyFlatsCount' => $busyFlatsCount,
        //     'data'=>$data,
        //     'all_notifications' => $this->getNotification()
        // ]);
    }

    // public function indexLayout()
    // {
    //     $houses = House::all();
    //     return view('forthebuilder::layouts.forthebuilder',[
    //         'houses' => $houses,
    //     ]);
    // }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    // public function create()
    // {
    //     return view('forthebuilder::create', ['all_notifications' => $this->getNotification()]);
    // }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    // public function show($id)
    // {
    //     return view('forthebuilder::show', ['all_notifications' => $this->getNotification()]);
    // }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    // public function edit($id)
    // {
    //     return view('forthebuilder::edit', ['all_notifications' => $this->getNotification()]);
    // }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
