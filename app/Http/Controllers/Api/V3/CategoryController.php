<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\CategoryResource;
use App\Models\V3\BusinessSetting;
use App\Models\V3\Category;
use App\Http\Controllers\Api\BaseController as BaseController;

class CategoryController extends BaseController
{
    public function index()
    {
        $cat['data'] = CategoryResource::collection(Category::where('level', 0)->get());
        return $this->sendResponse($cat, translate('categories'));
    }

    public function featured()
    {
        $cat['data'] = CategoryResource::collection(Category::where('level', 0)->get());
        return $this->sendResponse($cat, translate('featured categories'));
    }

    public function home()
    {
        $homepageCategories = BusinessSetting::where('type', 'home_categories')->first();
        $homepageCategories = json_decode($homepageCategories->value);
        $cat['data'] = CategoryResource::collection(Category::whereIn('id', $homepageCategories)->get());
        return $this->sendResponse($cat, translate('home categories'));
    }
}
