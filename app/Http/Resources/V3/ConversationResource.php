<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\Message;
use App\Models\V3\User;

class ConversationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'sender_name' => @$this->sender->name,
            'receiver_id' => $this->receiver_id,
            'receive_name' => @$this->receiver->name,
            'title' => $this->title,
            'message' => @$this->messages()->first()->message,
            'replay' => $this->get_message($this),
            'time' => $this->created_at,
        ];
    }

    public function get_message($data)
    {
        $message_id = Message::where('conversation_id', $data->id)->first()->id;
        $messages = $data->messages->where('id', '!=', $message_id);
        foreach ($messages as $k => $v) {
            $messages[$k]['user_name'] = User::find($v->user_id)->name;
        }
        return $messages;
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
