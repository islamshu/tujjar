<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserCollection;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Auth;
class UserController extends BaseController
{
    public function info()
    {
        $user_id = auth('api')->id();
        $user= new UserCollection(User::where('id', $user_id)->get());
        return $this->sendResponse($user , 'User Info.');

    }

    public function updateName(Request $request)
    {
        $user = User::findOrFail(auth('api')->id());
        $user->update([
            'name' => $request->name
        ]);
                return $this->sendResponse($user , 'Profile information has been updated successfully');

      
    }
}
