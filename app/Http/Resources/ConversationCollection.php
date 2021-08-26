<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Message;
use App\User;
class ConversationCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $user1 = User::find($data->sender_id);
                if($user1 != null){
                    $user = User::find($data->sender_id)->name;
                }else{
                    $user= null;
                }
                 $user2 = User::find($data->receiver_id);
                if($user2 != null){
                    $usere = User::find($data->receiver_id)->name;
                }else{
                    $usere= null;
                }

                return [
                    'id'      => $data->id,
                    'sender_id' => $data->sender_id,
                    'sender_name'=>$user,
                    'receiver_id' => $data->receiver_id,
                     'receive_name'=>$usere,
                    'title' => $data->title,
                    'message'=>Message::where('conversation_id',$data->id)->first()->message,
                    'replay'=>$this->get_message($data),
                    'time' => $data->created_at,
                ];
            })
        ];
    }
    public function get_message($data){
        $mm=Message::where('conversation_id',$data->id)->first()->id;

        $dataa=[];
        $dataa = $data->messages->where('id','!=',$mm);
        foreach( $data->messages->where('id','!=',$mm) as $k=>$v){
           $dataa[$k]['user_name']= User::find($v->user_id)->name;
        }
    return $dataa;
        

    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
