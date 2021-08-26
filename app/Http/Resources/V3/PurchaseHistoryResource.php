<?php

namespace App\Http\Resources\V3;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'user' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => $this->user->avatar,
                'avatar_original' => api_asset($this->user->avatar_original)
            ],
            'shipping_address' => json_decode($this->shipping_address),
            'payment_type' => str_replace('_', ' ', $this->payment_type),
            'payment_status' => $this->payment_status,
            'grand_total' => (double) $this->grand_total,
            'coupon_discount' => (double) $this->coupon_discount,
            'shipping_cost' => (double) $this->orderDetails->sum('shipping_cost'),
            'subtotal' => (double) $this->orderDetails->sum('price'),
            'tax' => (double) $this->orderDetails->sum('tax'),
            'date' => Carbon::createFromTimestamp($this->date)->format('d-m-Y'),
            'links' => [
                'details' => route('purchaseHistory.details', $this->id)
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
}
