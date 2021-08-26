<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Models\PasswordReset;
use App\Notifications\PasswordResetRequest;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\BaseController as BaseController;
use Validator;
use Auth;
use Hash;
class PasswordResetController extends BaseController
{
    
    
    public function change(Request $request){
        $user = Auth::user();
        if (Hash::check($request->old_password, $user->password)) {
          $validator =    Validator::make($request->all(), [
                'new_password' => [ 'required','min:8'],
                'confirm_new_password' => ['same:new_password'],
                   ] );
                    if ($validator -> fails()) {
                       return $this->sendError('error validation', $validator->errors());
                   }
                   $user->password = bcrypt($request->new_password);
                   $user->save();
                  return $this->sendResponse($user ,'your password have been changed');

            }else{
                        return $this->sendError('the current password is uncorrect');

            }
                    return $this->sendError('error an occer');


    }       
    public function reset(Request $request){


               $validator =    Validator::make($request->all(), [
                   'password'=> 'required',
                   'email'=> 'required',
                   'otp'=> 'required',
                   ] );
                   if ($validator -> fails()) {
                       return $this->sendError('error validation', $validator->errors());
                   }
                   $pass = PasswordReset::where('code',$request->otp)->first();
                   if(!$pass) 
                   return $this->sendError('ther are no account');
   
                       
               $user = User::where('email',$pass->email)->update(['password'=>bcrypt($request->password)]);
               $pass->delete();
               
          
            //   $pass->save();
               $success = 'success';
               
   
               return $this->sendResponse($success ,'your password have been changed');
   
           }
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user)
        
                   return $this->sendError('ERROR','We can not find a user with that e-mail address');
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email,],
            [
                'email' => $user->email,
                'token' => Str::random(60),
                'code' => mt_rand(00000,99999)

            ]
        );
        

        if ($user && $passwordReset)
        
            $user->notify(
                new PasswordResetRequest($passwordReset)
            );
            $success = 'success';
  return $this->sendResponse($success,'Please check your email. We have e-mailed your password reset link');
        
    }
}
