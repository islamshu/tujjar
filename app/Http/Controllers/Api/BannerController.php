<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BannerCollection;
use App\Http\Controllers\Api\BaseController as BaseController;

class BannerController extends BaseController
{
    public function index()
    {
        $banners= new BannerCollection(json_decode(get_setting('home_banner1_images'), true));
        return $this->sendResponse($banners,translate( 'this is all banner'));
    }
}
