<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\WalletCollection;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\ThawaniController;

use App\Session;
use App\Http\Controllers\Api\BaseController as BaseController;

class WalletController extends BaseController
{
    public function balance()
    {
        $id = auth('api')->id();
        $user = User::find($id);
        return $this->sendResponse($user->balance , 'This is User balance.');

        // return response()->json([
        //     'balance' => $user->balance
        // ]);
    }

    public function walletRechargeHistory()
    {
         $id = auth('api')->id();
        return new WalletCollection(Wallet::where('user_id', $id)->latest()->get());
    }

    public function processPayment(Request $request)
    {
        $order = new OrderController;
        $user = User::find($request->user_id);

        if ($user->balance >= $request->grand_total) {
            $user->balance -= $request->grand_total;
            $user->save();

            return $order->processOrder($request);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'The order was not completed becuase the paymeent is invalid'
            ]);
        }
    }
        public function recharge(Request $request)
    {
        $data['amount'] = $request->amount;
        $data['payment_method'] = $request->payment_option;
        // dd($data);
        $session = Session::where('user_id',auth('api')->id())->first();
        $session->amount_wallet = json_encode($data);
        $session->save();
        if ($request->payment_option == 'thawani') {
            $thawani = new ThawaniController;
            return $thawani->getCheckout();
        }
        
  

       
    }
}
