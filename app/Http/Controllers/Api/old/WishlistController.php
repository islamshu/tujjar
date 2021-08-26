<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\WishlistCollection;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Auth;
use App\Product;
use App\Http\Controllers\Api\BaseController as BaseController;

class WishlistController extends BaseController
{
    public function addtowishlist($id){
        $product = Product::where('id',$id)->first();
        if($product){
         $user_id= Auth::id();
         if(!$user_id){
                             return $this->sendError('no user');

         }
      $wish=  Wishlist::updateOrCreate(
            ['user_id' => $user_id, 'product_id' => $id]
        );
                return $this->sendResponse($wish,'Product is successfully added to your wishlist');

    }
        else{
                return $this->sendError('the product id not found');
    
        }
    }
    public function removeFormwishlist($id){
        $product = Product::where('id',$id)->first();
        if($product){
         $user_id= Auth::id();
      $wish=  Wishlist::where('user_id',$user_id)->where('product_id',$id)->first();
      if($wish){
        $wish ->delete();
        $message='deleted';
                return $this->sendResponse($message,'Product is successfully remove to your wishlist');
          
      }else{
                             return $this->sendError('the product  not found in wishlist');
       
            }

    }
        else{
                return $this->sendError('the product id not found');
    
        }
    }
    public function index()
    {
                 $id = Auth::id();  

        
         
        $wish= new WishlistCollection(Wishlist::where('user_id', $id)->latest()->get());
                         return $this->sendResponse($wish,'Wishlist');

    }

    public function store(Request $request)
    {
     
        $user_id= Auth::id();
      $wish=  Wishlist::updateOrCreate(
            ['user_id' => $user_id, 'product_id' => $request->product_id]
        );
         return $this->sendResponse($wish,'Product is successfully added to your wishlist');

        // return response()->json(['message' => 'Product is successfully added to your wishlist'], 201);
    }

    public function destroy($id)
    {
        Wishlist::destroy($id);
    $succrss='success';
        return $this->sendResponse($succrss,'Product is successfully removed from your wishlist');

    }

    public function isProductInWishlist(Request $request)
    {
                $user_id= Auth::id();

        $product = Wishlist::where(['product_id' => $request->product_id, 'user_id' =>$user_id])->count();
        
        if ($product > 0)
            return response()->json([
                'message' => 'Product present in wishlist',
                'is_in_wishlist' => true,
                'product_id' => (integer) $request->product_id,
                'wishlist_id' => (integer) Wishlist::where(['product_id' => $request->product_id, 'user_id' => $request->user_id])->first()->id
            ], 200);

        return response()->json([
            'message' => 'Product is not present in wishlist',
            'is_in_wishlist' => false,
            'product_id' => (integer) $request->product_id,
            'wishlist_id' => 0
        ], 200);
    }
}
