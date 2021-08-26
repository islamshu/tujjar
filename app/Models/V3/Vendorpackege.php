<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class Vendorpackege extends Model
{
    protected $hidden = ['description', 'dec_en'];
    protected $guarded = [];

    public function get_title()
    {
        $lang = Session()->get('locale');
        $dir = Language::where('code', $lang)->first()->rtl;
        if ($dir == 1) {
            return $this->title;
        } else {
            return $this->title_en;
        }
    }
}
