<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class ClubPoint extends Model
{
    public function user(){
    	return $this->belongsTo(user::class);
    }
}
