<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Resources\V3\BrandResource;
use App\Models\V3\Brand;

class BrandController extends BaseController
{
    public function index()
    {
        $brands['data'] = BrandResource::collection(Brand::all());
        return $this->sendResponse($brands, translate('brands'));
    }

    public function top()
    {
        $brands['data'] = BrandResource::collection(Brand::where('top', 1)->get());
        return $this->sendResponse($brands, translate('top brands'));
    }
}
