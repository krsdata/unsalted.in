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
use App\Models\ReferralCode;



class PaymentController extends BaseController
{
   
    public $token;
    public $date;

    public function __construct(Request $request) {
        $this->date = date('Y-m-d');
        $this->token = "7f7c1c8df02f5f8c25a405fbbc7d59cf";
        if ($request->header('Content-Type') != "application/json")  {
            $request->headers->set('Content-Type', 'application/json');
        }  
       // $uname      = Helper::generateRandomString(); 
    } 
  /**
    * Check Repeat Rank;
    *
    */
    public function checkReaptedRank($rank, $match_id,$contest_id){
        $rank = JoinContest::where('match_id',$match_id)
                            ->where('contest_id',$contest_id)
                            ->where('ranks',$rank)
                            ->count();
        return $rank; 
    }
  /**
    *@var match_id
    *@var contest_id
    *@var rank
    *Description get Amount as per Rank
    */
    public function getAmountPerRank($rank,$match_id=null,$contest_id=null,$repeat_rank=1)
    {
        $rank_from = $rank; //$rank;
        $rank_to   = $rank+($repeat_rank-1);
      
        $cid = $contest_id;  
        $rank_prize    =    $prizeBreakup = \DB::table('prize_breakups')
                                ->where(function($q) use ($rank,$cid,$rank_to){
                                    $q->where('rank_upto','>=',$rank_to);
                                    $q->where('rank_from','<=',$rank_to);
                                    $q->where('default_contest_id',$cid);

                                })
                                ->orwhere(function($q) use ($rank_from,$rank_to,$cid){
                                    $q->where('rank_from','>=',$rank_from);
                                    $q->where('rank_from','<=',$rank_to);
                                    $q->where('default_contest_id',$cid);
                                }) 
                                ->avg('prize_amount');  
        if($rank_prize){
            return $prizeBreakup;    
        }else{
            return $prizeBreakup=0;
        }
        
    }

