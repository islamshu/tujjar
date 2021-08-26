<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\SettingsResource;
use App\Models\V3\AppSettings;

class SettingsController extends Controller
{
    public function index()
    {
        $data['data'] = SettingsResource::collection(AppSettings::all());
        return $data;
    }
}
