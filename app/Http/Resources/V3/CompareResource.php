<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\Product;

class CompareResource extends JsonResource
{
    public function toArray($request)
    {
        $product = Product::find($this->product_id);
        $dataa = [
            'id' => $this->id,
            'product' => [
                'name_ar' => @$product->name_ar,
                'name_en' => @$product->name,
                'thumbnail_image' => api_asset($product->thumbnail_img),
                'base_price' => single_price_api((double)$this->unit_price),
                'base_discounted_price' => single_price_api((double)getPrice($this)),
                'brand' => @$product->name,
            ],
            'links' => [
                'details' => route('products.show', $this->product_id),
                'delete' => route('api.delete_compare', $this->id)
            ]
        ];
        $dataa['reset'] = route('api.reset_compare');
        return $dataa;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