  /**
    *@var match_id
    *Description Prize distribution
    */
    public function prizeDistribution(Request $request)
    {  
        $match_id = $request->match_id;  
        $get_join_contest = JoinContest::where('match_id',  $match_id)
          ->where('ranks','!=',0)  
          ->get();
        $get_join_contest->transform(function ($item, $key)   {
            
            $ct = CreateTeam::where('match_id',$item->match_id)
                            ->where('user_id',$item->user_id)
                            ->where('id',$item->created_team_id)
                            ->first();
            
            $user = User::where('id',$item->user_id)->select('id','first_name','last_name','user_name','email','profile_image','validate_user','phone','device_id','name')->first();
             
            $team_id    =   $item->created_team_id;
            $match_id   =   $item->match_id;
            $user_id    =   $item->user_id;
            $rank       =   $item->ranks; 
            $team_name  =   $item->team_count;
            $points     =   $item->points;
            $contest_id =   $item->contest_id;

            $contest    =  CreateContest::with('contestType','defaultContest')
                          ->with(['prizeBreakup'=>function($q) use($rank,$points,$contest_id  )
                            {
                              $q->where('rank_from','>=',$rank);
                              $q->orwhere('rank_upto','<=',$rank)
                              ->where('rank_from','>=',$rank); 
                            }
                          ]
                        )
                          ->where('match_id',$item->match_id)
                          ->where('id',$item->contest_id) 
                          ->where('is_cancelled',0) 
                          ->get() 
                          ->transform(function ($contestItem, $ckey) use($team_id,$match_id,$user_id,$rank,$team_name,$points, $contest_id)  {
                            // check wether rank is repeated
                            
                            $rank_repeat = $this->checkReaptedRank($rank, $match_id,$contest_id);
                            //get average amount in case of repeated rank
                            $rank_amount = $this->getAmountPerRank($rank,$match_id,$contestItem->default_contest_id,$rank_repeat);
                              
                             $contestItem->prize_amount = $rank_amount;
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
            //echo $rank.'-'.$match_id.'-'.$user_id.'-'.$team_id.'<br>';
            $prize_dist =  PrizeDistribution::updateOrCreate(
                          [
                            'match_id'        => $match_id,
                            'user_id'         => $user_id,
                            'created_team_id' => $team_id,
                            'team_name'       => $team_name,
                            'contest_id'       => $item->contest_id
                          ],
                          [
                            'points'          => $points,
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
 
                            'prize_amount'      => $item->contest->prize_amount??0.0,
                            'contest_type_id'   => $item->contest->prizeBreakup->contest_type_id??null,
                            'captain'           => $item->createdTeam->captain,
                            'vice_captain'      => $item->createdTeam->vice_captain,
                            'trump'             => $item->createdTeam->trump,
                            'match_team_id'     => $item->createdTeam->team_id,
                            'user_teams'        => $item->createdTeam->teams

                          ]
                        ); 
        });
        
        /*$data = [
                'action' => 'notify' ,
                'title' => $notification->title,
                'message' => $notification->message
            ];

            $this->sendNotification($device_id,$data);*/


        $prize_distributions = PrizeDistribution::where('match_id',$match_id)
            ->get();
        $cid = \DB::table('matches')
                    ->where('match_id',$match_id)
                    ->select('match_id','title')
                    ->first();

        $puser = PrizeDistribution::where('match_id',$match_id)->pluck('user_id')->toArray();
        $device_id = User::whereIn('id',$puser)->pluck('device_id')->toArray();
        if(count($device_id)){
            $data = [
                'action' => 'notify' ,
                'title' => 'Prize is distributed for '.$cid->title,
                'message' => 'Check your wallets.Prize is available for you respected team'
            ];

            $this->sendNotification($device_id,$data);
        }    
        $prize_distributions->transform(function($item,$key) use($match_id){
              $cid = \DB::table('matches')
                    ->where('match_id',$match_id)
                    ->first();

            $subject = "You won prize for match - ".$cid->title??null;
            if((int)$item->prize_amount > 0){

                $prize_amount = PrizeDistribution::where('match_id',$match_id)
                           ->where('user_id',$item->user_id)->sum('prize_amount');

                $wallets = Wallet::updateOrCreate(
                            [
                                'user_id'       => $item->user_id,
                                'payment_type'  => 4
                            ],
                            [
                                'user_id'       =>  $item->user_id,
                                'validate_user' =>  Hash::make($item->user_id),
                                'payment_type'  =>  4,
                                'payment_type_string' => 'prize',
                                'amount'        =>  $prize_amount,
                                'prize_amount'  =>  $prize_amount,
                                'prize_distributed_id' => $item->id
                            ]
                        );

                $walletsTransaction = WalletTransaction::updateOrCreate(
                            [
                                'user_id'               => $item->user_id,
                                'prize_distributed_id'  => $item->id
                            ],
                            [
                                'user_id'           =>  $item->user_id, 
                                'payment_type'      =>  4,
                                'payment_type_string' => 'prize',
                                'amount'            =>  $item->prize_amount,
                                'prize_distributed_id' => $item->id,
                                'payment_mode'      =>  'sportsfight',
                                'payment_details'   =>  json_encode($item),
                                'payment_status'    =>  'success',
                                'transaction_id'    =>  time().'-'.$item->user_id
                            ]
                        );

                $device_id = $item->device_id;
                $email_content = [ //$item->email
                        'receipent_email'=> $item->email,
                        'subject'=>$subject,
                        'greeting'=> 'SportsFight',
                        'first_name'=> ucfirst($item->name),
                        'content' => 'You have won the prize of Rs.<b>'.$item->prize_amount.'</b> for the <b>'.$cid->title.'</b> match.',
                        'rank' => $item->rank
                        ];

            

                $helper = new Helper;
              //  $m = $helper->sendNotificationMail($email_content,'prize');
                $item->user_id = $item->user_id;
                $item->email = $item->email;
            }   
            return $item;
        });

        $match_id = $request->match_id;  
        \DB::table('matches')->where('match_id',$match_id)->update(['current_status'=>1]);
        
        return  Redirect::to(route('match','prize=true'));
    }
    
    public function sendNotification($tokenList, $data){
     
        $serverLKey = 'AIzaSyAFIO8uE_q7vdcmymsxwmXf-olotQmOCgE';
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

       $extraNotificationData = $data;

       if(is_array($tokenList)){  
            $fcmNotification = [
           'registration_ids' => $tokenList, //multple token array
         //  'to' => $token, //single token
           //'notification' => $notification,
           'data' => $extraNotificationData
        ];
       }else{
            $fcmNotification = [
          // 'registration_ids' => $tokenList, //multple token array
            'to' => $tokenList, //single token
           //'notification' => $notification,
           'data' => $extraNotificationData
       ];
       }

       

       $headers = [
           'Authorization: key='.$serverLKey,
           'Content-Type: application/json'
       ];


       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $fcmUrl);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
       $result = curl_exec($ch);
       //echo "result".$result;
       //die;
       curl_close($ch);
       return true;
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
                    ->select('user_id')
                    ->get()
                    ->transform(function($item,$key){
                        $item->bonus_amount = 0;
                        $item->prize_amount = 0;
                        $item->referral_amount = 0;
                        $item->deposit_amount = 0;
                        
                        $prize_amounts = Wallet::where('user_id',$item->user_id)->get();

                        foreach ($prize_amounts  as $key => $prize_amount) {
                            if($prize_amount->payment_type==1){
                                $item->bonus_amount   = $prize_amount->amount;
                            }
                            elseif($prize_amount->payment_type==4){
                                $item->prize_amount   = $prize_amount->amount;
                            }
                            elseif($prize_amount->payment_type==2){
                                $item->referral_amount = $prize_amount->amount;
                            }
                            elseif($prize_amount->payment_type==3){
                                $item->deposit_amount = $prize_amount->amount;
                            }
                        }
                        

                        $transaction = [];
                        $wallet_transactions = \DB::table('wallet_transactions')->where('user_id',$item->user_id)->orderBy('id','desc')->get();
                        foreach ($wallet_transactions as $key => $value) {
                            
                            $d =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value->created_at, 'UTC')
                                ->setTimezone('Asia/Kolkata')
                                ->format('d-m-Y, h:i A');
                                                    
                             $transaction[] =  [
                                'user_id'        => $item->user_id,
                                'amount'         => $value->amount??$item->deposit_amount,
                                'payment_mode'   => $value->payment_mode??'Online',
                                'payment_status' => $value->payment_status??'success',
                                'transaction_id' => $value->transaction_id??time(),
                                'payment_type'   => $value->payment_type_string??'Deposit',
                                'debit_credit_status' => $value->debit_credit_status,
                                'date'           => $d 

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
                            "transaction_history"=>$wallet[0]??null
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
