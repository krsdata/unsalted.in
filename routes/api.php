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

Route::middleware('auth:api')->get('app', function (Request $request) {
	     Route::get('users', 'Api\ApiController@index');

});


// 
// Route::middleware('auth:api')->group( function () {
// 
//     Route::get('user', 'Api\ApiController@index');
// });
 
// 
Route::group([
	    'prefix' => 'v2'
	], function()
    {

    	//Apis used in customer application  
      Route::match(['post','get'],'member/customerLogin', 'Api\UserController@customerLogin');
      Route::match(['post','get'],'member/registration', 'Api\UserController@registration');
			Route::match(['post','get'],'member/updateProfile', 'Api\UserController@updateProfile');
                  

      Route::match(['post','get'],'storeMatchData', 'Api\ApiController@storeMatchData');
     	//Cron 1
     	Route::match(['post','get'],'updateMatchData', 'Api\ApiController@getMatchDataFromApi');
     	// cron 2 and store match by type
     	Route::match(['post','get'],'storeMatchInfo/{fileName}', 'Api\ApiController@storeMatchInfo');

     	// cron 3 and store match by type
     	// url : url/1
     	Route::match(['post','get'],'updateMatchDataByStatus/{status}', 'Api\ApiController@updateMatchDataByStatus');
       	
	
		//mobile API

      	Route::match(['post','get'],'getMatch', 'Api\ApiController@getMatch');
       	
		
		 // if route not found
	    Route::any('{any}', function(){ 
				$data = [
							'status'=>0,
							'code'=>400,
							'message' => 'Bad request'
						];
				return \Response::json($data);

		});
       
});
     
     