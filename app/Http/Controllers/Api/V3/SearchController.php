<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\ProductResource;
use App\Http\Resources\V3\PakegeResource;
use Illuminate\Http\Request;
use App\Models\V3\Product;
use DB;
use App\Models\V3\Attribute;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\V3\Brand;
use App\Models\V3\Vendorpackege;

class SearchController extends BaseController
{
    public function test_cokkies()
    {
        setCookie::queue("user", "value", 15);
        return $this->sendResponse(request()->cookie('user'), translate('this is all products  .'));
    }

    public function test_cokkies2()
    {
        return $this->sendResponse(request()->cookie('asss'), translate('this is all products  .'));
    }

    public function vendor_bakege()
    {
        $product['data'] = PakegeResource::collection(Vendorpackege::get());
        return $this->sendResponse($product, translate('this is all pakeges  .'));
    }

    public function pages($page)
    {
        $langs = Request()->header('lang');
        if ($page == 'terms') {
            if ($langs == 'en') {
                //   dd($langs);
                $terms = DB::table('page_translations')->where('page_id', 5)->where('lang', 'en')->first()->content;
                $te = strip_tags($terms);
                return $this->sendResponse($te, translate('this is terms page  .'));
            } else {
                $terms = DB::table('page_translations')->where('page_id', 5)->where('lang', 'sa')->first()->content;
                $te = strip_tags($terms);
                return $this->sendResponse($te, translate('this is terms page  .'));
            }
        } elseif ($page == 'return') {
            if ($langs == 'en') {
                $reutrn = DB::table('page_translations')->where('page_id', 3)->where('lang', 'en')->first()->content;
                $tee = strip_tags($reutrn);
                return $this->sendResponse($tee, translate('this is return page  .'));
            } else {
                $reutrn = DB::table('page_translations')->where('page_id', 3)->where('lang', 'sa')->first()->content;
                $tee = strip_tags($reutrn);
                return $this->sendResponse($tee, translate('this is return page  .'));
            }
        }
    }

    public function fillter_search(Request $request)
    {
        $products = Product::query();
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $key = $request->name;
        $products->when($key, function ($q) use ($key) {
            return $q->where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%");
        });
        $products->when($request->brand_id, function ($q) use ($request) {
            return $q->where('brand_id', '=', $request->brand_id);
        });
        $products->when($request->color, function ($q) use ($request) {
            return $q->where('colors', 'like', '%' . $request->color . '%');
        });
        $products->when($request->size, function ($q) use ($request) {
            return $q->where('attributes', 'like', '%' . '1' . '%')->where('choice_options', 'like', '%' . $request->size . '%');
        });
        if ($min_price != null && $max_price == null)
            $products = $products->where('unit_price', '>=', $min_price);
        if ($min_price != null && $max_price != null)
            $products = $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
        if ($min_price == null && $max_price != null)
            $products = $products->where('unit_price', '<=', $max_price);
        $product['data'] = ProductResource::collection($products->get());
        return $this->sendResponse($product, translate('this is all products  .'));
    }

    public function index($att)
    {
        $conditions = ['published' => 1];
        $products = Product::where($conditions);
        $non_paginate_products = filter_products($products)->get();
        switch ($att) {
            case 'colors':
                $all_colors = array();
                foreach ($non_paginate_products as $key => $product) {
                    if ($product->colors != null) {
                        foreach (json_decode($product->colors) as $key => $color) {
                            if (!in_array($color, $all_colors))
                                array_push($all_colors, $color);
                        }
                    }
                }
                return $this->sendResponse($all_colors, translate('this is all colors for product .'));
            case 'attributes':
                $attributes = array();
                foreach ($non_paginate_products as $key => $product) {
                    if ($product->attributes != null && is_array(json_decode($product->attributes))) {
                        foreach (json_decode($product->attributes) as $key => $value) {
                            $flag = false;
                            $pos = 0;
                            foreach ($attributes as $key => $attribute) {
                                if ($attribute['id'] == $value) {
                                    $flag = true;
                                    $pos = $key;
                                    break;
                                }
                            }
                            if (!$flag) {
                                $item['id'] = $value;
                                $item['name'] = Attribute::find($value)->name;
                                $item['values'] = array();
                                foreach (json_decode($product->choice_options) as $key => $choice_option) {
                                    if ($choice_option->attribute_id == $value) {
                                        $item['values'] = $choice_option->values;
                                        break;
                                    }
                                }
                                array_push($attributes, $item);
                            } else {
                                foreach (json_decode($product->choice_options) as $key => $choice_option) {
                                    if ($choice_option->attribute_id == $value) {
                                        foreach ($choice_option->values as $key => $value) {
                                            if (!in_array($value, $attributes[$pos]['values']))
                                                array_push($attributes[$pos]['values'], $value);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                return $this->sendResponse($attributes, translate('this is all attributes for product .'));
            case 'brands':
                $brands = array();
                foreach ($non_paginate_products as $key => $product) {
                    if ($product->brand_id != null)
                        array_push($brands, $product->brand_id);
                }
                $dd = collect($brands)->unique();
                $br = array();
                foreach ($dd as $bra) {
                    if ($bra = Brand::find($bra)) {
                        $item['id'] = $bra->id;
                        $item['name_ar'] = $bra->name;
                        $item['name_en'] = $bra->name_en;
                        $item['logo'] = api_asset($bra->logo);
                        array_push($br, $item);
                    }
                }
                return $this->sendResponse($br, translate('this is all brands for product .'));
                break;
        }
    }

    public function get_all_size()
    {
        return $this->sendResponse(DB::table('size_att')->get(), translate('this is all size .'));
    }

    public function get_all_fabrics()
    {
        return $this->sendResponse(DB::table('fabrics')->get(), translate('this is all fabric .'));
    }

    public function get_all_colors()
    {
        return $this->sendResponse(DB::table('colors')->get(), translate('this is all colors .'));
    }

    public function get_all_langs()
    {
        return $this->sendResponse(DB::table('languages')->get(), translate('this is all languages .'));
    }
}


   
 