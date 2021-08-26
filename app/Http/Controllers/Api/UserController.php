<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserCollection;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use Validator;
use App\Upload;

class UserController extends BaseController
{
    public function info()
    {
        $user = new UserCollection(User::where('id', auth('api')->id())->get());
        return $this->sendResponse($user, translate('User Info.'));
    }

    public function updateName(Request $request)
    {
        $user = User::findOrFail(auth('api')->id());
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'name' => 'required|string',
        ]);
        if ($validator->fails())
            return $this->sendError('error validation', $validator->errors());
        $request_all = $request->except(['image']);
        $user->update($request_all);
        $users = new UserCollection(User::where('id', $user->id)->get());
        return $this->sendResponse($users, translate('Profile information has been updated successfully'));
    }

    public function change_image(Request $request)
    {
        $user = User::findOrFail(auth('api')->id());
        $request_all = $request->except(['image']);
        if ($request->image != null) {
            $upload = new Upload;
            $upload->file_original_name = null;
            $arr = explode('.', $request->image->getClientOriginalName());
            for ($i = 0; $i < count($arr) - 1; $i++) {
                if ($i == 0)
                    $upload->file_original_name .= $arr[$i];
                else
                    $upload->file_original_name .= "." . $arr[$i];
            }
            $upload->file_name = $request->image->store('uploads/all');
            $upload->user_id = auth('api')->user()->id;
            $upload->extension = strtolower($request->image->getClientOriginalExtension());
            if (isset($type[$upload->extension]))
                $upload->type = $type[$upload->extension];
            else
                $upload->type = "others";
            $upload->file_size = $request->image->getSize();
            $upload->save();
            $user->avatar_original = $upload->id;
        }
        $user->update($request_all);
        $users = new UserCollection(User::where('id', $user->id)->get());
        return $this->sendResponse($users, translate('Profile information has been updated successfully'));
    }
}
