<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City2 extends Model
{
    protected $table = 'citys';

    public function name()
    {
        $lang = Session()->get('locale');
        $dir = Language::where('code', $lang)->first()->rtl;
        if ($dir == 1)
            return $this->name;
        else
            return $this->name_en;
    }

    public function longName()
    {
        $lang = Session()->get('locale');
        $dir = Language::where('code', $lang)->first()->rtl;
        if ($dir == 1)
            return "محافظة -" . $this->name;
        else
            return "Governorate -" . $this->name_en;
    }

    protected $hidden = [
        'parent_id'
    ];
}
