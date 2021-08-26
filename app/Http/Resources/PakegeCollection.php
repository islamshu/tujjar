<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Message;
class PakegeCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'      => $data->id,
                    'title' => $data->title,
                    'title_en' => $data->title_en,
                    'image' =>api_asset($data->image) ,
                    'price' => single_price_api($data->price),
                    'description_ar' => strip_tags($data->description),
                    'description_en' =>strip_tags( $data->dec_en),
                   
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
