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
use Modules\Admin\Models\EditorPosts as EditorPost;
use Modules\Admin\Models\Document;
use Modules\Admin\Models\BankAccounts;
use Illuminate\Support\Facades\Cache;

/**
 * Class AdminController
 */
class DocumentController extends Controller
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
        View::share('viewPage', 'documents');
        View::share('sub_page_title', 'Document');
        View::share('helper', new Helper);
        View::share('heading', 'Customer Document');
        View::share('route_url', route('documents'));
        $this->record_per_page = Config::get('app.record_per_page');
    }

    /*
     * Dashboard
     * */

    public function bankAccount(BankAccounts $bankAccounts, Request $request){

        $page_title     = 'Document';
        $sub_page_title = 'Document';
        $page_action    = 'View Bank Accounts'; 
 
        
        // Search by name ,email and  
        $search = Input::get('search'); 
        $user = User::where('email','LIKE',"%$search%")
                            ->orWhere('first_name','LIKE',"%$search%")
                            ->orWhere('name','LIKE',"%$search%")
                            ->get('id')->pluck('id');
        
        if ((isset($search) && !empty($search))) {

            $documents = BankAccounts::with('user')->where(function ($query) use ($search,$user) {
                if (!empty($search) && $user->count()) {
                    
                   $query->whereIn('user_id', $user);
                }elseif ($user->count()==0) { 
                    $query->orWhere('bank_name','LIKE',"%$search%");
                    $query->orWhere('account_name','LIKE',"%$search%");
                    $query->orWhere('account_number','LIKE',"%$search%");
                    $query->orWhere('ifsc_code','LIKE',"%$search%");
                }
            })->orderBy('id','desc')->Paginate($this->record_per_page);
        } else {
            $documents = BankAccounts::with('user')
                        ->orderBy('id','desc')
                        ->Paginate($this->record_per_page);
        }
       
        return view('packages::documents.bank', compact('documents', 'page_title', 'page_action', 'sub_page_title'));
    }

    public function index(Document $documents, Request $request)
    {
        $page_title     = 'Document';
        $sub_page_title = 'Document';
        $page_action    = 'View Document'; 
        
        // Search by name ,email and  
        $search = Input::get('search'); 
        $user = User::where('email','LIKE',"%$search%")
                            ->orWhere('first_name','LIKE',"%$search%")
                            ->get('id')->pluck('id');
          
        if ((isset($search) && !empty($search))) {

            $documents = Document::with('user')->where(function ($query) use ($search,$user) {
                if (!empty($search) && !empty($user)) {
                   $query->whereIn('user', $user);
                }
            })->orderBy('id','desc')->Paginate($this->record_per_page);
        } else {
            $documents = Document::with('user')
                        ->orderBy('id','desc')
                        ->Paginate($this->record_per_page);
        }
        //dd($documents);
        return view('packages::documents.index', compact('documents', 'page_title', 'page_action', 'sub_page_title'));
    }

    /*
     * create   method
     * */

    public function create(Document $documents)
    {
         return Redirect::to(route('documents'))
                            ->with('flash_alert_notice', 'You can not create Document');
    }
    /*
     * Save   method
     * */
    public function store(Request $request, Document $documents)
    {
        $doc_id = $request->get('doc_id');
        $documents =  Document::where('id',$doc_id)->first();
        $documents->status = 1;
        $documents->save(); 
        return Redirect::to(route('documents'))
                            ->with('flash_alert_notice', 'Documents Approved successfully   !');
    }
    /*
     * Edit   method
     * @param
     * object : $documents
     * */
    public function edit( Request $request ,$id)
    {
        $editor = User::where('role_type',5)->pluck('first_name','id');
        $editorPost  = EditorPost::find($id);
        $page_title = '  Document';
        $page_action = 'Edit   Document';
        $url = '';//url::asset('storage/uploads/editorPortfolio/'.$editorPost->image_name)  ;
        
        $category_name = Category::pluck('category_name','id');
        $software_editor = SoftwareEditor::pluck('software_name','id');

        return view('packages::documents.edit', compact('url', 'editorPost', 'page_title', 'page_action','category_name','software_editor','editor'));
    }

    public function update(EditorPortfolioRequest $request, $id)
    { 
        return Redirect::to(route('documents'))
                        ->with('flash_alert_notice','Document successfully updated.');
    }
    /*
     *Delete User
     * @param ID
     *
     */
    public function destroy($id)
    {
        Document::where('id', $id)->delete();
        return Redirect::to(route('documents'))
                        ->with('flash_alert_notice', '  Document successfully deleted.');
    }

    public function show(Document $document)
    {
    }
}
