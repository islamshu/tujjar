<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class BannerResource extends JsonResource
{
    public function toArray($request)
    {
        $url = URL::to('/') .'/public/'.$this->file_name;
        return [
            'id' => $this->id,
            'photo' => $url,
            'url' => route('home'),
            'position' => 1
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
