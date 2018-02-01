<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Broker;
use App\Coin;
use App\Coin_address;
use App\Withdraw_request;
use App\Http\Requests;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
class Withdrawcontroller extends Controller
{
    //
  private $user;
  private $broker;
  private $coin;
  private $coin_address;
  private $jwtauth;
  private $withdraw_request;

  public function __construct(User $user, JWTAuth $jwtauth, Broker $broker, Coin_address $coin_address, Coin $coin,Withdraw_request $withdraw_request)
{
  $this->user = $user;
  $this->jwtauth = $jwtauth;
  $this->broker = $broker;
  $this->coin = $coin;
  $this->coin_address = $coin_address;
  $this->withdraw_request = $withdraw_request;
}

	public function Withdraw_request(Request $request){
    $user = $this->jwtauth->parseToken()->authenticate();
    
    if($user->is_broker == 1){
      $rules = [
                'coin' => 'required',
                'coinid' => 'required',
                'userid' => 'required',
                'username' => 'required',
                'message' => '',
                'withdraw_address' => 'required'
            ];
            $input = $request->only(
                'broker_id',
                'coin',
                'coinid',
                'userid',
                'username',
                'message',
                'withdraw_address'
            );
            $validator = Validator::make($input, $rules);
            if($validator->fails()) {
                $error = $validator->messages()->toJson();
                return response()->json(['success'=> false, 'error'=> $error]);
            }
            else{
              $coin = $this->coin->where(['broker_id'=>$request->broker_id,'id'=>$request->coinid,'coin'=>$request->coin])->get();
              $maxid = ($this->withdraw_request->max('id'));
              $maxid = $maxid + 1;
              $code = md5($maxid * $request->userid);
              if($coin == "[]"){
                return response()->json(['success'=> false, 'message'=> 'Invalid coin']);
              }
              else {
                  $coin_data = json_encode($coin);
                  $coin_data = substr($coin_data,1);
                  $coin_data = substr_replace($coin_data,"", -1);
                  $coin_data = (json_decode($coin_data,true));
                  if($coin_data['second_api'] == "NULL"){
                    return response()->json(['success'=> false, 'message'=> 'Withdrawals Disabled']);
                  }
                    else{
                $added = $this->withdraw_request->create(['broker_id'=>$user->id,'broker_username'=>$user->username,'coin'=>$request->coin,'coin_id'=>$request->coinid,'withdraw_address'=>$request->withdraw_address,'message'=>$request->message,'userid'=>$request->userid,'username'=>$request->username,'auth_code'=>$code]);
                if (!$added) {
            return response()->json(['failed_to_add_data'], 500);
          }
          else{
            return response()->json($added);
          }
              }
              }

            }
    }
    else {
      return response()->json(['success'=> false, 'message'=> 'Access Denied']);
    
	}
}

  public function withdraw_approve(Request $request){

     $user = $this->jwtauth->parseToken()->authenticate();
    
    if($user->is_broker == 1){
            $rules = [
                'withdraw_id' => 'required',
                'coinid' => 'required',
                'withdraw_code' => 'required',
            ];
            $input = $request->only(
                'withdraw_id',
                'coinid',
                'withdraw_code'
            );
          $validator = Validator::make($input, $rules);
            if($validator->fails()) {
                $error = $validator->messages()->toJson();
                return response()->json(['success'=> false, 'error'=> $error]);
            }
            else{
              $withdraw_request1 = $this->withdraw_request->where(['id'=>$request->withdraw_id])->get();
                  $withdraw_data = json_encode($withdraw_request1);
                  $withdraw_data = substr($withdraw_data,1);
                  $withdraw_data = substr_replace($withdraw_data,"", -1);
                  $withdraw_data = (json_decode($withdraw_data,true));
                  $code = md5($request->withdraw_code);
              $withdraw_request = $this->withdraw_request->where(['id'=>$request->withdraw_id,'coin_id'=>$request->coinid,'auth_code'=>$code,'broker_id'=>$user->id,'userid'=>$withdraw_data['userid']])->get();
                  $withdraw_data1 = json_encode($withdraw_request);
                  $withdraw_data1 = substr($withdraw_data1,1);
                  $withdraw_data1 = substr_replace($withdraw_data1,"", -1);
                  $withdraw_data1 = (json_decode($withdraw_data1,true));
              if($withdraw_request == "[]"){
                return response()->json(['success'=> false, 'message'=> 'Invalid Request']);
              }
              else {
                if($withdraw_data1['status'] == ""){
                  $coin = $this->coin->where(['broker_id'=>$user->id,'id'=>$withdraw_data1['coin_id']])->get();
                  $coin_data = json_encode($coin);
                  $coin_data = substr($coin_data,1);
                  $coin_data = substr_replace($coin_data,"", -1);
                  $coin_data = (json_decode($coin_data,true));
                  $urls = $coin_data['second_api']."a=".$withdraw_data1['withdraw_address']."&amount=".$withdraw_data1['amount']."&key=hsyui&msg=with";
                  $curl = curl_init();

                  curl_setopt_array($curl, array(
                  CURLOPT_URL => $urls,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 60,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "GET",
                  CURLOPT_HTTPHEADER => array(
                    "Cache-Control: no-cache",
                    ),
                ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "Server Error";
      } else {
        $send = $this->withdraw_request->where(['id'=>$withdraw_data1['id']])->update(['details'=>$response,'status'=>'Approved']);
        if (!$send) {
            return response()->json(['failed_to_add_data'], 500);
          }
          else{
            return response()->json($response);
          }
      }
                }
                else {
                  return response()->json(['success'=> false, 'message'=> 'Your withdraw already'.$withdraw_data1['status']]);

                }
              }
            }
          }
        else{
          return response()->json(['success'=> false, 'message'=> 'Access Denied']);
        }
  }
}
