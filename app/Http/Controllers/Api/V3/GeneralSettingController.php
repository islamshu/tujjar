<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\ConversationResource;
use App\Http\Resources\V3\GeneralSettingResource;
use App\Models\V3\Message;
use App\Models\V3\GeneralSetting;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\V3\Notification;
use App\User;
use App\Models\V3\User as UserV3;

class GeneralSettingController extends BaseController
{
    public function index()
    {
        $data['data'] = GeneralSettingResource::collection(GeneralSetting::all());
        return $data;
    }

    public function get_noification()
    {
        $arr = array();
        $user = User::find(auth('api')->id())->notifications;
        $user2 = UserV3::find(auth('api')->id())->notifications;
        foreach ($user as $key => $no) {
            $arr[$key]['title_en'] = $no->data['title_en'];
            $arr[$key]['title_ar'] = $no->data['title_ar'];
            $arr[$key]['date'] = $no->created_at;
            $arr[$key]['link'] = route('notfy_single', $no->id);
            if ($no->type == Notification::MESSAGE) {
                $id = $no->data['id'];
                $conversation = Message::find($id)->conversation()->get();
                $arr[$key]['type'] = 'message';
                $arr[$key]['data']['data'] = ConversationResource::collection($conversation);
            }elseif ($no->type == Notification::ORDER || $no->type == Notification::STATUS){
                $arr[$key]['type'] = 'order';
            }elseif ($no->type == Notification::OFFER){
                $arr[$key]['type'] = 'offer';
            }else{
                $arr[$key]['type'] = 'system';
            }
        }
        foreach ($user2 as $key => $no) {
            $arr[$key]['title_en'] = $no->data['title_en'];
            $arr[$key]['title_ar'] = $no->data['title_ar'];
            $arr[$key]['date'] = $no->created_at;
            $arr[$key]['link'] = route('notfy_single', $no->id);
            if ($no->type == Notification::MESSAGE || $no->type == Notification::REPLY) {
                $id = $no->data['id'];
                $conversation = Message::find($id)->conversation()->get();
                $arr[$key]['type'] = 'message';
                $arr[$key]['data']['data'] = ConversationResource::collection($conversation);
            }elseif ($no->type == Notification::ORDER || $no->type == Notification::STATUS){
                $arr[$key]['type'] = 'order';
            }elseif ($no->type == Notification::OFFER){
                $arr[$key]['type'] = 'offer';
            }else{
                $arr[$key]['type'] = 'system';
            }
        }
        return $this->sendResponse($arr, 'this is all notofication');
    }

    public function get_noification_single($id)
    {
        $link = array();
        $userUnreadNotification = auth('api')->user()->unreadNotifications->where('id', $id)->first();
        if ($userUnreadNotification){
            $userUnreadNotification->markAsRead();
            $link = $userUnreadNotification->data['route_api'];
        }
        return $this->sendResponse($link, 'redirect to route');
    }

    public function get_phone_for_site()
    {
        return $this->sendResponse(GeneralSetting::first()->phone, 'whatsapp phone');
    }
}
