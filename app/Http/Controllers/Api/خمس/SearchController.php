<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCollection;
use Illuminate\Http\Request;
use App\BusinessSetting;
use App\Product;
use Auth;
use DB;
use App\Attribute;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Brand;
use Illuminate\Support\Facades\Cookie;

class SearchController extends BaseController
{
    public function test_cokkies(){
     $cookie_name = "user";
 $cookie_value = "value";
 $minutes=15;
    setCookie::queue($cookie_name, $cookie_value, $minutes);
//          $cookie_name1 = "asss";
//  $cookie_value1 = "asss";
//  $minutes=15;
//     Cookie::queue($cookie_name1, $cookie_value1, $minutes);


$aa=    request()->cookie('user');

                          return $this->sendResponse($aa , 'this is all products  .');

    }
    public function test_cokkies2(){
$aa=    request()->cookie('asss');

                          return $this->sendResponse($aa , 'this is all products  .');

 
        
    }
    public function fillter_search(Request $request){
                $conditions = ['published' => 1];
            $products = Product::query();
    
                $products->when($request->brand_id, function ($q) use ($request) {
                    
                    return $q->where('brand_id', '=', $request->brand_id);
                  
                });
               
                $products->when($request->color, function ($q) use ($request) {
                    return $q->where('colors', 'like', '%' . $request->color . '%');
                });
                
                $products->when($request->attriute_id, function ($q) use ($request) {
                                        return $q->where('attributes', 'like', '%' . $request->attriute_id . '%');

                      });
                      $product = new ProductCollection($products->get());
                         return $this->sendResponse($product , 'this is all products  .');

                    
       
    
    }
    public function index($att){
        // dd($att);
            $conditions = ['published' => 1];
            $products = Product::where($conditions);
            $non_paginate_products = filter_products($products)->get();
         
        switch ($att) {
        case 'colors':
             $all_colors = array();

        foreach ($non_paginate_products as $key => $product) {
            if ($product->colors != null) {
                foreach (json_decode($product->colors) as $key => $color) {
                    if(!in_array($color, $all_colors)){
                        array_push($all_colors, $color);
                       
                    }
                }
            }
        }
                return $this->sendResponse($all_colors , 'this is all colors for product .');

        // return $all_colors;
        case 'attributes':
        $attributes = array();
        foreach ($non_paginate_products as $key => $product) {
            if($product->attributes != null && is_array(json_decode($product->attributes))){
                foreach (json_decode($product->attributes) as $key => $value) {
                    $flag = false;
                    $pos = 0;
                    foreach ($attributes as $key => $attribute) {
                        if($attribute['id'] == $value){
                            $flag = true;
                            $pos = $key;
                            break;
                        }
                    }
                    if(!$flag){
                        $item['id'] = $value;
                        $item['name']=Attribute::find($value)->name;
                        $item['values'] = array();
                        foreach (json_decode($product->choice_options) as $key => $choice_option) {
                            if($choice_option->attribute_id == $value){
                                $item['values'] = $choice_option->values;
                                break;
                            }
                        }
                        array_push($attributes, $item);
                    }
                    else {
                        foreach (json_decode($product->choice_options) as $key => $choice_option) {
                            if($choice_option->attribute_id == $value){
                                foreach ($choice_option->values as $key => $value) {
                                    if(!in_array($value, $attributes[$pos]['values'])){
                                        array_push($attributes[$pos]['values'], $value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
            return $this->sendResponse($attributes , 'this is all attributes for product .');


            
            
            
        // return 'rrr';
 case 'brands':
             $brands = array();
             
 foreach ($non_paginate_products as $key => $product) {
            if ($product->brand_id != null) {
                
                
        

                 array_push($brands, $product->brand_id);

                }
       
            }
                               $dd= collect($brands)->unique();
                               $br=array();
                        foreach($dd as $bra){
                            
                            if($bra =Brand::find($bra)){
                                $item['id'] = $bra->id;
                                $item['name_ar'] = $bra->name;
                                $item['name_en'] = $bra->name_en;
                                $item['logo']= api_asset($bra->logo) ;

                                // dd($item);
                                
                               array_push($br, $item);

                            }
                        }
            return $this->sendResponse($br , 'this is all brands for product .');
            break;
        }
        
    }
    public function get_all_size(){
      $size=  DB::table('size_att')->get();
                  return $this->sendResponse($size , 'this is all size .');

    }
      public function get_all_colors(){
      $colors=  DB::table('colors')->get();
                  return $this->sendResponse($colors , 'this is all colors .');
    }
         public function get_all_langs(){
      $colors=  DB::table('languages')->get();
                  return $this->sendResponse($colors , 'this is all languages .');
    }
    
}


   
 