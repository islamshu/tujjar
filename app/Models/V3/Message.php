<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public function conversation(){
        return $this->belongsTo(Conversation::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
