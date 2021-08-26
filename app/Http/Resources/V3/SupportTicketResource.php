<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'code'=>$this->code,
            'subject' => $this->subject,
            'details' => $this->details,
            'date' => $this->created_at,
            'files' => $this->convertPhotos(explode(',', $this->files)),
            'status'=>$this->status,

        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
      protected function convertPhotos($data){
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, api_asset($item));
        }
        return $result;
    }
}
