<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseHistoryDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'product' => $this->product->name,
            'variation' => $this->variation,
            'price' => $this->price,
            'tax' => $this->tax,
            'shipping_cost' => $this->shipping_cost,
            'coupon_discount' => $this->coupon_discount,
            'quantity' => $this->quantity,
            'payment_status' => $this->payment_status,
            'delivery_status' => $this->delivery_status
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
