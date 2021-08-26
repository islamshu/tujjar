<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'amount' => single_price_api($this->amount),
            'payment_method' => $this->payment_method,
            'approval' => $this->offline_payment ? ($this->approval == 1 ? "Approved" : "Decliend") : "N/A",
            'date' => $this->created_at,
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
