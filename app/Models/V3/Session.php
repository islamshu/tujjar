<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
       public $timestamp = false;

   protected $table = 'sessions';
   protected $guarded =[];
}
