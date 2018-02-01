<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', 'Api\AuthController@register');
Route::post('login', 'Api\AuthController@login');
Route::post('checker', 'Api\Depositscontroller@check_deposits');

Route::group(['middleware' => ['jwt.auth']], function() {
    Route::post('logout', 'Api\AuthController@logout');
    Route::post('addbroker', 'Api\AuthController@add_broker_data');
    Route::get('display', 'Api\Coincontroller@display');
	Route::post('addcoin', 'Api\Coincontroller@add_coin');
	Route::post('updatecoin_apis', 'Api\Coincontroller@update_apis');
	Route::post('set_withdraw', 'Api\Coincontroller@withdraw');
	Route::post('get_address', 'Api\Coincontroller@address_generation');
	Route::post('withdraw_req', 'Api\Withdrawcontroller@Withdraw_request');
	Route::post('withdraw_approve', 'Api\Withdrawcontroller@Withdraw_approve');

	// Route::post('login', 'AuthController@login');
    //Route::post('user/deposit/', 'DWController@crypto_deposit');
    //Route::get('credentials', 'AuthController@credentials');
   // Route::get('orders', 'AuthController@crypto_invoices');
    Route::get('test', function(){

        return response()->json(['msg'=>'success']);
    });
});
