<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V3\CategoryResource;
use App\Models\V3\Category;

class SubCategoryController extends Controller
{
    public function index($id)
    {
        $data['data'] = CategoryResource::collection(Category::where('parent_id', $id)->get());
        return $data;
    }
}
