<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\User;
use Illuminate\Support\Facades\Auth; 
use App\Models\Notification;
use Illuminate\Contracts\Encryption\DecryptException;
use Config,Mail,View,Redirect,Validator,Response; 
use Crypt,okie,Hash,Lang,JWTAuth,Input,Closure,URL; 
use App\Helpers\Helper as Helper;
use Illuminate\Support\Facades\Storage;
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
    
    public function getContestByMatch(Request $request){

        $contest = CreateContest::with('contestType')
                    ->where('match_id',$request->match_id)
                    ->get();

        
        if($contest){
            $matchcontests = [];
            foreach ($contest as $key => $result) {
                 
              //  $data['contestTitle'] = $result->contestType->contest_type;
              //  $data['contestSubTitle'] = $result->contestType->description;
              //  $data['contests'] =
                $data2['contestId'] =    $result->contestType->id;
                $data2['totalWinningPrize'] =    $result->total_winning_prize;
                $data2['entryFees'] =    $result->entry_fees;
                $data2['totalSpots'] =   $result->total_spots;
                $data2['filledSpots'] =  $result->filled_spot;
                $data2['firstPrice'] =   $result->first_prize;
                $data2['winnerPercentage'] = $result->winner_percentage;
                $data2['maxAllowedTeam'] =   $result->contestType->max_entries;
                $data2['cancellation'] = $result->contestType->cancellable;

                $matchcontests['matchcontests'][] = [
                    'contestTitle'=>$result->contestType->contest_type,
                    'contestSubTitle'=>$result->contestType->description,
                    'contests'=>$data2
                ];

            }

            return ['status'=>'true','code'=>'200','message'=>'success','response'=>$matchcontests];
        }


    }

    public function getMatchDataFromApi()
    {

        $date = date('Y-m-d');
        $token = "8740931958a5c24fed8b66c7609c1c49";
        //upcoming
        $upcoming =    file_get_contents('https://rest.entitysport.com/v2/matches/?status=1&pre_squad=true&date='.$date.'&per_page=100&paged=1&token='.$token);

        \File::put(public_path('/upload/json/upcoming.txt'),$upcoming);

        //complted
        $completed =    file_get_contents('https://rest.entitysport.com/v2/matches/?status=2&pre_squad=true&date='.$date.'&per_page=20&paged=1&token='.$token);

        \File::put(public_path('/upload/json/completed.txt'),$completed);

        //live
        $live =    file_get_contents('https://rest.entitysport.com/v2/matches/?status=3&pre_squad=true&date='.$date.'&per_page=10&paged=1&token='.$token);

        \File::put(public_path('/upload/json/live.txt'),$live);

        return ['file updated'];

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
        }else{
            return ['data not available'];
        }

        $date = date('Y-m-d');
        $token = "8740931958a5c24fed8b66c7609c1c49";
        //upcoming
        $data =    file_get_contents('https://rest.entitysport.com/v2/matches/?status='.$status.'&pre_squad=true&date='.$date.'&per_page=100&paged=1&token='.$token);
        
        \File::put(public_path('/upload/json/'.$fileName.'.txt'),$data);

        return [$fileName.' match data updated successfully'];

    } 

    //get file data from local
    public function getJsonFromLocal($path=null)
    {
        return json_decode(file_get_contents($path));

    }

    // store by match type
    public function storeMatchInfo($fileName=null){

        $files = ['live','completed','upcoming'];
        
        try {

            if(in_array($fileName, $files)){

                 $data = $this->getJsonFromLocal(public_path('/upload/json/'.$fileName.'.txt'));
                
                $this->saveMatchDataFromAPI($data);

            }
            
        } catch (Exception $e) {
                dd($e);
        }

        return ['match info stored'];
    }

    public function saveMatchDataFromAPI($data){

       // dd($data->response->items);
        if(count($data->response->items)){

            $results = $data->response->items;
            
            foreach ($results as $key => $result_set) {
                    
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
                    
            }            

        }
        return ["match info updated "];

    }

    // get Match by status and all
    public function getMatch(Request $request){

        $status =  $request->status;
        $user = $request->user_id;
        $banner = \DB::table('banners')->select('title','url','actiontype')->get();

        $join_contest =  \DB::table('join_contests')->where('user_id',$user)->first('match_id');
        
        if($join_contest){  

            $joinedmatches = Matches::with('teama','teamb')->where('match_id',$join_contest->match_id)->select('match_id','title','short_title','status','status_str','timestamp_start','timestamp_end')->get();

            $data['matchdata'][] = ['viewType'=>1,'joinedmatches'=>$joinedmatches];
            
        }
        
       $match = Matches::with('teama','teamb')->where('status',1)->select('match_id','title','short_title','status','status_str','timestamp_start','timestamp_end')->get();

        $data['matchdata'][] = ['viewType'=>2,'banners'=>$banner];
        $data['matchdata'][] = ['viewType'=>3,'upcomingmatches'=>$match];



        return ['total_result'=>count($match),'status'=>'true','code'=>'200','message'=>'success','response'=>$data];
    }


    public function getAllCompetition(){

        $com = \DB::table('competitions')->select('id','match_id','cid')->get()->toArray();
        return $com;
    }

    // get players
    public function getPlayer($match_id=null)
    {
        $players =  Player::with('teama','teamb')->where('match_id',$match_id)
                    ->where(function($q){
                        $q->groupBy('playing_role');
                    })->get();

        if(!$players->count()){  
            return ['status'=>'true','code'=>'404','message'=>'record not found',
                    'response'=>[
                        'players'=>[]
                    ]
                ];
        }
         
         foreach ($players as $key => $results) {
              
             
             if($results->teama){
                    $data['playing11'] = $results->teama->playing11;
             }else{
                 
                 $data['playing11'] = $results->teamb->playing11;
             }

             $data['pid'] = $results->pid;
             $data['match_id'] = $results->match_id;
             $data['team_id'] = $results->team_id;
             $data['short_name'] = $results->short_name;
             $data['fantasy_player_rating'] = $results->fantasy_player_rating;

             $rs[$results->playing_role][]  = $data; 
             $data = [];
         }
       

         return ['status'=>'true','code'=>'200','message'=>'success',
                    'response'=>[
                        'players'=>$rs
                    ]
                ];


    }
    // update player by match_id

    public function getSquad($match_id=null){
        $t1 =  date('h:i:s');
        $token =  $this->token;
        $path = 'https://rest.entitysport.com/v2/matches/'.$match_id.'/squads/?token='.$token;

            $data = $this->getJsonFromLocal($path);
             
            
            // update team a players
            $teama = $data->response->teama;
            foreach ($teama->squads as $key => $squads) {

                  $teama_obj = TeamASquad::firstOrNew(['team_id'=>$teama->team_id,'player_id'=>$squads->player_id,'match_id'=>$match_id]);
                  
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
        $token = $this->token ;
        $com =  Matches::select('match_id')->get();
        $players = [];    
      
        foreach ($com as $key => $value) {
            $this->getSquad($value->match_id); 
        }

        echo date('h:i:s');  

    }

    public function updatePlayerFromCompetition(){

        
        echo date('h:i:s ');
        $token = $this->token ;

        $com = $this->getAllCompetition(); 
        $players = [];    
        foreach ($com as $key => $result) {

             $path = 'https://rest.entitysport.com/v2/competitions/'.$result->cid.'/squads/?token='.$token;

            $data = $this->getJsonFromLocal($path);
           
              
            if(isset($data->response->squads)){
                foreach ($data->response->squads as $key => $rs) {  
                    if($rs->players){
                         foreach ($rs->players as $pkey => $pvalue) {
                              
                            $data_set =   Player::firstOrNew(['cid'=>$result->cid,'team_id'=>$rs->team_id,'pid'=>$pvalue->pid,'match_id'=>$result->match_id]); 
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
          dd (date('h:i:s ')); 
        
    }

    
 
}
