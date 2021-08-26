<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\BusinessSetting;
use App\Models\V3\Currency;

class SettingsResource extends JsonResource
{
    public function toArray($request)
    {
        $currancy = BusinessSetting::where('type', 'system_default_currency')->first();
        $currancy = Currency::findOrFail($currancy->value);
        return [
            'name' => $this->name,
            'logo' => $this->logo,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'google_plus' => $this->google_plus,
            'currency' => [
                'name' => $currancy->name,
                'symbol' => $currancy->symbol,
                'exchange_rate' => (double) $this->exchangeRate($currancy),
                'code' => $currancy->code
            ],
            'currency_format' => $this->currency_format
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    public function exchangeRate($currency){
        $base_currency = Currency::find(BusinessSetting::where('type', 'system_default_currency')->first()->value);
        return $currency->exchange_rate/$base_currency->exchange_rate;
    }
}
