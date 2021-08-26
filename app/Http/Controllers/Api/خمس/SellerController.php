<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductDetailCollection;
use App\Http\Resources\SearchProductCollection;
use App\Http\Resources\FlashDealCollection;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\Shop;
use App\OrderDetail;
use App\Models\Color;
use App\BusinessSetting;
use Auth;
use App\Upload;
use App\User;
use DB;
use App\Colorcard;
use App\Review;
use Illuminate\Http\Request;
use App\Utility\CategoryUtility;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Order;
use App\Payment;
use App\City2 as City;
use Validator;
use App\Card;
use App\SellerWithdrawRequest;
class SellerController extends BaseController
{
     public function update_delivery_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->delivery_viewed = '0';
        $order->save();
        if(Auth::user()->user_type == 'seller'){
            foreach($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail){
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();
            }
        }
        else{
            foreach($order->orderDetails as $key => $orderDetail){
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();
            }
        }

        $sta= 'success';

            return $this->sendResponse($sta,'order has been updated successfully');

    }
    public function seller_withdrow(Request $request ){
         $seller_withdraw_request = new SellerWithdrawRequest;
        $seller_withdraw_request->user_id = Auth::user()->seller->id;
        $seller_withdraw_request->amount = $request->amount;
        $seller_withdraw_request->message = $request->message;
        $seller_withdraw_request->status = '0';
        $seller_withdraw_request->viewed = '0';
        if ($seller_withdraw_request->save()) {
      return $this->sendResponse($seller_withdraw_request,'Request has been sent successfully');
        }             

        else{
                 return $this->sendError('Error');

        }
    }
    public function Pending_Balance_for_seller(){
              return $this->sendResponse(single_price(Auth::user()->seller->admin_to_pay),'Pending Balance');

    }  
    public  function seller_withdraw_requests(){
      $ree=  SellerWithdrawRequest::where('user_id',Auth::user()->seller->id)->get();
      foreach($ree as $key=> $re){
        if ($re->status == 1){
            
                $ree[$key]['status']= 'Paid';
                }else{
                                    $ree[$key]['status']= 'Pending';

                                                  }
      }
                    return $this->sendResponse($ree,' Seller Withdraw Request');

    }
    public function payments(){
        $payments = Payment::where('seller_id', Auth::user()->seller->id)->get();
                            return $this->sendResponse($payments,'payment');

    }
    public function Seller_card(Request $request){
          $user = auth('api')->id();
         
        if(Card::where('user_id',$user)->first()){
           
                 return $this->sendError('Your request all ready sent');

        }
 $validator = Validator::make($request->all(), [
            'logo'=>'required',
            'email'=>'required|email',
            'phone'=>'required|numeric',   
            'shop_name_ar'=>'required',
            'color'=>'required',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
        //   flash( $errors)->error();
                 return $this->sendError('Please verify all entered data', $validator->errors());

     
            }   
               $rquest_all = $request->all();
          
           $upload = new Upload;
            $upload->file_original_name = null;

            $arr = explode('.', $request->logo->getClientOriginalName());

            for($i=0; $i < count($arr)-1; $i++){
                if($i == 0){
                    $upload->file_original_name .= $arr[$i];
                }
                else{
                    $upload->file_original_name .= ".".$arr[$i];
                }
            }

            $upload->file_name = $request->logo->store('uploads/all');
            $upload->user_id = Auth::user()->id;
            $upload->extension = strtolower($request->logo->getClientOriginalExtension());
            if(isset($type[$upload->extension])){
                $upload->type = $type[$upload->extension];
            }
            else{
                $upload->type = "others";
            }
            $upload->file_size = $request->logo->getSize();
            $upload->save();
           $rquest_all['logo'] = $upload->id;

             $rquest_all['user_id']=$user;
$card=  Card::create($rquest_all);
             return $this->sendResponse($card,'card has been inserted successfully');

    }
    public function get_color_card(){
             return $this->sendResponse(Colorcard::get(),'card has been inserted successfully');

    }
    public function get_orders(){
        $id = Auth::user()->seller->id;
             $orders = DB::table('orders')
                    ->orderBy('code', 'desc')
                    ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                    ->where('order_details.seller_id', Auth::user()->id)
                    ->select('orders.id')
                    ->distinct()->get();
                // $ors=    json_decode();
        $orderr=[];
                    foreach( $orders as $order){
                        $orderq = Order::find($order->id);
               $item['id'] = $orderq->id;
               $item['code'] = $orderq->code;      
               $item['num product'] =  count($orderq->orderDetails->where('seller_id', Auth::user()->id));
       if ($orderq->user_id != null){
           $item['Customer']= $orderq->user->name ;
                                             
            } else{
           $item['Customer']= $orderq->guest_id ;
            }
            $item['Amount'] = single_price($orderq->orderDetails->where('seller_id', Auth::user()->id)->sum('price'));
            $item['Delivery Status'] =ucfirst(str_replace('_', ' ', $orderq->orderDetails->first()->delivery_status));
            $item['Payment Status'] =ucfirst(str_replace('_', ' ', $orderq->orderDetails->first()->delivery_status));
      if ($orderq->orderDetails->where('seller_id', Auth::user()->id)->first()->payment_status == 'paid'){
           $item['Payment Status'] ='Paid';   
         }else{
        $item['Payment Status'] ='Unpaid';   
        }
        $item['link']['view']=route('api.ordersDetails',$orderq->id);
            

                                                array_push($orderr, $item);

                    }
        return $this->sendResponse($orderr,'this is all orders');
    }
    public function  PurchaseHistory(){
              $orders = Order::where('user_id', Auth::user()->id)->orderBy('code', 'desc')->get();
              
               $orderr=[];
                    foreach( $orders as $orderq){
               $item['id'] = $orderq->id;
               $item['code'] = $orderq->code;      
               $item['num product'] =  count($orderq->orderDetails->where('seller_id', Auth::user()->id));
       if ($orderq->user_id != null){
           $item['Customer']= $orderq->user->name ;
                                             
            } else{
           $item['Customer']= $orderq->guest_id ;
            }
            $item['Amount'] = single_price($orderq->orderDetails->where('seller_id', Auth::user()->id)->sum('price'));
            $item['Delivery Status'] =ucfirst(str_replace('_', ' ', $orderq->orderDetails->first()->delivery_status));
            $item['Payment Status'] =ucfirst(str_replace('_', ' ', $orderq->orderDetails->first()->payment_status));
            
   
        $item['link']['view']=route('api.ordersDetails',$orderq->id);
            

                                                array_push($orderr, $item);
         
    }
            return $this->sendResponse($orderr,'this is  Purchase History');

    }
    public function ordersDetails($id){
        $order = Order::find($id);
             $orderr=[];
             $item['code']=$order->code;
             $item['Customer']=json_decode($order->shipping_address)->name ;
                 if ($order->user_id != null){
                      $item['email']     =  $order->user->email;
                 }else{
                      $item['email']=null;
                 }
                 $item['address']['address']=json_decode($order->shipping_address)->address ;
                                  $item['address']['country']=json_decode($order->shipping_address)->country;

                 
                //  $item['address']['governorate_en']=City::find(json_decode($order->shipping_address)->city)->name_en ;
                //   $item['address']['governorate_ar']=City::find(json_decode($order->shipping_address)->city)->name ;
                //   $item['address']['state_en']=City::find(json_decode($order->shipping_address)->postal_code)->name_en ;
                //   $item['address']['state_ar']=City::find(json_decode($order->shipping_address)->postal_code)->name ;
                  $item['order date']=date('d-m-Y H:i A', $order->date) ;
                  $item ['order status'] = $order->orderDetails->where('seller_id', Auth::user()->id)->first()->delivery_status;
                  $item['Total order amount']=single_price($order->grand_total) ;
                  $item['Contact']=json_decode($order->shipping_address)->phone ;
                   $item['Payment method']= ucfirst(str_replace('_', ' ', $order->payment_type));
                   
                    foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail){
                        if ($orderDetail->product != null){
                            $itemd['product']['id'] = $orderDetail->product->id;

                            $itemd['product']['name_en'] = $orderDetail->product->name;
                            $itemd['product']['name_ar'] = $orderDetail->product->name_ar;
                            $itemd['product']['Variation']= $orderDetail->variation ;
                            $itemd['product']['quantity']= $orderDetail->quantity ;
                             $itemd['product']['Delivery Type']='Home Delivery';
                             $itemd['product']['price']=$orderDetail->price;
                           
                                        }else{
                          $itemd['product']['name']       ='Product Unavailablel' ;
    
                                        }
                         array_push($orderr, $itemd);
                }
                $item['Order Ammount']['Subtotal']=single_price($order->orderDetails->where('seller_id', Auth::user()->id)->sum('price'));
                $item['Order Ammount']['Shipping']=BusinessSetting::where('type','shipping_cost')->first()->value;
                $item['Order Ammount']['tax']=single_price($order->orderDetails->where('seller_id', Auth::user()->id)->sum('tax'));
                $item['Order Ammount']['Coupon']=single_price($order->coupon_discount);
                $item['Order Ammount']['total']=single_price($order->grand_total);

              array_push($orderr, $item);
        return $this->sendResponse($orderr,'this is orders Details');

    }
    
    
    public function orders_delete($id){
        Order::destroy($id);
        $orderr = 'success';
        return $this->sendResponse($orderr,'order deleted');

        
    }
    public function product_seller_review(){
          $reviews = DB::table('reviews')
                    ->orderBy('id', 'desc')
                    ->join('products', 'reviews.product_id', '=', 'products.id')
                    ->where('products.user_id', Auth::user()->id)
                    ->select('reviews.id')
                    ->distinct()
                    ->get();
        
        $rev = [];
        
        foreach ($reviews as $key => $value) {
            $review = Review::find($value->id);
            $review->viewed = 1;
            $review->save();
            $item['id']=$review->id;
            $item['product_name_ar']=product::find($review->product_id)->name_ar;
            $item['product_name_en']=product::find($review->product_id)->name;
            $item['rating']=$review->rating;
            $item['comment']=$review->comment;
                if ($review->status == 1){
          $item['status']='Published';
            }else{
             $item['status']='Unpublished';
             }
            $item['viewed']=$review->viewed;

                

                          array_push($rev, $item);

            
        }
                return $this->sendResponse($rev,'this is revire ');

    }
    public function home(){
        
        $user_id = auth('api')->id();
        $user_type= User::find($user_id)->user_type;
        if($user_type != 'seller'){
                 return $this->sendError('error occer');
  
        }
                   $date = date("Y-m-d");
              $days_ago_30 = date('Y-m-d', strtotime('-30 days', strtotime($date)));
              $days_ago_60 = date('Y-m-d', strtotime('-60 days', strtotime($date)));
        $item['Products'] = Product::where('user_id', Auth::user()->id)->count();
        $item['Total sale']= OrderDetail::where('seller_id', Auth::user()->id)->where('delivery_status', 'delivered')->count();
        $orderDetails = OrderDetail::where('seller_id', Auth::user()->id)->get();
                                    $total = 0;
                                    foreach ($orderDetails as $key => $orderDetail) {
                                        if($orderDetail->order != null && $orderDetail->order->payment_status == 'paid'){
                                            $total += $orderDetail->price;
                                        }
       }
       $item['Total earnings'] = single_price($total);
       $item['Successful orders']= $item['Total sale'];
   $orderDetailss = \App\OrderDetail::where('seller_id', Auth::user()->id)->where('created_at', '>=', $days_ago_30)->get();
                        $total = 0;
                        foreach ($orderDetailss as $key => $orderDetail) {
                            if($orderDetail->order != null && $orderDetail->order != null && $orderDetail->order->payment_status == 'paid'){
                                $total += $orderDetail->price;
                            }
                        }
        $item['current month'] = single_price($total);
        
        
        
           $orderDetailssa = \App\OrderDetail::where('seller_id', Auth::user()->id)->where('created_at', '>=', $days_ago_60)->where('created_at', '<=', $days_ago_30)->get();
                            $total = 0;
                            foreach ($orderDetailssa as $key => $orderDetail) {
                                if($orderDetail->order != null && $orderDetail->order->payment_status == 'paid'){
                                    $total += $orderDetail->price;
                                }
                            }
        $item['Last Month Sold'] = single_price($total);
        $item['Orders']['total orders']=OrderDetail::where('seller_id', Auth::user()->id)->count();
        $item['Orders']['Pending orders']=OrderDetail::where('seller_id', Auth::user()->id)->where('delivery_status', 'pending')->count();
        $item['Orders']['Cancelled orders']=OrderDetail::where('seller_id', Auth::user()->id)->where('delivery_status', 'cancelled')->count();
        $item['Orders']['Delivered orders']=OrderDetail::where('seller_id', Auth::user()->id)->where('delivery_status', 'delivered')->count();
        foreach (Category::all() as $key => $category){
                                        if(count($category->products->where('user_id', Auth::user()->id))>0){
                                              $item['products']['category']['category_name_ar']= $category->name_ar;
                                              $item['products']['category']['category_name_en']= $category->name;
                                               $item['products']['category']['Product']= count($category->products->where('user_id', Auth::user()->id));
                       
                }
        }
        // $item['links']['create product']= route('api.create_product');
        // $item['links']['mange account']= route('api.create_product');
        // $item['links']['verfy']= route('api.create_product');
        // $item['links']['paid']= route('api.create_product');


       
        

       return $this->sendResponse($item,'data seller');
    }
      public function store_edit(Request $request){
        //   dd(Auth::user());
        $id = Auth::user()->shop->id;
                $shop = Shop::find($id);

               if($request->has('name') || $request->has('meta_title') || $request->has('meta_description') || $request->has('address')){
            $shop->name = $request->name_en;
            $shop->name_ar = $request->name_ar;

            $shop->address = $request->address;
            $shop->slug = preg_replace('/\s+/', '-', $request->name_en).'-'.$shop->id;
            $shop->meta_title =$request->name_en;
            $shop->meta_description = $request->name_en;
            if($request->logo != null){
           $upload = new Upload;
            $upload->file_original_name = null;

            $arr = explode('.', $request->logo->getClientOriginalName());

            for($i=0; $i < count($arr)-1; $i++){
                if($i == 0){
                    $upload->file_original_name .= $arr[$i];
                }
                else{
                    $upload->file_original_name .= ".".$arr[$i];
                }
            }

            $upload->file_name = $request->logo->store('uploads/all');
            $upload->user_id = Auth::user()->id;
            $upload->extension = strtolower($request->logo->getClientOriginalExtension());
            if(isset($type[$upload->extension])){
                $upload->type = $type[$upload->extension];
            }
            else{
                $upload->type = "others";
            }
            $upload->file_size = $request->logo->getSize();
            $upload->save();
            $shop->logo = $upload->id;

            }
            
            
            $shop->save();

    }
     if($request->has('facebook') ||  $request->has('twitter') || $request->has('youtube') || $request->has('instagram')){
            $shop->facebook = $request->facebook;
            $shop->instagram = $request->instagram;
            $shop->twitter = $request->twitter;
            $shop->youtube = $request->youtube;
            $shop->save();

        }
        if($request->banners != null){
         foreach($request->banners as $att){
             $upload = new Upload;
            $upload->file_original_name = null;

            $arr = explode('.', $att->getClientOriginalName());

            for($i=0; $i < count($arr)-1; $i++){
                if($i == 0){
                    $upload->file_original_name .= $arr[$i];
                }
                else{
                    $upload->file_original_name .= ".".$arr[$i];
                }
            }

            $upload->file_name = $att->store('uploads/all');
            $upload->user_id = Auth::user()->id;
            $upload->extension = strtolower($att->getClientOriginalExtension());
            if(isset($type[$upload->extension])){
                $upload->type = $type[$upload->extension];
            }
            else{
                $upload->type = "others";
            }
            $upload->file_size = $att->getSize();
            $upload->save();
            $arrr[]=$upload->id;
            
       }
        $arrt= json_encode($arrr);
        $array = str_replace('[','',$arrt);
        $array1=str_replace(']','',$array);
        $shop->sliders = $array1;
              $shop->save();
        }
        
        return $this->sendResponse($shop,'shop setting created sussfuly');
        }
            public function bank_setting(Request $request){
        $user = Auth::user();
                $seller = $user->seller;
                //  if($request->cash_on_delivery_status == 1){
        $seller->cash_on_delivery_status = $request->cash_on_delivery_status;
                //  }
                //  if($request->bank_payment_status ==1){
                             $seller->bank_payment_status = $request->bank_payment_status;

                //  }
                 
        $seller->bank_name = $request->bank_name;
        $seller->bank_acc_name = $request->bank_acount_name;
        $seller->bank_acc_no = $request->bank_acount_number;
        // $seller->bank_routing_no = $request->bank_routing_no;
        $seller->save();
        $shop = 'success';
                return $this->sendResponse($shop,'bank information updated');

    }
        
        
        
        
    
}