<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\WalletCollection;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\ThawaniController;
use App\Session;
use App\Http\Controllers\Api\BaseController as BaseController;

class WalletController extends BaseController
{
    public function balance()
    {
        $id = auth('api')->id();
        $user = User::find($id);
        return $this->sendResponse(single_price_api($user->balance), translate('This is User balance.'));
    }

    public function walletRechargeHistory()
    {
        return new WalletCollection(Wallet::where('user_id', auth('api')->id())->latest()->get());
    }

    public function processPayment(Request $request)
    {
        $order = new OrderController;
        $user = User::find($request->user_id);
        if ($user->balance >= $request->grand_total) {
            $user->balance -= $request->grand_total;
            $user->save();
            return $order->processOrder($request);
        } else
            return response()->json(['success' => false, 'message' => 'The order was not completed becuase the paymeent is invalid']);
    }

    public function recharge(Request $request)
    {
        $data['amount'] = $request->amount;
        $data['payment_method'] = $request->payment_option;
        $session = Session::where('user_id', auth('api')->id())->first();
        $session->amount_wallet = json_encode($data);
        $session->save();
        if ($request->payment_option == 'thawani') {
            $thawani = new ThawaniController;
            return $thawani->getCheckout();
        }
    }
}
