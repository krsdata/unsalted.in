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

   
    /*
     * Dashboard
     * */

    public function index(Match $match, Request $request) 
    { 
        $page_title = 'Match';
        $sub_page_title = 'View Match';
        $page_action = 'View Match'; 

        // Search by name ,email and group
        $search = Input::get('search');
        $status = Input::get('status');
        if ((isset($search) || isset($status))) {
             
            $search = isset($search) ? Input::get('search') : '';
               
            $match = Match::with('teama','teamb')->where(function($query) use($search,$status) {    
                        if (!empty($status)) {
                            $query->Where('status', '=', $status);
                        }
                        if (!empty($search)) {
                            $query->Where('title', 'LIKE', "%$search%");
                        }
                        
                    })->Paginate($this->record_per_page);
             
        } else {
            $match = Match::with('teama','teamb')->orderBy('timestamp_start','DESC')->Paginate($this->record_per_page);
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
        $program = Program::find($id);
        $page_title     = 'Promotion';
        $page_action    = 'Edit Promotion'; 
        $status         = [
                            'last_15_days'=>'inactive from last 15 days',
                            'last_30_days'=>'inactive from last 30 days',
                            'last_45_days'=>'inactive from last 45 days'
                        ];
        return view('packages::program.edit', compact('program','status', 'page_title', 'page_action'));
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
        $match = Match::find($id);
        $page_title     = 'Match';
        $page_action    = 'Show Match'; 
        $result = $match;
        $match = Match::where('id',$match->id)->first()->toArray();
        
        return view('packages::match.show', compact( 'result','match','page_title', 'page_action'));

    }

}