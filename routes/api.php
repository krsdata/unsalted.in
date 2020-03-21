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
      Route::match(['post','get'],'member/logout', 'Api\UserController@logout'); 
	
      Route::match(['post','get'],'member/logout', 'Api\UserController@logout');

      Route::match(['post','get'],'deviceNotification', 'Api\UserController@deviceNotification');

      Route::match(['post','get'],'sendPushNotification', 'Api\UserController@sendPushNotification');

      Route::match(['post','get'],'transactionHistory', 'Api\PaymentController@transactionHistory');


      Route::match(['post','get'],'temporaryPassword', 'Api\UserController@temporaryPassword');

      
      Route::match(['post','get'],'member/registration', 'Api\UserController@registration');
			Route::match(['post','get'],'member/updateProfile', 'Api\UserController@updateProfile');
      Route::match(['post','get'],'email_verification','Api\UserController@emailVerification'); 
       
     	// cron 2 and store match by type
     	Route::match(['post','get'],'storeMatchInfo/{fileName}', 'Api\ApiController@storeMatchInfo');

     	// cron 3 and store match by type
     	// url : url/1
     		//Cron 1
     	Route::match(['post','get'],'getMatchDataFromApi', 'Api\ApiController@getMatchDataFromApi');
     	Route::match(['post','get'],'updateMatchDataByStatus/{status}', 'Api\ApiController@updateMatchDataByStatus');

     	// cron from backedn
   		Route::match(['post','get'],'getMatchDataFromApiAdmin', 'Api\CronController@getMatchDataFromApi');
     	Route::match(['post','get'],'updateMatchDataByStatusAdmin/{status}', 'Api\CronController@updateMatchDataByStatus');
	
		//mobile API

    	Route::match(['post','get'],'getMatch', 'Api\ApiController@getMatch');
      
      Route::match(['post','get'],'updatePlayerFromCompetition', 'Api\ApiController@updatePlayerFromCompetition');  

      Route::match(['post','get'],'updatePlayerByMatch/{match_id}', 'Api\ApiController@getCompetitionByMatchId');  


	Route::match(['post','get'],'getSquad/{match_id}', 'Api\ApiController@getSquad'); 

	Route::match(['post','get'],'getPlayer', 'Api\ApiController@getPlayer');		

	Route::match(['post','get'],'updateAllSquad', 'Api\ApiController@updateAllSquad');		

	Route::match(['post','get'],'getContestByMatch', 'Api\ApiController@getContestByMatch');
	Route::match(['post','get'],'cloneMyTeam', 'Api\ApiController@cloneMyTeam');

	
	Route::match(['post','get'],'createTeam', 'Api\ApiController@createTeam');

	Route::match(['post','get'],'getMyTeam', 'Api\ApiController@getMyTeam');

	Route::match(['post','get'],'createContest/{match_id}', 'Api\ApiController@createContest');

	Route::match(['post','get'],'updateSquad/{match_id}', 'Api\ApiController@updateSquad');
	Route::match(['post','get'],'joinContest', 'Api\ApiController@joinContest');

	Route::match(['post','get'],'getMyContest', 'Api\ApiController@getMyContest');

	Route::match(['post','get'],'updateMatchDataById/{match_id}', 'Api\ApiController@updateMatchDataById');
	Route::match(['post','get'],'updateMatchInfo', 'Api\ApiController@updateMatchInfo');


	Route::match(['post','get'],'updateLiveMatchFromApp', 'Api\ApiController@updateLiveMatchFromApp');

	
	Route::match(['post','get'],'prizeDistribution', 'Api\PaymentController@prizeDistribution');	
	
	Route::match(['post','get'],'getWallet', 'Api\ApiController@getWallet');	
	Route::match(['post','get'],'addMoney', 'Api\ApiController@addMoney');	
	Route::match(['post','get'],'leaderBoard', 'Api\ApiController@leaderBoard');	

	Route::match(['post','get'],'getPrizeBreakup', 'Api\ApiController@getPrizeBreakup');
	// get point by match ID
	Route::match(['post','get'],'getPoints', 'Api\ApiController@getPoints');
	Route::match(['post','get'],'updatePoints', 'Api\ApiController@updatePoints');
	Route::match(['post','get'],'getPointsByMatch', 'Api\ApiController@getPointsByMatch');
	
	Route::match(['post','get'],'updatePointAfterComplete', 'Api\ApiController@updatePointAfterComplete');
	
	Route::match(['post','get'],'updateUserMatchPoints', 'Api\ApiController@updateUserMatchPoints');

	Route::match(['post','get'],'getContestStat', 'Api\ApiController@getContestStat'); 
	Route::match(['post','get'],'getPrizeBreakup', 'Api\ApiController@prizeBreakup'); 
	
	Route::match(['post','get'],'joinNewContestStatus', 'Api\ApiController@joinNewContestStatus'); 
	
	Route::match(['post','get'],'getScore', 'Api\ApiController@getScore'); 
	
	
	


		    
		
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
     
     