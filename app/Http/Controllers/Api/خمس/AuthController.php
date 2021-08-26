<?php /** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api;

use App\Models\BusinessSetting;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Validator;
use App\Session;
use App\Http\Controllers\Api\BaseController as BaseController;

use App\User;
use App\Notifications\EmailVerificationNotification;

class AuthController extends BaseController
{
    public function signup(Request $request)
    {
        // dd($request);
        $validator =    Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6'
        ]);
        if ($validator -> fails()) {
            # code...
            return $this->sendError('error validation', $validator->errors());
        }
       
        
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'phone'=> $request->phone,
            'password' => bcrypt($request->password)
        ]);

        if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
            $user->email_verified_at = date('Y-m-d H:m:s');
        }
        else {
            $user->notify(new EmailVerificationNotification());
        }
        $user->save();

        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();
       $is_verfy = BusinessSetting::where('type','email_verification')->first()->value;
        if($is_verfy != 1){
        return $this->loginRegister( $request,$user->email,$request->password);
        }
        return $this->sendResponse($user , 'Registration Successful. Please verify and log in to your account.');

        // return response()->json([
        //     'message' => 'Registration Successful. Please verify and log in to your account.'
        // ], 201);
    }

    public function login(Request $request)
    {
        $validator =    Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        if ($validator -> fails()) {
            return $this->sendError('error validation', $validator->errors());
        }
        // $credentials = request(['email', 'password']);
        // dd(User::where('phone',$request->phone)->first());
        if (! (Auth::attempt(['email' => $request->email, 'password'=>$request->password]) || Auth::attempt(['phone' => $request->email, 'password'=>$request->password]))  )
        return $this->sendError('error validation',translate('Confirm your phone number,or enter your phone number again') );
        $user = $request->user();
        $is_verfy = BusinessSetting::where('type','email_verification')->first()->value;
        if($is_verfy != 0){
            return $this->sendError('error validation',translate('Please verify your account'));

    }
        $tokenResult = $user->createToken('Personal Access Token');
        return $this->loginSuccess($tokenResult, $user);
    }
    public function loginRegister(Request $request,$email ,$password){

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
        $request->user()->token()->revoke();
        // return response()->json([
        //     'message' => 'Successfully logged out'
        // ]);
        return $this->sendError('error validation',translate('Successfully logged out'));

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
        $success['access_token']=$tokenResult->accessToken;
        if(! $session =Session::where('user_id',$user->id)->first()){
        $session = new Session();
        $session->user_id = $user->id;
        $session->token= $tokenResult->accessToken;
        $session->save();
        }else{
         $session->token= $tokenResult->accessToken;
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
                'country'  => $user->country,
                'city' => $user->city,
                'postal_code' => $user->postal_code,
                'phone' => $user->phone,
    
            ]
        ],'login succeffly');
    }
}
