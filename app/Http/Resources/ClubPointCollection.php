<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\BusinessSetting;
class ClubPointCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $datae['data'] =
             $this->collection->map(function($data) {
                $a = [
                    'id'=>$data->id,
                    'date' => date('d-m-Y', strtotime($data->created_at)),
                    'points' => $data->points,
                    'converted'=> $data->convert_status == 1 ? 'yes' : 'no' ,
                  
                ];
             if($data->convert_status == 1){

                $a['links']= 'no links';
             }else {
                 $a['links']= route('api_convert_club',$data->id); 
             }
             return $a;
                
               
            });
        
        $datae['Exchange Rate'] =  single_price_api(BusinessSetting::where('type', 'club_point_convert_rate')->first()->value)  ;
        return $datae;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
