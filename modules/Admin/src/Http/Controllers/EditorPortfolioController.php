<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\EditorPortfolioRequest;
use Modules\Admin\Models\User;
use Input,Validator, Auth, Paginate, Grids, HTML, Form;
use Hash, View, URL, Lang, Session, DB, Route, Crypt, Str;
use Illuminate\Http\Dispatcher;
use App\Helpers\Helper;
use Modules\Admin\Models\Roles;
use Modules\Admin\Models\EditorPortfolio;
use Modules\Admin\Models\Category;
use Modules\Admin\Models\SoftwareEditor;
use Illuminate\Support\Facades\Cache;


/**
 * Class AdminController
 */
class EditorPortfolioController extends Controller
{
    /**
     * @var  Repository
     */

    /**
     * Displays all admin.
     *
     * @return \Illuminate\View\View
     */
    public function __construct()
    {
        $this->middleware('admin');
        View::share('viewPage', 'EditorPortfolio');
        View::share('sub_page_title', ' Editor Portfolio');
        View::share('helper', new Helper);
        View::share('heading', 'Editor Portfolio');
        View::share('route_url', route('editorPortfolio'));

        $this->record_per_page = Config::get('app.record_per_page');
    }

   
    /*
     * Dashboard
     * */

    public function index(EditorPortfolio $editorPortfolio, Request $request)
    {
        $page_title = '  Editor Portfolio';
        $sub_page_title = '  Editor Portfolio';
        $page_action = 'View   Editor Portfolio';


        if ($request->ajax()) {
            $id = $request->get('id');
            $editorPortfolio = EditorPortfolio::find($id);
            $editorPortfolio->status = $s;
            $editorPortfolio->save();
            echo $s;
            exit();
        }

        // Search by name ,email and  
        $search = Input::get('search');
        $status = Input::get('status');
        if ((isset($search) && !empty($search))) {
            $search = isset($search) ? Input::get('search') : '';
               
            $editorPortfolio = EditorPortfolio::with('category','softwareEditor')->where(function ($query) use ($search,$status) {
                if (!empty($search)) {
                    $query->Where('title', 'LIKE', "%$search%");
                }
            })->Paginate($this->record_per_page);
        } else {
            $editorPortfolio = EditorPortfolio::with('category','softwareEditor')->Paginate($this->record_per_page);
        }

       // dd($editorPortfolio);
        
        return view('packages::editorPortfolio.index', compact('editorPortfolio', 'page_title', 'page_action', 'sub_page_title'));
    }

    /*
     * create   method
     * */

    public function create(EditorPortfolio $editorPortfolio)
    {
        $page_title = '  Editor Portfolio';
        $page_action = 'Create   Editor Portfolio';

        $category_name = Category::pluck('category_name','id');
        $software_editor = SoftwareEditor::pluck('software_name','id');
        
        $url = '';

        return view('packages::editorPortfolio.create', compact('editorPortfolio', 'url', 'page_title', 'page_action','category_name','software_editor'));
    }

    /*
     * Save   method
     * */

    public function store(EditorPortfolioRequest $request, EditorPortfolio $editorPortfolio)
    {

        $photo = $request->file('image_name');
        $destinationPath = storage_path('uploads/editorPortfolio');
        $photo->move($destinationPath, time().$photo->getClientOriginalName());
        $photo_name = time().$photo->getClientOriginalName();
        $request->merge(['photo'=>$photo_name]);
 
        $editorPortfolio->title   =  $request->get('title');
        $editorPortfolio->slug            =  strtolower(Str::slug($request->get('title')));
        $editorPortfolio->image_name  =  $photo_name;
        $editorPortfolio->description     =  $request->get('description');
        $editorPortfolio->category_name     =  $request->get('category_name');
        $editorPortfolio->software_editor     =  $request->get('software_editor');
        $editorPortfolio->editor_id     =   Auth::id();
        
        $editorPortfolio->save();
         
        return Redirect::to(route('editorPortfolio'))
                            ->with('flash_alert_notice', 'New   Editor Portfolio successfully created !');
    }

    /*
     * Edit   method
     * @param
     * object : $editorPortfolio
     * */

    public function edit($id)
    {
      


        $editorPortfolio = EditorPortfolio::find($id);
        $page_title = '  Editor Portfolio';
        $page_action = 'Edit   Editor Portfolio';
        $url = url::asset('storage/uploads/editorPortfolio/'.$editorPortfolio->image_name)  ;
        
        $category_name = Category::pluck('category_name','id');
        $software_editor = SoftwareEditor::pluck('software_name','id');

        return view('packages::editorPortfolio.edit', compact('url', 'editorPortfolio', 'page_title', 'page_action','category_name','software_editor'));
    }

    public function update(EditorPortfolioRequest $request, $id)
    { 

        if ($request->file('image_name')) {
            $photo = $request->file('image_name');
            $destinationPath = storage_path('uploads/editorPortfolio');
            $photo->move($destinationPath, time().$photo->getClientOriginalName());
            $photo_name = time().$photo->getClientOriginalName();
            $request->merge(['photo'=>$photo_name]);
        }
        $editorPortfolio = editorPortfolio::find($id);
        
        $editorPortfolio->title   =  $request->get('title');
        $editorPortfolio->slug            =  strtolower(Str::slug($request->get('title')));
        $editorPortfolio->description     =  $request->get('description');
        $editorPortfolio->category_name     =  $request->get('category_name');
        $editorPortfolio->software_editor     =  $request->get('software_editor');
        $editorPortfolio->editor_id     =   Auth::id();

        if (isset($photo_name)) {
            $editorPortfolio->image_name  =  $photo_name;
        }
         
        $editorPortfolio->save();


        return Redirect::to(route('editorPortfolio'))
                        ->with('flash_alert_notice', '    Editor Portfolio successfully updated.');
    }
    /*
     *Delete User
     * @param ID
     *
     */
    public function destroy($id)
    {
        editorPortfolio::where('id', $id)->delete();
        return Redirect::to(route('editorPortfolio'))
                        ->with('flash_alert_notice', '  Editor Portfolio successfully deleted.');
    }

    public function show(EditorPortfolio $editorPortfolio)
    {
    }
}
