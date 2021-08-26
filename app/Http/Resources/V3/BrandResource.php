<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'logo' => api_asset($this->logo),
            'links' => [
                'products' => route('api.products.brand', $this->id)
            ]
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
