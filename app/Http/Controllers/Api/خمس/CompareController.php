<?php

namespace App\Http\Controllers\Api;
use App\Http\Resources\CompareCollection;
use App\Comparisons;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Auth;
class CompareController extends BaseController
{
    public function create(Request $request )
    {
        $user_id = auth('api')->id();
        $product = Product::find($request->product_id);
        if(!$product){
         return     $this->sendError('Product not found!');

        }
       $com = Comparisons::where('user_id',$user_id)->count();
       if($com ==3){
                    return     $this->sendError('Maximum number for comparison is 3 ');
       }else{
           $eexi=  Comparisons::where('user_id',$user_id)->where('product_id',$request->product_id)->first();
           if($eexi){
                        return     $this->sendError('The product is already in the compare list');
           }
           $co = new Comparisons();
           $co->user_id = auth('api')->id();
           $co->product_id = $request->product_id;
           $co->save();
           return     $this->sendResponse($co , 'product add successfully .');

       }
    }
    public function index(){
        $user_id = auth('api')->id();
        $comps =  new CompareCollection( Comparisons::where('user_id',$user_id)->get());
        return     $this->sendResponse($comps , 'There is all compare list');
    }
      public function delete($id){
        $user_id = 
        $comps =  Comparisons::destroy($id);
        return  $this->sendResponse('deleted' , 'product deleted successfully');
    }
    public function reste(){
                $user_id = auth('api')->id();
                $cos=   Comparisons::where('user_id',$user_id)->get();
                foreach($cos as $co){
                  Comparisons::destroy($co->id);  
                }
                        return  $this->sendResponse('reset' , 'compareList reset successfully ');

    }

}
