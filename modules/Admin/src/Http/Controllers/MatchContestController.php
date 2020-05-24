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
use Modules\Admin\Models\MatchContest;
use Modules\Admin\Models\MatchTeams;
use App\Models\Matches as Match;
use App\Models\JoinContest;
use App\Models\WalletTransaction;
use App\Models\CreateContest;
use App\Models\CreateTeam;
use Modules\Admin\Models\Player;

/**
 * Class MenuController
 */
class MatchContestController extends Controller {
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
        View::share('viewPage', 'matchContest');
        View::share('sub_page_title', 'Match Contest');
        View::share('helper',new Helper);
        View::share('heading','Match Contest');
        View::share('route_url',route('matchContest'));

        $this->record_per_page = Config::get('app.record_per_page');
    }

    public function matchTeams(MatchTeams $matchTeams, Request $request)
    { 
        $page_title = 'Users Team';
        $sub_page_title = 'User Teams';
        $page_action = 'View user Teams'; 
         
        // Search by name ,email and group
        $search = Input::get('search');
        $status = Input::get('status');
        $user = User::where('email','LIKE',"%$search%")
                            ->orWhere('first_name','LIKE',"%$search%")
                            ->orWhere('phone','LIKE',"%$search%")
                            ->get('id')->pluck('id');

        if ((isset($search) && !empty($search))) { 

            $matchTeams = MatchTeams::where(function($query) use($search,$status,$user) {
                        if (!empty($search)) {
                             $query->where('match_id', $search);

                         }
                         if (!empty($user)) {
                             $query->orwhereIn('user_id', $user);
                             
                         }
                    })->orderBy('created_at','desc')->Paginate($this->record_per_page);
            
            $matchTeams->transform(function($item,$key){ 
                    $match = Match::where('match_id',$item->match_id)->select('short_title','status_str')->first();
                    $item->status = $match->status_str??null;
                    $item->match_name = $match->short_title??null;

                    $teams = json_decode($item->teams);

                    $teams = Player::where('match_id',$item->match_id)->whereIn('pid',$teams)
                        ->get(['pid','team_id','match_id','title','short_name','playing_role','fantasy_player_rating']);

                    $user = User::find($item->user_id);
                    
                    $item->user_name = $user->name??null;    

                    $item->teams = $teams;    
                    $item->join_status =  ($item->team_join_status==1)?'<span class="btn btn-success btn-xs">Joined</span>':'<span class="btn btn-danger btn-xs">Not Joined</span>';
                                        
                    return $item; 
            });
        } else {
            $matchTeams = MatchTeams::orderBy('created_at','desc')->orderBy('created_at','desc')->Paginate($this->record_per_page);
                                                    
            $matchTeams->transform(function($item,$key){ 
                    $match = Match::where('match_id',$item->match_id)->select('short_title','status_str')->first();
                    $item->status = $match->status_str??null;
                    $item->match_name = $match->short_title??null;

                    $teams = json_decode($item->teams);

                    $teams = Player::where('match_id',$item->match_id)->whereIn('pid',$teams)
                        ->get(['pid','team_id','match_id','title','short_name','playing_role','fantasy_player_rating']);
                    $item->teams = $teams; 

                    $user = User::find($item->user_id);
                    $item->user_name = $user->name??null;    

                    $item->join_status =  ($item->team_join_status==1)?'<span class="btn btn-success btn-xs">Joined</span>':'<span class="btn btn-danger btn-xs">Not Joined</span>';
                    
                    return $item; 
            });

        } 
        $table_cname = \Schema::getColumnListing('create_teams');
        
        $except = ['id','created_at','updated_at','contest_id','user_id','isWinning','edit_team_count','team_id','teams','captain','vice_captain','trump','team_join_status'];
        $data = [];

        $tables[] = 'match_name';
        $tables[] = 'status';
        $tables[] = 'user_name';
        $tables[] = 'join_status';

        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
              
            $tables[] = $value;
        }

        return view('packages::matchContest.matchTeams', compact('matchTeams', 'page_title', 'page_action','sub_page_title','tables'));
    }

    /*
     * Dashboard
     * */

    public function index(MatchContest $matchContest, Request $request)
    {

        $page_title = 'Match Contest';
        $sub_page_title = 'Match Contest';
        $page_action = 'View Match Contest'; 
         
        // Search by name ,email and group
        $search = Input::get('search');
        $status = Input::get('status');
        $user = User::where('email','LIKE',"%$search%")
                            ->orWhere('first_name','LIKE',"%$search%")
                            ->orWhere('phone','LIKE',"%$search%")
                            ->get('id')->pluck('id');

        if ((isset($search) && !empty($search))) { 

            $matchContest = MatchContest::where(function($query) use($search,$status,$user) {
                        if (!empty($search)) {
                             $query->where('match_id', $search);
                         }
                    })->orderBy('created_at','desc')->Paginate($this->record_per_page);
            
            $matchContest->transform(function($item,$key){ 
                    $contest_name = \DB::table('contest_types')->where('id',$item->contest_type)->first();
                    $item->contest_name = $contest_name->contest_type??null;

                    return $item; 
            });
        } else {
            $matchContest = MatchContest::orderBy('created_at','desc')->Paginate(8);
                                                    
            $matchContest->transform(function($item,$key){ 
                    $contest_name = \DB::table('contest_types')->where('id',$item->contest_type)->first();
                    $item->contest_name = $contest_name->contest_type??null;
                    $match = Match::where('match_id',$item->match_id)->select('short_title','status_str')->first();
                    $item->status = $match->status_str??'Cancel';
                    
                    return $item; 
            });

        } 
        
        $table_cname = \Schema::getColumnListing('create_contests');
        
        $except = ['id','created_at','updated_at','winner_percentage','prize_percentage','is_cancelled','contest_type','default_contest_id','cancellation'];
        $data = [];

        $tables[] = 'contest_name';
        $tables[] = 'status';
        
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
              
            $tables[] = $value;
        }

        return view('packages::matchContest.index', compact('matchContest', 'page_title', 'page_action','sub_page_title','tables'));
    }

    /*
     * create Group method
     * */

    public function create(MatchContest $matchContest)
    {

        $page_title     = 'Match Contest';
        $page_action    = 'Create Wallets';
        $table_cname = \Schema::getColumnListing('create_contests');
        $except = ['id','created_at','updated_at'];
        $data = [];
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
             $tables[] = $value;
        }

        return view('packages::matchContest.create', compact('matchContest', 'page_title', 'page_action','tables'));
    }

    /*
     * Save Group method
     * */

    public function store(Request $request, MatchContest $matchContest)
    {
        $data = [];
        $table_cname = \Schema::getColumnListing('create_contests');
        $except = ['id','created_at','updated_at','_token','_method'];
        $data = [];
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
            if($request->$value!=null){
                $wallets->$value = $request->$value;
           }
        }
        $wallets->save();
        return Redirect::to(route('Match Contest'))
                            ->with('flash_alert_notice', 'Wallets successfully created !');
        }

    /*
     * Edit Group method
     * @param
     * object : $menu
     * */

    public function edit($id) {
        $wallets = MatchContest::find($id);
        $page_title = 'Match Contest';
        $page_action = 'Match Contest';

        $table_cname = \Schema::getColumnListing('create_contests');
        $except = ['id','created_at','updated_at'];
        $data = [];
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
             $tables[] = $value;
        }


        return view('packages::matchContest.edit', compact( 'create_contests', 'page_title','page_action', 'tables'));
    }

    public function update(Request $request, $id) {

        $wallets = MatchContest::find($id);
        $data = [];
        $table_cname = \Schema::getColumnListing('create_contests');
        $except = ['id','created_at','updated_at','_token','_method'];
        $data = [];
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
            if($request->$value){
                $wallets->$value = $request->$value;
           }
        }
        $wallets->save();

        return Redirect::to(route('matchContest'))
                        ->with('flash_alert_notice', ' Match Contest  successfully updated.');
    }
    /*
     * Delete User
     * @param ID
     *
     */
    public function destroy($id) {
        #PrizeDistribution::where('id',$id)->delete();
        return Redirect::to(route('matchContest'))
                        ->with('flash_alert_notice', ' wallets  successfully deleted.');

    }

}
