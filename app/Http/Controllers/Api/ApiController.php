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


class ApiController extends BaseController
{
   
    public $token;
    public $date;

    public function __construct(Request $request) {
        $this->date = date('Y-m-d');
        $this->token = "8740931958a5c24fed8b66c7609c1c49";

        if ($request->header('Content-Type') != "application/json")  {
            $request->headers->set('Content-Type', 'application/json');
        }  

        
 
    } 

    public function apkUpdate(Request $request ){

        $version_code = $request->version_code;

        if($version_code){

           $apk_update_status = \DB::table('apk_updates')
                        ->where('version_code','>',$version_code)
                        ->first();
            if($apk_update_status){
                return [
                    'status'        =>  true,
                    'code'          =>  200,
                    'message'       =>  $apk_update_status->message?$apk_update_status->message:'Update is available',
                    'url'           =>  $apk_update_status->url,
                    'title'         =>  $apk_update_status->title,
                    'url'           =>  $apk_update_status->url,
                    'release_note'  =>  $apk_update_status->release_note
                ];
            }else{
                return [
                    'status'        =>  false,
                    'code'          =>  201,
                    'message'       =>  'No update available',
                    'url'           =>  null,
                    'title'         =>  null,
                    'url'           =>  null,
                    'release_note'  =>  null
                ];
            }

        }else{
            return [
                    'status'        =>  false,
                    'code'          =>  201,
                    'message'       =>  'No update available',
                    'url'           =>  null,
                    'title'         =>  null,
                    'url'           =>  null,
                    'release_note'  =>  null
                ];
        }



    }

    public function joinNewContestStatus(Request $request){

        $match_id   = $request->match_id;
        $contest_id = $request->contest_id;

        $cc = CreateContest::where('match_id',$match_id)
                            ->where('id',$contest_id)
                            ->first();

        $create_teams_count = \DB::table('create_teams')
                        ->where('match_id',$match_id)
                        ->where('user_id',$request->user_id)
                        ->count();

        $join_contests_count = \DB::table('join_contests')
                            ->where('match_id',$match_id)
                            ->where('user_id',$request->user_id)
                            ->selectRaw('distinct contest_id')
                            ->get()->count();
                 

        if($cc && ($cc->filled_spot!=0 && $cc->total_spots==$cc->filled_spot)){
             return [
                'status'=>true,
                'code' => 200,
                'message' => 'Contest is full',
                'action'=>3
            ];
        }elseif($create_teams_count > $join_contests_count){
            return [
                'status'=>true,
                'code' => 200,
                'message' => ' Join contest ',
                 'action'=>2
            ];
        }else{
             return [
                'status'=>true,
                'code' => 200,
                'message' => 'create new team to join this contest',
                 'action'=>1
            ];
        }

    }
    
     public function prizeBreakup(Request $request){

        $match_id   = $request->match_id;
        $contest_id = $request->contest_id;
        
        $contest =  CreateContest::where('match_id',$match_id)
                          ->where('id',$contest_id)
                          ->get();
                      
        $contest->transform(function ($item, $key)   {
                          
                    $defaultContest  = \DB::table('prize_breakups')
                                        ->where('default_contest_id',$item->default_contest_id)
                                        ->where('contest_type_id',$item->contest_type)
                                        ->get();
                                     
                        $rank = [];                
                        foreach ($defaultContest as $key => $value) {
                            $prize = $value->prize_amount;
                            if($value->rank_from == $value->rank_upto){
                                $rank_rang = "$value->rank_from";
                            }else{
                                 $rank_rang = $value->rank_from.'-'.$value->rank_upto;
                            }

                            if($item->total_spots==0 && $rank_rang==1){

                                $prize = round(($item->entry_fees*$item->filled_spot)*(0.25));

                                \DB::table('prize_breakups')->where('id',$value->id)
                                            ->update(['prize_amount'=>$prize]);
                            }
                                

                            $rank[] = [
                                    'range' => $rank_rang,
                                    'price' => $prize
                                    ];
                            
                        }

            $item->rank = $rank;
            return $item;
                              
        });
          
        $data['prizeBreakup'] = $contest[0]->rank??null ;
            


        return [
                'status'=>true,
                'code' => 200,
                'message' => 'Prize Breakup',
                'response' => $data
            ];

    }

