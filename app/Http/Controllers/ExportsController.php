<?php

namespace App\Http\Controllers;

use App\components\StaticFunctions;
use Facade\FlareClient\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Deal;
use App\Models\Notification_;
use App\Exports\RassrochkaExport;
use App\Exports\LeadsExport;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Response;

class ExportsController extends Controller
{

    public function getNotification(){
        $notification = ['Booking', 'BookingPrepayment'];
        $all_task = Notification_::where('type', 'Task')->where(['read_at' => NULL,  'user_id' => Auth::user()->id])->orderBy('created_at', 'desc')->get();
        $all_booking = Notification_::whereIn('type', $notification)->where('read_at', NULL)->orderBy('created_at', 'desc')->get();
        return ['all_task'=>$all_task, 'all_booking'=>$all_booking];
    }

    public function rassrochka($id)
    {
        return Excel::download(new RassrochkaExport($id), 'rassrochka.xlsx');
    }

    public function leads()
    {
        return Excel::download(new LeadsExport, 'leads.xlsx');
    }

    public function generateContract(Request $request,$id)
    {
        $model = Deal::findOrFail($id);
        $month = StaticFunctions::getMonth(date('m',strtotime($model->dateDl)));
        $price_word = StaticFunctions::convertNumberToWord($model->house_flat->price);
        $headers = [
            "Content-type"=>"text/html",
            "Content-Disposition"=>"attachment;Filename=".$model->id.".doc"
        ];

        return \Illuminate\Support\Facades\Response::make(view('forthebuilder::deal.contract', [
            'model' => $model,
            'month' => $month,
            'price_word' => $price_word,
            'all_notifications' => $this->getNotification()
        ]),200, $headers);
    }

}
