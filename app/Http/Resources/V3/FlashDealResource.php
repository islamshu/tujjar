<?php

namespace App\Http\Resources\V3;

use App\Models\V3\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V3\ProductResource;

class FlashDealResource extends JsonResource
{
    public function toArray($request)
    {
        $products = collect();
        foreach ($this->flashDealProducts as $key => $flash_deal_product) {
            if(Product::find($flash_deal_product->product_id) != null){
                $products->push(Product::find($flash_deal_product->product_id));
            }
        }
        $arr = [
            'id' => $this->id,
            'title' => $this->title,
            'end_date' => $this->end_date,
        ];
        $arr['products']['data'] = ProductResource::collection($products);
        return $arr;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
