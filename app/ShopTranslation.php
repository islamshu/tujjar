<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopTranslation extends Model
{
    protected $fillable = ['shop_id','name', 'lang' , 'address','meta_title','meta_description'];

}
