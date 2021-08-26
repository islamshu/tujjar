<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V3\BusinessSettingResource;
use App\Models\V3\BusinessSetting;

class BusinessSettingController extends Controller
{
    public function index()
    {
        $data['data'] = BusinessSettingResource::collection(BusinessSetting::all());
        return $data;
    }
}
