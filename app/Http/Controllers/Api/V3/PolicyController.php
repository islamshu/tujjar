<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\PolicyResource;
use App\Models\V3\Page;

class PolicyController extends Controller
{
    public function sellerPolicy()
    {
        $data['data'] = PolicyResource::collection(Page::where('type', 'seller_policy_page')->get());
        return $data;
    }

    public function supportPolicy()
    {
        $data['data'] = PolicyResource::collection(Page::where('type', 'support_policy_page')->get());
        return $data;
    }

    public function returnPolicy()
    {
        $data['data'] = PolicyResource::collection(Page::where('type', 'return_policy_page')->get());
        return $data;
    }
}
