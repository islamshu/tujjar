<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\AddressResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;

class AddressController extends BaseController
{
    public function addresses()
    {
        $addresses['data'] = auth('api')->user()->addresses;
        if ($addresses['data']) {
            $addresses['data'] = AddressResource::collection($addresses['data']);
        }
        return $this->sendResponse($addresses, translate('this is all addresses .'));
    }

    public function createShippingAddress(Request $request)
    {
        $user = auth('api')->user();
        $data['address'] = $request->address;
        $data['country'] = $request->country;
        $data['city'] = $request->governorate_id;
        $data['postal_code'] = $request->state_id;
        $data['phone'] = $request->phone;
        $address_default = $user->addresses()->where('set_default',1)->first();
        if ($address_default)
            $address_default->update(['set_default'=>0]);
        $data['set_default'] = 1;
        $user->addresses()->create($data);
        $addresses['data'] = AddressResource::collection($user->addresses);
        return $this->sendResponse($addresses, translate('this is all addresses .'));
    }

    public function updateShippingAddress(Request $request, $id)
    {
        $data = null;
        $user = auth('api')->user();
        $address = $user->addresses()->find($id);
        if (!$address)
            return $this->sendError('Address not found');
        if ($request->has('address'))
            $data['address'] = $request->address;
        if ($request->has('address'))
            $data['country'] = $request->country;
        if ($request->has('address'))
            $data['city'] = $request->governorate_id;
        if ($request->has('address'))
            $data['postal_code'] = $request->state_id;
        if ($request->has('address'))
            $data['phone'] = $request->phone;
        if ($data && $address)
            $address->update($data);
        $addresses['data'] = AddressResource::collection($user->addresses);
        return $this->sendResponse($addresses, translate('update success .'));
    }

    public function set_defulf_address($id)
    {
        $user = auth('api')->user();
        $address = $user->addresses()->find($id);
        if (!$address)
            return $this->sendError('Address not found');
        $address_default = $user->addresses()->where('set_default',1)->first();
        if ($address_default)
            $address_default->update(['set_default'=>0]);
        $address->update(['set_default'=>1]);
        $addresses['data'] = AddressResource::collection($user->addresses);
        return $this->sendResponse($addresses, translate('success .'));
    }

    public function deleteShippingAddress($id)
    {
        $user = auth('api')->user();
        $address = $user->addresses()->find($id);
        if (!$address)
            return $this->sendError('Address not found');
        $address->delete();
        $addresses['data'] = AddressResource::collection($user->addresses);
        return $this->sendResponse($addresses, translate('this is all addresses .'));
    }
}
