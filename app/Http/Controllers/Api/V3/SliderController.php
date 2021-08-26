<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\SliderResource;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\V3\Upload;

class SliderController extends BaseController
{
    public function index()
    {
        $data = array();
        $slider = get_setting('home_slider_images');
        if ($slider) {
            $slider = json_decode($slider, true);
            $slider = Upload::whereIn('id',$slider )->get();
            $data['sliders'] = SliderResource::collection($slider);
        }
        return $this->sendResponse($data,translate('this is all sliders'));
    }
}
