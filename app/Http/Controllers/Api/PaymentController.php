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
use App\Models\PrizeDistribution;



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

    public function prizeDistribution(Request $request)
    {
        $match_id = $request->match_id;
        $get_join_contest = JoinContest::where('match_id',  $match_id)
          ->get()
          ->transform(function ($item, $key)   {

            $ct = CreateTeam::where('match_id',$item->match_id)
                            ->where('user_id',$item->user_id)
                            ->where('id',$item->created_team_id)
                            ->first(); 

            $user = User::where('id',$item->user_id)->select('id','first_name','last_name','user_name','email','profile_image','validate_user','phone','device_id','name')->first();
             
            $team_id  =   $ct->id;
            $match_id =   $ct->match_id;
            $user_id  =   $ct->user_id;
            $rank     =   $ct->rank; 
            $team_name =  $ct->team_count;
   
            $contest =  CreateContest::with('contestType','defaultContest')
                          ->with(['prizeBreakup'=>function($q) use($rank )
                            {
                              $q->where('rank_from','>=',$rank);
                              $q->orwhere('rank_upto','<=',$rank)->where('rank_from','>=',$rank); 
                            }
                          ]
                        )
                          ->where('match_id',$item->match_id)
                          ->where('id',$item->contest_id) 
                          ->get()
                          ->transform(function ($contestItem, $ckey) use($team_id,$match_id,$user_id,$rank,$team_name)  {
                             
                            if($contestItem->prizeBreakup){
                             $contestItem->prize_amount = $contestItem->prizeBreakup->prize_amount; 
                            }else{
                              $contestItem->prize_amount = 0;
                            }
                             $contestItem->team_id = $team_id;
                             $contestItem->match_id = $match_id;
                             $contestItem->user_id = $user_id;
                             $contestItem->rank = $rank;
                             $contestItem->team_name = $team_name;
                             return $contestItem;
                           });


           // $item->createdTeam = $ct;
            $item->user = $user;
            $item->team_id = $team_id;
            $item->match_id = $match_id;
            $item->user_id = $user_id;
            $item->rank = $rank;
            $item->team_name = $team_name;
            $item->contest  = $contest[0]??null ;
            $item->createdTeam = $ct;
             
            $prize_dist =  PrizeDistribution::updateOrCreate(
                          [
                            'match_id'        => $match_id,
                            'user_id'         => $user_id,
                            'created_team_id' => $team_id
                          ],
                          [
                            'match_id'        => $match_id,
                            'user_id'         => $user_id,
                            'created_team_id' => $team_id,
                            'rank'            => $rank,
                            'contest_id'        => $item->contest_id,

                            'team_name'        => $item->team_name,
                            'user_name'        => $item->user->user_name,
                            'name'             => $item->user->first_name??$item->user->name,
                            'mobile'           => $item->user->phone,
                            'email'            => $item->user->email,
                            'device_id'        => $item->user->device_id,

                            'contest_name'     => $item->contest->contestType->contest_type??null,
                            'entry_fees'       => $item->contest->entry_fees,
                            'total_spots'      => $item->contest->total_spots,
                            'filled_spot'      => $item->contest->filled_spot,

                            'first_prize'        => $item->contest->first_prize,
                            'default_contest_id'=> $item->contest->default_contest_id,
 
                            'prize_amount'      => $item->contest->prize_amount,
                            'contest_type_id'   => $item->contest->prizeBreakup->contest_type_id??null,


                            'captain'           => $item->createdTeam->captain,
                            'vice_captain'      => $item->createdTeam->vice_captain,
                            'trump'             => $item->createdTeam->trump,
                            'match_team_id'     => $item->createdTeam->team_id,
                            'user_teams'        => $item->createdTeam->teams

                          ]
                        );
        }) ; 

        
        $prize_distributions = PrizeDistribution::where('match_id',$match_id)
                                ->get()
                                ->transform(function($item,$key){

                                  $cid = \DB::table('matches')
                                        ->where('match_id',44305)
                                        ->first();
                                     $subject = "You won prize for match - ".$cid->title??null;
                                 
                                 if((int)$item->prize_amount > 0){
                                     $email_content = [
                                            'receipent_email'=> $item->email,
                                            'subject'=>$subject,
                                            'greeting'=> 'SportsFight',
                                            'first_name'=> ucfirst($item->name),
                                            'content' => 'You have won the prize of Rs.<b>'.$item->prize_amount.'</b> for the <b>'.$cid->title.'</b> match.',
                                            'rank' => $item->rank
                                            ];
                                      $helper = new Helper;
                                      $m =   $helper->sendMailFrontEnd($email_content,'prize');

                                      $item->user_id = $item->user_id;
                                      $item->email = $item->email;
                                 }     
                                 return $item;
                                });

        return 'successfully prize distributed';
  

       
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
            $wallet = Wallet::where('user_id',$user->id)
                    ->get(['user_id','bonus_amount','referal_amount','prize_amount','deposit_amount','usable_amount'])
                    ->transform(function($item,$key){ 
                        $transaction = [];
                        $wallet_transactions = \DB::table('wallet_transactions')->where('user_id',$item->user_id)->get();

                        foreach ($wallet_transactions as $key => $value) {
                            $t = json_decode($value->payment_details);
                          
                             $transaction[] =  [
                                'deposit_amount' => $t->deposit_amount,
                                'payment_mode' => $t->payment_mode,
                                'payment_status' => $t->payment_status,
                                'transaction_id' => $t->transaction_id,

                             ];
                        } 
                        $item->transaction = $transaction;
                        return $item;

                    }); 
                     
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
                            "walletInfo"=>null
                        ]
                    );

        }

    }
}
