<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ShopCollection;
use App\Http\Resources\ConversationCollection;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Conversation;
use App\BusinessSetting;
use App\Message;
use Auth;
// use App\Product;
use Mail;
use App\Mail\ConversationMailManager;
use App\Http\Controllers\Api\BaseController as BaseController;

class ConversationController extends BaseController
{
    public function create(Request $request)
  {
    
      if(!Auth::user()){
             return $this->sendError('error','Please login first')   ;
      }
      $proo = Product::find($request->product_id);
      if(!$proo){
                      return $this->sendError('error','Error ocer')   ;

      }
        $user_type = Product::findOrFail($request->product_id)->user->user_type;
        // dd($user_type);

        $conversation = new Conversation;
        $conversation->sender_id = Auth::user()->id;
        $conversation->receiver_id = Product::findOrFail($request->product_id)->user->id;
        $conversation->title = $request->title;

        if($conversation->save()) {
            $message = new Message;
            $message->conversation_id = $conversation->id;
            $message->user_id = Auth::user()->id;
            $message->message = $request->message;

            if ($message->save()) {
                $this->send_message_to_seller($conversation, $message, $user_type);
            }
        }
         return $this->sendResponse($message,'Message has been send to seller');

     
    }
       public function send_message_to_seller($conversation, $message, $user_type)
    {
        $array['view'] = 'emails.conversation';
        $array['subject'] = 'Sender:- '.Auth::user()->name;
        $array['from'] = env('MAIL_USERNAME');
        $array['content'] = 'Hi! You recieved a message from '.Auth::user()->name.'.';
        $array['sender'] = Auth::user()->name;

        if($user_type == 'admin') {
            $array['link'] = route('conversations.admin_show', encrypt($conversation->id));
        } else {
            $array['link'] = route('conversations.show', encrypt($conversation->id));
        }

        $array['details'] = $message->message;

        try {
            Mail::to($conversation->receiver->email)->queue(new ConversationMailManager($array));
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }
    }
    public function get_meesage(){
         $message=  new ConversationCollection(Conversation::where('sender_id', auth('api')->id())->orWhere('receiver_id',auth('api')->id())->latest()->paginate(10));
         return $this->sendResponse($message,'this is all message');
    }
      public function replay(Request $request)
    {
        $message = new Message;
        $message->conversation_id = $request->conversation_id;
        $message->user_id = auth('api')->id();
        $message->message = $request->message;
        $message->save();
        $conversation = $message->conversation;
        if ($conversation->sender_id == Auth::user()->id) {
            $conversation->receiver_viewed ="1";
        }
        elseif($conversation->receiver_id == Auth::user()->id) {
            $conversation->sender_viewed ="1";
        }
        $conversation->save();
        
        return $this->sendResponse($message,'this is all message');

    }
}

