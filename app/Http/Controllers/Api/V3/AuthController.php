<?php /** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V3;

use App\Models\V3\BusinessSetting;
use App\Models\V3\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Resources\V3\UserResource;
use App\Models\V3\Session;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\V3\User;
use App\Notifications\EmailVerificationNotification;

class AuthController extends BaseController
{
    public function signup(Request $request)
    {
        if (User::where('email', $request->email)->first())
            return $this->sendError(translate('Email is already in use'));
        if (User::where('phone', $request->phone)->first())
            return $this->sendError(translate('Phone is already in use'));
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'fcm_token' => $request->header('X-Client-FCM-Token')
        ]);
        if (BusinessSetting::where('type', 'email_verification')->first()->value != 1)
            $user->email_verified_at = date('Y-m-d H:m:s');
        else
            $user->notify(new EmailVerificationNotification());
        $user->save();
        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();
        $is_verfy = BusinessSetting::where('type', 'email_verification')->first()->value;
        if ($is_verfy != 1)
            return $this->loginRegister($request, $user->email, $request->password);
        return $this->sendResponse($user, translate('Registration Successful. Please verify and log in to your account.'));
    }

    public function login(Request $request)
    {
        if (!(Auth::attempt(['email' => $request->email, 'password' => $request->password]) || Auth::attempt(['phone' => $request->email, 'password' => $request->password])))
            return $this->sendError('error validation', translate('Confirm your phone number,or enter your phone number again'));
        $user = $request->user();
        $user->fcm_token = $request->header('X-Client-FCM-Token');
        $user->save();
        $is_verfy = BusinessSetting::where('type', 'email_verification')->first()->value;
        if ($is_verfy != 0)
            return $this->sendError('error validation', translate('Please verify your account'));
        $tokenResult = $user->createToken('Personal Access Token');
        return $this->loginSuccess($tokenResult, $user);
    }

    public function loginRegister(Request $request, $email, $password)
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password]))
            return response()->json(['message' => 'Unauthorized', 'user' => null], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        return $this->loginSuccess($tokenResult, $user);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->fcm_token = null;
        $request->user()->save();
        $request->user()->token()->revoke();
        return $this->sendError('error validation', translate('Successfully logged out'));
    }

    public function socialLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email'
        ]);
        if (User::where('email', $request->email)->first() != null) {
            $user = User::where('email', $request->email)->first();
        } else {
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'provider_id' => $request->provider,
                'email_verified_at' => Carbon::now()
            ]);
            $user->save();
            $customer = new Customer;
            $customer->user_id = $user->id;
            $customer->save();
        }
        $tokenResult = $user->createToken('Personal Access Token');
        return $this->loginSuccess($tokenResult, $user);
    }

    protected function loginSuccess($tokenResult, $user)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();
        $success['access_token'] = $tokenResult->accessToken;
        if (!$session = Session::where('user_id', $user->id)->first()) {
            $session = new Session();
            $session->user_id = $user->id;
            $session->token = $tokenResult->accessToken;
            $session->save();
        } else {
            $session->token = $tokenResult->accessToken;
            $session->save();
        }
        return $this->sendResponse([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => $user->avatar_original,
                'address' => $user->address,
                'country' => $user->country,
                'city' => $user->city,
                'postal_code' => $user->postal_code,
                'phone' => $user->phone,

            ]
        ], translate('login succeffly'));
    }

    public function userinfo($id)
    {
        $user['data'] = UserResource::collection(User::where('id', $id)->get());
        return $this->sendResponse($user, translate('User Info'));
    }
}
