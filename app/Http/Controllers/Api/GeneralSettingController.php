<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ConversationCollection;
use App\Http\Resources\GeneralSettingCollection;
use App\Message;
use App\Models\GeneralSetting;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Notification;

class GeneralSettingController extends BaseController
{
    public function index()
    {
        return new GeneralSettingCollection(GeneralSetting::all());
    }

    public function get_noification()
    {
        $arr = array();
        foreach (auth('api')->user()->unreadNotifications as $key => $no) {
            $arr[$key]['title_en'] = $no->data['title_en'];
            $arr[$key]['title_ar'] = $no->data['title_ar'];
            $arr[$key]['date'] = $no->created_at;
            $arr[$key]['link'] = route('notfy_single', $no->id);
            if ($no->type == Notification::MESSAGE || $no->type == Notification::REPLY) {
                $id = $no->data['id'];
                $conversation = Message::find($id)->conversation()->get();
                $arr[$key]['type'] = 'message';
                $arr[$key]['data'] = new ConversationCollection($conversation);
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
        $userUnreadNotification = auth('api')->user()->unreadNotifications->where('id', $id)->first();
        if ($userUnreadNotification)
            $userUnreadNotification->markAsRead();
        $link = $userUnreadNotification->data['route_api'];
        return $this->sendResponse($link, 'redirect to route');
    }

    public function get_phone_for_site()
    {
        return $this->sendResponse(GeneralSetting::first()->phone, 'whatsapp phone');
    }
}
