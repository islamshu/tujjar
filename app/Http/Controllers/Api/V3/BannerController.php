<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\BannerResource;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\V3\Upload;

class BannerController extends BaseController
{
    public function index()
    {
        $banners['data'] = get_setting('home_banner1_images');
        if ($banners['data'] != "" && $banners['data'] != "[]") {
            $banners['data'] = json_decode(get_setting('home_banner1_images'), true);
            if ($banners['data']) {
                $banners['data'] = Upload::whereIn('id',$banners['data'])->get();
                if ($banners['data']) {
                    $banners['data'] = BannerResource::collection($banners['data']);
                }
            }
        }
        return $this->sendResponse($banners,translate( 'this is all banner'));
    }
}
