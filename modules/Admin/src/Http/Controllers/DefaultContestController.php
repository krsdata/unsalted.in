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
use Response; 
use Modules\Admin\Http\Requests\DefaultContestRequest;
use Modules\Admin\Models\DefaultContest;
use Modules\Admin\Models\ContestType;
use Modules\Admin\Models\PrizeBreakups;
use App\Models\Matches;



/**
 * Class AdminController
 */
class DefaultContestController extends Controller {
    /**
     * @var  Repository
     */

    /**
     * Displays all admin.
     *
     * @return \Illuminate\View\View
     */
    public function __construct(DefaultContest $defaultContest) { 
        $this->middleware('admin');
        View::share('viewPage', 'Default Contest');
        View::share('sub_page_title', 'Default Contest');
        View::share('helper',new Helper);
        View::share('heading','Default Contest');
        View::share('route_url',route('defaultContest')); 
        $this->record_per_page = Config::get('app.record_per_page'); 
    }
    /*
     * Dashboard
     * */

    public function index(DefaultContest $defaultContest, Request $request) 
    { 
        $page_title = 'Default Contest';
        $sub_page_title = 'View Default Contest';
        $page_action = 'View Default Contest'; 

 
        // Search by name ,email and group
        $search = Input::get('search');
        $status = Input::get('status');
        if ((isset($search) && !empty($search))) {

            $search = isset($search) ? Input::get('search') : '';
               
            $defaultContest = DefaultContest::where(function($query) use($search,$status) {
                        if (!empty($search)) {
                            $query->Where('contest_type', 'LIKE', "%$search%");
                        }
                        
                    })->Paginate($this->record_per_page);
        } else {
            $defaultContest = DefaultContest::Paginate($this->record_per_page);
        }
        
        $contest_type   = ContestType::pluck('contest_type','id');
        
        return view('packages::defaultContest.index', compact('defaultContest','page_title', 'page_action','sub_page_title','contest_type'));
    }

    /*
     * create DefaultContest
     * */

    public function create(DefaultContest $defaultContest , Request $request) 
    {
        

        $page_title     = 'Default Contest';
        $page_action    = 'Create Default Contest'; 
        $contest_type   = ContestType::pluck('contest_type','id'); 
        
        $match = false;    
        if($request->match_id){
            $match_id = $request->match_id;

            $match = \App\Models\Matches::where('match_id',$match_id)->first();
            if($match){
                $match = $match->match_id;
            }
        }

        return view('packages::defaultContest.create', compact( 'defaultContest','page_title', 'page_action','contest_type','match'));
    }

    /*
     * Save Group method
     * */
    //DefaultContestRequest
    public function store(DefaultContestRequest $request, DefaultContest $defaultContest) 
    {   
        $defaultContest->fill(Input::all()); 
        $defaultContest->save(); 

        $default_contest_id = $defaultContest->id;

        if($request->match_id){
            $match  = Matches::where('match_id',$request->match_id)->get('match_id');
        }else{0
            $match  = Matches::where('status',1)->get('match_id');
        }

        $request->merge(['filled_spot' => 0]);
        foreach ($match as $key => $result) {
            $request->merge(['match_id' => $result->match_id]);
            $request->merge(['default_contest_id' => $default_contest_id]);
        }

        \DB::table('create_contests')->insert($request->except('_token'));
         
        return Redirect::to(route('defaultContest'))
                            ->with('flash_alert_notice', 'New Contest successfully created!');
    }

    /*
     * Edit Group method
     * @param 
     * object : $category
     * */

    public function edit(Request $request, $id) {
        $defaultContest = DefaultContest::find($id);
        $page_title     = 'Default Contest';
        $page_action    = 'Edit Default Contest'; 
        $contest_type   = ContestType::pluck('contest_type','id');

        $match = false;    
        if($defaultContest->match_id){
            $match = $defaultContest->match_id;
        }

        return view('packages::defaultContest.edit', compact('defaultContest', 'page_title', 'page_action','contest_type','match'));
    }

