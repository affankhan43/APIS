<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Broker;
use App\Coin;
use App\Coin_address;
use App\Http\Requests;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;

class Coincontroller extends Controller
{
    //
  private $user;
  private $broker;
  private $coin;
  private $coin_address;
  private $jwtauth;

  public function __construct(User $user, JWTAuth $jwtauth, Broker $broker, Coin_address $coin_address, Coin $coin)
{
  $this->user = $user;
  $this->jwtauth = $jwtauth;
  $this->broker = $broker;
  $this->coin = $coin;
  $this->coin_address = $coin_address;
}

	
	// Add coins to exchange
	public function add_coin(Request $request){

		$user = $this->jwtauth->parseToken()->authenticate();
		//$broker = $this->broker->where('broker_id', $user->id)->get();

    	// If Admin Role
    	if($user->is_admin == 1){
    		$rules = [
            'broker_id' => 'required',
            'broker_username' => 'required',
            'coin' => 'required',
            'coin_name' => 'required',
            'withdraw_fees' => 'required',
            'min_withdraw' => 'required|max:55',
        ];
        $input = $request->only(
            'broker_id',
            'broker_username',
            'coin',
            'coin_name',
            'withdraw_fees',
            'min_withdraw'
        );
        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['success'=> false, 'error'=> $error]);
        }
        $broker_id = $request->broker_id;
        $broker_username = $request->broker_username;
        $coin = $request->coin;
        $coin_name = $request->coin_name;
        $withdraw_fees = $request->withdraw_fees;
        $min_withdraw = $request->min_withdraw;
       
