<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\ApkUpdateRequest;
use Modules\Admin\Models\User; 
use Input, Validator, Auth, Paginate, Grids, HTML;
use Form, Hash, View, URL, Lang, Session, DB;
use Route, Crypt, Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Dispatcher; 
use App\Helpers\Helper;
use Modules\Admin\Models\Roles; 
use Modules\Admin\Models\ApkUpdate; 
 

/**
 * Class AdminController
 */
class ApkUpdateController extends Controller {
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
        View::share('viewPage', 'ApkUpdate');
        View::share('sub_page_title', 'ApkUpdate');
        View::share('helper',new Helper);
        View::share('heading','ApkUpdate');
        View::share('route_url',route('apkUpdate'));

        $this->record_per_page = Config::get('app.record_per_page');
    }

   
    /*
     * Dashboard
     * */

    public function index(ApkUpdate $apkUpdate, Request $request) 
    { 
        $page_title = 'ApkUpdate';
        $sub_page_title = 'ApkUpdate';
        $page_action = 'View ApkUpdate'; 


        if ($request->ajax()) {
            $id = $request->get('id'); 
            $banner = ApkUpdate::find($id); 
            $banner->status = $s;
            $banner->save(); 
            exit();
        }

        // Search by name ,email and group
        $search = Input::get('search');
        if ((isset($search) && !empty($search))) {

            $search = isset($search) ? Input::get('search') : '';
               
            $apkUpdate = ApkUpdate::where(function($query) use($search) {
                        if (!empty($search)) {
                            $query->Where('title', 'LIKE', "%$search%");
                        }
                        
                    })->Paginate($this->record_per_page);
        } else {
            $apkUpdate = ApkUpdate::Paginate($this->record_per_page);
        }
         
          
        return view('packages::apkUpdate.index', compact('apkUpdate', 'page_title', 'page_action','sub_page_title'));
    }

    /*
     * create Group method
     * */

    public function create(ApkUpdate $apkUpdate) 
    {
         
        $page_title = 'apkUpdate';
        $page_action = 'Create apkUpdate';
 
        $url = '';

        return view('packages::apkUpdate.create', compact('url','apkUpdate', 'page_title', 'page_action'));
    }

    /*
     * Save Group method
     * */
    public function sendNotification($token, $data){
     
        $serverLKey = 'AIzaSyAFIO8uE_q7vdcmymsxwmXf-olotQmOCgE';
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

       $extraNotificationData = $data;

       $fcmNotification = [
           //'registration_ids' => $tokenList, //multple token array
           'to' => $token, //single token
           //'notification' => $notification,
           'data' => $extraNotificationData
       ];

       $headers = [
           'Authorization: key='.$serverLKey,
           'Content-Type: application/json'
       ];


       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $fcmUrl);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
       $result = curl_exec($ch);
       //echo "result".$result;
       //die;
       curl_close($ch);
       return true;
    }
    public function store(ApkUpdateRequest $request, ApkUpdate $apkUpdate) 
    {  
        
         
        $apk = $request->file('apk');
        $destinationPath = public_path('upload/apk');
        $apk->move($destinationPath, 'sportsfight.'.$apk->getClientOriginalExtension());
        $apkUrl = 'sportsfight.'.$apk->getClientOriginalExtension();
        $request->merge(['apkUrl'=>$apkUrl]);
        
        $apkUpdate = new ApkUpdate;

        $apkUpdate->title           =  $request->get('title');
        $apkUpdate->apk             =  $apkUrl;
        $apkUpdate->url             =  url('public/upload/apk/'.$apkUrl);
        $apkUpdate->message         =  $request->get('message');
        $apkUpdate->version_code    =  $request->get('version_code');
        $apkUpdate->release_notes   =  $request->get('release_notes');
         
        $apkUpdate->save();   
        $apkUrl = url('public/upload/apk/'.$apkUrl);    
        User::whereNotNull('device_id')
                ->get()
                ->transform(function($item, $key) use($apkUpdate,$apkUrl,$request){
                    
                    $token = $item->device_id;

                    $data = [
                            'action' => 'update' ,
                            'title' => 'New update available' ,
                            'message' => 'Stable release' ,
                            'apk_update_url' => $apkUrl,
                            'release_note' => $request->get('release_notes')
                        ];

                    $this->sendNotification($token,$data);
                });
       

        return Redirect::to(route('apkUpdate'))
                            ->with('flash_alert_notice', 'New apkUpdate  successfully uploaded !');
        }

    /*
     * Edit Group method
     * @param 
     * object : $banner
     * */

    public function edit($id) {
        $apkUpdate = ApkUpdate::find($id);
        $page_title = 'apkUpdate';
        $page_action = 'Edit apkUpdate'; 
        $url = $apkUpdate->url;

        return view('packages::apkUpdate.edit', compact( 'url','apkUpdate', 'page_title', 'page_action'));
    }

    public function update(ApkUpdateRequest $request,  $id) {
        $apkUpdate = ApkUpdate::find($id);

        $validate_cat = ApkUpdate::where('version_code',$request->get('version_code'))
                            ->where('id','!=',$apkUpdate->id)
                            ->first();
         
        if($validate_cat){
              return  Redirect::back()->withInput()->with(
                'field_errors','The ApkUpdate version_code already been uploaded!'
            );
        } 

        if ($request->file('url')) 
        {
            $apk = $request->file('url');
            $destinationPath = public_path('upload/apk');
            $apk->move($destinationPath, 'sportsfight.'.$apk->getClientOriginalExtension());
            $apkUrl = 'sportsfight.'.$apk->getClientOriginalExtension();
            $request->merge(['apkUrl'=>$apkUrl]);
            $apkUpdate->url          =  url('public/upload/apk/'.$apkUrl);
            $apkUpdate->apk          =  $apkUrl;	
        } 
        $apkUpdate->title           =  $request->get('title');
        $apkUpdate->message         =  $request->get('message');
        $apkUpdate->version_code    =  $request->get('version_code');
        $apkUpdate->release_notes   =  $request->get('release_notes');
         
        $apkUpdate->save();    


        return Redirect::to(route('apkUpdate'))
                        ->with('flash_alert_notice', ' ApkUpdate  successfully updated.');
    }
    /*
     *Delete User
     * @param ID
     * 
     */
    public function destroy($id) {

        ApkUpdate::where('id',$id)->delete(); 
        return Redirect::to(route('apkUpdate'))
                        ->with('flash_alert_notice', ' ApkUpdate  successfully deleted.');
        
    }

    public function show(Banner $apkUpdate) {
        
    }

}
