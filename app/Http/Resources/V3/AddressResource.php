<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\City2;

class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        $governorate = City2::find($this->city);
        $state = City2::find($this->postal_code);
        return [
            'id'      => $this->id,
            'user_id' => $this->user_id,
            'address' => $this->address,
            'country' => $this->country,
            'governorate_ar' => $governorate->name,
            'governorate_en' => $governorate->name_en,
            'state_ar' =>  $state->name,
            'state_en' =>  $state->name_en,
            'phone' => $this->phone,
            'set_default' => $this->set_default
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
