<?php

namespace App\Http\Controllers\Api\V3;

use Illuminate\Http\Request;
use App\Models\V3\User;
use App\Models\V3\PasswordReset;
use App\Notifications\PasswordResetRequest;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\BaseController as BaseController;
use Validator;
use Auth;
use Hash;

class PasswordResetController extends BaseController
{
    public function change(Request $request)
    {
        $user = Auth::user();
        if (Hash::check($request->old_password, $user->password)) {
            if ($request->new_password != $request->confirm_new_password)
                return $this->sendError(translate('Password does not match'));
            $user->password = bcrypt($request->new_password);
            $user->save();
            return $this->sendResponse($user, translate('your password have been changed'));
        } else
            return $this->sendError(translate('the current password is uncorrect'));
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'email' => 'required',
            'otp' => 'required',
        ]);
        if ($validator->fails())
            return $this->sendError('error validation', $validator->errors());
        $pass = PasswordReset::where('code', $request->otp)->first();
        if (!$pass)
            return $this->sendError(translate('ther are no account'));
        User::where('email', $pass->email)->update(['password' => bcrypt($request->password)]);
        $pass->delete();
        return $this->sendResponse('success', translate('your password have been changed'));
    }

    public function create(Request $request)
    {
        $request->validate(['email' => 'required|string|email',]);
        $user = User::where('email', $request->email)->first();
        if (!$user)
            return $this->sendError('ERROR', translate('We can not find a user with that e-mail address'));
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email,],
            ['email' => $user->email, 'token' => Str::random(60), 'code' => mt_rand(00000, 99999)]
        );
        if ($user && $passwordReset)
            $user->notify(new PasswordResetRequest($passwordReset));
        return $this->sendResponse('success', translate('Please check your email. We have e-mailed your password reset link'));

    }
}
