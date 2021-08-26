<?php

namespace App\Traits;

use App\Notification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

trait ApiResponser
{
    public function apiResponse($status = true,$message = null,$data = null, $code = Response::HTTP_OK,$name = 'data'){
        $array = [
            'status' => $status,
            'message' => $message,
            $name => $data,
        ];
        return response($array,$code);
    }

    public function noti($title,$body,$tokens,$dataObj=['a_data' => 'my_data'],$sound='default')
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);
        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($body)->setSound($sound);

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($dataObj);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        FCM::sendTo($tokens, $option, $notification, $data);
    }

    public function create_notification($user_id,$object, $title_ar, $title_en)
    {
        $data = ['id'=>$object->id,
            'title_ar'=>$title_ar,
            'title_en'=>$title_en,
            'route_api'=>route('api.ordersDetails',$object->id)];


        $notification = new Notification();
        $notification->id = $this->generate_uuid();
        $notification->type = get_class($object);
        $notification->notifiable_type = 'App\User';
        $notification->notifiable_id  = $user_id;
        $notification->data = json_encode($data);
        $notification->save();
    }

    function generate_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0C2f ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0x2Aff ), mt_rand( 0, 0xffD3 ), mt_rand( 0, 0xff4B )
        );
    }
}
