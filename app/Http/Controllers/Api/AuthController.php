<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Broker;
use App\Http\Requests;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use DB, Hash, Mail;

class AuthController extends Controller
{
  private $user;
  private $broker;
  private $jwtauth;

  public function __construct(User $user, JWTAuth $jwtauth, Broker $broker)
{
  $this->user = $user;
  $this->jwtauth = $jwtauth;
  $this->broker = $broker;
}

   public function register(RegisterRequest $request)
  {
    $newUser = $this->user->create([
      'username' => $request->get('username'),
      'email' => $request->get('email'),
      'password' => bcrypt($request->get('password')),
      'is_broker' => ($request->get('is_broker'))

    ]);
     if (!$newUser) {
      return response()->json(['failed_to_create_new_user'], 500);
    }
    else{
    	$verification_code = str_random(30); //Generate verification code
        DB::table('user_verifications')->insert(['user_id'=>$newUser->id,'token'=>$verification_code]);
        $subject = "Please verify your email address.";
        // $email = $newUser->email;
        // $name = $newUser->username;
        // Mail::send('email.verify', ['name' => $newUser->username, 'verification_code' => $verification_code],
        //     function($mail) use ($email, $name, $subject){
        //         $mail->from(getenv('affankhan@gmail.com'), "From User/Company Name Goes Here");
        //         $mail->to($email, $name);
        //         $mail->subject($subject);
        //     });
        return response()->json(['success'=> true, 'message'=> 'Thanks for signing up! Please check your email to complete your registration.']);}
   
    //TODO: implement JWT
     return response()->json($this->jwtauth->setToken($this->jwtauth->fromUser($newUser))->toUser());
    //return response()->json([
   // 'token' => $this->jwtauth->fromUser($newUser)
 // ]);
  }

  public function verifyUser($verification_code)
    {
        $check = DB::table('user_verifications')->where('token',$verification_code)->first();
        if(!is_null($check)){
            $user = User::find($check->user_id);
            if($user->is_verified == 1){
                return response()->json([
                    'success'=> true,
                    'message'=> 'Account already verified..'
                ]);
            }
            $user->update(['is_verified' => 1]);
            DB::table('user_verifications')->where('token',$verification_code)->delete();
            return response()->json([
                'success'=> true,
                'message'=> 'You have successfully verified your email address.'
            ]);
        }
        return response()->json(['success'=> false, 'error'=> "Verification code is invalid."]);
    }


  public function login(LoginRequest $request)
{
  // get user credentials: email, password
  $credentials = $request->only('email', 'password');
  $token = null;
  try {
    $token = $this->jwtauth->attempt($credentials);
    if (!$token) {
      return response()->json(['invalid_email_or_password'], 422);
    }
  } catch (JWTAuthException $e) {
    return response()->json(['failed_to_create_token'], 500);
  }
  return response()->json(compact('token'));
}


public function logout(Request $request) {
        $this->validate($request, ['token' => 'required']);
        try {
            $this->jwtauth->invalidate($request->input('token'));
            return response()->json(['success' => true]);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
    }


    public function add_broker_data(Request $request){

    	$user = $this->jwtauth->parseToken()->authenticate();

    	// If Admin Role
    	if($user->is_admin == 1){
    		$rules = [
            'broker_id' => 'required',
            'broker_username' => 'required',
            'broker_email' => 'required|email',
            'no_coins' => 'required',
            'no_pairs' => 'required',
            'coins' => 'required',
            'pairs' => 'required',
            'country' => 'required|max:55',
            'domain' => 'required|max:75',
        ];
        $input = $request->only(
            'broker_id',
            'broker_username',
            'broker_email',
            'no_coins',
            'no_pairs',
            'coins',
            'pairs',
            'country',
            'domain'
        );
        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['success'=> false, 'error'=> $error]);
        }
        $broker_id = $request->broker_id;
        $broker_username = $request->broker_username;
        $broker_email = $request->broker_email;
        $no_coins = $request->no_coins;
        $no_pairs = $request->no_pairs;
        $coins = $request->coins;
        $pairs = $request->pairs;
        $country = $request->country;
        $domain = $request->domain;

        $added = $this->broker->create(['broker_id' => $broker_id, 'broker_username' => $broker_username, 'broker_email' => $broker_email,'no_coins' => $no_coins,'no_pairs' => $no_pairs,'coins' => $coins,'pairs' => $pairs, 'country' => $country,"domain" => $domain]);
        	if (!$added) {
     		 return response()->json(['failed_to_add_data'], 500);
    		}
    		return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);
    	}

    	if($user->is_broker == 1){
    		$rules = [
            'no_coins' => 'required',
            'no_pairs' => 'required',
            'coins' => 'required',
            'pairs' => 'required',
            'country' => 'required|max:55',
            'domain' => 'required|max:75',
        ];
        $input = $request->only(
            'no_coins',
            'no_pairs',
            'coins',
            'pairs',
            'country',
            'domain'
        );
        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['success'=> false, 'error'=> $error]);
        }
        $broker_id = $user->id;
        $broker_username = $user->username;
        $broker_email = $user->email;
        $no_coins = $request->no_coins;
        $no_pairs = $request->no_pairs;
        $coins = $request->coins;
        $pairs = $request->pairs;
        $country = $request->country;
        $domain = $request->domain;

        $added = $this->broker->create(['broker_id' => $broker_id, 'broker_username' => $broker_username, 'broker_email' => $broker_email,'no_coins' => $no_coins,'no_pairs' => $no_pairs,'coins' => $coins,'pairs' => $pairs, 'country' => $country,"domain" => $domain]);
        	if (!$added) {
     		 return response()->json(['failed_to_add_data'], 500);
    		}
    		return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);
    	}
    }
    public function display(Request $request){
    	$user = $this->jwtauth->parseToken()->authenticate();
		$broker = $this->broker->where('broker_id', $user->id)->get();
		return response()->json($broker);
    }
	}