<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\User;
use Illuminate\Support\Facades\Auth; 
use App\Models\Notification;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Config,Mail,View,Redirect,Validator,Response; 
use Crypt,okie,Hash,Lang,JWTAuth,Input,Closure,URL; 
use App\Helpers\Helper as Helper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use App\Models\Competition;
use App\Models\TeamA;
use App\Models\TeamB;
use App\Models\Toss;
use App\Models\Venue;
use App\Models\Matches;
use App\Models\Player;
use App\Models\TeamASquad;
use App\Models\TeamBSquad;
use App\Models\CreateContest;
use App\Models\CreateTeam;
use App\Models\Wallet;
use App\Models\JoinContest;
use App\Models\WalletTransaction;
use App\Models\MatchPoint;
use App\Models\MatchStat;


class PaymentController extends BaseController
{
   
    public $token;
    public $date;

    public function __construct(Request $request) {

        $this->date = date('Y-m-d');
        $this->token = "8740931958a5c24fed8b66c7609c1c49";

        if ($request->header('Content-Type') != "application/json")  {
            $request->headers->set('Content-Type', 'application/json');
        }  
       // $uname      = Helper::generateRandomString(); 
    } 

    
    // Add Money
    public function addMoney(Request $request){
        
        $myArr = [];
        $user = User::find($request->user_id);
        

        $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'deposit_amount' => 'required',
                'transaction_id' => 'required', 
                'payment_mode' => 'required',
                'payment_status' => 'required'
            ]); 
        
       
        // Return Error Message
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'code' => 201,
                'status' => false,
                'message' => $error_msg
                )
            );
        }

        Log::channel('payment_info')->info($request->all());

        if($user){
            $check_user = Hash::check($user->id,$user->validate_user);    
            
            if($check_user){
                $wallet     = Wallet::where('user_id',$user->id)->first();
                $deposit_amount = (float) $request->deposit_amount;
                $message    = "Amount not added successfully";
                $status     = false;
                $code       = 201;
                if($wallet){
                   \DB::beginTransaction();

                    $wallet->deposit_amount   =  $wallet->deposit_amount+$deposit_amount;
                    $wallet->usable_amount    =  $wallet->usable_amount+$deposit_amount;
                    $wallet->save();
                    
                    $myArr['wallet_amount']   = (float) $wallet->usable_amount; 
                    $myArr['bonus_amount']    = (float)$wallet->bonus_amount;
                    $myArr['user_id']         = (float)$wallet->user_id; 

                    $transaction = new WalletTransaction;
                    $transaction->user_id        =  $request->user_id;
                    $transaction->amount         =  $request->deposit_amount;
                    $transaction->transaction_id =  $request->transaction_id;
                    $transaction->payment_mode   =  $request->payment_mode;
                    $transaction->payment_status =  $request->payment_status;
                    $transaction->payment_details =  json_encode($request->all());
                    $transaction->save();

                    $message    = "Amount added successfully";
                    $status     = true;
                    $code       = 200;
                    \DB::commit();                       
                }
                return response()->json(
                        [ 
                            "status"=>$status,
                            "code"=>$code,
                            "message" =>$message,
                            "walletInfo"=>$myArr
                        ]
                    );    
            }else{
                return response()->json(
                        [ 
                            "status"=>false,
                            "code"=>201,
                            "message" => "user is not valid",
                            "walletInfo"=>$myArr
                        ]
                    );
            }
               
        }else{
            return response()->json(
                        [ 
                            "status"=>false,
                            "code"=>201,
                            "message" => "User is invalid",
                            "walletInfo"=>$myArr
                        ]
                    );
        }
    }

    public function transactionHistory(Request $request){

        $user = User::find($request->user_id);

        if($user){

            $wallet = Wallet::with('transaction')->get();

            return response()->json(
                        [ 
                            "status"=>true,
                            "code"=>200,
                            "message" => "Transaction history",
                            "walletInfo"=>$wallet
                        ]
                    );
        }else{

             return response()->json(
                        [ 
                            "status"=>true,
                            "code"=>200,
                            "message" => "Transaction history",
                            "walletInfo"=>""
                        ]
                    );

        }

    }
}
