<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\City2;

class ShopResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name_ar' => $this->name_ar,
            'name_en' => $this->name,

            'user' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => $this->user->avatar,
                'avatar_original' => $this->user->avatar_original
            ],
//                    'logo' => api_asset($this->logo),
            'logo' => uploaded_asset_nullable($this->logo),
            'sliders' => $this->convertPhotos(explode(',', $this->sliders)),
            'address_id'=>$this->address,
            'address_ar' => City2::find($this->address)->name,
            'address_en' => City2::find($this->address)->name_en,

            'facebook' => $this->facebook,
            'google' => $this->google,
            'twitter' => $this->twitter,
            'youtube' => $this->youtube,
            'instagram' => $this->instagram,
            'links' => [
                'featured' => route('shops.featuredProducts', $this->id),
                'top' => route('shops.topSellingProducts',  $this->id),
                'new' => route('shops.newProducts', $this->id),
                'all' => route('shops.allProducts', $this->id),
                'brands' => route('shops.brands', $this->id)
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

    protected function convertPhotos($data){
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, api_asset($item));
        }
        return $result;
    }
}
