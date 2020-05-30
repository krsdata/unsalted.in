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
use Modules\Admin\Models\Notification;  
use Response; 
/**
 * Class AdminController
 */
class NotificationController extends Controller {
    /**
     * @var  Repository
     */

    /**
     * Displays all admin.
     *
     * @return \Illuminate\View\View
     */
    public function __construct(Notification $notification) { 
        $this->middleware('admin');
        View::share('viewPage', 'Notification');
        View::share('sub_page_title', 'Notification');
        View::share('helper',new Helper);
        View::share('heading','Notification');
        View::share('route_url',route('notification')); 
        $this->record_per_page = Config::get('app.record_per_page'); 
    }

   
    /*
     * Dashboard
     * */

    public function index(Notification $notification, Request $request) 
    { 
        $page_title = 'Notification';
        $sub_page_title = 'View Notification';
        $page_action = 'View Notification'; 

 
        // Search by name ,email and group
        $search = Input::get('search');
        $status = Input::get('status');
        if ((isset($search) && !empty($search))) {

            $search = isset($search) ? Input::get('search') : '';
               
            $notification = Notification::where(function($query) use($search,$status) {
                        if (!empty($search)) {
                            $query->Where('title', 'LIKE', "%$search%");
                        }
                        
                    })->where('notified_user',1)->Paginate($this->record_per_page);
        } else {
            $notification = Notification::where('notified_user',1)->Paginate($this->record_per_page);
        }
         
        return view('packages::notification.index', compact('notification','page_title', 'page_action','sub_page_title'));
    }

    /*
     * create ContestType
     * */

    public function create(Notification $notification) 
    {
        $page_title     = 'Notification';
        $page_action    = 'Create Notification'; 
        

        return view('packages::notification.create', compact( 'notification','page_title', 'page_action'));
    }

    

    /*
     * Save Group method
     * */

    public function store(Request $request, Notification $notification) 
    {   
        $notification->fill(Input::all()); 
        $notification->notified_user=1;
        $notification->save();   

        $user = User::get()->transform(function($item,$key)use($notification){

                $data = [
                'action' => 'notify' ,
                'title' => $notification->title,
                'message' => $notification->message
            ];


                $device_id =  $item->device_id;
                $this->sendNotification($device_id,$data);

        });

        return Redirect::to(route('notification'))
                            ->with('flash_alert_notice', 'New Notification  successfully created!');
    }

    /*
     * Edit Group method
     * @param 
     * object : $category
     * */

    public function edit($id) {
        $notification = Notification::find($id);
        $page_title     = 'Notification';
        $page_action    = 'Edit Notification'; 
        
        return view('packages::notification.edit', compact('notification', 'page_title', 'page_action'));
    }

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

    public function update(Request $request, $id) {
        $notification = Notification::find($id);
        $notification->fill(Input::all()); 
        $notification->save(); 


        $user = User::get()->transform(function($item,$key)use($notification){

                $data = [
                'action' => 'notify' ,
                'title' => $notification->title,
                'message' => $notification->message
            ];


                $device_id =  $item->device_id;
                $this->sendNotification($device_id,$data);

        });

        return Redirect::to(route('notification'))
                        ->with('flash_alert_notice', 'Notification  successfully updated.');
    }
    /*
     *Delete User
     * @param ID
     * 
     */
    public function destroy($id) { 
        
        Notification::where('id',$id)->delete();
        return Redirect::to(route('notification'))
                        ->with('flash_alert_notice', 'Notification  successfully deleted.');
    }

    public function show($id) {
	
try{
 
        $notification = Notification::find((int)$id);
 	if(!$notification){
		return Redirect::to(route('notification'));
	}
        $page_title     = 'Notification';
        $page_action    = 'Show Notification'; 
        $result = $notification;
        $notification = Notification::where('id',$notification->id)->select('*')->first()->toArray();
    
 } catch(Exception $e){
 	 
}
	   
        return view('packages::notification.show', compact( 'result','notification','page_title', 'page_action'));

    }

}