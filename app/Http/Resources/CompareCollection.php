<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Product;
use App\Http\Resources\ProductCollection;

class CompareCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $dataa['data']= 
            $this->collection->map(function($data) {
                $product = Product::find($data->product_id);
                return [
                    'id' => $data->id,
                    'product' =>[
                        'name_ar' => $product->name_ar,
                        'name_en' => $product->name,
                        
                        'thumbnail_image' => api_asset($product->thumbnail_img),
                        'base_price' => single_price_api((double) $product->unit_price),
                        'base_discounted_price' => single_price_api((double) getPrice($product)),
                        'brand'=>$this->get_brand($data),
                        ],
                    'links' => [
                        'details' => route('products.show', $data->product_id),
                        'delete'=>route('api.delete_compare',$data->id)
                    ]
                ];
            });
        
        $dataa['reset']=route('api.reset_compare');
        return $dataa;
    }
    public function get_brand($data){
        // Product::find($data->product_id)->brand->name;
        $pp = Product::find($data->product_id)->brand;
        if($pp != null){
            $name = $pp->name;
        }else{
            $name=null;
        }
        return $name ;
    }
    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
