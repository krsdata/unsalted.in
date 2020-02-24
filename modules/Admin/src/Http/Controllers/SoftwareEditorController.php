<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\SoftwareEditorRequest;
use Modules\Admin\Models\User;
use Input,Validator, Auth, Paginate, Grids, HTML, Form;
use Hash, View, URL, Lang, Session, DB, Route, Crypt, Str;
use Illuminate\Http\Dispatcher;
use App\Helpers\Helper;
use Modules\Admin\Models\Roles;
use Modules\Admin\Models\SoftwareEditor;

/**
 * Class AdminController
 */
class SoftwareEditorController extends Controller
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
        View::share('viewPage', 'SoftwareEditor');
        View::share('sub_page_title', 'Software Editor');
        View::share('helper', new Helper);
        View::share('heading', 'Software Editor');
        View::share('route_url', route('softwareEditor'));

        $this->record_per_page = Config::get('app.record_per_page');
    }

   
    /*
     * Dashboard
     * */

    public function index(SoftwareEditor $softwareEditor, Request $request)
    {
        $page_title = 'Software Editor';
        $sub_page_title = 'Software Editor';
        $page_action = 'View Software Editor';


        if ($request->ajax()) {
            $id = $request->get('id');
            $softwareEditor = SoftwareEditor::find($id);
            $softwareEditor->status = $s;
            $softwareEditor->save();
            echo $s;
            exit();
        }

        // Search by name ,email and  
        $search = Input::get('search');
        $status = Input::get('status');
        if ((isset($search) && !empty($search))) {
            $search = isset($search) ? Input::get('search') : '';
               
            $softwareEditor = SoftwareEditor::where(function ($query) use ($search,$status) {
                if (!empty($search)) {
                    $query->Where('software_name', 'LIKE', "%$search%");
                }
            })->Paginate($this->record_per_page);
        } else {
            $softwareEditor = SoftwareEditor::Paginate($this->record_per_page);
        }
        
        return view('packages::softwareEditor.index', compact('softwareEditor', 'page_title', 'page_action', 'sub_page_title'));
    }

    /*
     * create   method
     * */

    public function create(SoftwareEditor $softwareEditor)
    {
        $page_title = 'Software Editor';
        $page_action = 'Create Software Editory';
 
        $url = '';

        return view('packages::softwareEditor.create', compact('softwareEditor', 'url', 'page_title', 'page_action'));
    }

    /*
     * Save   method
     * */

    public function store(SoftwareEditorRequest $request, SoftwareEditor $softwareEditor)
    {

        $photo = $request->file('image_name');
        $destinationPath = storage_path('uploads/softwareEditor');
        $photo->move($destinationPath, time().$photo->getClientOriginalName());
        $photo_name = time().$photo->getClientOriginalName();
        $request->merge(['photo'=>$photo_name]);
 
        $se = new SoftwareEditor;
        $se->software_name   =  $request->get('software_name');
        $se->slug            =  strtolower(Str::slug($request->get('software_name')));
        $se->image_name  =  $photo_name;
        $se->description     =  $request->get('description');
        
        $se->save();
         
        return Redirect::to(route('softwareEditor'))
                            ->with('flash_alert_notice', 'New Software Editor  successfully created !');
    }

    /*
     * Edit   method
     * @param
     * object : $SoftwareEditor
     * */

    public function edit($id)
    {
        $softwareEditor = SoftwareEditor::find($id);
        $page_title = 'Software Editor';
        $page_action = 'Edit Software Editor';
        $url = url::asset('storage/uploads/softwareEditor/'.$softwareEditor->image_name)  ;
        return view('packages::softwareEditor.edit', compact('url', 'softwareEditor', 'page_title', 'page_action'));
    }

    public function update(SoftwareEditorRequest $request, $id)
    {
        $validate_cat = SoftwareEditor::where('software_name', $request->get('software_name'))
                            ->where('id', '!=', $id)
                            ->first();
         
        if ($validate_cat) {
            return  Redirect::back()->withInput()->with(
                'field_errors',
                'The software editor name already been taken!'
            );
        }


        if ($request->file('image_name')) {
            $photo = $request->file('image_name');
            $destinationPath = storage_path('uploads/softwareEditor');
            $photo->move($destinationPath, time().$photo->getClientOriginalName());
            $photo_name = time().$photo->getClientOriginalName();
            $request->merge(['photo'=>$photo_name]);
        }
        $softwareEditor = SoftwareEditor::find($id);
        
        $softwareEditor                  =  SoftwareEditor::find($id);

        $softwareEditor->software_name   =  $request->get('software_name');
        $softwareEditor->slug            =  strtolower(Str::slug($request->get('software_name')));
        // $softwareEditor->image_name      =  $request->get('photo');
        $softwareEditor->description     =  $request->get('description');

        if (isset($photo_name)) {
            $softwareEditor->image_name  =  $photo_name;
        }
         
        $softwareEditor->save();


        return Redirect::to(route('softwareEditor'))
                        ->with('flash_alert_notice', '  Software Editor  successfully updated.');
    }
    /*
     *Delete User
     * @param ID
     *
     */
    public function destroy($id)
    {
        SoftwareEditor::where('id', $id)->delete();
        return Redirect::to(route('softwareEditor'))
                        ->with('flash_alert_notice', 'Software Editor successfully deleted.');
    }

    public function show(SoftwareEditor $softwareEditor)
    {
    }
}
