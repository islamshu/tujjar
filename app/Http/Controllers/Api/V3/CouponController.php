<?php

namespace App\Http\Controllers\Api\V3;

use App\Models\V3\BusinessSetting;
use App\Models\V3\Currency;
use App\Http\Controllers\Api\Controller;
use App\Models\V3\Cart;
use App\Models\V3\Coupon;
use App\Models\V3\CouponUsage;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();
        if ($coupon != null && strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date && CouponUsage::where('user_id', $request->user_id)->where('coupon_id', $coupon->id)->first() == null) {
            $couponDetails = json_decode($coupon->details);
            if ($coupon->type == 'cart_base') {
                $sum = 0;
                if (auth('api')->check())
                    $carts = Cart::where('owner_id', $request->store_id)->where('user_id', auth('api')->id())->get();
                else
                    $carts = Cart::where('owner_id', $request->store_id)->where('cokkeies', request()->cookie('cookes'))->get();
                $shipping = BusinessSetting::where('type', 'shipping_cost')->first()->value;
                foreach ($carts as $cart){
                    $sum += (($cart->price+$cart->tax) * $cart->quantity)+$shipping;
                }
                if ($sum > $couponDetails->min_buy) {
                    if ($coupon->discount_type == 'percent') {
                        $couponDiscount =  ($sum * $coupon->discount) / 100;
                        if ($couponDiscount > $couponDetails->max_discount)
                            $couponDiscount = $couponDetails->max_discount;
                    } elseif ($coupon->discount_type == 'amount')
                        $couponDiscount = $coupon->discount;
                    if ($this->isCouponAlreadyApplied($request->user_id, $coupon->id))
                        return response()->json(['success' => false, 'message' => 'The coupon is already applied. Please try another coupon']);
                    else {
                        $code = Currency::findOrFail(BusinessSetting::where('type', 'system_default_currency')->first()->value)->code;
                        $header = request()->header('currency');
                        if($header != null)
                            $currency = Currency::where('code', $header)->first();
                        else
                            $currency = Currency::where('code', $code)->first();
                        $data['discount'] = single_price_api((double) $couponDiscount);
                        $data['total'] = single_price_api((double) ($sum - $couponDiscount) . $currency->symbol);
                        return response()->json(['success' => true, 'data' => $data]);
                    }
                }else
                    return response()->json(['success' => false, 'message' => 'قيمة المشتريات اقل من الحد الادني للاستخدام الكوبون']);
            } elseif ($coupon->type == 'product_base') {
                $couponDiscount = 0;
                $cartItems = Cart::where('user_id', $request->user_id)->get();
                foreach ($cartItems as $key => $cartItem) {
                    foreach ($couponDetails as $key => $couponDetail) {
                        if ($couponDetail->product_id == $cartItem->product_id) {
                            if ($coupon->discount_type == 'percent')
                                $couponDiscount += $cartItem->price * $coupon->discount / 100;
                            elseif ($coupon->discount_type == 'amount')
                                $couponDiscount += $coupon->discount;
                        }
                    }
                }
                if ($this->isCouponAlreadyApplied($request->user_id, $coupon->id))
                    return response()->json(['success' => false, 'message' => 'The coupon is already applied. Please try another coupon']);
                else
                    return response()->json(['success' => true, 'discount' => (double) $couponDiscount, 'message' => 'Coupon code applied successfully']);
            }
        } else
            return response()->json(['success' => false, 'message' => 'The coupon is invalid']);
    }

    protected function isCouponAlreadyApplied($userId, $couponId) {
        return CouponUsage::where(['user_id' => $userId, 'coupon_id' => $couponId])->count() > 0;
    }
}
