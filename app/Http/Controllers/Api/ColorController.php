<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ColorCollection;
use App\Models\Color;
use App\City2 as City;
use App\Http\Controllers\Api\BaseController as BaseController;

class ColorController extends BaseController
{
    public function index()
    {
        return new ColorCollection(Color::all());
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
