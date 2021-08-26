<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class GeneralSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'logo' => $this->logo,
            'site_name' => $this->site_name,
            'address' => $this->address,
            'description' => $this->description,
            'phone' => $this->phone,
            'email' => $this->email,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'google_plus' => $this->google_plus
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
