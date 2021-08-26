<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WishlistCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id' => (integer)$data->id,
                    'product' => [
                        'id' => $data->product->id,
                        'name_ar' => $data->product->name_ar,
                        'name_en' => $data->product->name,
                        'thumbnail_image' => api_asset($data->product->thumbnail_img),
                        'base_price' => (double)$data->product->unit_price,
                        'base_discounted_price' => (double)getPrice($data->product),
                        'unit' => $data->product->unit,
                        'rating' => (double)$data->product->rating,
                        'links' => [
                            'details' => route('products.show', $data->product->id),
                            'reviews' => route('api.reviews.index', $data->product->id),
                            'related' => route('products.related', $data->product->id),
                            'top_from_seller' => route('products.topFromSeller', $data->product->id)
                        ]
                    ]
                ];
            })
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
