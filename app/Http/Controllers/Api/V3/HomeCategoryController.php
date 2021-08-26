<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V3\HomeCategoryResource;
use App\Models\V3\HomeCategory;

class HomeCategoryController extends Controller
{
    public function index()
    {
        $data['data'] = HomeCategoryResource::collection(HomeCategory::all());
        return $data;
    }
}
