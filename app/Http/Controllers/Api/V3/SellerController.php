<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\ShopResource;
use App\Http\Resources\V3\ProductResource;
use App\Models\V3\Category;
use App\Models\V3\Product;
use App\Models\V3\Shop;
use App\Models\V3\OrderDetail;
use App\Models\V3\BusinessSetting;
use App\Models\V3\Vendorpackege;
use App\Models\V3\Upload;
use App\Models\V3\User;
use DB;
use App\Models\V3\Colorcard;
use App\Models\V3\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\V3\Order;
use App\Models\V3\Payment;
use Validator;
use App\Models\V3\Card;
use App\SellerWithdrawRequest;
use App\Notifications\V3\OrderStatus;

class SellerController extends BaseController
{
    public function update_delivery_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->delivery_viewed = '0';
        $order->save();
        if ($order->user_id != null) {
            $user = User::find($order->user_id);
            $user->notify(new OrderStatus($order));
            $token = @$user->fcm_token;
            if ($token) {
                $this->noti('تم تغير حالة الطلب','تم تغير حالة الطلب',$token);
            }
        }
        if (auth('api')->user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', auth('api')->id()) as $key => $orderDetail) {
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();
            }
        }
        return $this->sendResponse('success', translate('order has been updated successfully'));
    }

    public function seller_withdrow(Request $request)
    {
        $seller_withdraw_request = new SellerWithdrawRequest;
        $seller_withdraw_request->user_id = auth('api')->user()->seller->id;
        $seller_withdraw_request->amount = $request->amount;
        $seller_withdraw_request->message = $request->message;
        $seller_withdraw_request->status = '0';
        $seller_withdraw_request->viewed = '0';
        if ($seller_withdraw_request->save())
            return $this->sendResponse($seller_withdraw_request, translate('Request has been sent successfully'));
        else
            return $this->sendError(translate('Error'));
    }

    public function Pending_Balance_for_seller()
    {
        return $this->sendResponse(single_price(auth('api')->user()->seller->admin_to_pay), translate('Pending Balance'));
    }

    public function seller_withdraw_requests()
    {
        $ree = SellerWithdrawRequest::where('user_id', auth('api')->user()->seller->id)->get();
        foreach ($ree as $key => $re) {
            if ($re->status == 1)
                $ree[$key]['status'] = 'Paid';
            else
                $ree[$key]['status'] = 'Pending';
        }
        return $this->sendResponse($ree, translate(' Seller Withdraw Request'));
    }

    public function payments()
    {
        $payments = Payment::where('seller_id', auth('api')->user()->seller->id)->get();
        return $this->sendResponse($payments, translate('payment'));
    }

    public function Seller_card(Request $request)
    {
        $user = auth('api')->id();
        if (Card::where('user_id', $user)->first()) {
            return $this->sendError(translate('Your request all ready sent'));
        }
        $validator = Validator::make($request->all(), [
            'logo' => 'required',
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'shop_name_ar' => 'required',
            'color' => 'required',
        ]);
        if ($validator->fails())
            return $this->sendError('Please verify all entered data', $validator->errors());
        $rquest_all = $request->all();
        $upload = new Upload;
        $upload->file_original_name = null;
        $arr = explode('.', $request->logo->getClientOriginalName());
        for ($i = 0; $i < count($arr) - 1; $i++) {
            if ($i == 0)
                $upload->file_original_name .= $arr[$i];
            else
                $upload->file_original_name .= "." . $arr[$i];
        }
        $upload->file_name = $request->logo->store('uploads/all');
        $upload->user_id = auth('api')->user()->id;
        $upload->extension = strtolower($request->logo->getClientOriginalExtension());
        if (isset($type[$upload->extension]))
            $upload->type = $type[$upload->extension];
        else
            $upload->type = "others";
        $upload->file_size = $request->logo->getSize();
        $upload->save();
        $rquest_all['logo'] = $upload->id;
        $rquest_all['user_id'] = $user;
        $rquest_all['color']=$request->color;
        $card = Card::create($rquest_all);
        return $this->sendResponse($card, translate('card has been inserted successfully'));
    }

    public function get_color_card()
    {
        return $this->sendResponse(Colorcard::get(), translate('card has been inserted successfully'));
    }

    public function get_orders()
    {
        $orders = DB::table('orders');
        $orders = $orders->orderBy('orders.created_at', 'desc')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->where('order_details.seller_id', auth('api')->id())
            ->select('orders.id')
            ->distinct()->get();
        $orderr = [];
        foreach ($orders as $order) {
            $orderq = Order::find($order->id);
            $item['id'] = $orderq->id;
            $item['code'] = $orderq->code;
            $item['num product'] = count($orderq->orderDetails->where('seller_id', auth('api')->user()->id));
            if ($orderq->user_id != null)
                $item['Customer'] = $orderq->user->name;
            else
                $item['Customer'] = $orderq->guest_id;
            $item['Amount'] = single_price($orderq->grand_total);
            $details = OrderDetail::where('order_id', $orderq->id)->first();
            $item['Delivery Status'] = $details->delivery_status;
            $item['Delivery Status Text'] =  translate($details->delivery_status);
            $item['Payment Status'] =  translate($details->payment_status);
            $item['link']['view'] = route('api.ordersDetails', $orderq->id);
            array_push($orderr, $item);
        }
        return $this->sendResponse($orderr, translate('this is all orders'));
    }

    public function get_orders_status(Request $request)
    {
        $orders = DB::table('orders')
            ->orderBy('code', 'desc')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->where('order_details.seller_id', auth('api')->user()->id)
            ->where('order_details.delivery_status', $request)
            ->select('orders.id')->distinct()->get();
        $orderr = [];
        foreach ($orders as $order) {
            $orderq = Order::find($order->id);
            $item['id'] = $orderq->id;
            $item['code'] = $orderq->code;
            $item['num product'] = count($orderq->orderDetails->where('seller_id', auth('api')->user()->id));
            if ($orderq->user_id != null)
                $item['Customer'] = $orderq->user->name;
            else
                $item['Customer'] = $orderq->guest_id;
            $item['Amount'] = single_price($orderq->grand_total);
            $item['Delivery Status'] = OrderDetail::where('order_id', $orderq->id)->first()->delivery_status;
            $item['Payment Status'] = OrderDetail::where('order_id', $orderq->id)->first()->payment_status;
            if ($orderq->orderDetails->where('seller_id', auth('api')->user()->id)->first()->payment_status == 'paid')
                $item['Payment Status'] = 'Paid';
            else
                $item['Payment Status'] = 'Unpaid';
            $item['link']['view'] = route('api.ordersDetails', $orderq->id);
            array_push($orderr, $item);
        }
        return $this->sendResponse($orderr, translate('this is all orders'));
    }

    public function PurchaseHistory()
    {
        $orders = Order::where('user_id', auth('api')->user()->id)->orderBy('code', 'desc')->get();
        $orderr = [];
        foreach ($orders as $orderq) {
            $item['id'] = $orderq->id;
            $item['code'] = $orderq->code;
            foreach (OrderDetail::where('order_id', $orderq->id)->get() as $key => $proo) {
                $pro = Product::withTrashed()->find($proo->product_id);
                $item['products'][$key]['id'] = $pro->id;
                $item['products'][$key]['product_name_ar'] = $pro->name_ar;
                $item['products'][$key]['product_name_en'] = $pro->name;
                $item['products'][$key]['price'] = $proo->price;
                $item['products'][$key]['quantity'] = $proo->quantity;
                $item['products'][$key]['total'] = $proo->quantity * $proo->price;
                $item['products'][$key][' link'] = route('products.show', $proo->product_id);
                $item['products'][$key]['product_details'] = new ProductResource($pro);
            }
            $item['num product'] = OrderDetail::where('order_id', $orderq->id)->count();
            if ($orderq->user_id != null)
                $item['Customer'] = $orderq->user->name;
            else
                $item['Customer'] = $orderq->guest_id;
            $item['Amount'] = single_price($orderq->grand_total);
            if ($orderq->orderDetails != '[]') {
                $item['Delivery Status'] = $orderq->orderDetails->first()->delivery_status;
                $item['Payment Status'] = $orderq->orderDetails->first()->payment_status;
            } else {
                $item['Delivery Status'] = null;
                $item['Payment Status'] = null;
            }
            $item['payment_type'] = $orderq->payment_type;
            $item['date'] = $orderq->created_at;
            $item['link']['view'] = route('api.ordersDetails', $orderq->id);
            array_push($orderr, $item);
        }
        return $this->sendResponse($orderr, translate('this is  Purchase History'));
    }

    public function ordersDetails($id)
    {
        $order = Order::find($id);
        $item['code'] = $order->code;
        $item['Customer'] = json_decode($order->shipping_address)->name;
        if ($order->user_id != null)
            $item['email'] = $order->user->email;
        else
            $item['email'] = json_decode($order->shipping_address)->email;
        $item['address']['address'] = json_decode($order->shipping_address)->address;
        $item['address']['country'] = json_decode($order->shipping_address)->country;
        $item['order date'] = date('d-m-Y H:i A', $order->date);
        $item ['order status'] = translate(OrderDetail::where('order_id', $id)->first()->delivery_status);
        $item['Total order amount'] = single_price($order->grand_total);
        $item['Contact'] = json_decode($order->shipping_address)->phone;
//        $item['Payment method'] = ucfirst(str_replace('_', ' ', $order->payment_type));
//        $item['payment status'] = ucfirst(str_replace('_', ' ', $order->payment_status));
        $item['Payment method'] = translate(ucfirst(str_replace('_', ' ', $order->payment_type)));
        $item['payment status'] = translate(ucfirst(str_replace('_', ' ', $order->payment_status)));
        $item['Delivery Type'] = 'Home Delivery';
        foreach ($order->orderDetails->where('seller_id', auth('api')->user()->id) as $ke => $orderDetail) {
            $item['product'][$ke]['id'] = @$orderDetail->product->id;
            $item['product'][$ke]['name_en'] = @$orderDetail->product->name;
            $item['product'][$ke]['name_ar'] = @$orderDetail->product->name_ar;
            $item['product'][$ke]['thumbnail_image'] = $orderDetail->product ?api_asset($orderDetail->product->thumbnail_img):'';
            $item['product'][$ke]['Variation'] = @$orderDetail->variation;
            $item['product'][$ke]['quantity'] = @$orderDetail->quantity;
            $item['product'][$ke]['price'] = @$orderDetail->price;
            $item['product'][$ke]['base_price'] = (double) $orderDetail->product->unit_price;
            $item['product'][$ke]['base_discounted_price'] = (double) getPrice($orderDetail->product);
            $item['product'][$ke]['unit'] = $orderDetail->product->unit;
            $item['product'][$ke]['rating'] = (double) $orderDetail->product->rating;
            $item['product'][$ke]['link'] = $orderDetail->product ? route('products.show', $orderDetail->product->id) : null;
            $item['product'][$ke]['links']['details'] = $orderDetail->product ? route('products.show', $orderDetail->product->id) : null;
            $item['product'][$ke]['links']['reviews'] = $orderDetail->product ? route('api.reviews.index', $orderDetail->product->id) : null;
            $item['product'][$ke]['links']['related'] = $orderDetail->product ? route('products.related', $orderDetail->product->id) : null;
            $item['product'][$ke]['links']['top_from_seller'] = $orderDetail->product ? route('products.topFromSeller', $orderDetail->product->id) : null;
        }
        $item['Order Ammount']['Subtotal'] = single_price($order->orderDetails->where('seller_id', auth('api')->user()->id)->sum('price'));
        $item['Order Ammount']['Shipping'] = BusinessSetting::where('type', 'shipping_cost')->first()->value;
        $item['Order Ammount']['tax'] = single_price($order->orderDetails->where('seller_id', auth('api')->user()->id)->sum('tax'));
        $item['Order Ammount']['Coupon'] = single_price($order->coupon_discount);
        $item['Order Ammount']['total'] = single_price($order->grand_total);
        return $this->sendResponse($item, translate('this is orders Details'));
    }

    public function orders_delete($id)
    {
        Order::destroy($id);
        return $this->sendResponse('success', translate('order deleted'));
    }

    public function product_seller_review()
    {
        $reviews = DB::table('reviews')
            ->orderBy('id', 'desc')
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->where('products.user_id', auth('api')->user()->id)
            ->select('reviews.id')->distinct()->get();
        $rev = [];
        foreach ($reviews as $key => $value) {
            $review = Review::find($value->id);
            $review->viewed = 1;
            $review->save();
            $item['id'] = $review->id;
            $item['product_name_ar'] = product::find($review->product_id)->name_ar;
            $item['product_name_en'] = product::find($review->product_id)->name;
            $item['rating'] = $review->rating;
            $item['user_name'] = User::find($review->user_id)->name;
            $item['comment'] = $review->comment;
            if ($review->status == 1)
                $item['status'] = 'Published';
            else
                $item['status'] = 'Unpublished';
            $item['viewed'] = $review->viewed;
            array_push($rev, $item);
        }
        return $this->sendResponse($rev, translate('this is revire '));
    }

    public function home()
    {
        $user_id = auth('api')->id();
        $user_type = User::find($user_id)->user_type;
        if ($user_type != 'seller')
            return $this->sendError(translate('error occer'));
        $date = date("Y-m-d");
        $days_ago_30 = date('Y-m-d', strtotime('-30 days', strtotime($date)));
        $days_ago_60 = date('Y-m-d', strtotime('-60 days', strtotime($date)));
        $item['verification_status'] = User::find($user_id)->seller->verification_status;
        $item['paid'] = User::find($user_id)->seller->paid;
        $item['packege_name_ar'] = Vendorpackege::find(User::find($user_id)->seller->vendor_pakege_id)->title;
        $item['packege_name_en'] = Vendorpackege::find(User::find($user_id)->seller->vendor_pakege_id)->title_en;
        $item['link to paid'] = route('api.paid_to_be_vendor');
        $item['shop name ar'] = User::find($user_id)->shop->name_ar;
        $item['shop name en'] = User::find($user_id)->shop->name_en;
        $item['featured seller'] = User::find($user_id)->seller->verify;
        $item['Products'] = Product::where('user_id', auth('api')->user()->id)->count();
        $item['Total sale'] = OrderDetail::where('seller_id', auth('api')->user()->id)->where('delivery_status', 'delivered')->count();
        $orderDetails = OrderDetail::where('seller_id', auth('api')->user()->id)->get();
        $total = 0;
        foreach ($orderDetails as $key => $orderDetail) {
            if ($orderDetail->order != null && $orderDetail->order->payment_status == 'paid')
                $total += $orderDetail->price;
        }
        $item['Total earnings'] = single_price($total);
        $item['Successful orders'] = $item['Total sale'];
        $orderDetailss = \App\OrderDetail::where('seller_id', auth('api')->user()->id)->where('created_at', '>=', $days_ago_30)->get();
        $total = 0;
        foreach ($orderDetailss as $key => $orderDetail) {
            if ($orderDetail->order != null && $orderDetail->order != null && $orderDetail->order->payment_status == 'paid')
                $total += $orderDetail->price;
        }
        $item['current month'] = single_price($total);
        $orderDetailssa = \App\OrderDetail::where('seller_id', auth('api')->user()->id)->where('created_at', '>=', $days_ago_60)->where('created_at', '<=', $days_ago_30)->get();
        $total = 0;
        foreach ($orderDetailssa as $key => $orderDetail) {
            if ($orderDetail->order != null && $orderDetail->order->payment_status == 'paid')
                $total += $orderDetail->price;
        }
        $item['Last Month Sold'] = single_price($total);
        $item['Orders']['total orders'] = OrderDetail::where('seller_id', auth('api')->user()->id)->count();
        $item['Orders']['Pending orders'] = OrderDetail::where('seller_id', auth('api')->user()->id)->where('delivery_status', 'pending')->count();
        $item['Orders']['Cancelled orders'] = OrderDetail::where('seller_id', auth('api')->user()->id)->where('delivery_status', 'cancelled')->count();
        $item['Orders']['Delivered orders'] = OrderDetail::where('seller_id', auth('api')->user()->id)->where('delivery_status', 'delivered')->count();
        $cats = [];
        foreach (Category::all() as $key => $category) {
            if (count($category->products->where('user_id', auth('api')->user()->id)) > 0)
                array_push($cats, $category->id);
        }
        foreach ($cats as $k => $c) {
            $categoryd = Category::find($c);
            $item['products']['category'][$k]['category_name_ar'] = $categoryd->name;
            $item['products']['category'][$k]['category_name_en'] = $categoryd->name_en;
            $item['products']['category'][$k]['Product'] = count($categoryd->products->where('user_id', auth('api')->user()->id));
        }
        return $this->sendResponse($item, translate('data seller'));
    }

    public function store_edit(Request $request)
    {
        $id = auth('api')->user()->shop->id;
        $shop = Shop::find($id);
        if ($request->has('name') || $request->has('meta_title') || $request->has('meta_description') || $request->has('address')) {
            $shop->name = $request->name_en;
            $shop->name_ar = $request->name_ar;
            $shop->address = $request->address;
            $shop->slug = preg_replace('/\s+/', '-', $request->name_en) . '-' . $shop->id;
            $shop->meta_title = $request->name_en;
            $shop->meta_description = $request->name_en;
            if ($request->logo != null) {
                $upload = new Upload;
                $upload->file_original_name = null;
                $arr = explode('.', $request->logo->getClientOriginalName());
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    if ($i == 0)
                        $upload->file_original_name .= $arr[$i];
                    else
                        $upload->file_original_name .= "." . $arr[$i];
                }
                $upload->file_name = $request->logo->store('uploads/all');
                $upload->user_id = auth('api')->user()->id;
                $upload->extension = strtolower($request->logo->getClientOriginalExtension());
                if (isset($type[$upload->extension]))
                    $upload->type = $type[$upload->extension];
                else
                    $upload->type = "others";
                $upload->file_size = $request->logo->getSize();
                $upload->save();
                $shop->logo = $upload->id;
            }
            $shop->save();
        }
        if ($request->has('facebook') || $request->has('twitter') || $request->has('youtube') || $request->has('instagram')) {
            $shop->facebook = $request->facebook;
            $shop->instagram = $request->instagram;
            $shop->twitter = $request->twitter;
            $shop->youtube = $request->youtube;
            $shop->save();
        }
        if ($request->banners != null) {
            foreach ($request->banners as $att) {
                $upload = new Upload;
                $upload->file_original_name = null;
                $arr = explode('.', $att->getClientOriginalName());
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    if ($i == 0)
                        $upload->file_original_name .= $arr[$i];
                    else
                        $upload->file_original_name .= "." . $arr[$i];
                }
                $upload->file_name = $att->store('uploads/all');
                $upload->user_id = auth('api')->user()->id;
                $upload->extension = strtolower($att->getClientOriginalExtension());
                if (isset($type[$upload->extension]))
                    $upload->type = $type[$upload->extension];
                else
                    $upload->type = "others";
                $upload->file_size = $att->getSize();
                $upload->save();
                $arrr[] = $upload->id;
            }
            $arrt = json_encode($arrr);
            $array = str_replace('[', '', $arrt);
            $array1 = str_replace(']', '', $array);
            $shop->sliders = $array1;
            $shop->save();
        }
        $shops['data'] = new ShopResource(Shop::where('id', $shop->id)->get());
        return $this->sendResponse($shops, translate('shop setting created sussfuly'));
    }

    public function bank_setting(Request $request)
    {
        $user = auth('api')->user();
        $seller = $user->seller;
        $seller->cash_on_delivery_status = $request->cash_on_delivery_status;
        $seller->bank_payment_status = $request->bank_payment_status;
        $seller->bank_name = $request->bank_name;
        $seller->bank_acc_name = $request->bank_acount_name;
        $seller->bank_acc_no = $request->bank_acount_number;
        $seller->save();
        return $this->sendResponse('success', translate('bank information updated'));
    }
}