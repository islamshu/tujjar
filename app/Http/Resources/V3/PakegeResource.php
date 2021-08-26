<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class PakegeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'image' =>api_asset($this->image) ,
            'price' => single_price_api($this->price),
            'description_ar' => strip_tags($this->description),
            'description_en' =>strip_tags( $this->dec_en),
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
