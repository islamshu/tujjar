<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class City2 extends Model
{
    protected $table = 'citys';

    protected $hidden = ['parent_id'];
}
