<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V3\CurrencyResource;
use App\Models\V3\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        $data['data'] = CurrencyResource::collection(Currency::all());
        return $data;
    }
}
