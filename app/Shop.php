<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'user_id');
    }

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? \App::getLocale() : $lang;
        $shop_translations = $this->hasMany(ShopTranslation::class)->where('lang', $lang)->first();

        return $shop_translations != null ? $shop_translations->$field : $this->$field;
    }

    public function shop_translations()
    {
        return $this->hasMany(ShopTranslation::class);
    }

    public function shop_name()
    {
        $lang = Session()->get('locale');
        $dir = Language::where('code', $lang)->first()->rtl;
        if ($dir == 1) {
            return $this->name_ar;

        } else {
            return $this->name;
        }

    }

    public function shop_address()
    {
        $lang = Session()->get('locale');
        $dir = Language::where('code', $lang)->first()->rtl;
        if ($dir == 1) {
            return $this->address_ar;

        } else {
            return $this->address;
        }

    }

    public function city()
    {
        return $this->belongsTo(City2::class,'address');
    }
}
