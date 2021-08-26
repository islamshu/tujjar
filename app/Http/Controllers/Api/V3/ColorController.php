<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\ColorResource;
use App\Models\V3\Color;
use App\Models\V3\City2 as City;
use App\Http\Controllers\Api\BaseController as BaseController;

class ColorController extends BaseController
{
    public function index()
    {
        $data['data'] = ColorResource::collection(Color::all());
        return $data;
    }

    public function get_governorate()
    {
        return $this->sendResponse(City::where('parent_id', 0)->get(), translate('this is all governorates  .'));
    }

    public function get_states($id)
    {
        return $this->sendResponse(City::where('parent_id', $id)->get(), translate('this is all governorates  .'));
    }
}
