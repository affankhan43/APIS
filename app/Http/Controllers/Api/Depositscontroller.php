<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Broker;
use App\Coin;
use App\Coin_address;
use App\Deposit;
use App\Block_hash;
use App\Http\Requests;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;

class Depositscontroller extends Controller
{
    //
  private $user;
  private $broker;
  private $coin;
  private $coin_address;
  private $deposit;
  private $jwtauth;
  private $block_hash;

  public function __construct(User $user, JWTAuth $jwtauth, Broker $broker, Coin_address $coin_address, Coin $coin, Deposit $deposit, Block_hash $block_hash)
{
  $this->user = $user;
  $this->jwtauth = $jwtauth;
  $this->broker = $broker;
  $this->coin = $coin;
  $this->coin_address = $coin_address;
  $this->deposit = $deposit;
  $this->block_hash = $block_hash;
}

	public function check_deposits(Request $request){

		$rules = [
            'coinid' => 'required',
            'coin' => 'required',
            'broker_id' => 'required',
              ];
        $input = $request->only(
            'coinid',
            'coin',
            'broker_id'
        );
        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['success'=> false, 'error'=> $error]);
        }
        else{
        	$coin = $this->coin->where(['id'=>$request->coinid,'coin'=>$request->coin,'broker_id'=>$request->broker_id])->get();
        	if($coin == '[]'){
        		return response()->json(['success'=> false, 'error'=> 'Invalid Coin']);
        	}
        	else{
        			$coin_data = json_encode($coin);
					$coin_data = substr($coin_data,1);
					$coin_data = substr_replace($coin_data,"", -1);
					$coin_data = (json_decode($coin_data,true));
			$blockhash = $this->block_hash->where(['coin_id'=>$request->coinid,'broker_id'=>$request->broker_id,'coin'=>$request->coin])->get();
					$blockdata = json_encode($blockhash);
					$blockdata = substr($blockdata, 1);
					$blockdata = substr_replace($blockdata, "", -1);
					$blockdata = json_decode($blockdata,true);
				$url = $coin_data['third_api'].$blockdata['blockhash'];
			$curl = curl_init();

				curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
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
				$response = json_decode($response,true);
				$length = sizeof($response['transactions']);


				/* ------------------LOOP ------------------- */
			  for ($i=0; $i < $length; $i++) { 
				  	if($response['transactions'][$i]['category'] == 'receive'){
				  		$coin_address = $this->coin_address->where(['broker_id'=>$request->broker_id,'coin_id'=>$request->coinid,'address'=>$response['transactions'][$i]['address']])->get();
				  		$coin_add = json_encode($coin_address);
						$coin_add = substr($coin_add,1);
						$coin_add = substr_replace($coin_add,"", -1);
						$coin_add = (json_decode($coin_add,true));

					$deposit = $this->deposit->where(['broker_id'=>$request->broker_id,'coin_id'=>$request->coinid,'txid'=>$response['transactions'][$i]['txid']])->get();
							$coin_add = json_encode($coin_address);
							$coin_add = substr($coin_add,1);
							$coin_add = substr_replace($coin_add,"", -1);
							$coin_add = (json_decode($coin_add,true));

							/* ---unknown--- */
				  	if($coin_address == "[]"){
				  		if($deposit == "[]"){
				  			$added = $this->deposit->create(['coin_id'=>$request->coinid,'coin'=>$coin_add['coin'],'broker_id'=>$request->broker_id,'broker_username'=>$coin_add['broker_username'],'userid'=>'NULL','username'=>'NULL','address'=>$response['transactions'][$i]['address'],'category'=>'receive','amount'=>$response['transactions'][$i]['amount'],'confirmations'=>$response['transactions'][$i]['confirmations'],'txid'=>$response['transactions'][$i]['txid'],'message'=>$response['transactions'][$i]['blocktime']]);
				  		if (!$added) {
     		 			echo 'error';
    					}

				  	}
				  		else{
				  			$updated = $this->deposit->where(['coin_id'=>$request->coinid,'broker_id'=>$request->broker_id,'txid'=>$response['transactions'][$i]['txid']])->update(['confirmations'=>$response['transactions'][$i]['confirmations']]);
				  			if (!$updated) {
     		 				echo 'error';
    						}
				  		}				  			
					}
					/* ---unknown end--- */

					/* ---user --- */
				  		else{

				  			if($deposit == "[]"){
				  			$added = $this->deposit->create(['coin_id'=>$request->coinid,'coin'=>$coin_add['coin'],'broker_id'=>$request->broker_id,'broker_username'=>$coin_add['broker_username'],'userid'=>$coin_add['userid'],'username'=>$coin_add['username'],'address'=>$response['transactions'][$i]['address'],'category'=>'receive','amount'=>$response['transactions'][$i]['amount'],'confirmations'=>$response['transactions'][$i]['confirmations'],'txid'=>$response['transactions'][$i]['txid'],'message'=>'NULL']);
				  			if (!$added) {
     		 				echo 'error';
    						}
    					  }

    					  else{
				  			$updated = $this->deposit->where(['coin_id'=>$request->coinid,'broker_id'=>$request->broker_id,'txid'=>$response['transactions'][$i]['txid']])->update(['confirmations'=>$response['transactions'][$i]['confirmations']]);
				  			if (!$updated) {
     		 				echo 'error';
    						}
				  		}
				  		}
				  		/* --- user --- */
				  	}
			  }
			  /* ----------------------- Loop end ---------------------- */
			  $add_block = $this->block_hash->where(['broker_id'=>$request->broker_id,'coin_id'=>$request->coinid])->update(['blockhash' =>$response['lastblock']]);
			}
		  }
        }
	}
}