    public function updateUserMatchPoints(Request $request){
        
        $matches = Matches::where('status',3)
                        ->get();
        $tp = [];
        $data = [];
            
        foreach ($matches as $key => $match) {
                                         
            $join_contests = \DB::table('join_contests')
                        ->where('match_id',$match->match_id)
                        ->select('match_id','created_team_id')
                        ->pluck('created_team_id');
                      //  dd($join_contests);
            $ct = CreateTeam::whereIn('id',$join_contests)
                            ->where('match_id',$match->match_id)
                            ->get();
                              
            foreach ($ct as $key => $value) {
                
                $teams  = json_decode($value->teams);
                $mp     = MatchPoint::where('match_id',$match->match_id)
                                ->get();
                $data['points'] = [];
                foreach ($mp as $key => $result) {
                    if(in_array($result->pid, $teams))
                    {
                        $pt = $result->point;
                        if($value->captain==$result->pid)
                        {
                            $pt = 2*$result->point;
                        }
                        if($value->vice_captain==$result->pid)
                        {   
                            $pt = (1.5)*$result->point;
                            
                        }
                        if($value->trump==$result->pid)
                        {
                            $pt = 3*$result->point;
                        }
                        $data['points'][] = $pt;
                        $p[$result->pid] = $pt;   
                    }
                }
                $total_points = array_sum($data['points']);

                $create_team = CreateTeam::find($value->id);
                $create_team->points = $total_points;
                $create_team->save();
                // update match stat
                $match_stat = MatchStat::firstOrNew(
                    [
                        'match_id'  =>  $value->match_id,
                        'user_id'   =>  $value->user_id,
                        'team_id'   =>  $value->id
                    ]
                 );

                $match_stat->points = $total_points;
                $match_stat->save();

                $tp['team_id:'.$value->id] = $create_team->points;
                $this->updateMatchRankByMatchId($match_stat->match_id);

                $match_stats_team_id = \DB::table('match_stats')
                                ->where('match_id',$match_stat->match_id)
                                ->get();
                               // dd($match_stats_team_id);
                foreach ($match_stats_team_id as $key => $value) {
                    \DB::table('create_teams')
                        ->where('id',$value->team_id)
                        ->update(['rank'=>$value->ranking]);
                }
            }
        }

        return [
                'status'=>true,
                'code' => 200,
                'message' => 'points update',
                'response' => $data
                
            ];     
    }
    // update Ranking
    public function updateMatchRankByMatchId($match_id=null)
    {
        $servername =  env('DB_HOST','localhost');
        $username   =  env('DB_USERNAME','root');
        $password   =  env('DB_PASSWORD','');
        $dbname     =  env('DB_DATABASE','fantasy');
        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        $sql = 'SELECT *, FIND_IN_SET( points, (SELECT GROUP_CONCAT( points ORDER BY points DESC ) FROM match_stats )) AS rank FROM match_stats where match_id='.$match_id.' ORDER BY ranking ASC';
  
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_object($result)) {
               MatchStat::updateOrCreate(
                            [
                                'match_id'  => $row->match_id,
                                'user_id'   => $row->user_id,
                                'team_id'   => $row->team_id
                            ],
                            ['ranking'=>$row->rank]);
            }
        } 
        mysqli_close($conn);

        return ['match_id'=>$match_id];
        
    }

    public function getPoints(request $request){

        $team_id = CreateTeam::find($request->team_id);

         $validator = Validator::make($request->all(), [
                'team_id' => 'required' 
            ]); 
         

        // Return Error Message
        if ($validator->fails() ||  $team_id==null) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'system_time'=>time(),
                'status' => false,
                "code"=> 201,
                'message' => $error_msg[0]??"Team is not available"
                )
            );
        }

        $player_id = json_decode($team_id->teams,true);
        $team_arr  = json_decode($team_id->team_id,true);
                  
 
        $mpObject    =   MatchPoint::where('match_id',$team_id->match_id)->first();
        
        $playerObject = Player::where('match_id',$team_id->match_id)
                    ->whereIn('pid',$player_id);

        $player_team_id = $playerObject->pluck('team_id','pid')->toArray();
          
        if(!$mpObject){

            $captain        =   $team_id->captain;    
            $vice_captain   =   $team_id->vice_captain;
            $trump          =   $team_id->trump; 

            $players =$playerObject->get();

            foreach ($players as $key => $result) {
                    
                    $data[] = [

                    'pid'       => $result->pid,
                    'team_id'   => $result->team_id,
                    'name'      => $result->short_name,
                    'short_name'=> $result->short_name,
                    'points'    => 0,
                    'rating'    => 0,
                    'role'      => $result->playing_role,
                    'captain'   =>  ($captain==$result->pid)?true:false,
                    'vice_captain'   => ($vice_captain==$result->pid)?true:false,
                    'trump'     => ($trump==$result->pid)?true:false
                ];
            }
        }
        $total_points = 0;
        if($team_id && $mpObject!=null)  {
            $teams_id = json_decode($team_id->team_id,true); 
            $captain        =   $team_id->captain;    
            $vice_captain   =   $team_id->vice_captain;
            $trump          =   $team_id->trump; 

            $player_id = json_decode($team_id->teams,true);

            $mpObject = MatchPoint::where('match_id',$team_id->match_id)
                            ->whereIn('pid',$player_id)
                            ->select('match_id','pid','name','role','rating','point','starting11');
            //mp=match point
            foreach ($mpObject->get() as $key => $result) {
                 
                $point = $result->point;
                if($captain==$result->pid){
                    $point = 2*$result->point;
                    $cname = true;
                }
                elseif($vice_captain==$result->pid){   
                    $point = (1.5)*$result->point;
                    $vcname =true;
                }
                elseif($trump==$result->pid){
                    $point = 3*$result->point;
                    $tname = true;
                }

                $array_sum[] = $point;

                $name = explode(' ', $result->name);
                 
                $fname = $name[0]??"";
                $lname = $name[1]??"" ;

                 $fl = strlen(trim($fname.trim($lname)));
                 if($fl<=10){
                     $short_name = $result->short_name;
                 }else{
                    if(strlen($lname)>=10)
                    {
                        $short_name = $lname;
                    }
                    else{
                        $short_name = $fname[0].' '.$lname;  
                    }
                 }  
                $data[] = [
                    'pid'       => $result->pid,
                    'team_id'   => $player_team_id[$result->pid]??null,
                    'name'      => $result->name,
                    'short_name'=> $short_name??$result->name,
                    'points'    => (float)$point,
                    'rating'    => (float)$result->rating,
                    'role'      => $result->role,
                    'captain'   =>  ($captain==$result->pid)?true:false,
                    'vice_captain'   => ($vice_captain==$result->pid)?true:false,
                    'trump'     => ($trump==$result->pid)?true:false
                ];
            } 
            $total_points = array_sum($array_sum); 
        } 
        return [
                'status'=>true,
                'code' => 200,
                "match_id" => $team_id->match_id,
                'message' => 'points update',
                'total_points' => $total_points,
                'response' => [
                        'player_points' => $data 
                    ]                
            ]; 
    }

    public function getPlayerPoints(Request $request){
        
        $mp = MatchPoint::where('match_id',$request->match_id)
                        ->select('match_id','pid','name','role','rating','point','starting11')
                        ->get();

       // $total_points = MatchPoint::where('match_id',$request->match_id)
         //               ->get()
           //             ->sum('point');
        $join_contests = \DB::table('join_contests')
                        ->where('match_id',$request->match_id)
                        ->select('match_id','created_team_id')
                        ->pluck('created_team_id');
       
        $ct = CreateTeam::whereIn('id',$join_contests)
                        ->where('match_id',$request->match_id)
                        ->get();
        $pp     = [];
        $data   = [];        
        foreach ($ct as $key => $value) {
            $teams  = json_decode($value->teams);
            $mp     = MatchPoint::where('match_id',$request->match_id)
                            ->get();
            
            foreach ($mp as $key => $result) {
                if(in_array($result->pid, $teams))
                {
                    $pt = $result->point;
                    if($value->captain==$result->pid){
                        $pt = 2*$result->point;
                    }
                    if($value->vice_captain==$result->pid){   
                        $pt = (1.5)*$result->point;
                    }
                    if($value->trump==$result->pid){
                        $pt = 3*$result->point;
                    }
                    $data['points'][] = $pt;
                }
            }

            if($mp && isset($data['points'])){

                    $total_points         = array_sum($data['points']);
                $create_team          = CreateTeam::find($value->id);
                $create_team->points  = $total_points;
                $create_team->save();

                $match_stat = MatchStat::firstOrNew(
                    [
                        'match_id'  =>  $value->match_id,
                        'user_id'   =>  $value->user_id,
                        'team_id'   =>  $value->id
                    ]
                 );

                $match_stat->points = $total_points;
                $match_stat->save();


                $pp['user_id_'.$value->user_id][$value->team_count] = $total_points;
            }

            
        }

        
        return [
                'status'=>true,
                'code' => 200,
                'message' => 'points update',
                'response' => $pp
                
            ];  
        
    }

    // update points by LIVE Match
    public function updatePointAfterComplete(Request $request){
        $matches = Matches::whereIn('status',[2,3])
                ->where('timestamp_start','>=',strtotime("-1 days"))
                ->get();
        foreach ($matches as $key => $match) {   # code...
            
            $points = file_get_contents('https://rest.entitysport.com/v2/matches/'.$match->match_id.'/point?token='.$this->token);
            $points_json = json_decode($points);
            $m = [];    
            foreach ($points_json->response->points as $team => $teams) {
                if($teams==""){
                    continue;
                }
                foreach ($teams as $key => $players) {
                    foreach ($players as $key => $result) {
                        $result->match_id = $match->match_id;
                        if($result->pid==null){
                            continue;
                        }
                       $m[] = MatchPoint::updateOrCreate(
                            ['match_id'=>$match->match_id,'pid'=>$result->pid],
                            (array)$result);

                    }
                }
            }
        }
        
        echo 'points_updated';
    }
    // update points by LIVE Match
    public function updatePoints(Request $request){
        $matches = Matches::where('status',3)
                        ->get();


        foreach ($matches as $key => $match) {   # code...
            
            $points = file_get_contents('https://rest.entitysport.com/v2/matches/'.$match->match_id.'/point?token='.$this->token);
            $points_json = json_decode($points);
            $m = [];    
            foreach ($points_json->response->points as $team => $teams) {
                if($teams==""){
                    continue;
                }
                foreach ($teams as $key => $players) {
                    foreach ($players as $key => $result) {
                        $result->match_id = $match->match_id;
                        if($result->pid==null){
                            continue;
                        }
                       $m[] = MatchPoint::updateOrCreate(
                            ['match_id'=>$match->match_id,'pid'=>$result->pid],
                            (array)$result);

                    }
                }
            }
        }
        
        echo 'points_updated';
    }

    public function getContestStat(Request $request){

        $match_stat =  MatchPoint::with(['player' => function($q){
                    $q->with('team_a');
                    $q->with('team_b');
                }])
                ->where('match_id',$request->match_id)
                ->select('match_id','pid','name','rating','point','role')
                ->get();
        $data = [];
        foreach ($match_stat as $key => $stat) {
            
            if(isset($stat->player->team_a)){
                $team_name = $stat->player->team_a->short_name;
            }
            if(isset($stat->player->team_b)){
                $team_name = $stat->player->team_b->short_name;
            }

            $data[] = [
                'match_id' => $stat->match_id,
                'pid' => $stat->pid,
                'rating' => $stat->rating,
                'point' => $stat->point,
                'role' => strtoupper($stat->role),
                'team_id' => $stat->player->team_id,
                'player_name' => $stat->player->short_name,
                'team_name' => $team_name
            ];
            
        }

         return [
                'status'=>true,
                'code' => 200,
                'message' => 'contestStat',
                'response' => ['contestStat'=>$data]
                
            ];

    }
    // update points by LIVE Match ID
    public function getPointsByMatch(Request $request){
        
        $points = file_get_contents('https://rest.entitysport.com/v2/matches/'.$request->match_id.'/point?token='.$this->token);
        $points_json = json_decode($points);
       // dd($points_json->response->points);
        foreach ($points_json->response->points as $team => $teams) {
            foreach ($teams as $key => $players) {
                foreach ($players as $key => $result) {
                    $result->match_id = $request->match_id;
                    if($result->pid==null){
                        continue;
                    }
                  //  dd($result);
                   $m[] = MatchPoint::updateOrCreate(
                        ['match_id'=>$request->match_id,'pid'=>$result->pid],
                        (array)$result);
                }
            }
        }
        return ['points'=>$m];
    }
    //LeaderBoard
    public function leaderBoard(Request $request){
       // $join_contests = [];
        $join_contests = JoinContest::where('match_id',$request->get('match_id'))
                                ->where('contest_id',$request->get('contest_id'))
                                ->pluck('created_team_id')->toArray();
            
        $leader_board = CreateTeam::with('user')
                        ->where('match_id',$request->match_id)
                        ->whereIn('id',$join_contests)
                        ->select('match_id','id as team_id','user_id','team_count as team','points as point','rank')
                        ->orderBy('rank','ASC')
                        ->get();

        if($leader_board){
            return [
                'status'=>true,
                'code' => 200,
                'message' => 'leaderBoard',
                'leaderBoard' =>$leader_board
                
            ];    
        }else{
            return [
                'status'=>false,
                'code' => 201,
                'message' => 'leaderBoard not available'
            ];
        }

    }

     /*
     @method : createTeam

    */
    public function getMyTeam(Request $request){

        $match_id =  $request->match_id;
        $user_id  =  $request->user_id;


        $userVald = User::find($request->user_id);
        $matchVald = Matches::where('match_id',$request->match_id)->count();

        if(!$userVald || !$matchVald){
            return [
                'status'=>false,
                'code' => 201,
                'message' => 'user id or match id is invalid'
                
            ];
        }

        $myTeam   =  CreateTeam::where('match_id',$match_id)
                                ->where('user_id',$user_id )
                                ->get();
        
        $user_name = User::find($user_id);
        $data = [];    
        foreach ($myTeam as $key => $result) {
            
            $team_id =  json_decode($result->team_id,true); 
            $teams = json_decode($result->teams,true); 
            if($team_id==null or $teams==null){
                continue;
            }

            $captain = $result->captain;
            $trump = $result->trump;
            $vice_captain = $result->vice_captain;
            $team_count = $result->team_count;
            $team_count = $result->team_count;
            $user_id = $result->user_id;
            $match_id = $result->match_id;
           
            $k['created_team'] = ['team_id' => $result->id]; 
           
            $player = Player::WhereIn('team_id',$team_id)
                            ->whereIn('pid',$teams)
                            ->where('match_id',$result->match_id)
                            ->get();
                            
             foreach ($player as $key => $value) {
                
                if($value->playing_role=="wkbat"){
                    $team_role["wk"][] = $value->pid;  
                }else{
                    $team_role[$value->playing_role][] = $value->pid;
                }
                
            }  
           //dd($team_role);
            foreach ($team_role as $key => $value) {
                 
                     $k[$key] = $value;
            }  
            $team_role = [];
            $c = Player::WhereIn('team_id',$team_id)
                            ->whereIn('pid',[$captain,$vice_captain,$trump])
                            ->where('match_id',$result->match_id)
                            ->pluck('short_name','pid');
            
           $k['c'] = ['pid'=> (int)$captain,'name' => $c[$captain]];
           $k['vc'] = ['pid'=>(int)$vice_captain,'name' => $c[$vice_captain]];
           $k['t'] = ['pid'=>(int)$trump,'name' => $c[$trump]];


           $t_a = TeamA::WhereIn('team_id',$team_id) 
                            ->where('match_id',$result->match_id)
                            ->first();
            $t_b = TeamB::WhereIn('team_id',$team_id) 
                            ->where('match_id',$result->match_id)
                            ->first();

            $tac = Player::Where('team_id',$t_a->team_id)
                            ->whereIn('pid',$teams)
                            ->where('match_id',$result->match_id)
                            ->get();
            $tbc = Player::Where('team_id',$t_b->team_id)
                            ->whereIn('pid',$teams)
                            ->where('match_id',$result->match_id)
                            ->get();
            // team count with name                 
            $t[]   = ['name' => $t_a->short_name, 'count' => $tac->count()]; 
            $t[]   = ['name' => $t_b->short_name, 'count' => $tbc->count()];
            

            $k['match']         = [$t_a->short_name.'-'.$t_b->short_name];
            $k['team']          = $t; 
            $k['c_img']         = "";
            $k['vc_img']        = "";
            $k['t_img']         = "";
            // username
            $k['team_name'] =  $user_name->user_name. '('.$result->team_count.')';

            $data[] = $k;
            $t = [];
             
       }

        return response()->json(
                            [ 
                                "status"=>true,
                                "code"=>200,
                                "teamCount" => $myTeam->count(),
                                "message"=>"success",
                                "response"=>["myteam"=>$data]
                            ]
                        );
    }
    /*
     @method : createTeam

    */
    public function createTeam(Request $request){
        
        $ct = CreateTeam::firstOrNew(['id'=>$request->create_team_id]);
        Log::channel('before_create_team')->info($request->all());
        if($request->create_team_id){

            if($ct->id==null){
                return [
                    'status'=>false,
                    'code' => 201,
                    'message' => 'Team list is empty!'
                    
                ];
            }
        } 

        $team_count = CreateTeam::where('user_id',$request->user_id)
                        ->where('match_id',$request->match_id)->count();
        if($team_count>=11){
            return [
                    'status'=>false,
                    'code' => 201,
                    'message' => 'Max team limit exceeded'
                    
                ];
        }

        $userVald = User::find($request->user_id);
        $matchVald = Matches::where('match_id',$request->match_id)->first();

        if($matchVald){
            $timestamp = $matchVald->timestamp_start;
            $t = time();
            if($t > $timestamp){
                 return [
                    'status'=>false,
                    'code' => 201,
                    'message' => 'Match time up'
                    
                ];
            } 
        } 

        if(!$userVald || !$matchVald){
            return [
                'status'=>false,
                'code' => 201,
                'message' => 'user_id or match_id is invalid'
                
            ];
        }

                
        if($request->create_team_id==null){
            $c_t = CreateTeam::where( 
                        'match_id',$request->match_id)
                ->where('user_id' , $request->user_id)
                ->count(); 

            $t_count = $c_t+1;

            $ct->team_count = "T".$t_count;  
        }
        
        $ct->match_id       = $request->match_id;
        $ct->contest_id     = $request->contest_id;
        $ct->team_id        = json_encode($request->team_id);
        $ct->teams          = json_encode($request->teams);
        $ct->captain        = $request->captain;
        $ct->vice_captain   = $request->vice_captain;
        $ct->trump          = $request->trump;
        $ct->user_id        = $request->user_id;

        if($request->create_team_id){
            $ct->edit_team_count = $ct->edit_team_count+1;
        }


        try {
            $ct->save();
            $ct->team_id  = $request->team_id; 
            $ct->create_team_id  = $ct->id;

            Log::channel('after_create_team')->info($request->all());
            return response()->json(
                            [ 
                                "status"=>true,
                                "code"=>200,
                                "message"=>"Success",
                                "response"=>["matchconteam"=>$ct]
                            ]
                        );

        } catch (QueryException $e) {
             
            return response()->json(
                            [ 
                                "status"=>false,
                                "code"=>201,
                                "message"=>"Failed"
                            ]
                        );
        }
    }


     public function updateContestByMatch($match_id=null){

        $default_contest = \DB::table('default_contents')
                            ->where('match_id',$match_id)
                            ->get();

        foreach ($default_contest as $key => $result) {
            $createContest = CreateContest::firstOrNew(
                    [
                        'match_id'          =>  $match_id,
                        'contest_type'      =>  $result->contest_type,
                        'entry_fees'        =>  $result->entry_fees,
                        'total_spots'       =>  $result->total_spots,
                        'first_prize'       =>  $result->first_prize

                    ]
                );  

            $createContest->match_id            =   $match_id;
            $createContest->contest_type        =   $result->contest_type;
            $createContest->total_winning_prize =   $result->total_winning_prize;
            $createContest->entry_fees          =   $result->entry_fees;
            $createContest->total_spots         =   $result->total_spots;
            $createContest->first_prize         =   $result->first_prize;
            $createContest->winner_percentage   =   $result->winner_percentage;
            $createContest->cancellation        =   $result->cancellation;
            $createContest->default_contest_id  =   $result->id;
            $createContest->save();
            return true;
        }       
           
    }
    // crrate contest dyanamic
    public function createContest($match_id=null){

        $default_contest = \DB::table('default_contents')
                            ->whereNull('match_id')
                            ->get();

        foreach ($default_contest as $key => $result) {
            $createContest = CreateContest::firstOrNew(
                    [
                        'match_id'          =>  $match_id,
                        'contest_type'      =>  $result->contest_type,
                        'entry_fees'        =>  $result->entry_fees,
                        'total_spots'       =>  $result->total_spots,
                        'first_prize'       =>  $result->first_prize

                    ]
                );  

            $createContest->match_id            =   $match_id;
            $createContest->contest_type        =   $result->contest_type;
            $createContest->total_winning_prize =   $result->total_winning_prize;
            $createContest->entry_fees          =   $result->entry_fees;
            $createContest->total_spots         =   $result->total_spots;
            $createContest->first_prize         =   $result->first_prize;
            $createContest->winner_percentage   =   $result->winner_percentage;
            $createContest->cancellation        =   $result->cancellation;
            $createContest->default_contest_id  =   $result->id;
            $createContest->save();

            $default_contest_id = \DB::table('default_contents')
                            ->where('match_id',$match_id)
                            ->get();

            if($default_contest_id){
                foreach ($default_contest_id as $key => $value) {
                    $this->updateContestByMatch($match_id);
                }
            }
            
        }       
           
    }
    // get contest details by match id
    public function getContestByMatch(Request $request){
 
        $match_id =  $request->match_id;
        
        $matchVald = Matches::where('match_id',$request->match_id)->count();

        if(!$matchVald){
            return [
                'system_time'=>time(),
                'status'=>false,
                'code' => 201,
                'message' => 'match id is invalid'
                
            ];
        } 
        
        $validator = Validator::make($request->all(), [
              //  'match_id' => 'required' 
            ]); 
                 
        // Return Error Message
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'system_time'=>time(),
                'status' => false,
                "code"=> 201,
                'message' => $error_msg[0]
                )
            );
        }

        $contest = CreateContest::with('contestType')
                    ->where('match_id',$match_id)
                    ->orderBy('contest_type','ASC')
                    ->get();
        if($contest){
            $matchcontests = [];
            foreach ($contest as $key => $result) {  
                   // dd($result);
                    $data2['totalSpots'] =   $result->total_spots;
                    $data2['firstPrice'] =   $result->first_prize;
                    $data2['totalWinningPrize'] =    $result->total_winning_prize;
                    if($result->total_spots==0)
                    {
                       $data2['totalSpots'] =   0;
                       
                       $twp = round(($result->filled_spot)*($result->entry_fees)*(0.5));
                       $data2['totalWinningPrize'] = round(($result->filled_spot)*($result->entry_fees)*(0.5));

                       $data2['firstPrice'] =   round($twp*(0.2));
                    }
                    elseif($result->total_spots!=0 && $result->filled_spot==$result->total_spots)
                    {
                        continue;
                    }
                    $data2['contestId'] =    $result->id;
                    
                    $data2['entryFees'] =    $result->entry_fees;
                    
                    $data2['filledSpots'] =  $result->filled_spot;
                   
                    $data2['winnerPercentage'] = $result->winner_percentage;
                    $data2['maxAllowedTeam'] =   $result->contestType->max_entries;
                    $data2['cancellation'] = $result->contestType->cancellable; 
                $matchcontests[$result->contest_type][] = [
                    'contestTitle'=>$result->contestType->contest_type,
                    'contestSubTitle'=>$result->contestType->description,
                    'contests'=>$data2
                ]; 
            }
            $data = [];

             foreach ($matchcontests as $key => $value) {
                   
                  foreach ($value as $key2 => $value2) {
                       $k['contestTitle'] = $value2['contestTitle'];
                       $k['contestSubTitle'] = $value2['contestSubTitle'];
                       $k['contests'][] = $value2['contests']; 
                  }
                  $data[] = $k;
                  $k= [];
             }


             // $join_contests = \DB::table('join_contests')
             //                ->where('match_id',$request->match_id)
             //                ->where('user_id',$request->user_id)
             //                ->select('created_team_id as team_id','id as joined_contest_id')
             //                ->get();

             $join_contests = \DB::table('create_teams')
                            ->where('match_id',$request->match_id)
                            ->where('user_id',$request->user_id)
                            ->select('id as team_id')
                            ->get();


            $myjoinedContest = $this->myJoinedContest($request->match_id,$request->user_id);
            return response()->json(
                            [ 
                               'system_time'=>time(),
                                "status"=>true,
                                "code"=>200,
                                "message"=>"Success",
                                "response"=>[
                                    'matchcontests'=>$data,
                                    'myjoinedTeams' =>$join_contests,
                                    'myjoinedContest' => $myjoinedContest
                                    ]
                            ]
                        );
        }
    }

    public function getMatchDataFromApi()
    {

        //upcoming
        $upcoming =    file_get_contents('https://rest.entitysport.com/v2/matches/?status=1&token='.$this->token);

        \File::put(public_path('/upload/json/upcoming.txt'),$upcoming);

        //complted
        $completed =    file_get_contents('https://rest.entitysport.com/v2/matches/?status=2&token='.$this->token);

        \File::put(public_path('/upload/json/completed.txt'),$completed);

        //live
        $live =    file_get_contents('https://rest.entitysport.com/v2/matches/?status=3&token='.$this->token);

        \File::put(public_path('/upload/json/live.txt'),$live);

        return ['file updated'];
    } 

    public function updateMatchDataById($match_id=null)
    {
        //upcoming

        $data =    file_get_contents('https://rest.entitysport.com/v2/matches/'.$match_id.'/info?token='.$this->token);

        $this->saveMatchDataById($data);
        return [$match_id.' : match id updated successfully'];

    }

    public function updateMatchInfo(Request $request)
    {
        //upcoming
        $match_id = $request->match_id;
        $matches =  Matches::where('status',3)
                ->where('timestamp_start','>=',strtotime("-1 days"))
                ->where('timestamp_start','<=',time())
                ->get(); 
        foreach ($matches as $key => $match) {
            
            $data =    file_get_contents('https://rest.entitysport.com/v2/matches/'.$match->match_id.'/info?token='.$this->token);
            $this->saveMatchDataFromAPI2DB($data);
        }

        return [$matches->count().' Match is updated successfully'];

    }
    public function updateLiveMatchFromApp()
    {
        //upcoming
        $match = Matches::where('status',3)->get();
        foreach ($match as $key => $result) {
            
        $data =    file_get_contents('https://rest.entitysport.com/v2/matches/'.$result->match_id.'/info?token='.$this->token);

        $this->saveMatchDataById($data);
        }
        return [' Live match  updated successfully'];

    }

    public function updateMatchDataByStatus($status=1)
    {
        if($status==1){
            $fileName="upcoming";
        }
        elseif($status==2){
            $fileName="completed";
        }
        elseif($status==3){
            $fileName="live";
        }elseif($status==4){
            $fileName="cancelled";
        }
        else{
            return ['data not available'];
        }

        //upcoming
        $data =    file_get_contents('https://rest.entitysport.com/v2/matches/?status='.$status.'&token='.$this->token.'&per_page=20');
          
        \File::put(public_path('/upload/json/'.$fileName.'.txt'),$data);
        
        $data = $this->storeMatchInfo($fileName);

        return $this->saveMatchDataFromAPI($data);

        return [$fileName.' match data updated successfully'];

    } 

    //get file data from local
    public function getJsonFromLocal($path=null)
    {
        return json_decode(file_get_contents($path));
    }

    // store by match type
    public function storeMatchInfo($fileName=null){
        if($fileName){
            $files = [$fileName]; 
        }else{
            $files = ['live','completed','upcoming']; 
        }
        try {
            if(in_array($fileName, $files)){
                 return $this->getJsonFromLocal(public_path('/upload/json/'.$fileName.'.txt'));
            }
            
        } catch (Exception $e) {
              //  dd($e);
        }
        return ['match info stored'];
    }

    public function saveMatchDataById($data){
        $data = json_decode($data);

        if(isset($data->response)){

            $result_set = $data->response;
             
            foreach ($result_set as $key => $rs) {
                $data_set[$key] = $rs;
            }  
                $remove_data = ['toss','venue','teama','teamb','competition'];
               
                $matches = Matches::firstOrNew(['match_id' => $data_set['match_id']]);
                
                foreach ($data_set as $key => $value) {
                    
                    if(in_array($key, $remove_data)){
                        continue;
                    }
                    $matches->$key = $value;
                }
                $matches->save();       
            }
      //  
        return ["match info updated "];

    }

    public function saveMatchDataFromAPI2DB($data){
         
         $data = json_decode($data);

         if(isset($data->response)){
            $result_set = $data->response;
            $mid = [];
          //  foreach ($results as $key => $result_set) {

                    if($result_set->format==5   or $result_set->format==17){
                       // continue;
                    }
                    foreach ($result_set as $key => $rs) {
                        $data_set[$key] = $rs;
                    }    
                        $competition = Competition::firstOrNew(['match_id' => $data_set['match_id']]);
                        $competition->match_id   = $data_set['match_id'];

                        foreach ($data_set['competition'] as $key => $value) {
                            $competition->$key = $value;
                        }
                        $competition->save();
                        $competition_id = $competition->id;

                        /*TEAM A*/
                        $team_a = TeamA::firstOrNew(['match_id' => $data_set['match_id']]);
                        $team_a->match_id   = $data_set['match_id'];

                        foreach ($data_set['teama'] as $key => $value) {
                            $team_a->$key = $value;
                        }

                        $team_a->save();

                        $team_a_id = $team_a->id;


                        /*TEAM B*/
                        $team_b = TeamB::firstOrNew(['match_id' => $data_set['match_id']]);
                        $team_b->match_id   = $data_set['match_id'];

                        foreach ($data_set['teamb'] as $key => $value) {
                            $team_b->$key = $value;
                        }

                        $team_b->save();
                        $team_b_id = $team_b->id;


                          /*Venue */
                        $venue = Venue::firstOrNew(['match_id' => $data_set['match_id']]);
                        $venue->match_id   = $data_set['match_id'];

                        foreach ($data_set['venue'] as $key => $value) {
                            $venue->$key = $value;
                        }

                        $venue->save();
                        $venue_id = $venue->id;


                          /*Venue */
                        $toss = Toss::firstOrNew(['match_id' => $data_set['match_id']]);
                        $toss->match_id   = $data_set['match_id'];

                        foreach ($data_set['toss'] as $key => $value) {
                            $toss->$key = $value;
                        }

                        $toss->save();
                        $toss_id = $toss->id;
                 
                        $remove_data = ['toss','venue','teama','teamb','competition'];

                       
                        $matches = Matches::firstOrNew(['match_id' => $data_set['match_id']]);
                        
                        foreach ($data_set as $key => $value) {
                            
                            if(in_array($key, $remove_data)){
                                continue;
                            }
                            $matches->$key = $value;

                        }
                        $matches->toss_id = $toss_id;
                        $matches->venue_id = $venue_id;
                        $matches->teama_id = $team_a_id;
                        $matches->teamb_id = $team_b_id;
                        $matches->competition_id = $toss_id;

                        $matches->save();

                        $mid[] = $data_set['match_id'];
                        $this->createContest($data_set['match_id']);
                      //  
           // }            

            if(count($mid)){
                $this->getSquad($mid);
               // $this->saveSquad($mid);
            }
            
        }  
      //  
        return [$mid,"match info updated "];
    }

    public function saveMatchDataFromAPI($data){
        
        if(isset($data->response) && count($data->response->items)){

            $results = $data->response->items;
            $mid = [];
            foreach ($results as $key => $result_set) {
                    if($result_set->format==5   or $result_set->format==17){
                        continue;
                    }
                    foreach ($result_set as $key => $rs) {
                        $data_set[$key] = $rs;
                    }    
                        $competition = Competition::firstOrNew(['match_id' => $data_set['match_id']]);
                        $competition->match_id   = $data_set['match_id'];

                        foreach ($data_set['competition'] as $key => $value) {
                            $competition->$key = $value;
                        }
                        $competition->save();
                        $competition_id = $competition->id;

                        /*TEAM A*/
                        $team_a = TeamA::firstOrNew(['match_id' => $data_set['match_id']]);
                        $team_a->match_id   = $data_set['match_id'];

                        foreach ($data_set['teama'] as $key => $value) {
                            $team_a->$key = $value;
                        }

                        $team_a->save();

                        $team_a_id = $team_a->id;


                        /*TEAM B*/
                        $team_b = TeamB::firstOrNew(['match_id' => $data_set['match_id']]);
                        $team_b->match_id   = $data_set['match_id'];

                        foreach ($data_set['teamb'] as $key => $value) {
                            $team_b->$key = $value;
                        }

                        $team_b->save();
                        $team_b_id = $team_b->id;


                          /*Venue */
                        $venue = Venue::firstOrNew(['match_id' => $data_set['match_id']]);
                        $venue->match_id   = $data_set['match_id'];

                        foreach ($data_set['venue'] as $key => $value) {
                            $venue->$key = $value;
                        }

                        $venue->save();
                        $venue_id = $venue->id;


                          /*Venue */
                        $toss = Toss::firstOrNew(['match_id' => $data_set['match_id']]);
                        $toss->match_id   = $data_set['match_id'];

                        foreach ($data_set['toss'] as $key => $value) {
                            $toss->$key = $value;
                        }

                        $toss->save();
                        $toss_id = $toss->id;
                 
                        $remove_data = ['toss','venue','teama','teamb','competition'];

                       
                        $matches = Matches::firstOrNew(['match_id' => $data_set['match_id']]);
                        
                        foreach ($data_set as $key => $value) {
                            
                            if(in_array($key, $remove_data)){
                                continue;
                            }
                            $matches->$key = $value;

                        }
                        $matches->toss_id = $toss_id;
                        $matches->venue_id = $venue_id;
                        $matches->teama_id = $team_a_id;
                        $matches->teamb_id = $team_b_id;
                        $matches->competition_id = $toss_id;

                        $matches->save();

                        $mid[] = $data_set['match_id'];
                        $this->createContest($data_set['match_id']);
                      //  
            }            

            if(count($mid)){
                $this->getSquad($mid);
               // $this->saveSquad($mid);
            }
            
        }  
      //  
        return ["match info updated "];

    }

    public function saveSquad($match_ids=null){
        foreach ($match_ids as $key => $match_id) {
            # code...
            $cid = Competition::where('match_id',$match_id)->first();

            $token =  $this->token;
            $path = 'https://rest.entitysport.com/v2/competitions/'.$cid->cid.'/squads/'.$match_id.'?token='.$this->token;

            $data = $this->getJsonFromLocal($path); 

            foreach ($data->response->squads as $key => $pvalue) {
                
                if(!isset($pvalue->players)){
                    continue;
                }

                foreach ($pvalue->players as $key2 => $results) {

                    $data_set =   Player::firstOrNew(
                            [
                                'pid'       =>  $results->pid,
                                'team_id'   =>  $pvalue->team_id,
                                'match_id'  =>  $match_id
                            ]
                        );

                     foreach ($results as $key => $value) {
                        if($key=="primary_team"){
                            continue;
                            $data_set->$key = json_encode($value);
                        } 
                        $data_set->$key         =   $value;
                        $data_set->match_id     =   $match_id; 
                        $data_set->team_id      =   $pvalue->team_id;
                        $data_set->cid          =   $cid->cid;

                    }

                     $data_set->save();
                     
                }  
            }
        }
        echo "player saved";
        //return ['saved'];
    }

     public function updateSquad($match_id=null){

            # code...
            $cid = Competition::where('match_id',$match_id)->first();

            $token =  $this->token;
            $path = 'https://rest.entitysport.com/v2/competitions/'.$cid->cid.'/squads/'.$match_id.'?token='.$this->token;

            $data = $this->getJsonFromLocal($path); 

            foreach ($data->response->squads as $key => $pvalue) {
               if(!isset($pvalue->players)){
                    continue;
                }

                foreach ($pvalue->players as $key2 => $results) {

                    
                    $data_set =   Player::firstOrNew(
                            [
                                'pid'=>$results->pid,
                                'team_id'=>$pvalue->team_id,
                                'match_id'=>$match_id
                            ]
                        );


                     foreach ($results as $key => $value) {
                        if($key=="primary_team"){
                            continue;
                            $data_set->$key = json_encode($value);
                        } 
                        $data_set->$key  =  $value;
                        $data_set->match_id  =  $match_id; 
                        $data_set->team_id = $pvalue->team_id;
                    }
                     $data_set->save();
                }  
            }
        
        echo "played saved";
        //return ['saved'];
    }


    // get Match by status and all
    public function getMatch(Request $request){

        //$status =  $request->status;
        $user = $request->user_id;

        $banner = \DB::table('banners')->select('title','url','actiontype')->get();

        $join_contests =  \DB::table('join_contests')->where('user_id',$user)->get('match_id');
        $jm = [];


        $created_team = \DB::table('create_teams')
                        ->where('user_id',$user) 
                        ->select('match_id','user_id','id')
                        ->orderBy('id','DESC')
                        ->distinct('match_id')
                        ->get(); 

        if($created_team->count()){  
            foreach ($created_team as $key => $join_contest) {
                # code...
               $jmatches = Matches::with('teama','teamb')->where('match_id',$join_contest->match_id)->select('match_id','title','short_title','status','status_str','timestamp_start','timestamp_end','game_state','game_state_str');

               $join_match = $jmatches->first();
               $join_match_count[] = $jmatches->count();
            //   $join_contest_count = $jmatches->count();

               $join_contests_count =  \DB::table('join_contests')
                            ->where('user_id',$user)
                            ->where('match_id',$join_contest->match_id)
                            ->selectRaw('distinct contest_id')
                            ->get();
                          
                $join_match->total_joined_team   =  count($join_match_count);
                $join_match->total_join_contests =  $join_contests_count->count();
                $jm[$join_contest->match_id] = $join_match;
            }

            $data['matchdata'][] = [
                    'viewType'=>1,
                   // 'total_joined_team' => $join_match_count,
                   // 'total_join_contests' => $join_contests_count,
                    'joinedmatches'=>array_values($jm)
                ];
            
        }                                          
        $match = Matches::with('teama','teamb')
                ->whereIn('status',[1,3])
                ->select('match_id','title','short_title','status','status_str','timestamp_start','timestamp_end','date_start','date_end','game_state','game_state_str')
                ->orderBy('timestamp_start','ASC')
             ->where('timestamp_start','>=' , time())
             ->limit(10)
            ->get();


        $data['matchdata'][] = ['viewType'=>2,'banners'=>$banner];
        $data['matchdata'][] = ['viewType'=>3,'upcomingmatches'=>$match];

        return ['total_result'=>count($match),'status'=>true,'code'=>'200','message'=>'success','system_time'=>time(),'response'=>$data];
    }

    public function getAllCompetition(){
        $com = \DB::table('competitions')->select('id','match_id','cid')->get()->toArray();
        return $com;
    }

    // get players
    public function getPlayer(Request $request)
    {
        $match_id =  $request->get('match_id');

        $matchVald = Matches::where('match_id',$request->match_id)->count();

        if(!$matchVald){
            return [
                'status'=>false,
                'code' => 201,
                'message' => ' match_id is invalid'
                
            ];
        }

         $players =  Player::with(['teama'=>function($q) use ($match_id){
                     $q->where('match_id',$match_id);
                    }])
                    ->with(['teamb'=>function($q)use ($match_id){
                     $q->where('match_id',$match_id);
                     }])
                    ->with('team_b','team_a')
                    ->where(function($q) use($match_id){
                        $q->groupBy('playing_role');
                        $q->where('match_id',$match_id);
                    })->get(); 
               
        if(!$players->count()){  
            return ['status'=>'true','code'=>404,'message'=>'record not found',
                    'response'=>[
                        'players'=>[]
                    ]
                ];
        }
         $rs['wk'] = [];
         $bat['bat'] = [];
         $bat['all'] = [];
         $bat['bowl'] = [];

         $match_points= MatchPoint::where('match_id',$match_id)->pluck('point','pid')->toArray();
 
         foreach ($players as $key => $results) { 
            if($results->teama ){
               
                $data['playing11'] =  filter_var($results->teama->playing11, FILTER_VALIDATE_BOOLEAN); 

             }
             elseif($results->teamb){
                 
                 $data['playing11'] =  filter_var($results->teamb->playing11, FILTER_VALIDATE_BOOLEAN);
             }
              
            if($results->team_a){
                    $data['team_name'] = $results->team_a->short_name;
             }else{
                 
                 $data['team_name'] = $results->team_b->short_name;
             } 

             $data['pid'] = $results->pid;
             $data['match_id'] = $results->match_id;
             $data['team_id'] = $results->team_id;
             $data['points'] = ($match_points[$results->pid])??0;
             $fname = $results->first_name;
             $lname = $results->last_name;

             $fl = strlen(trim($fname.trim($lname)));
             if($fl<=10){
                 $data['short_name'] = $results->short_name;
             }else{
                if(strlen($lname)>=10)
                {
                    $data['short_name'] = $lname;
                }
                else{
                    $data['short_name'] = $fname[0].' '.$lname;  
                }
             }
             $data['fantasy_player_rating'] = $results->fantasy_player_rating;
             if($results->playing_role=="wkbat")
             {
                 $rs['wk'][]  = $data; 
             }else{
                 $rs[$results->playing_role][]  = $data; 
             }

             $data = [];
         }
       

         return Response::json([
                    'system_time'=>time(),
                    'status'=>'true',
                    'code'=>200,
                    'message'=>'success',
                    'response'=>[
                        'players'=>$rs
                    ]
                ]);
    }
    // update player by match_id

    public function getSquad($match_ids=null){

        foreach ($match_ids as $key => $match_id) {
            # code...
            $t1 =  date('h:i:s');
            $token =  $this->token;
            $path = 'https://rest.entitysport.com/v2/matches/'.$match_id.'/squads/?token='.$token;


                $data = $this->getJsonFromLocal($path); 

                // update team a players
                $teama = $data->response->teama;
                foreach ($teama->squads as $key => $squads) {

                    $teama_obj = TeamASquad::firstOrNew(
                        [
                            'team_id'=>$teama->team_id,
                            'player_id'=>$squads->player_id,
                            'match_id'=>$match_id
                        ]
                    );

                    $teama_obj->team_id   =  $teama->team_id;
                    $teama_obj->player_id =  $squads->player_id;
                    $teama_obj->role      =  $squads->role;
                    $teama_obj->role_str  =  $squads->role_str;
                    $teama_obj->playing11 =  $squads->playing11;
                    $teama_obj->name      =  $squads->name;
                    $teama_obj->match_id  =  $match_id;

                    $teama_obj->save();
                    $team_id[$squads->player_id] = $teama->team_id;
                 }  

                $teamb = $data->response->teamb;
                foreach ($teamb->squads as $key => $squads) {

                      $teamb_obj = TeamBSquad::firstOrNew(['team_id'=>$teamb->team_id,'player_id'=>$squads->player_id,'match_id'=>$match_id]);
                      
                      $teamb_obj->team_id   =  $teamb->team_id;
                      $teamb_obj->player_id =  $squads->player_id;
                      $teamb_obj->role      =  $squads->role;
                      $teamb_obj->role_str  =  $squads->role_str;
                      $teamb_obj->playing11 =  $squads->playing11;
                      $teamb_obj->name      =  $squads->name;
                      $teamb_obj->match_id  =  $match_id;
                      $teamb_obj->save();

                      $team_id[$squads->player_id] = $teamb->team_id;
                 }  
                 // update all players
                foreach ($data->response->players as $pkey => $pvalue) 
                {

                    $data_set =   Player::firstOrNew(
                                [
                                    'pid'=>$pvalue->pid,
                                    'team_id'=>$team_id[$pvalue->pid],
                                    'match_id'=>$match_id
                                ]
                            );

                    foreach ($pvalue as $key => $value) {
                        if($key=="primary_team"){
                            continue;
                            $data_set->$key = json_encode($value);
                        } 
                        $data_set->$key  =  $value;
                        $data_set->match_id  =  $match_id;
                        $data_set->pid = $pvalue->pid;
                        $data_set->team_id = $team_id[$pvalue->pid];
                    }

                    $data_set->save();                             
                } 

                $t2=  date('h:i:s'); 
        }           
    }

    public function getCompetitionByMatchId($match_id=null){
        $d['start_time'] = date('d-m-Y h:i:s A'); 
        $com = \DB::table('competitions')
                ->select('id','match_id','cid')
                ->where(function($query) use ($match_id){
                    $query->where('match_id',$match_id);
                })->get()->toArray();
         
        $token = $this->token ;         
        $players = [];   

        foreach ($com as $key => $result) {

             $path = 'https://rest.entitysport.com/v2/competitions/'.$result->cid.'/squads/?token='.$token;

            $data = $this->getJsonFromLocal($path);
           
            if(isset($data->response->squads)){
                foreach ($data->response->squads as $key => $rs) {  
                    if($rs->players){

                         foreach ($rs->players as $pkey => $pvalue) {
                              
                            $data_set =   Player::firstOrNew(['pid'=>$pvalue->pid]); 
                             foreach ($pvalue as $key => $value) {

                                if($key=="primary_team"){
                                    continue;
                                    $data_set->$key = json_encode($value);
                                }

                                $data_set->$key = $value;
                             }
                            $data_set->match_id = $result->match_id;
                            $data_set->cid = $result->cid;
                            if($rs->team_id){
                                $data_set->team_id = $rs->team_id;
                            } 
                            $data_set->save();  
                         } 

                    }

                    
               }  

            }

        } 
        $d['end_time'] = date('d-m-Y h:i:s A');
        $d['message'] ="Player information updated";
        $d['status'] ="ok"; 
         return  $d;
    }
    

    public function updateAllSquad(){
        echo date('h:i:s').'--time--';
       
        $com =  Matches::where('status',3)->select('match_id')->get();
        $players = []; 
        
        foreach ($com as $key => $value) {
            $this->getSquad([$value->match_id]); 
        }

        echo date('h:i:s');  
    }


    public function  joinContest(Request  $request)
    {
        $match_id           = $request->match_id;
        $user_id            = $request->user_id;
        $created_team_id    = $request->created_team_id;
        $contest_id         = $request->contest_id;

         $validator = Validator::make($request->all(), [
                'match_id' => 'required',
                'user_id' => 'required',
                'contest_id' => 'required',
                'created_team_id' => 'required'

            ]); 
         
        
        // Return Error Message
        if ($validator->fails() || !isset($created_team_id)) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
            
            return Response::json(array(
                'system_time'=>time(),
                'status' => false,
                "code"=> 201,
                'message' => $error_msg[0]??'Team id missing'
                )
            );
        }

        Log::channel('before_join_contest')->info($request->all());            
        
        $check_join_contest = \DB::table('join_contests')
                                ->whereIn('created_team_id',$created_team_id)
                                ->where('match_id',$match_id)
                                ->where('user_id',$user_id)
                                ->where('contest_id',$contest_id)
                                ->get();
         
        if(count($created_team_id)==1 AND  $check_join_contest->count()==1){
            return [
                'status'=>false,
                'code' => 201,
                'message' => 'This team already Joined'
                
            ];
        }
        
        $cc = CreateContest::find($contest_id);

        if($cc && ($cc->total_spots!=0 && $cc->filled_spot>=$cc->total_spots)){
            return [
                'status'=>false,
                'code' => 201,
                'message' => 'This contest already full'
                
            ];
        }
        $userVald = User::find($request->user_id);
        $matchVald = Matches::where('match_id',$request->match_id)->count();

        if(!$userVald || !$matchVald || !$contest_id){
            return [
                'status'=>false,
                'code' => 201,
                'message' => 'user_id or match_id or contest_id is invalid'
                
            ];
        }
        
        $data = [];
        $cont = [];

        $ct = \DB::table('create_teams')
                    ->whereIn('id',$created_team_id)->count();

        if($ct)
        {  
            \DB::beginTransaction();
            foreach ($created_team_id as $key => $ct_id) {
              

                $check_join_contest = \DB::table('join_contests')
                                ->where('created_team_id',$ct_id)
                                ->where('match_id',$match_id)
                                ->where('user_id',$user_id)
                                ->where('contest_id',$contest_id)
                                ->first();
                                
                if($check_join_contest){ 

                    continue;
                } 
                $data['match_id'] = $match_id;
                $data['user_id'] = $user_id;
                $data['created_team_id'] = $ct_id;
                $data['contest_id'] = $contest_id;
                
                $ctid  = CreateTeam::find($ct_id); 
                $data['team_count'] = $ctid->team_count??null;

                if($cc->total_spots==0 || $cc->total_spots > $cc->filled_spot){
                   
                    $cc->filled_spot = CreateTeam::where('match_id',$match_id)
                                        ->where('team_join_status',1)->count();
                    $cc->save();
                    // payment deduct

                    $total_fee                  =  $cc->entry_fees;
                    $deduct_from_bonus          =  $total_fee*(0.1);
                    $deduct_from_usable_amount  =  $total_fee-$deduct_from_bonus;

                    $wallets = Wallet::where('user_id',$user_id)->first();

                    $wallets->usable_amount = $wallets->usable_amount-$deduct_from_usable_amount;
                    $wallets->bonus_amount = $wallets->bonus_amount-$deduct_from_bonus;
                    $wallets->save();

                }else{

                    continue;
                }

                $jcc = \DB::table('join_contests')
                        ->where('match_id',$match_id)
                        ->where('contest_id',$contest_id)
                        ->count(); 
                if($jcc<=$cc->total_spots || $cc->total_spots==0){
                    
                $t =   JoinContest::updateOrCreate($data,$data); 
 
                }        
                // End spot count
                $cont[] = $data;
                $ct = \DB::table('create_teams')
                       ->where('id',$ct_id)
                  ->update(['team_join_status'=>1]); 

                $cc->filled_spot = CreateTeam::where('match_id',$match_id)
                                        ->where('team_join_status',1)->count();
                $cc->save();
              
            }
             \DB::commit();

        }else{
            $cont = ["error"=>"contest id not found"];
        }    
       Log::channel('after_join_contest')->info($cont);
             
       return response()->json(
                            [ 
                                "status"=>true,
                                "code"=>200,
                                "message"=>"success",
                                "response"=>["joinedcontest"=>$cont]
                            ]
                        );
    }


    // get contest details by match id
    public function getMyContest(Request $request){

        $match_id =  $request->match_id;
        
        $matchVald = Matches::where('match_id',$request->match_id)->count();

        if(!$matchVald){
            return [
                'system_time'=>time(),
                'status'=>false,
                'code' => 201,
                'message' => 'match id is invalid'
                
            ];
        }

        $join_contests = JoinContest::where('user_id',$request->user_id)
                            ->where('match_id',$match_id)
                            ->pluck('contest_id')->toArray(); 
                           
        $validator = Validator::make($request->all(), [
              //  'match_id' => 'required' 
            ]); 
         
        // Return Error Message
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'system_time'=>time(),
                'status' => false,
                "code"=> 201,
                'message' => $error_msg[0]
                )
            );
        }

        $contest = CreateContest::with('contestType')
                    ->where('match_id',$match_id)
                    ->whereIn('id',$join_contests)
                    ->orderBy('contest_type','ASC')
                    ->get();
 
        if($contest){
            $matchcontests = [];
            
            foreach ($contest as $key => $result) { 
                    $myjoinedContest = $this->myJoinedTeam($request->match_id,$request->user_id,$result->id);
             
                   // dd($result);
                    $data2['totalSpots'] =   $result->total_spots;
                    $data2['firstPrice'] =   $result->first_prize;
                    $data2['totalWinningPrize'] =    $result->total_winning_prize;
                    if($result->total_spots==0)
                    {
                       $data2['totalSpots'] =   0;
                       
                       $twp = round(($result->filled_spot)*($result->entry_fees)*(0.5));
                       $data2['totalWinningPrize'] = round(($result->filled_spot)*($result->entry_fees)*(0.5));

                       $data2['firstPrice'] =   round($twp*(0.2));
                    }
                    elseif($result->total_spots!=0 && $result->filled_spot==$result->total_spots)
                    {
                        continue;
                    }

                    $data2['contestTitle'] = $result->contestType->contest_type;
                    $data2['contestSubTitle'] =$result->contestType->description;
                    $data2['contestId'] =    $result->id;
                  //  $data2['totalWinningPrize'] =    $result->total_winning_prize;
                    $data2['entryFees'] =    $result->entry_fees;
                   // $data2['totalSpots'] =   $result->total_spots;
                    $data2['filledSpots'] =  $result->filled_spot;
                  //  $data2['firstPrice'] =   $result->first_prize;
                    $data2['winnerPercentage'] = $result->winner_percentage;
                    $data2['maxAllowedTeam'] =   $result->contestType->max_entries;
                    $data2['cancellation'] = $result->cancellable; 
                    $data2['maxEntries'] =  $result->contestType->max_entries;
                    $data2['joinedTeams'] = $myjoinedContest;


                $matchcontests[] = $data2;
            } 
            $data = $matchcontests;

            return response()->json(
                            [ 
                               'system_time'=>time(),
                                "status"=>true,
                                "code"=>200,
                                "message"=>"Success",
                                "response"=>[
                                    'my_joined_contest'=>$data
                                    ]
                            ]
                        );
        }
    }


    public function getMyContest2(Request $request)
    {
        $match_id =  $request->match_id;
        $user_id  = $request->user_id;

        $userVald = User::find($request->user_id);
        $matchVald = Matches::where('match_id',$request->match_id)->count();

        if(!$userVald || !$matchVald){
            return [
                'status'=>false,
                'code' => 201,
                'message' => 'user_id or match_id is invalid'
            ];
        }

        $check_my_contest = \DB::table('join_contests')
                ->where('match_id',$match_id)
                ->where('user_id',$user_id);


        $contest_id = $check_my_contest->pluck('contest_id');

        $myContest  =     $check_my_contest->first();

        if(!$myContest){
            return response()->json(
                            [ 
                                "status"=>false,
                                "code"=>201,
                                "message"=>"Contest details not found"
                            ]
                        );
        }
 
        $joinMyContest =  JoinContest::with('createTeam','contest')
                            ->where('match_id',$match_id)
                            ->where('user_id',$user_id)
                          //  ->whereIn('created_team_id',$contest_id)
                            ->whereIn('contest_id',$contest_id)
                            ->get();

        if($joinMyContest){
            $matchcontests = [];
            
            foreach ($joinMyContest as $key => $result) {  
                    $t_c = $result->createTeam->team_count;
                    $data2['teamName'] = ($userVald->first_name??$userVald->name).'('.$t_c.')'; 
                   // $data2['team'] = $result->createTeam->team_count;
                    $data2['createdTeamId'] =    $result->created_team_id;
                    $data2['contestId'] =    $result->contest_id;
                    $data2['totalWinningPrize'] =    $result->contest->total_winning_prize;
                    $data2['entryFees'] =    $result->contest->entry_fees;
                    $data2['totalSpots'] =   $result->contest->total_spots;
                    $data2['filledSpots'] =  $result->contest->filled_spot;
                    $data2['firstPrice'] =   $result->contest->first_prize;
                    $data2['winnerPercentage'] = $result->contest->winner_percentage;
                    $data2['cancellation'] = $result->contest->cancellable; 
                    $contest_type_id = $result->contest->contest_type;

                    $contestType = \DB::table('contest_types')
                            ->where('id',$contest_type_id)
                            ->first();

                    $data2['maxEntries'] = $contestType->max_entries; 
                            
                    $matchcontests[$result->contest_type][] = [
                        'contestTitle'=>$contestType->contest_type,
                        'contestSubTitle'=>$contestType->description,
                        'contests'=>$data2
                    ]; 
            }

            $data = [];
             foreach ($matchcontests as $key => $value) {
                   
                  foreach ($value as $key2 => $value2) {
                       $k['contestTitle'] = $value2['contestTitle'];
                       $k['contestSubTitle'] = $value2['contestSubTitle'];
                       $k['contests'][] = $value2['contests']; 
                  }
                  $data[] = $k;
                  $k= [];
             }
             if($data){
                 return response()->json(
                            [ 
                                'system_time'=>time(),
                                "status"=>true,
                                "code"=>200,
                                "message"=>"Success",
                                "response"=>['my_joined_contest'=>$data]
                            ]
                        );
              }else{
                 return response()->json(
                            [ 
                                'system_time'=>time(),
                                "status"=>false,
                                "code"=>404,
                                "message"=>"record not found"
                            ]
                        );
              }
           
        }
    }

    public function myJoinedTeam($match_id=null,$user_id=null,$contest_id=null)
    {
/*
        $check_my_contest = \DB::table('join_contests')
                ->where('match_id',$match_id)
                ->where('user_id',$user_id);


        $created_team_id = $check_my_contest->pluck('created_team_id')->toArray();
        $contest_id      = $check_my_contest->pluck('contest_id')->toArray();
        $myContest       =     $check_my_contest->first();*/

    
        $joinMyContest =  JoinContest::with('createTeam','contest')
                            ->where('match_id',$match_id)
                            ->where('user_id',$user_id)
                            ->where('contest_id',$contest_id)
                            ->get();
        $userVald = User::find($user_id);
        if($joinMyContest){
            $matchcontests = [];
            
            foreach ($joinMyContest as $key => $result) {  
                    $t_c = $result->createTeam->team_count;
                    $data2['team_name'] = ($userVald->user_name).'('.$t_c.')'; 
                   // $data2['team'] = $result->createTeam->team_count;
                    $data2['createdTeamId'] =    $result->created_team_id;
                    $data2['contestId'] =    $result->contest_id;
                    $data2['isWinning'] =    filter_var($result->createTeam->isWinning??'false', FILTER_VALIDATE_BOOLEAN); 
                    $data2['rank']      = $result->createTeam->rank;
                    $data2['points']    = $result->createTeam->points; 
                    $matchcontests[] =  $data2 ;
                    $data2 = [];
            }
              
             return $matchcontests;
           
        }
    }
    public function myJoinedContest($match_id=null,$user_id=null)
    {

        $check_my_contest = \DB::table('join_contests')
                ->where('match_id',$match_id)
                ->where('user_id',$user_id);


        $contest_id = $check_my_contest->pluck('created_team_id');
        $myContest  =     $check_my_contest->first();

 
        $joinMyContest =  JoinContest::with('createTeam','contest')
                            ->where('match_id',$match_id)
                            ->where('user_id',$user_id)
                            ->whereIn('created_team_id',$contest_id)
                            ->get();
        $userVald = User::find($user_id);
        if($joinMyContest){
            $matchcontests = [];
            
            foreach ($joinMyContest as $key => $result) {  
                    $t_c = $result->createTeam->team_count;
                    $data2['teamName'] = ($userVald->first_name??$userVald->name).'('.$t_c.')'; 
                   // $data2['team'] = $result->createTeam->team_count;
                    $data2['createdTeamId'] =    $result->created_team_id;
                    $data2['contestId'] =    $result->contest_id;
                    $data2['totalWinningPrize'] =    $result->contest->total_winning_prize;
                    $data2['entryFees'] =    $result->contest->entry_fees;
                    $data2['totalSpots'] =   $result->contest->total_spots;
                    $data2['filledSpots'] =  $result->contest->filled_spot;
                    $data2['firstPrice'] =   $result->contest->first_prize;
                    $data2['winnerPercentage'] = $result->contest->winner_percentage;
                    $data2['cancellation'] = $result->contest->cancellable; 
                    $contest_type_id = $result->contest->contest_type;

                    $contestType = \DB::table('contest_types')
                            ->where('id',$contest_type_id)
                            ->first();

                    $data2['maxEntries'] = $contestType->max_entries; 
                            
                    $matchcontests[$result->contest_type][] = [
                        'contestTitle'=>$contestType->contest_type,
                        'contestSubTitle'=>$contestType->description,
                        'contests'=>$data2
                    ]; 
            }

            $data = [];
             foreach ($matchcontests as $key => $value) {
                   
                  foreach ($value as $key2 => $value2) {
                       $k['contestTitle'] = $value2['contestTitle'];
                       $k['contestSubTitle'] = $value2['contestSubTitle'];
                       $k['contests'][] = $value2['contests']; 
                  }
                  $data[] = $k;
                  $k= [];
             }
             
             return $data;
           
        }
    }
    
     //Added by manoj
    public function getWallet(Request $request){
      $myArr = array();

      $user_id = User::find($request->user_id);
      $wallet = Wallet::where('user_id',$request->user_id)->first();
      if($wallet){
        $myArr['wallet_amount']   = (float) $wallet->usable_amount; 
        $myArr['bonus_amount']    = (float)$wallet->bonus_amount;
        $myArr['user_id']         = (float)$wallet->user_id;  
      }
      
     return response()->json(
                        [ 
                            "status"=>true,
                            "code"=>200,
                            "walletInfo"=>$myArr
                        ]
                    );
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

    public function getScore(Request $request){


        $score = Matches::with(['teama' => function ($query) {
            $query->select('match_id', 'team_id', 'name','short_name','scores_full','scores','overs');
        }])
        ->with(['teamb' => function ($query) {
            $query->select('match_id', 'team_id', 'name','short_name','scores_full','scores','overs');
        }])->where('match_id',$request->match_id)
                    ->select('match_id','title','short_title','status','status_str','result','status_note')
                    ->first();
        
            return response()->json(
                        [ 
                            "status"=>true,
                            "code"=>200,
                            "message" => "Match Score",
                            "scores"=>$score
                        ]
                    );        
    }


    public function cloneMyTeam(Request $request){


        $clone_team =   CreateTeam::where('id',$request->team_id)->where('user_id',$request->user_id)->first();
      //  dd($clone_team);
        $data = null;
        if($clone_team){
            $clone_team2  = new CreateTeam;
            
            $clone_team2->match_id      =   $clone_team->match_id;
            $clone_team2->user_id       =   $clone_team->user_id;
            $clone_team2->contest_id    =   $clone_team->contest_id;
            $clone_team2->team_id       =   $clone_team->team_id;
            $clone_team2->teams         =   $clone_team->teams;
            $clone_team2->captain       =   $clone_team->captain;
            $clone_team2->vice_captain  =   $clone_team->vice_captain;
            $clone_team2->trump         =   $clone_team->trump;

            $clone_team2->team_count    =   $clone_team->team_count;
            $clone_team2->team_join_status =   $clone_team->team_join_status;
            $clone_team2->rank          =   $clone_team->rank;
            $clone_team2->edit_team_count =   $clone_team->edit_team_count;
                    
            $clone_team2->save();

            $data = ['created_team_id'=> $clone_team2->id];
        }        
        
        return response()->json(
                        [ 
                            "status"=>true,
                            "code"=>200,
                            "message" => "team created",
                            "response"=>$data
                        ]
                    );        
    }
}
