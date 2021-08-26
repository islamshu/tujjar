<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\CompareResource;
use App\Models\V3\Comparisons;
use App\Models\V3\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;

class CompareController extends BaseController
{
    public function create(Request $request)
    {
        $user_id = auth('api')->id();
        $product = Product::find($request->product_id);
        if (!$product)
            return $this->sendError(translate('Product not found!'));
        $com = Comparisons::where('user_id', $user_id);
        if ($com->count() == 3)
            return $this->sendError(translate('Maximum number for comparison is 3 '));
        else {
            $com = $com->where('product_id', $request->product_id)->first();
            if ($com)
                return $this->sendError(translate('The product is already in the compare list'));
            $co = new Comparisons();
            $co->user_id = auth('api')->id();
            $co->product_id = $request->product_id;
            $co->save();
            return $this->sendResponse($co, translate('product add successfully .'));
        }
    }

    public function index()
    {
        $user_id = auth('api')->id();
        $comps['data'] = CompareResource::collection(Comparisons::where('user_id', $user_id)->get());
        return $this->sendResponse($comps, translate('There is all compare list'));
    }

    public function delete($id)
    {
        Comparisons::destroy($id);
        return $this->sendResponse('deleted', translate('product deleted successfully'));
    }

    public function reste()
    {
        $cos = Comparisons::where('user_id', auth('api')->id())->pluck('id');
        Comparisons::destroy($cos);
        return $this->sendResponse('reset', translate('compareList reset successfully '));
    }
}
