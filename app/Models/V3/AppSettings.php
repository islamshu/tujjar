<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class AppSettings extends Model
{
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
