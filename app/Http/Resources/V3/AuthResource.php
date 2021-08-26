<?php

namespace App\Http\Resources\V3;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    protected $tokenResult;

    public function __construct($resource,$tokenResult)
    {
        parent::__construct($resource);
        $this->tokenResult = $tokenResult;
    }

    public function toArray($request)
    {
        return [
            'access_token' => $this->tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($this->tokenResult->token->expires_at)->toDateTimeString(),
            'user' => new UserResource($this)
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
