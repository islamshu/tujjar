<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name' => $this->name,
            'thumbnail_image' => api_asset($this->thumbnail_img),
            'base_price' => (double) $this->unit_price,
            'base_discounted_price' => (double) getPrice($this),
            'rating' => (double) $this->rating,
            'links' => [
                'details' => route('products.show', $this->id),
                'reviews' => route('api.reviews.index', $this->id),
                'related' => route('products.related', $this->id),
                'top_from_seller' => route('products.topFromSeller', $this->id)
            ]
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