        $added = $this->coin->create(['broker_id' => $broker_id, 'broker_username' => $broker_username, 'coin' => $coin,'coin_name' => $coin_name,'withdraw_fees' => $withdraw_fees, 'min_withdraw' => $min_withdraw]);
        	if (!$added) {
     		 return response()->json(['failed_to_add_data'], 500);
    		}
    		return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);
    	}	
    	else{
    		return response()->json(['success'=> false, 'message'=> 'Access Denied']);
    	}
      }

      // Coin Apis
      public function update_apis(Request $request){

      	$user = $this->jwtauth->parseToken()->authenticate();

      	if($user->is_admin == 1){

      		//first_API_Update
      		if($request->msg == "first"){
		      	$rules = [
		            'broker_id' => 'required',
		            'coin' => 'required',
		            'coinid' => 'required',
		            'first_api' => 'required',
		        ];
		        $input = $request->only(
		            'broker_id',
		            'coin',
		            'coinid',
		            'first_api'
		        );
		        $validator = Validator::make($input, $rules);
		        if($validator->fails()) {
		            $error = $validator->messages()->toJson();
		            return response()->json(['success'=> false, 'error'=> $error]);
		        }

		        $broker_id = $request->broker_id;
        		$coin = $request->coin;
        		$coinid = $request->coinid;
        		$first_api = $request->first_api;
		        $updated = $this->coin->where(['broker_id'=>$broker_id,'id'=>$coinid,'coin'=>$coin])->update(['first_api' => $first_api]);
		        if (!$updated) {
     		 		return response()->json(['failed_to_add_data'], 500);
    			}
    		return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);


      		}

      		//Second_API_update
      		elseif($request->msg == "second"){
      			$rules = [
		            'broker_id' => 'required',
		            'coin' => 'required',
		            'coinid' => 'required',
		            'second_api' => 'required',
		        ];
		        $input = $request->only(
		            'broker_id',
		            'coin',
		            'coinid',
		            'second_api'
		        );
		        $validator = Validator::make($input, $rules);
		        if($validator->fails()) {
		            $error = $validator->messages()->toJson();
		            return response()->json(['success'=> false, 'error'=> $error]);
		        }

		        $broker_id = $request->broker_id;
        		$coin = $request->coin;
        		$coinid = $request->coinid;
        		$second_api = $request->second_api;
		        $updated = $this->coin->where(['broker_id'=>$broker_id,'id'=>$coinid,'coin'=>$coin])->update(['second_api' => $second_api]);
		        if (!$updated) {
     		 		return response()->json(['failed_to_add_data'], 500);
    			}
    		return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);
      		}

      		//Both
      		elseif($request->msg == "both"){
      			$rules = [
		            'broker_id' => 'required',
		            'coin' => 'required',
		            'coinid' => 'required',
		            'first_api' => 'required',
		            'second_api' => 'required',
		        ];
		        $input = $request->only(
		            'broker_id',
		            'coin',
		            'coinid',
		            'first_api',
		            'second_api'
		        );
		        $validator = Validator::make($input, $rules);
		        if($validator->fails()) {
		            $error = $validator->messages()->toJson();
		            return response()->json(['success'=> false, 'error'=> $error]);
		        }

		        $broker_id = $request->broker_id;
        		$coin = $request->coin;
        		$coinid = $request->coinid;
        		$first_api = $request->first_api;
        		$second_api = $request->second_api;
		        $updated = $this->coin->where(['broker_id'=>$broker_id,'id'=>$coinid,'coin'=>$coin])->update(['first_api' => $first_api,'second_api'=>$second_api]);
		        if (!$updated) {
     		 		return response()->json(['failed_to_add_data'], 500);
    			}
    		return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);
      		}
      		else {
      			return response()->json(['success'=> false, 'message'=> 'Method Not Found']);
      		}
      	}
      	else{
      		return response()->json(['success'=> false, 'message'=> 'Access Denied']);
      	}

      }


      //Withdraw_fees
      public function withdraw(Request $request){

      		$user = $this->jwtauth->parseToken()->authenticate();

      	if($user->is_admin == 1){

      		//first_API_Update
      		if($request->msg == "fees"){
		      	$rules = [
		            'broker_id' => 'required',
		            'coin' => 'required',
		            'coinid' => 'required',
		            'withdraw_fees' => 'required',
		        ];
		        $input = $request->only(
		            'broker_id',
		            'coin',
		            'coinid',
		            'withdraw_fees'
		        );
		        $validator = Validator::make($input, $rules);
		        if($validator->fails()) {
		            $error = $validator->messages()->toJson();
		            return response()->json(['success'=> false, 'error'=> $error]);
		        }

		        $broker_id = $request->broker_id;
        		$coin = $request->coin;
        		$coinid = $request->coinid;
        		$withdraw_fees = $request->withdraw_fees;
		        $updated = $this->coin->where(['broker_id'=>$broker_id,'id'=>$coinid,'coin'=>$coin])->update(['withdraw_fees' => $withdraw_fees]);
		        if (!$updated) {
     		 		return response()->json(['failed_to_add_data'], 500);
    			}
    		return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);


      		}

      		//Second_API_update
      		elseif($request->msg == "minimum"){
      			$rules = [
		            'broker_id' => 'required',
		            'coin' => 'required',
		            'coinid' => 'required',
		            'min_withdraw' => 'required',
		        ];
		        $input = $request->only(
		            'broker_id',
		            'coin',
		            'coinid',
		            'min_withdraw'
		        );
		        $validator = Validator::make($input, $rules);
		        if($validator->fails()) {
		            $error = $validator->messages()->toJson();
		            return response()->json(['success'=> false, 'error'=> $error]);
		        }

		        $broker_id = $request->broker_id;
        		$coin = $request->coin;
        		$coinid = $request->coinid;
        		$min_withdraw = $request->min_withdraw;
		        $updated = $this->coin->where(['broker_id'=>$broker_id,'id'=>$coinid,'coin'=>$coin])->update(['min_withdraw' => $min_withdraw]);
		        if (!$updated) {
     		 		return response()->json(['failed_to_add_data'], 500);
    			}
    		return response()->json(['success'=> true, 'message'=> 'Thanks for submitting data.']);
      		}
      		
      		else {
      			return response()->json(['success'=> false, 'message'=> 'Method Not Found']);
      		}
      	}
      	else{
      		return response()->json(['success'=> false, 'message'=> 'Access Denied']);
      	}

      }

      public function address_generation(Request $request){

      		$user = $this->jwtauth->parseToken()->authenticate();
      		if($user->is_broker == 1){
      			$rules = [
		            'broker_id' => 'required',
		            'coin' => 'required',
		            'coinid' => 'required',
		            'userid' => 'required',
		            'username' => 'required',
		            'message' => ''
		        ];
		        $input = $request->only(
		            'broker_id',
		            'coin',
		            'coinid',
		            'userid',
		            'username',
		            'message'
		        );
		        $validator = Validator::make($input, $rules);
		        if($validator->fails()) {
		            $error = $validator->messages()->toJson();
		            return response()->json(['success'=> false, 'error'=> $error]);
		        }
		        else{

      			//$broker = $this->broker->where('broker_id', $user->id)->get();
      			$coin = $this->coin->where(['broker_id'=>$request->broker_id,'id'=>$request->coinid,'coin'=>$request->coin])->get();
      			if($coin == "[]"){
      				return response()->json(['success'=> false, 'message'=> 'Invalid coin']);
      			}
      			else{

      				$coin_data = json_encode($coin);
					$coin_data = substr($coin_data,1);
					$coin_data = substr_replace($coin_data,"", -1);
					$coin_data = (json_decode($coin_data,true));

					if($coin_data['first_api'] == "NULL"){
						return response()->json(['success'=> false, 'message'=> 'Deposists Disabled']);
					}
					else{
      				$coin_addresses = $this->coin_address->where(['coin_id'=>$request->coinid,'coin'=>$request->coin,'broker_id'=>$request->broker_id,'broker_username'=>$user->username,'userid'=>$request->userid,'username'=>$request->username])->get();
      				if($coin_addresses == "[]"){
      				
					
					$curl = curl_init();

					curl_setopt_array($curl, array(
				  CURLOPT_URL => $coin_data['first_api'],
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_HTTPHEADER => array(
				    "Cache-Control: no-cache"
				  ),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				if ($err) {
				  echo "Server Error";
				} else {
					
				  $response = (json_decode($response,true));
				  $added =  $this->coin_address->create(['broker_id'=>$user->id,'broker_username'=>$user->username,'coin'=>$coin_data['coin'],'coin_id'=>$coin_data['id'],'address'=>$response['address'],'message'=>$request->message,'userid'=>$request->userid,'username'=>$request->username]);
				  if (!$added) {
     		 		return response()->json(['failed_to_add_data'], 500);
    			}
    			else{
    				return response()->json($added);
    			}

				}
			}
			else{
				return response()->json($coin_addresses);
			}
      			//$coin_address = $this->coin_address->where(['broker_id'=>])->get();
				}
			}
      	}
      	
    }
      else{
      		return response()->json(['success'=> false, 'message'=> 'Access Denied']);
      	}
	}


      //Testing
       public function display(Request $request){
    	$user = $this->jwtauth->parseToken()->authenticate();
		$broker = $this->broker->where('broker_id', $user->id)->get();
		$data = json_encode($broker);
		$data1 = substr($data,1);
		$data1 = substr_replace($data1,"", -1);
		$data12 = (json_decode($data1,true));
		return $data12['id'];
    }
}
