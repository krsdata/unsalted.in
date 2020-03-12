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

    public function store(DefaultContestRequest $request, DefaultContest $defaultContest) 
    {   
        $defaultContest->fill(Input::all()); 
        $defaultContest->save();   
         
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

    public function update(DefaultContestRequest $request, $id) {
        $defaultContest = DefaultContest::find($id);
        $defaultContest->fill(Input::all()); 
        $defaultContest->save(); 
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
        return Redirect::to(route('defaultContest'))
                        ->with('flash_alert_notice', 'Contest successfully deleted.');
    }

    public function show($id) {
	
try{
 
        $defaultContest = DefaultContest::find((int)$id);
 	if(!$defaultContest){
		return Redirect::to(route('defaultContest'));
	}
        $page_title     = 'Default Contest';
        $page_action    = 'Show Default Contest'; 
        $result = $defaultContest;
        $defaultContest = DefaultContest::where('id',$defaultContest->id)->select(['contest_type','description','max_entries','cancellable','created_at'])->first()->toArray();
    
 } catch(Exception $e){
 	 
}
	   
        return view('packages::defaultContest.show', compact( 'result','defaultContest','page_title', 'page_action'));

    }

}
