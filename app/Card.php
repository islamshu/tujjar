<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $table = 'cards';
    protected $fillable = ['user_id','logo','color','shop_name_ar','name','email','phone','address'];
    public function user()
    {
      return $this->belongsTo(User::class);
    }
}
