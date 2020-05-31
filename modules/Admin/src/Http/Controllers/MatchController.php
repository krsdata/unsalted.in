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
use App\Models\Wallet;
use App\Models\JoinContest;
use App\Models\WalletTransaction;
use App\Models\CreateContest;
use App\Models\CreateTeam;
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

    
    /*cancelMatch*/
    public function cancelContest(Request $request){
        
        if($request->cancel_contest){
            $JoinContest = JoinContest::whereHas('user')->with('contest')
                        ->where('match_id',$request->match_id)
                        ->whereIn('contest_id',$request->cancel_contest)
                        ->get()
                        ->transform(function($item,$key){
                        
                        $cancel_contest = CreateContest::find($item->contest_id);
                        if($cancel_contest->is_cancelled==0){
                            
                            $cancel_contest->is_cancelled = 1;
                            $cancel_contest->save();

                            if(isset($item->contest) && $item->contest->entry_fees){
                                
                                $transaction_id = $item->match_id.$item->contest_id.$item->created_team_id.'-'.$item->user_id;

                                $wt =    WalletTransaction::firstOrNew(
                                        [
                                           'user_id' => $item->user_id,
                                           'transaction_id' => $transaction_id
                                        ]
                                    );
                                $wt->user_id            = $item->user_id;   
                                $wt->amount             = $item->contest->entry_fees;  
                                $wt->payment_type       = 7;  
                                $wt->payment_type_string = "Refunded";
                                $wt->transaction_id     = $transaction_id;
                                $wt->payment_mode       = 'Sportsfight';    
                                $wt->payment_status     = "success";
                                $wt->debit_credit_status = "+";   
                                $wt->save();


                                $wallet = Wallet::firstOrNew(
                                        [
                                           'user_id' => $item->user_id,
                                           'payment_type' => 4
                                        ]
                                    );

                                $wallet->user_id        =  $item->user_id;
                                $wallet->amount = $wallet->amount+$item->contest->entry_fees;
                                $wallet->deposit_amount = $wallet->amount+$item->contest->entry_fees;
                                $wallet->save();
                            }

                            \DB::commit();
                            
                            $item->cancel_message = 'Contest Cancelled' ;
                            return $item;
                        }else{
                            $item->cancel_message = 'Already Cancelled' ; 
                            return $item; 
                        }
                    });               
        
        if($JoinContest->count()==0 and count($request->cancel_contest)){
           
            foreach ($request->cancel_contest as $key => $value) {
                $cancel_contest = CreateContest::find($value);
                $cancel_contest->is_cancelled = 1;
                $cancel_contest->save();
            }

           return Redirect::to(route('match'))->with('flash_alert_notice', 'Selected contest is cancelled');

        }
        return Redirect::to(route('match'))->with('flash_alert_notice', 'Match Contest Cancelled successfully');
        }else{
            return Redirect::to(route('match'))->with('flash_alert_notice', 'No Contest selected for cancellation'); 
        }
    }
    /*cancelMatch*/
    public function cancelMatch(Request $request){
        $match_id = $request->match_id;
        if($request->match_id){
            $data['status']         = 4;
            $data['status_str']     = 'Cancelled';
            $data['is_cancelled']   = 1;
           
            $match = Match::firstOrNew([
                'match_id' => $request->match_id
            ]);

            if($match->is_cancelled==0 && $match->status==1){

                $match->status= 4;
                $match->status_str= 'Cancelled';
                $match->is_cancelled= 1;
                $match->save();
            }else{
                $match->status= 4;
                $match->status_str= 'Cancelled';
                $match->is_cancelled= 1;
                $match->save();

                if($match->status==4){
                    return Redirect::to(route('match','search='.$match_id))->with('flash_alert_notice', 'This Match already Cancelled'); 
                }
                if($match->status!=1){
                    return Redirect::to(route('match','search='.$match_id))->with('flash_alert_notice', 'This Match can not be cancelled'); 
                }
            }
        }

        return Redirect::to(route('match','search='.$match_id))->with('flash_alert_notice', 'Match Cancelled successfully'); 


        $JoinContest = JoinContest::whereHas('user')->with('contest')
                        ->where('match_id',$request->match_id)
                        ->get()
                        ->transform(function($item,$key){
                            if(isset($item->contest) && $item->contest->entry_fees){
                                
                            $transaction_id = $item->match_id.$item->contest_id.$item->created_team_id.'-'.$item->user_id;

                            $wt =    WalletTransaction::firstOrNew(
                                    [
                                       'user_id' => $item->user_id,
                                       'transaction_id' => $transaction_id
                                    ]
                                );
                            $wt->user_id            = $item->user_id;   
                            $wt->amount             = $item->contest->entry_fees;  
                            $wt->payment_type       = 7;  
                            $wt->payment_type_string = "Refunded";
                            $wt->transaction_id     = $transaction_id;
                            $wt->payment_mode       = 'Sportsfight';    
                            $wt->payment_status     = "success";
                            $wt->debit_credit_status = "+";   
                            $wt->save();


                            $wallet = Wallet::firstOrNew(
                                    [
                                       'user_id' => $item->user_id,
                                       'payment_type' => 4
                                    ]
                                );

                            $wallet->user_id        =  $item->user_id;
                            $wallet->amount = $wallet->amount+$item->contest->entry_fees;
                            $wallet->deposit_amount = $wallet->amount+$item->contest->entry_fees;
                            $wallet->save();

                            }
                        });               
        
        return Redirect::to(route('match','search='.$match_id))->with('flash_alert_notice', 'Match Cancelled successfully'); 

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
                if($item->prize_amount>0){
                    $helper = new Helper;
                    $m = $helper->sendNotificationMail($email_content,'prize'); 
                }
                 
                \DB::table('prize_distributions')->where('id',$item->id)->update(['email_trigger'=>1]);  
                }); 
        return  Redirect::to(route('match','search='.$match_id.'&email=true'));
    }
    /*
     * Dashboard
     * */

    public function index(Match $match, Request $request) 
    {  
        $page_title = 'Match';
        $sub_page_title = 'View Match';
        $page_action = 'View Match'; 

        if($request->match_id && (($request->date_start && $request->date_end) || $request->status)){
            if($request->date_end && $request->date_start){
                $date_start = \Carbon\Carbon::createFromFormat('Y-m-d H:i',$request->date_start)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');

                $date_end = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date_end)
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');

                $timestamp_start = strtotime($date_start);
                $timestamp_end   = strtotime($date_end);

                if($timestamp_start > $timestamp_end) {

                    return Redirect::to(route('match'))->with('End date should not be greater than start date');  
                }

            }
            
            $status = $request->status;
            if($status==1){
                $status_str = "Upcoming";
            }elseif($status==2){
                $status_str = "Completed";
            }elseif($status==3){
                $status_str = "Live";
            }elseif($status==4){
                $status_str = "Cancelled";
                $data['is_cancelled'] = 1;
            }
            
            if($request->match_id && $request->date_end && $request->date_start && $request->change_date){
                $data =   [
                                'timestamp_start' => $timestamp_start,
                                'timestamp_end' => $timestamp_end,
                                'date_start'  => $date_start,
                                'date_end'  => $date_end 
                          ];  
            }

            if($request->match_id && $request->status && $request->change_status){
                $data['status'] =   $request->status;
                $data['status_str'] =   $status_str;
            }
            
            \DB::table('matches')->where('match_id',$request->match_id)
                        ->update($data);

                     
            return Redirect::to('admin/match?search='.$request->match_id)->with('flash_alert_notice', 'Match updated successfully!');

        }

        // Search by name ,email and group
        $search = Input::get('search');
        $status = Input::get('status');
        if ((isset($search) || isset($status))) {
             
            $search = isset($search) ? Input::get('search') : '';
               
            $match = Match::with('teama','teamb')->where(function($query) use($search,$status) {    
                        if (!empty($status) && empty($search)) {
                           // $query->Where('status', '=', $status);
                            if($status==1){
                                $query->where('timestamp_start','>=',time());
                                $query->where('status','=',1);
                            }
                            if($status==2){
                                $query->orderBy('timestamp_start','DESC');
                                $query->where('status','=',2);
                            }
                            if($status==3){ 
                                $query->where('status',3);
                            }
                        }else{
                            if (!empty($status) && !empty($search)) {
                                $query->Where('match_id',$search);
                                $query->where('status', $status);
                            }elseif(!empty($search)){
                                $query->orWhere('match_id',$search);
                                $query->orWhere('title', 'LIKE', "$search%");
                                $query->orWhere('short_title', 'LIKE', "$search%"); 
                                $query->orWhere('title', 'LIKE', "%$search");
                                $query->orWhere('short_title', 'LIKE', "%$search"); 
                               // $query->orWhere('title', 'LIKE', "%$search%"); 
                            }    
                        }
                        
                         
                        
                    })->orderBy('updated_at','DESC')->Paginate($this->record_per_page);

                $match->transform(function($item,$key){
                    $playing11_teamA= \DB::table('team_a_squads')
                                ->where('playing11',"true")
                                ->where('match_id',$item->match_id)
                                ->get();
                    $playing11_teamB= \DB::table('team_b_squads')
                                    ->where('match_id',$item->match_id)
                                    ->where('playing11',"true")
                                    ->get();
                                    //dd($playing11_teamA);
                    $item->playing11_teamA = $playing11_teamA;
                    $item->playing11_teamB = $playing11_teamB;
                                   
                $contests = CreateContest::where('match_id',$item->match_id)->get()
                            ->transform(function($item,$key){
                                $contest_name = \DB::table('contest_types')
                                        ->where('id',$item->contest_type)->first();
                                $item->contest_name = $contest_name->contest_type;
                                return $item;
                            });
                $item->contests = $contests;
                return $item;            

            }); 
             
        } else {
            $match = Match::with('teama','teamb')->orderBy('created_at','DESC')->Paginate($this->record_per_page);
            $match->transform(function($item,$key){

                $playing11_teamA= \DB::table('team_a_squads')
                            ->where('playing11',"true")
                            ->where('match_id',$item->match_id)
                            ->get();
                $playing11_teamB= \DB::table('team_b_squads')
                                ->where('match_id',$item->match_id)
                                ->where('playing11',"true")
                                ->get();

                $item->playing11_teamA = $playing11_teamA;
                $item->playing11_teamB = $playing11_teamB;
                $contests = CreateContest::where('match_id',$item->match_id)->get()
                            ->transform(function($item,$key){
                                $contest_name = \DB::table('contest_types')
                                        ->where('id',$item->contest_type)->first();
                                $item->contest_name = $contest_name->contest_type;
                                return $item;
                            });
                $item->contests = $contests;
                return $item;            

            });
        }    
        return view('packages::match.index', compact('match','page_title', 'page_action','sub_page_title'));
    }

    public function create(Match $match)
    {
        $page_title     = 'Match';
        $page_action    = 'Create Match';
        $table_cname = \Schema::getColumnListing('matches');
        $except = ['id','created_at','updated_at','pid','team_id'];
       
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
            $tables[] = $value;
        }

        return view('packages::match.create', compact('match', 'page_title', 'page_action','tables'));
    }

    /*
     * Save Group method
     * */

    public function store(Request $request, Match $program) 
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
        $match = Match::find($id);
        $page_title = 'Match';
        $page_action = 'Match';

        $table_cname = \Schema::getColumnListing('matches');
        $except = ['id','created_at','updated_at','pid','team_id'];
        $data = [];
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
             $tables[] = $value;
        }
        return view('packages::match.edit', compact( 'match', 'page_title','page_action', 'tables'));
    }

     public function update(Request $request, $id) {

        $match = Match::find($id);
        $data = [];
        $table_cname = \Schema::getColumnListing('matches');
        $except = ['id','created_at','updated_at','_token','_method','match_id','pid'];
        $data = [];
        foreach ($table_cname as $key => $value) {

           if(in_array($value, $except )){
                continue;
           }
            if($request->$value){
                $match->$value = $request->$value;
           }

        }

        $match->save();

        return Redirect::to(route('match'))
                        ->with('flash_alert_notice', ' Match  successfully updated.');
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