<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\ConversationResource;
use App\Models\V3\Product;
use App\Notifications\V3\ReplyNofication;
use Illuminate\Http\Request;
use App\Models\V3\Conversation;
use App\Models\V3\Message;
use Mail;
use App\Models\V3\User;
use App\Mail\ConversationMailManager;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Notifications\V3\MessageNofication;

class ConversationController extends BaseController
{
    public function create(Request $request)
    {
        if (!auth('api')->user())
            return $this->sendError(translate('Please login first'));
        $proo = Product::find($request->product_id);
        if (!$proo)
            return $this->sendError(translate('Error ocer'));
        $user_type = Product::findOrFail($request->product_id)->user->user_type;
        $conversation = new Conversation;
        $conversation->sender_id = auth('api')->user()->id;
        $conversation->receiver_id = Product::findOrFail($request->product_id)->user->id;
        $conversation->title = $request->title;
        if ($conversation->save()) {
            $message = new Message;
            $message->conversation_id = $conversation->id;
            $message->user_id = auth('api')->user()->id;
            $message->message = $request->message;
            if ($message->save()) {
                $user = User::find($message->conversation->receiver_id);
                $user->notify(new MessageNofication($message));
                $token = @$user->fcm_token;
                if ($token) {
                    $this->noti('وصلتك رسالة جديدة','وصلتك رسالة جديدة',$token);
                }
                $this->send_message_to_seller($conversation, $message, $user_type);
            }
        }
        return $this->sendResponse($message, translate('Message has been send to seller'));
    }

    public function message_id($id)
    {
        return $this->sendResponse(Message::find($id), translate('Message '));
    }

    public function send_message_to_seller($conversation, $message, $user_type)
    {
        $array['view'] = 'emails.conversation';
        $array['subject'] = 'Sender:- ' . auth('api')->user()->name;
        $array['from'] = env('MAIL_USERNAME');
        $array['content'] = 'Hi! You recieved a message from ' . auth('api')->user()->name . '.';
        $array['sender'] = auth('api')->user()->name;
        if ($user_type == 'admin')
            $array['link'] = route('conversations.admin_show', encrypt($conversation->id));
        else
            $array['link'] = route('conversations.show', encrypt($conversation->id));
        $array['details'] = $message->message;
        try {
            Mail::to($conversation->receiver->email)->queue(new ConversationMailManager($array));
        } catch (\Exception $e) {}
    }

    public function get_meesage()
    {
        $message['data'] = ConversationResource::collection(Conversation::where('sender_id', auth('api')->id())
            ->orWhere('receiver_id', auth('api')->id())->with('messages')->latest()->paginate(10));
        return $this->sendResponse($message, translate('this is all message'));
    }

    public function replay(Request $request)
    {
        $message = new Message;
        $message->conversation_id = $request->conversation_id;
        $message->user_id = auth('api')->id();
        $message->message = $request->message;
        $message->save();
        $conversation = $message->conversation;
        if ($conversation->sender_id == auth('api')->user()->id)
            $conversation->receiver_viewed = "1";
        elseif ($conversation->receiver_id == auth('api')->user()->id)
            $conversation->sender_viewed = "1";
        if ($conversation->save()) {
            try {
                $user = User::find($message->conversation->sender_id);
                $user->notify(new ReplyNofication($message));
                $token = @$user->fcm_token;
                if ($token) {
                    $this->noti('تم الرد على رسالتك','تم الرد على رسالتك',$token);
                }
            } catch (\Exception $e) {}
        }
        return $this->sendResponse($message, translate('this is all message'));
    }
}
