<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\WalletResource;
use App\Models\V3\User;
use App\Models\V3\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\ThawaniController;
use App\Models\V3\Session;
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
        $data['data'] = WalletResource::collection(Wallet::where('user_id', auth('api')->id())->latest()->get());
        $data['status'] = true;
        return $data;
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
