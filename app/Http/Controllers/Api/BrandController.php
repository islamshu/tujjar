<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BrandCollection;
use App\Models\Brand;
use App\Http\Controllers\Api\BaseController as BaseController;

class BrandController extends BaseController
{
    public function index()
    {
        $brands = new BrandCollection(Brand::all());
        return $this->sendResponse($brands, translate('brands'));
    }

    public function top()
    {
        $brands = new BrandCollection(Brand::where('top', 1)->get());
        return $this->sendResponse($brands, translate('top brands'));
    }
}
