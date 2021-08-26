<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $fillable = ['email', 'token','code'];
}
