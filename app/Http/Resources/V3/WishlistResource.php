<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (integer) $this->id,
            'product' => [
                'id'=>$this->product->id,
                'name_ar' => $this->product->name_ar,
                'name_en' => $this->product->name,
                'thumbnail_image' => api_asset($this->product->thumbnail_img),
                'base_price' => (double) $this->product->unit_price,
                'base_discounted_price' => (double) getPrice($this->product),
                'unit' => $this->product->unit,
                'rating' => (double) $this->product->rating,
                'links' => [
                    'details' => route('products.show', $this->product->id),
                    'reviews' => route('api.reviews.index', $this->product->id),
                    'related' => route('products.related', $this->product->id),
                    'top_from_seller' => route('products.topFromSeller', $this->product->id)
                ]
            ]
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
