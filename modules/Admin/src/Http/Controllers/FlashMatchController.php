<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\CategoryRequest;
use Modules\Admin\Models\User;
use Input, Validator, Auth, Paginate, Grids, HTML;
use Form, Hash, View, URL, Lang, Session, DB;
use Route, Crypt, Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Dispatcher;
use App\Helpers\Helper;
use Modules\Admin\Models\Roles;
use Modules\Admin\Models\Menu; 
use Modules\Admin\Models\Wallets;

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
use App\Models\ReferralCode;
use File;

/**
 * Class MenuController
 */
class FlashMatchController extends Controller {
    /**
     * @var  Repository
     */

    /**
     * Displays all admin.
     *
     * @return \Illuminate\View\View
     */
    public function __construct() {
        $this->middleware('admin');
        View::share('viewPage', 'Wallets');
        View::share('sub_page_title', 'Wallets');
        View::share('helper',new Helper);
        View::share('heading','Wallets');
        View::share('route_url',route('flashMatch'));

        $this->record_per_page = Config::get('app.record_per_page');
    }


    /*
     * Dashboard
     * */

    public function index(Wallets $wallets, Request $request)
    {
        $page_title = 'Wallets';
        $sub_page_title = 'Wallets';
        $page_action = 'View Wallets'; 
        
        $total_flash_match = Matches::where('upload_type','manual')->count();
        $total_live_match = Matches::where('upload_type','manual')->where('status',3)->count();
        $total_completed_match = Matches::where('upload_type','manual')->where('status',2)->count();
        $total_upcoming_match = Matches::where('upload_type','manual')->where('status',1)->count();

        $total_cancel_match = Matches::where('upload_type','manual')->where('status',4)->count(); 

        return view('packages::flashMatch.index', compact('total_flash_match', 'page_title', 'page_action','sub_page_title','total_upcoming_match','total_completed_match','total_live_match','total_cancel_match'));
    }

    /*
     * create Group method
     * */

    public function create(Wallets $wallets)
    {

        $page_title     = 'Wallets';
        $page_action    = 'Create Wallets';
        $table_cname = \Schema::getColumnListing('wallets');
        $except = ['id','created_at','updated_at'];
        $data = [];
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
             $tables[] = $value;
        }

        return view('packages::flashMatch.create', compact('wallets', 'page_title', 'page_action','tables'));
    }

    /*
     * Save Group method
     * */

    public function store(Request $request, Wallets $wallets)
    {
        $validator = Validator::make($request->all(), [
            'match_json'    => 'mimes:json,txt',
            'player_json'   => 'mimes:json,txt',
            'point_json'    => 'mimes:json,txt'
        ]);

        // Return Error Message
        if ($validator->fails()) {
            $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                array_push($error_msg, $value);
            }
            return Redirect::to(route('flashMatch'))
                            ->with('flash_alert_notice', 'File must be json or text type'); 
        } 

        if ($request->file('match_json')) {
            $file = file_get_contents($request->match_json);
            
           $status =  $this->saveMatchDataFromJson($file);
           if( $status ){
                $msg = " Match info is uploaded";
           }else{
                 $msg = " Match info contain invalid key";
           }
           return Redirect::to(route('flashMatch'))
                            ->with('flash_alert_notice',$msg); 
        }
        elseif ($request->file('player_json')) {
            $file = file_get_contents($request->player_json);
            $player_json = json_decode($file); 
            $rs = $this->getSquad($player_json);
            if( $rs){
                $msg = " Player info is uploaded";
           }else{
                 $msg = " Player info contain invalid key";
           }
            return Redirect::to(route('flashMatch'))
                            ->with('flash_alert_notice', $msg);
        }
        elseif ($request->file('point_json')) {

           $file = file_get_contents($request->point_json);
           $status =  $this->saveMatchDataFromJson($file);
           if( $status ){
                $msg = " Match player points is uploaded";
           }else{
                 $msg = " File contain invalid key";
           }
           return Redirect::to(route('flashMatch'))
                            ->with('flash_alert_notice', $msg);
        }else{
             return Redirect::to(route('flashMatch'))
                            ->with('flash_alert_notice', 'No file selected');
        }
 
    }

    /*
     * Edit Group method
     * @param
     * object : $menu
     * */

    public function edit($id) { 

        return view('packages::flashMatch.edit', compact( 'wallets', 'page_title','page_action', 'tables'));
    }

    public function update(Request $request, $id) {
 

        return Redirect::to(route('flashMatch'))
                        ->with('flash_alert_notice', ' Wallets  successfully updated.');
    }
    /*
     * Delete User
     * @param ID
     *
     */
    public function destroy($id) {
        return Redirect::to(route('flashMatch'))
                        ->with('flash_alert_notice', ' wallets  successfully deleted.');
    }

     public function saveMatchDataFromJson($data){

        $data = json_decode($data);

        if(isset($data->response) && isset($data->response->format)){
            
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

            $remove_data = ['toss','venue','teama','teamb','competition','points'];

            $matches = Matches::firstOrNew(['match_id' => $data_set['match_id']]);

            foreach ($data_set as $key => $value) {

                if(in_array($key, $remove_data)){
                    continue;
                }
                $matches->$key = $value;

            }
            $matches->toss_id   = $toss_id;
            $matches->venue_id  = $venue_id;
            $matches->teama_id  = $team_a_id;
            $matches->teamb_id  = $team_b_id;
            $matches->competition_id = $toss_id;
            $matches->upload_type = 'manual';
            
            $matches->save();
          
            $this->createContest($data_set['match_id']);

            return true;
        }else{

            return false;
        }
        //
         
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

    public function getSquad($data=null){ 
            # code...    
        
        $match_id = $data->response->match_id;
        if(isset($data->response) && isset($data->response->match_id))
        {
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
            // update player in updatepoint table

            foreach ($data->response->players as $pkey => $pvalue)
            {
                $data_mp =  MatchPoint::firstOrNew(
                    [
                        'pid'=>$pvalue->pid,
                        'match_id'=>$match_id
                    ]
                ); 
                if($data_mp->short_name==null){
                    $data_mp->match_id  =  $match_id;
                    $data_mp->pid = $pvalue->pid; 
                    $data_mp->role = $pvalue->playing_role; 
                    $data_mp->name = $pvalue->short_name; 
                    $data_mp->rating = $pvalue->fantasy_player_rating;
                
                    $data_mp->save(); 
                } 
            }
            
            return true;
        }else{
            return false;
        }    
    }
}
