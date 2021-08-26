<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryCollection;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Http\Controllers\Api\BaseController as BaseController;

class CategoryController extends BaseController
{
    public function index()
    {
       $cat= new CategoryCollection(Category::where('level', 0)->get());
        return $this->sendResponse($cat,translate( 'categories'));
    }

    public function featured()
    {
        $cat=new CategoryCollection(Category::where('level', 0)->get());
         return $this->sendResponse($cat,translate( 'featured categories'));
    }

    public function home()
    {
        $homepageCategories = BusinessSetting::where('type', 'home_categories')->first();
        $homepageCategories = json_decode($homepageCategories->value);
        $cat= new CategoryCollection(Category::whereIn('id', $homepageCategories)->get());
         return $this->sendResponse($cat,translate( 'home categories'));
    }
}
