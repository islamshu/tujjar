<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
       public $timestamp = false;

   protected $table = 'sessions';
   protected $guarded =[];
}
