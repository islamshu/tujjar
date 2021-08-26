<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V3\PurchaseHistoryDetailResource;
use App\Models\V3\OrderDetail;

class PurchaseHistoryDetailController extends Controller
{
    public function index($id)
    {
        $data['data'] = PurchaseHistoryDetailResource::collection(OrderDetail::where('order_id', $id)->get());
        return $data;
    }
}
