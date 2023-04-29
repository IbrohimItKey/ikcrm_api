<?php

namespace App\Http\Controllers;

use App\components\ImageResize;
use App\components\StaticFunctions;
use App\Http\Controllers\Controller;
use App\Models\ApartmentSaleContacts;
use App\Models\ObjectContacts;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Deal;
use App\Models\DealsFile;
use App\Models\House;
use App\Models\HouseDocument;
use App\Models\HouseFlat;
use App\Models\InstallmentPlan;
use App\Models\Notification_;
use App\Models\PayStatus;
use App\Models\PersonalInformations;
use App\Http\Requests\DealRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Models\Booking;
use App\Models\Clients;
use App\Models\Constants;

use function Illuminate\Support\Str;


class DealController extends Controller
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
     *     path="/api/deal/index?page=1",
     *     tags={"Deal"},
     *     summary="Get Deals",
     *     description="Get Deals",
     *     operationId="deal_index",
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
    public function deal_index()
    {
        date_default_timezone_set("Asia/Tashkent");
        $models = Deal::with('house_flat', 'user')->where('status', Constants::ACTIVE)
            // ->select('id', 'user_id', 'house_flat_id', 'price_sell', 'date_deal', 'description')
            ->orderBy('type', 'asc')->get(); //->paginate(config('params.pagination'));

        $arr = [
            'First contact' => ['class' => '#FF9D9D'],
            'Negotiation' => ['class' => '#F7FF9C'],
            'Making a deal' => ['class' => 'lidiGreen'],
        ];
        if (!empty($models)) {
            $i = 0;
            foreach ($models as $key => $value) {
                $keyArr = '';
                $class = '';
                if ($value->client && $value->user) {
                    switch ($value->type) {
                        case Constants::NEGOTIATION:
                            $negotiation['title'] = 'Negotiation';
                            $negotiation['class'] = 'lidiYellow';
                            $negotiation['id'] = $value->id;
                            $negotiation['list'][] = [
                                "responsible_last_name" => $value->user->last_name,
                                "responsible_first_name" => $value->user->first_name,
                                "responsible_middle_name" => $value->user->middle_name,
                                "client_last_name" => $value->client->last_name,
                                "client_first_name" => $value->client->first_name,
                                "client_middle_name" => $value->client->middle_name,
                                "client_id" => $value->client->id ?? 0,
                                "day" => ($value->date_deal) ? date('d.m.Y', strtotime($value->date_deal)) : '',
                                "time" => ($value->date_deal) ? date('H:i', strtotime($value->date_deal)) : ''
                            ];
                            break;
                        case Constants::MAKE_DEAL:
                            $making_a_deal['title'] = 'Making a deal';
                            $making_a_deal['class'] = 'lidiGreen';
                            $making_a_deal['id'] = $value->id;
                            $making_a_deal['list'][] = [
                                "responsible_last_name" => $value->user->last_name,
                                "responsible_first_name" => $value->user->first_name,
                                "responsible_middle_name" => $value->user->middle_name,
                                "client_last_name" => $value->client->last_name,
                                "client_first_name" => $value->client->first_name,
                                "client_middle_name" => $value->client->middle_name,
                                "client_id" => $value->client->id ?? 0,
                                "day" => ($value->date_deal) ? date('d.m.Y', strtotime($value->date_deal)) : '',
                                "time" => ($value->date_deal) ? date('H:i', strtotime($value->date_deal)) : ''
                            ];
                            break;
                        default:
                            $first_contact['title'] = 'First contact';
                            $first_contact['class'] = 'lidiRed';
                            $first_contact['id'] = $value->id;
                            $first_contact['list'][] = [
                                "responsible_last_name" => $value->user->last_name,
                                "responsible_first_name" => $value->user->first_name,
                                "responsible_middle_name" => $value->user->middle_name,
                                "client_last_name" => $value->client->last_name,
                                "client_first_name" => $value->client->first_name,
                                "client_middle_name" => $value->client->middle_name,
                                "client_id" => $value->client->id ?? 0,
                                "day" => ($value->date_deal) ? date('d.m.Y', strtotime($value->date_deal)) : '',
                                "time" => ($value->date_deal) ? date('H:i', strtotime($value->date_deal)) : ''
                            ];
                            break;
                    }
                }
            }
        }
        $arr = [$first_contact??(Object)[], $making_a_deal??(Object)[], $negotiation??''];
        $response = [
            'status'=>true,
            'message'=>'success',
            'data'=> $arr
        ];
       return response($response);
    }
    /**
     * @OA\Post(
     *     path="/api/deal/update-status",
     *     tags={"Deal"},
     *     summary="Update a deal status with form data",
     *     operationId="deal_updateStatus",
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
     *                     description="Deal id",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     description="deal type",
     *                     type="integer",
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function deal_updateStatus(Request $request)
    {
        $user = Auth::user();
        $model = Deal::find($request->id);
        switch ($model->type) {
            case 1:
                $old_type = 'First contact';
                break;
            case 2:
                $old_type = 'Negotiation';
                break;
            case 3:
                $old_type = 'Making a deal';
                break;
        }
        switch ($request->type) {
            case 1:
                $new_type = 'First contact';
                break;
            case 2:
                $new_type = 'Negotiation';
                break;
            case 3:
                $new_type = 'Making a deal';
                break;
        }
        $model->type = $request->type;
        if ($model->history == NULL) {
            $model->history = json_encode([['date' => date('Y-m-d H:i:s'), 'user' => $user->first_name, 'user_id' => $user->id, 'user_photo' => $user->avatar, 'new_type' => $new_type, 'old_type' => $old_type]]);
        } else {
            $old_history = json_decode($model->history);
            $old_history[] = ['date' => date('Y-m-d H:i:s'), 'user' => $user->first_name, 'user_id' => $user->id,  'user_photo' => $user->avatar, 'new_type' => $new_type, 'old_type' => $old_type];
            $model->history = json_encode($old_history);
        }
        $model->save();
        $response = [
            "status"=>true,
            "message"=>"success"
        ];
        return response($response);

    }

    public function edit($id)
    {
        $houses = House::all();
        $model = Deal::findOrFail($id);
        // $installmentPlan = InstallmentPlan::where('deals_id',$id)->get();

        if (isset($model->house_flat->house_id)) {
            $houseFlats = HouseFlat::where('house_id', $model->house_flat->house_id)->get();
        }

        if (file_exists(public_path('/uploads/tmp_files/' . Auth::user()->id . '/deal'))) {
            $dealFiles = File::allFiles(public_path('/uploads/tmp_files/' . Auth::user()->id . '/deal'));
        }
        return view('forthebuilder::deal.edit', [
            'model' => $model,
            'houses' => $houses,
            'houseFlats' => $houseFlats ?? '',
            'dealFiles' => $dealFiles ?? '',
            'all_notifications' => $this->getNotification()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(DealRequest $request, $id)
    {
        $data = $request->validated();
        $model = Deal::findOrFail($id);

        $model->user_id = Auth::user()->id;
        $model->house_flat_id = $data['house_flat_id'];
        $model->house_flat_number = $data['house_flat_number'];
        $model->price_bought = $data['price_bought'];
        $model->phone = $data['phone_code'] . $data['phone'];
        $model->series_number = $data['series_number'];
        $model->gender = $data['gender'];
        $model->additional_phone = $data['phone_code'] . $data['additional_phone'];
        $model->email = $data['email'];
        $model->agreement_number = $data['agreement_number'];
        $model->dateDl = $data['dateDl'];
        $model->description = $data['description'];
        $model->save();
        $informations = $model->personal_informations;
        $personals = PersonalInformations::all();
        foreach ($personals as $personal) {
            $series_number[] = str_replace(' ', '', $personal->series_number);
        }
        if (!empty($informations)) {
            if (!in_array(str_replace(' ', '', $data['series_number']), $series_number) || str_replace(' ', '', $data['series_number']) == str_replace(' ', '', $informations->series_number)) {
                $informations->full_name = $data['full_name'];
                $informations->series_number = $data['series_number'];
                $informations->given_date = $data['given_date'];
                $informations->live_address = $data['live_address'];
                $informations->inn = $data['inn'];
                $model->informations()->save($informations);
            } else {
                return redirect()->route('forthebuilder.deals.edit', $model->id)->with('warning', __('locale.This series of passport belongs to another'));
            }
        } else {
            if (!in_array(str_replace(' ', '', $data['series_number']), $series_number)) {
                $informations = new PersonalInformations();
                $informations->full_name = $data['full_name'];
                $informations->series_number = $data['series_number'];
                $informations->given_date = $data['given_date'];
                $informations->live_address = $data['live_address'];
                $informations->inn = $data['inn'];
                $model->informations()->save($informations);
            }
        }


        if (!file_exists(public_path('uploads/deal/' . $model->id))) {
            $path = public_path('uploads/deal/' . $model->id);
            File::makeDirectory($path, $mode = 0777, true, true);
        }

        $dealFiles = File::allFiles(public_path('/uploads/tmp_files/' . Auth::user()->id . '/deal'));
        $j = 0;
        foreach ($dealFiles as $dealFileItem) {
            $j++;
            $sourcePath = public_path('uploads/tmp_files/' . Auth::user()->id . '/deal/' . $dealFileItem->getFilename());
            $filenamehash = md5($dealFileItem->getFilename() . time()) . '.' . $dealFileItem->getExtension();
            $filesize =  File::size($sourcePath);

            $pathInfo = pathinfo($sourcePath);
            if ($pathInfo['extension'] == 'jpg' || $pathInfo['extension'] == 'png' || $pathInfo['extension'] == 'jpeg') {
                $imageR = new ImageResize($sourcePath);
                $imageR->resizeToBestFit(config('params.large_image.width'), config('params.large_image.width'))->save(public_path('uploads/deal/' . $model->id . '/l_' . $filenamehash));
                $imageR->resizeToWidth(config('params.medium_image.width'))->save(public_path('uploads/deal/' . $model->id . '/m_' . $filenamehash));
                $imageR->crop(config('params.small_image.width'), config('params.small_image.height'))->save(public_path('uploads/deal/' . $model->id . '/s_' . $filenamehash));
            } else {
                $storageDestinationPath = public_path('uploads/deal/' . $model->id . '/' . $filenamehash);
                File::move($sourcePath, $storageDestinationPath);
            }

            DealsFile::create([
                'deal_id' => $model->id,
                'name' => $dealFileItem->getFilename(),
                'guid' => $filenamehash,
                'ext' => $dealFileItem->getExtension(),
                'size' => $filesize ?? '',
                'main_image' => $j == 1 ? 1 : 0,
            ]);

            File::delete($sourcePath);
        }

        Log::channel('action_logs2')->info("пользователь обновил deal", ['info-data' => $model]);
        return redirect()->route('forthebuilder.deal.index')->with('success', __('locale.successfully'));
    }

    //==================== kartik fileinput resource/deal/create scripts dagi fileinputga qara ==================================
    // StaticFunctions::convertNumberToWord();

    public function fileUpload(Request $request)
    {
        $foldername = 'deal'; //web.php dagi shu controllerning prefixi
        return StaticFunctions::fileUploadKartikWithAjax($request, 'forthebuilder', $foldername);
    }


    public function fileDelete(Request $request, $key)
    {
        $filePath = public_path('/uploads/tmp_files/' . Auth::user()->id . '/deal/' . $key);
        return File::delete($filePath);
    }

    //==================== yakunladni kartik fileinput resource/deal/create scripts dagi fileinputga qara ==================================

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $dealModel = Deal::findOrFail($id);

        $DealsFilemodels = DealsFile::where('deal_id', $id)->get();

        foreach ($DealsFilemodels as $DealsFilemodel) {
            File::delete(public_path('uploads/deal/' . $DealsFilemodel->deal_id . '/l_' . $DealsFilemodel->guid));
            File::delete(public_path('uploads/deal/' . $DealsFilemodel->deal_id . '/s_' . $DealsFilemodel->guid));
            File::delete(public_path('uploads/deal/' . $DealsFilemodel->deal_id . '/m_' . $DealsFilemodel->guid));
            $DealsFilemodel->delete();
        }

        $dealModel->delete();
        if (isset($dealModel->house_flat->id)) {
            $dealModel->house_flat->status = 0;
            $dealModel->house_flat->save();
        }
        if (isset($dealModel->plan)) {
            $dealModel->plan->delete();
        }
        Log::channel('action_logs2')->info("пользователь удалил deal", ['info-data' => $dealModel]);
        return back()->with('success', __('locale.deleted'));
    }



}
