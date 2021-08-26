<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\ReviewResource;
use Illuminate\Http\Request;
use App\Models\V3\Product;
use App\Models\V3\Review;
use App\Models\V3\User;
use App\Notifications\V3\ReviewNofication;
use App\Http\Controllers\Api\BaseController as BaseController;

class ReviewController extends BaseController
{
    public function index($id)
    {
        $data['data'] = new ReviewResource(Review::where('product_id', $id)->latest()->get());
        return $data;
    }

    public function store(Request $request)
    {
        $detailedProduct = Product::find($request->product_id);
        $commentable = 0;
        foreach ($detailedProduct->orderDetails as $key => $orderDetail) {
            if ($orderDetail->order != null && $orderDetail->order->user_id == auth('api')->user()->id && \App\Review::where('user_id', auth('api')->user()->id)->where('product_id', $detailedProduct->id)->first() == null) {
                $commentable = 1;
            }
        }
        if ($commentable == 1) {
            $review = new Review;
            $review->product_id = $request->product_id;
            $review->user_id = auth('api')->user()->id;
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->viewed = '0';
            if ($review->save()) {
                $user = User::find($review->product->user_id);
                $user->notify(new ReviewNofication($review));
                $product = Product::findOrFail($request->product_id);
                if (count(Review::where('product_id', $product->id)->where('status', 1)->get()) > 0)
                    $product->rating = Review::where('product_id', $product->id)->where('status', 1)->sum('rating') / count(Review::where('product_id', $product->id)->where('status', 1)->get());
                else
                    $product->rating = 0;
                $product->save();
                return $this->sendResponse($review, translate('Review has been submitted successfully'));
            }
            return $this->sendError(translate('Something went wrong'));
        }
        return $this->sendError(translate('You need to buy this product to rate it'));
    }
}
