<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ClubPointCollection;
use Illuminate\Http\Request;
use App\BusinessSetting;
use App\ClubPointDetail;
use App\ClubPoint;
use App\Product;
use App\Wallet;
use App\Order;
use Auth;
use App\Http\Controllers\Api\BaseController as BaseController;

class ClubPointController extends BaseController
{

    public function index()
    {
       $cat= new ClubPointCollection(ClubPoint::where('user_id', Auth::id())->get());
        return $this->sendResponse($cat,'user point');

    }
     public function convert_point_into_wallet_id($club_id)
    {
        $club_point_convert_rate = BusinessSetting::where('type', 'club_point_convert_rate')->first()->value;

        $club_point = ClubPoint::findOrFail($club_id);
        
        $wallet = new Wallet;
        $wallet->user_id = Auth::id();
        $wallet->amount = floatval($club_point->points / $club_point_convert_rate);
        $wallet->payment_method = 'Club Point Convert';
        $wallet->payment_details = 'Club Point Convert';
        $wallet->save();
        $user = Auth::user();
        $user->balance = $user->balance + floatval($club_point->points / $club_point_convert_rate);
        $user->save();
        $club_point->convert_status = 1;
        if ($club_point->save()) {
                 return $this->sendResponse($club_point,'sucssefly converted');
        }
        else {
            return $this->sendErorr('error','error occer');
        }
    }


}