    public function update(Request $request, $id) {
       
        $action = null;
        if($request->prize_break){ 
             $action = "prize_break";
        } 
        if($request->rank_list){ 
            $action = "rank_list";
        } 

        switch ($action) {
            case 'prize_break': 
                  if($request->prize_break){

                    $from   = $request->rank_from;
                    $to     = $request->rank_upto;
                    $prize  = $request->prize_amount;
                    $prize_break_id = $request->prize_break_id;
                    foreach ($request->rank_from as $key => $value) {
                       
                        PrizeBreakups::updateOrCreate(
                            [
                               'default_contest_id'  => $request->default_contest_id,
                               'contest_type_id'   => $request->contest_type_id,
                               'id' => $prize_break_id[$key]??null
                            ],
                            [
                               'default_contest_id'  => $request->default_contest_id,
                               'contest_type_id'   => $request->contest_type_id,
                               'rank_from' =>  $from[$key],
                               'rank_upto' =>  $to[$key],
                               'prize_amount' =>  $prize[$key],
                               'match_id'  => $request->match_id
                            ]);
                    } 

                  }

                return Redirect::to(route('defaultContest'))
                        ->with('flash_alert_notice', 'Prize Breakups add  successfully updated.');


                break;
             case 'rank_list':
                # code...
             
                return Redirect::to(route('defaultContest.show',$id).'?list='.$request->rank_list);
               
                break;
            
            default:
                # code...
                break;
        } 

        $defaultContest = DefaultContest::find($id);
        $defaultContest->fill(Input::all()); 
        $defaultContest->save(); 
        $default_contest_id = $id;
        $match  = Matches::where('status',1)->get('match_id');
        //$request->merge(['filled_spot' => 0]);
        foreach ($match as $key => $result) {
            $request->merge(['match_id' => $result->match_id]);
            $request->merge(['default_contest_id' => $default_contest_id]);
            \DB::table('create_contests')
                    ->where('default_contest_id',$result->match_id)
                    ->where('match_id',$id)
                    ->update($request->except(['_token','_method']));
        }
        
        return Redirect::to(route('defaultContest'))
                        ->with('flash_alert_notice', 'Default Contest  successfully updated.');
    }
    /*
     *Delete User
     * @param ID
     * 
     */
    public function destroy($id) { 
        
        DefaultContest::where('id',$id)->delete();
        
        $contest = \DB::table('create_contests')
                    ->where('default_contest_id',$id)
                    ->where('filled_spot',0)->delete();

        return Redirect::to(route('defaultContest'))
                        ->with('flash_alert_notice', 'Contest successfully deleted.');
    }

    public function show(Request $request, $id) {
	
        $page_title     = 'Contest Prize Breakup';
        $page_action    = 'Show   Contest Prize Breakup'; 
        $expected_amount    =   "";
        
        try{
            $defaultContest = DefaultContest::find((int)$id);
         	if(!$defaultContest){
        		return Redirect::to(route('defaultContest'));
        	} 
          $contestType   =  DefaultContest::with('contestType')->where('id',$id)->first();
            
          $contest_type  = [$contestType->contestType->id=>$contestType->contestType->contest_type] ;

          $match = false;    
          if($request->get('match_id')){
                $match_id = $request->match_id;

                $match = \App\Models\Matches::where('match_id',$match_id)->first();
                if($match){
                    $match = $match->match_id;
                }
            }

          $prizeBreakup = \DB::table('prize_breakups')
                        ->where('default_contest_id',$defaultContest->id)
                        ->where('contest_type_id',$defaultContest->contest_type)
                        ->get();

            $rank_list = $request->list??$prizeBreakup->count();
            $expected_amount =  $contestType->entry_fees*$contestType->total_spots;
            $html       = view::make('packages::defaultContest.addPrizeForm',compact('expected_amount','rank_list','prizeBreakup'));
            
           $default_contest_id = $id;
            return view('packages::defaultContest.prizeBreakup', compact( 'defaultContest','page_title', 'page_action','contest_type','match','expected_amount','html','rank_list','default_contest_id'));
            
         } catch(Exception $e){
         	 
        } 

    }
}

