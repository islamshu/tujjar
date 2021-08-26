<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V3\PurchaseHistoryResource;
use App\Models\V3\Order;

class PurchaseHistoryController extends Controller
{
    public function index($id)
    {
        $data['data'] = PurchaseHistoryResource::collection(Order::where('user_id', $id)->latest()->get());
        return $data;
    }
}
