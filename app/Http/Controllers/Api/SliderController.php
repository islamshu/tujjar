<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SliderCollection;
use App\Http\Controllers\Api\BaseController as BaseController;

class SliderController extends BaseController
{
    public function index()
    {
        $slider = new SliderCollection(json_decode(get_setting('home_slider_images'), true));
        return $this->sendResponse($slider,translate('this is all sliders'));
    }
}
