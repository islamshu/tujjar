<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AddressCollection;
use App\Address;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;

class AddressController extends BaseController
{
    public function addresses()
    {
        $id = Auth::id();
        $test= new AddressCollection(Address::where('user_id', $id)->get());
            return     $this->sendResponse($test , 'this is all addresses .');

    }

    public function createShippingAddress(Request $request)
    {
        $address = new Address;
        // if(auth('api')->check()){
            $user_id = auth('api')->user()->id;
        // }else{
        //               return     $this->sendError( 'you need to login');

        // }
        $address->user_id = $user_id;
        $address->address = $request->address;
        $address->country = $request->country;
        $address->city = $request->governorate_id;
        $address->postal_code = $request->state_id;
        $address->phone = $request->phone;
        $address->save();

            $test= new AddressCollection(Address::where('user_id',$user_id)->get());
            
            return     $this->sendResponse($test , 'this is all addresses .');

    
    }

    public function deleteShippingAddress($id)
    {
         if(auth('api')->check()){
            $user_id = auth('api')->user()->id;
        }else{
          return$this->sendError( 'you need to login');

        }
        $address = Address::findOrFail($id);
        $address->delete();
$test= new AddressCollection(Address::where('user_id', Auth::id())->get());
            return     $this->sendResponse($test , 'this is all addresses .');

       
    }
}
