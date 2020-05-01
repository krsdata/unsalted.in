<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use Illuminate\Http\Request;
use Modules\Admin\Models\User; 
use Input;
use Validator;
use Auth;
use Paginate;
use Grids;
use HTML;
use Form;
use Hash;
use View;
use URL;
use Lang;
use Session; 
use Route;
use Crypt; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Dispatcher; 
use App\Helpers\Helper;
use Modules\Admin\Models\Users;
use App\Models\Matches as Match;
use Response; 
/**
 * Class AdminController
 */
class MatchController extends Controller {
    /**
     * @var  Repository
     */

    /**
     * Displays all admin.
     *
     * @return \Illuminate\View\View
     */
    public function __construct(Match $match) { 
        $this->middleware('admin');
        View::share('viewPage', 'Match');
        View::share('sub_page_title', 'Match');
        View::share('helper',new Helper);
        View::share('heading','Match');
        View::share('route_url',route('match')); 
        $this->record_per_page = Config::get('app.record_per_page'); 
    }
  /**
    * @var $pd = prize distribution
    */
    public function triggerEmail(Request $request){

        $match_id = $request->match_id; 

        $pd = \DB::table('prize_distributions')
                ->where('match_id',$match_id)
                ->where('email_trigger',0)  
                ->get()  
                ->transform(function($item,$key)use($match_id){

                    $match = Match::where('match_id',$match_id)
                            ->select('match_id','title','short_title','status_note','format_str')->first();
                    $pd_user = \DB::table('prize_distributions')
                        ->where('match_id',$match_id)
                        ->where('user_id',$item->user_id);

                   // $item->prize_amount = $pd_user->sum('prize_amount');    
                   // $item->total_team = $pd_user->sum('team_name');

                    $email_content = [ //
                        'receipent_email'=> $item->email,
                        'subject'=> 'Sportsfight | Prize',
                        'greeting'=> 'SportsFight',
                        'first_name'=> ucfirst($item->name),
                        'content' => 'You have won the prize of Rs.<b>'.$item->prize_amount.'</b> for the <b>'.$match->title.'</b> match.',
                        'rank' => $item->rank
                        ];
                if($item->prize_amount){
                    $helper = new Helper;
                   // $m = $helper->sendNotificationMail($email_content,'prize'); 
                }
                 
                \DB::table('prize_distributions')->where('id',$item->id)->update(['email_trigger'=>1]);  
                }); 
        return  Redirect::to(route('match','email=true'));
    }
    /*
     * Dashboard
     * */

    public function index(Match $match, Request $request) 
    { 
        $page_title = 'Match';
        $sub_page_title = 'View Match';
        $page_action = 'View Match'; 

        if($request->date_start && $request->date_end && $request->match_id){
            $timestamp_start = strtotime($request->date_start);
            $timestamp_end   = strtotime($request->date_end);
            $status = $request->status;
            if($status==1){
                $status_str = "Upcoming";
            }elseif($status==2){
                $status_str = "Completed";
            }elseif($status==3){
                $status_str = "Live";
            }else{
                $status_str = "Cancelled";
            }
            \DB::table('matches')->where('match_id',$request->match_id)
                        ->update(
                            [
                                'timestamp_start' => $timestamp_start,
                                'timestamp_end' => $timestamp_end,
                                'date_start'  => $request->date_start,
                                'date_end'  => $request->date_end,
                                'status'  => $request->status,
                                'status_str' => $status_str
                            ]
                        );
            return Redirect::to(route('match'));            
        }



        // Search by name ,email and group
        $search = Input::get('search');
        $status = Input::get('status');
        if ((isset($search) || isset($status))) {
             
            $search = isset($search) ? Input::get('search') : '';
               
            $match = Match::with('teama','teamb')->where(function($query) use($search,$status) {    
                        if (!empty($status)) {
                            $query->Where('status', '=', $status);
                            if($status==1){
                                $query->where('timestamp_start','>=',time());
                            }
                             if($status==2){
                                $query->orderBy('timestamp_start','DESC');
                            }
                        }
                        if (!empty($search)) {
                            $query->orWhere('title', 'LIKE', "%$search%");
                        }
                        if (!empty($search)) {
                            $query->orWhere('match_id', 'LIKE', "%$search%");
                        }
                        if (!empty($search)) {
                            $query->orWhere('short_title', 'LIKE', "%$search%");
                        } 
                    })->orderBy('timestamp_start','DESC')->Paginate($this->record_per_page); 
             
        } else {
            $match = Match::with('teama','teamb')->orderBy('status','ASC')->Paginate($this->record_per_page);
        } 
        
        return view('packages::match.index', compact('match','page_title', 'page_action','sub_page_title'));
    }

    

    /*
     * Save Group method
     * */

    public function store(ProgramRequest $request, Program $program) 
    {   
        $program->fill(Input::all()); 
        $program->save();   
         
        return Redirect::to(route('match'))
                            ->with('flash_alert_notice', 'New Match  successfully created!');
    }

    /*
     * Edit Group method
     * @param 
     * object : $category
     * */

    public function edit($id) {
        
        return view('packages::match.edit');
    }

    public function update(Request $request, $id) {
        $program = Program::find($id);
        $program->fill(Input::all()); 
        $program->save();  
        return Redirect::to(route('program'))
                        ->with('flash_alert_notice', 'program  successfully updated.');
    }
    /*
     *Delete User
     * @param ID
     * 
     */
    public function destroy($program) { 
        
        Program::where('id',$program)->delete();
        return Redirect::to(route('program'))
                        ->with('flash_alert_notice', 'program  successfully deleted.');
    }

    public function show($id) {
        $matches = Match::find($id);
        $page_title     = 'Match';
        $page_action    = 'Show Match'; 
        $result = $matches;
        $match = Match::where('id',$matches->id)
            ->select('match_id','title','short_title','status_str','status_note','date_start','timestamp_start')->first()->toArray(); 

        $conetst = \DB::table('create_contests')->where('match_id',$matches->match_id)->get();    
         

        $team_a =  \DB::table('team_a_squads')->where('match_id',$matches->match_id)->pluck('player_id')->toArray();
        $team_b =  \DB::table('team_b_squads')->where('match_id',$matches->match_id)->pluck('player_id')->toArray(); 

        $team = array_merge($team_a,$team_b);
        

        $player =  \DB::table('players')
                    ->whereIn('pid',$team) 
                    ->where('match_id',$matches->match_id)
                    ->orderBy('title','ASC')
                    ->get(); 
        
        return view('packages::match.show', compact('player','conetst', 'result','match','page_title', 'page_action'));

    }

}