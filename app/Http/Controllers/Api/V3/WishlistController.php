<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\WishlistResource;
use App\Models\V3\Wishlist;
use Illuminate\Http\Request;
use App\Models\V3\Product;
use App\Http\Controllers\Api\BaseController as BaseController;

class WishlistController extends BaseController
{
    public function addtowishlist($id)
    {
        $product = Product::where('id', $id)->first();
        if ($product) {
            $user_id = auth('api')->id();
            if (!$user_id)
                return $this->sendError(translate('no user'));
            $wish = Wishlist::updateOrCreate(['user_id' => $user_id, 'product_id' => $id]);
            return $this->sendResponse($wish, translate('Product is successfully added to your wishlist'));
        } else
            return $this->sendError(translate('the product id not found'));
    }

    public function removeFormwishlist($id)
    {
        $product = Product::where('id', $id)->first();
        if ($product) {
            $user_id = auth('api')->id();
            $wish = Wishlist::where('user_id', $user_id)->where('product_id', $id)->first();
            if ($wish) {
                $wish->delete();
                return $this->sendResponse('deleted', translate('Product is successfully remove to your wishlist'));
            } else
                return $this->sendError(translate('the product  not found in wishlist'));
        } else
            return $this->sendError(translate('the product id not found'));
    }

    public function index()
    {
        $wish['data'] = WishlistResource::collection(Wishlist::where('user_id', auth('api')->id())->whereHas('product')->latest()->get());
        return $this->sendResponse($wish, translate('Wishlist'));
    }

    public function store(Request $request)
    {
        $wish = Wishlist::updateOrCreate(
            ['user_id' => auth('api')->id(), 'product_id' => $request->product_id]);
        return $this->sendResponse($wish, translate('Product is successfully added to your wishlist'));
    }

    public function destroy($id)
    {
        Wishlist::destroy($id);
        return $this->sendResponse('success', translate('Product is successfully removed from your wishlist'));
    }

    public function isProductInWishlist(Request $request)
    {
        $product = Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth('api')->id()])->count();
        if ($product > 0)
            return response()->json([
                'message' => 'Product present in wishlist',
                'is_in_wishlist' => true,
                'product_id' => (integer)$request->product_id,
                'wishlist_id' => (integer)Wishlist::where(['product_id' => $request->product_id, 'user_id' => $request->user_id])->first()->id
            ], 200);
        return response()->json([
            'message' => 'Product is not present in wishlist',
            'is_in_wishlist' => false,
            'product_id' => (integer)$request->product_id,
            'wishlist_id' => 0
        ], 200);
    }
}
