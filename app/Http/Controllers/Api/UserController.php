<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\User;
use Illuminate\Support\Facades\Auth; 
use App\Models\Notification;
use Illuminate\Contracts\Encryption\DecryptException;
use Config,Mail,View,Redirect,Validator,Response; 
use Crypt,okie,Hash,Lang,JWTAuth,Input,Closure,URL; 
use App\Helpers\Helper as Helper;
use PHPMailerAutoload;
use PHPMailer; 
use App\Models\Competition;
use App\Models\TeamA;
use App\Models\TeamB;
use App\Models\Toss;
use App\Models\Venue;
use App\Models\Matches;
use App\Models\Player;
use App\Models\TeamASquad;
use App\Models\TeamBSquad;
use App\Models\CreateContest;
use App\Models\CreateTeam;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Rank;
use App\Models\JoinContest;
use App\Models\ReferralCode;

class UserController extends BaseController
{
    public $download_link;
    public function __construct(Request $request) {

        $apk_updates = \DB::table('apk_updates')->orderBy('id','desc')->first(); 
        $this->download_link = $apk_updates->url??null;

        if ($request->header('Content-Type') != "application/json")  {
            $request->headers->set('Content-Type', 'application/json');
        }  
    } 

    public function inviteUser(Request $request,User $inviteUser)
    {   
        $messages = [
            'user_id.required' => 'Invalid User id', 
            'email.required' => 'Provide email id'

        ];
        $validator = Validator::make($request->all(), [
                'user_id' => 'required',   
                 'email' => 'required|email'
            ],$messages);  
         
        $user_id = $request->get('user_id'); 
        $invited_user = User::find($user_id); 
        // Return Error Message
        if ($validator->fails() || $invited_user ==null) {
            $error_msg =[];        
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }  
                return Response::json(array( 
                    'status' => false,
                    "code"=> 201,
                    'message' => $error_msg??'Opps! This user is not available'
                    )
                ); 
        } 
        
        $user_first_name = $invited_user->name ;
        
        $user_email = $request->input('email');

        /** --Send Mail after Sign Up-- **/
        
        $user_data     = User::find($user_id); 
        $sender_name   = $user_data->name;
        $invited_by    = $user_data->name==null?$invited_user->first_name.' '.$invited_user->last_name:$user_data->name;
        $referal_code  = $user_data->user_name;
        $receipent_name = "Hi,";
        $subject       = ucfirst($sender_name)." has invited you to join SportsFight";  

        $email_content = [
                'receipent_email'=> $user_email,
                'subject'=>$subject,
                'receipent_name'=>$receipent_name,
                'invite_by'=>$invited_by,
                'download_link' => $this->download_link,
                'referal_code' => $referal_code
            ];
        
        $helper = new Helper;
        
        $invite_notification_mail = $helper->sendNotificationMail($email_content,'invite_notification_mail');
        
        //$user->save();

        return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"You've invited your colleague, nice work!",
                    'data' => ['receipentEmail'=>$user_email]
                   ]
                );
    }

    public function generateUserName(){
        $uname =  Helper::generateRandomString();
        $is_user = 1;   
        while ($is_user=null) {
            $is_user = User::where('user_name',$uname)->first();
            if($is_user){
                $uname      = Helper::generateRandomString();
            }
        }
        return $uname;
    }

    public function verifyDocument(Request $request){
        
        $user = User::find($request->user_id); 
        $messages = [
            'user_id.required' => 'Invalid User id', 
            'adhar.required' => 'Please upload Adhar card'

        ];
        $validator = Validator::make($request->all(), [
                'user_id'   => 'required',  
                'pan'       => 'mimes:jpeg,bmp,jpg,png,gif,pdf',
                'adhar'     => 'mimes:jpeg,bmp,jpg,png,gif,pdf'
            ],$messages);  
         
        // Return Error Message
        if ($validator->fails() || $user ==null) {
            $error_msg =[];        
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }  
                return Response::json(array( 
                    'status' => false,
                    "code"=> 201,
                    'message' => $error_msg??'Opps! This user is not available'
                    )
                ); 
        } 
         $doc = \DB::table('verify_documents')
                    ->where('user_id',$user->id)
                    ->first();
        if($doc){
            
            return Response::json(array( 
                    'status' => true,
                    "code"=> 200,
                    'message' => $doc->status==1?'Document already verified':'Waiting for approval'
                    )
                );    
        }

        $data['user_id'] = $user->id;

        if ($request->file('pan')) {
            $pan = $request->file('pan');
            $destinationPath = public_path('upload/document/pan');
            $pan->move($destinationPath, $user->id.'_'.$pan->getClientOriginalName());
            $pan_name = $user->id.'_'.$pan->getClientOriginalName();
            $request->merge(['pan_url'=>$pan_name]);  
            $data['pan_url']  = url::to(asset('public/upload/document/pan/'.$pan_name));
            $data['pan'] = $pan_name;
            $data['upload_status'] = 'uploaded';
        } 

        if ($request->file('adhar')) {
            $adhar = $request->file('adhar');
            $destinationPath = public_path('upload/document');
            $adhar->move($destinationPath, $user->id.'_'.$adhar->getClientOriginalName());
            $adhar_name = $user->id.'_'.$adhar->getClientOriginalName();
            $request->merge(['adhar_url'=>$adhar_name]);  
            $data['adhar_url']  = url::to(asset('public/upload/document/adhar/'.$adhar_name));
            $data['adhar'] = $adhar_name;
            $data['upload_status'] = 'uploaded';
        } 

        $doc = \DB::table('verify_documents')
                ->updateOrInsert(['user_id'=>$user->id],$data);

        return Response::json(array( 
                    'status' => true,
                    "code"=> 200,
                    'message' => "Document uploaded.We'll notify you soon."
                    )
                ); 

    } 
    public function myReferralDetails(Request $request)
    {
        $referal_user = ReferralCode::where('refer_by',$request->user_id)
                        ->pluck('user_id')->toArray();
        $data = User::whereIn('id',$referal_user)->select('id','first_name as name')->get();
        if($data){
             return Response::json(array( 
                    'status' => true,
                    "code"=> 200,
                    'message' => "List of referal",
                    'response' => [
                            'referal_user' => $data
                        ] 
                    )
                );
         }else{
             return Response::json(array( 
                    'status' => false,
                    "code"=> 201,
                    'message' => "No referal user found"
                    )
                );
         }

    }
    public function updateAfterLogin(Request $request){

        $refer_by = User::where('referal_code',$request->referral_code)
                    ->orWhere('user_name',$request->referral_code)
                    ->first();

        $user_id = $request->user_id;
        $user = User::find($user_id);

        if($refer_by && $user)
        {    
            $referralCode = new ReferralCode;
            $referralCode->referral_code    =   $request->referral_code;
            $referralCode->user_id          =   $user_id;
            $referralCode->refer_by         =   $refer_by->id;
            $referralCode->save();
        }
        
        if($user){
            $user->name             = $request->name;
            $user->mobile_number    = $request->mobile_number;
            $user->phone            = $request->phone;
            $user->profile_image    = $request->image_url;
            $user->reference_code   = $request->referral_code;
            $user->save();

            return Response::json(array( 
                    'status' => true,
                    "code"=> 200,
                    'message' => "Details successfully saved",
                    'login_user' =>$user->id 
                    )
                );
        }else{
            return Response::json(array( 
                    'status' => false,
                    "code"=> 201,
                    'message' => "user is not registered"
                    )
                );
        }
        
    }
    public function registration(Request $request)
    {   
        $input['first_name']    = $request->get('first_name')??$request->get('name');
              
        $input['name']          = $request->name; 
        $input['email']         = $request->get('email'); 
        $input['password']      = Hash::make($request->input('password'));
        $input['role_type']     = 3; //$request->input('role_type'); ;
        $input['user_type']     = $request->get('user_type');
        $input['provider_id']   = $request->get('provider_id'); 
        $input['mobile_number']     = $request->get('mobile_number');
       
        if($input['user_type']=='googleAuth' || $input['user_type']=='facebookAuth' ){
                //Server side valiation
                $validator = Validator::make($request->all(), [
                   'email' => 'required|email',
                   'name' => 'required',
                   'provider_id' => 'required'
                ]);
        }else{
            //Server side valiation
            $validator = Validator::make($request->all(), [
               'email' => 'required|email|unique:users',
               'password' => 'required'
            ]);
        }
         
        /** Return Error Message **/
        if ($validator->fails()) {
            $error_msg      =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                    
            return Response::json(array(
                'status' => false,
                'code'=>201,
                'message' => $error_msg[0]
                )
            );
        } 
         
        \DB::beginTransaction();

        $helper = new Helper;
        /** --Create USER-- **/
        $user = new User;
        foreach ($input as $key => $value) {
            $user->$key = $value;    
        }
        $uname              = strtoupper(substr($user->name, 0, 3)).$this->generateUserName();
        $user->user_name    = $uname;
        $user->referal_code = $uname;
        
        $user->save(); 

        if($user->id){
            $wallet = new Wallet;
            $wallet->user_id = $user->id;
            $wallet->validate_user = Hash::make($user->id);
            $wallet->save();
            $wallet  =  Wallet::find($wallet->id);
        }
            
        \DB::commit();
        
        $user  = User::find($user->id);
        $user->validate_user    = Hash::make($user->id);
        $user->reference_code   = $request->referral_code;
        $user->mobile_number    = $request->mobile_number;
        $user->phone            = $request->phone;  
        $user->save();

        $token = $user->createToken('SportsFight')->accessToken;
        $user_data['referal_code']     =  $user->user_name;
        $user_data['user_id']          =  $user->id;
        $user_data['name']             =  $user->name; 
        $user_data['email']            =  $user->email; 
        $user_data['bonus_amount']     =  (float)$wallet->bonus_amount;
        $user_data['usable_amount']    =  (float)$wallet->usable_amount;
        $user_data['mobile_number']    =  ($user->phone==null)?$user->mobile_number:$user->phone; 
        
        $subject = "Welcome to SportsFight! Verify your email address to get started";
        $email_content = [
                'receipent_email'=> $request->input('email'),
                'subject'=>     $subject,
                'greeting'=>    'SportsFight',
                'first_name'=> $request->input('name')??$request->input('first_name')
                ];

      //$verification_email = $helper->sendMailFrontEnd($email_content,'verification_link');
        
        
        $notification = new Notification;
        $notification->addNotification('user_register',$user->id,$user->id,'User register','');

        // user device details
        $devD = \DB::table('hardware_infos')->where('user_id',$user->id)->first();
        if($devD){
            $deviceDetails = json_encode($request->deviceDetails);
            \DB::table('hardware_infos')->where('user_id',$devD->user_id)->update([
            'user_id' => $user->id,
            'device_details' => $deviceDetails
            ]);
        }else{
           $deviceDetails = json_encode($request->deviceDetails);
            \DB::table('hardware_infos')->insert([
            'user_id' => $user->id??0,
            'device_details' => $deviceDetails
            ]); 
        }
        $apk_updates = \DB::table('apk_updates')->orderBy('id','desc')->first();
        $data['apk_url'] =  $apk_updates->url??null;  
        //reference_code

        $refer_by = User::where('referal_code',$request->referral_code)
                    ->orWhere('user_name',$request->referral_code)
                    ->first();

        
        if($refer_by && $user)
        {    
            $referralCode = new ReferralCode;
            $referralCode->referral_code    =   $request->referral_code;
            $referralCode->user_id          =   $user->id;
            $referralCode->refer_by         =   $refer_by->id;
            $referralCode->save();
        }
        
        if($user){
            $user->name             = $request->name;
            $user->mobile_number    = $request->mobile_number;
            $user->phone            = $request->phone;
            $user->profile_image    = $request->image_url;
            $user->reference_code   = $request->referral_code;
            $user->save(); 
        }

        return response()->json(
                            [ 
                                "status"=>true,
                                "code"=>200,
                                "message"=>"Thank you for registration. Please verify  your email.",
                                'data' => $user_data,
                                'token' => $token??null
                            ]
                        );
    }

    public function updateProfile(Request $request)
    {     
        $user = User::find($request->user_id); 
        if(!$request->user_id && (User::find($request->user_id))==null)
        {
            return Response::json(array(
                'status' => false,
                'code' => 201,
                'message' => 'Invalid user Id!',
                'data'  =>  $request->all()
                )
            );
        } 

        if($request->user_name){

            $user_id = User::where('id','!=',$request->user_id)->where('user_name',$request->user_name)->first();
            
            if($user_id){
               return Response::json(array(
                    'status' => false,
                    'code' => 201,
                    'message' => 'User Id already taken!',
                    'data'  =>  $request->all()
                    )
                ); 
            }

        }

        $table_cname = \Schema::getColumnListing('users');
        $except = ['id','created_at','updated_at','profile_image','modeOfreach'];
        
        foreach ($table_cname as $key => $value) {
           
           if(in_array($value, $except )){
                continue;
           } 
            if($request->get($value)){
                $user->$value = $request->get($value);
           }
        }
       
        
        if($request->get('profile_image')){ 
            $profile_image = $this->createImage($request->get('profile_image')); 
            if($profile_image==false){
                return Response::json(array(
                    'status' => false,
                     'code' => 201,
                    'message' => 'Invalid Image format!',
                    'data'  =>  $request->all()
                    )
                );
            }
            $user->profile_image  = $profile_image;       
        }        
           

        try{
            $user->save();
            $status = true;
            $code  = 200;
            $message ="Profile updated successfully";
        }catch(\Exception $e){
            $status = false;
            $code  = 201;
            $message =$e->getMessage();
        }
         
        return response()->json(
                            [ 
                            "status" =>$status,
                            'code'   => $code,
                            "message"=> $message,
                            'data'=>isset($user)?$user:[]
                            ]
                        );
         
    }

    // Image upload

    public function createImage($base64)
    {
        try{
            $img  = explode(',',$base64);
            if(is_array($img) && isset($img[1])){
                $image = base64_decode($img[1]);
                $image_name= time().'.jpg';
                $path = storage_path() . "/image/" . $image_name;
              
                file_put_contents($path, $image); 
                return url::to(asset('storage/image/'.$image_name));
            }else{
                if(starts_with($base64,'http')){
                    return $base64;
                }
                return false; 
            }

            
        }catch(Exception $e){
            return false;
        }
        
    }

     // Validate user
    public function validateInput($request,$input){
        //Server side valiation 

        $validator = Validator::make($request->all(), $input);
         
        /** Return Error Message **/
        if ($validator->fails()) {
            $error_msg      =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }

            if($error_msg){
               return array(
                    'status' => false,
                    'code' => 201,
                    'message' => $error_msg[0],
                    'data'  =>  $request->all()
                    );
            }

        }
    }
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function customerLogin(Request $request)
    {       
       $data = [];
       // echo "Email:".$request->email;
        $input = $request->all();
       // print_r ($input);
        $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                     'user_type' => 'required'
                ]);
        if ($validator->fails()) {
            $error_msg = [];
            foreach ($validator->messages()->all() as $key => $value) {
                array_push($error_msg, $value);
            }
            if ($error_msg) {
                return array(
                    'status' => false,
                    'code' => 201,
                    'message' => $error_msg[0],
                    'data' => $request->all()
                );
            }
        }

        $user_type = $request->user_type;
        switch ($user_type) {
            case 'facebookAuth':

                $credentials = [
                        'email'=>$request->get('email'),
                        'provider_id'=>$request->get('provider_id'),
                        'user_type' => 'facebookAuth'
                    ];
                    
                if (User::where($credentials)->first() ){
                    $usermodel =  User::where('email',$request->email)->first();
                    $usermodel->provider_id = $request->get('provider_id'); 
                    $usermodel->save(); 
                    $status = true;
                    $code = 200;
                    $message = "login successfully"; 
                }else{ 
                   $user = new User;
                   
                    $user->last_name     = $request->get('last_name');
                    $usermodel->name        = $request->name;
                    $usermodel->first_name  = $request->name;
                    $user->email         = $request->get('email'); 
                    $user->role_type     = 3;//$request->input('role_type'); ;
                    $user->user_type     = $request->get('user_type');
                    $user->provider_id   = $request->get('provider_id');
                    $user->mobile_number = $request->get('mobile_number') ;
                    $user->password   = "";
                    $user->user_name =$this->generateUserName();
                     // strtoupper(substr($request->get('name'), 0, 3)).

                    /** Return Error Message **/
                    if (User::where(['email'=>$request->email])->first()) {
                       
                                
                        return Response::json(array(
                            'status' => false,
                            'code'=>201,
                            'message' =>'Invalid credentials',
                            'data'  =>  $request->all()
                            )
                        );
                    } 

                    $user->save() ;
                    if($user->id){
                        $wallet = new Wallet;
                        $wallet->user_id = $user->id;
                        $wallet->validate_user = Hash::make($user->id);
                        $wallet->save();
                        $wallet  =  Wallet::find($wallet->id);
                    }
                    $user->validate_user = Hash::make($user->id);
                    $user->save();
                    $usermodel = $user;
 
                    $status = true;
                    $code = 200;
                    $message = "login successfully"; 
                }

                break;
            case 'googleAuth':
                
               $credentials = [
                        'email'=>$request->get('email'),
                        'provider_id'=>$request->get('provider_id'),
                        'user_type' => 'googleAuth'
                    ];


                if (User::where('email',$request->email)->first()) {
                        $usermodel = User::where('email',$request->email)->first();
                        $usermodel->provider_id = $request->get('provider_id'); 
                        $usermodel->name        = $request->name;
                        $usermodel->first_name  = $request->name;
                        $usermodel->referal_code  = $usermodel->user_name;
                        $usermodel->save(); 
                        $status = true;
                        $code = 200;
                        $message = "login successfully"; 
                    } 
                else{    
                    $user = new User;
                   
                    $user->first_name    = $request->get('first_name');
                    $user->name          = $request->name;
                     
                    $user->email         = $request->get('email'); 
                    $user->role_type     = 3;//$request->input('role_type'); ;
                    $user->user_type     = $request->get('user_type');
                    $user->mobile_number     = $request->get('mobile_number');
                    $user->provider_id   = $request->get('provider_id'); 
                    $user->password   = ""; 
                    
                    $user->user_name = $this->generateUserName();

                    if (User::where(['email'=>$request->email])->first()) {
                       
                        return Response::json(array(
                                'status' => false,
                            'code'=>201,
                            'message' =>'Invalid credentials'
                            )
                        );
                    } 
                        
                    $user->save() ;
                    if($user->id){
                        $wallet = new Wallet;
                        $wallet->user_id = $user->id;
                        $wallet->validate_user = Hash::make($user->id);
                        $wallet->save(); 
                    }

                    $user->validate_user = Hash::make($user->id);
                    $user->save();
                    $usermodel =  $user;
                    $status = true;
                    $code = 200;
                    $message = "login successfully"; 
                }

                break;
            
            default:
                $credentials = [
                        'email'=>$request->get('email'),
                        'password'=>$request->get('password')
                    ];

                 $auth = Auth::attempt($credentials);

                if ($auth ){
                    $usermodel = Auth::user();
                    
                    $token = $usermodel->createToken('SportsFight')->accessToken;
 
                    $status = true;
                    $code = 200;
                    $message = "login successfully";
                }else{ 
                    $usermodel = null;
                    $status = false;
                    $code = 201;
                    $message = "login failed"; 
                }   
                break;
        }

        $data = []; 
        if($usermodel){
            $wallet  = Wallet::where('user_id',$usermodel->id)->first();
            if($wallet!=null){
                $data['referal_code']  = $usermodel->user_name;
                $data['name'] = $usermodel->name;
                $data['email'] = $usermodel->email;
                $data['user_id'] = $usermodel->id;
                $data['mobile_number'] = $usermodel->mobile_number??$usermodel->phone;
                $data['bonus_amount']     =  (float)$wallet->bonus_amount;
                $data['usable_amount']    = (float)$wallet->usable_amount;  
                $status = true;  
            }
            $devD = \DB::table('hardware_infos')->where('user_id',$usermodel->id)->first();

        if($devD){
            $deviceDetails = json_encode($request->deviceDetails);
            \DB::table('hardware_infos')->where('user_id',$devD->user_id)->update([
            'user_id' => $usermodel->id??0,
            'device_details' => $deviceDetails
            ]);

            \DB::table('users')->where('email',$request->email)->update([
                'device_id'=>$request->device_id
            ]);

        }else{
           $deviceDetails = json_encode($request->deviceDetails);
            \DB::table('hardware_infos')->insert([
            'user_id' => $usermodel->id??0,
            'device_details' => $deviceDetails
            ]); 
            }
          \DB::table('users')->where('id',$usermodel->id)->update([
                'login_status' => true,
                'device_id' => $request->device_id
            ]);       
        }

        $this->sendNotification($request->device_id, 'Login', "successfully logged in at ".date('d-m-Y h:i:s'));

        $token = Hash::make(1);
        if($usermodel){
            $token = $usermodel->createToken('SportsFight')->accessToken;
        }

        $apk_updates = \DB::table('apk_updates')->orderBy('id','desc')->first();
        $data['apk_url'] =  $apk_updates->url??null;    
        if($data){

            $server = [
                'USER_DEVICE_IP' => $_SERVER['HTTP_X_FORWARDED_FOR'],
                'COUNTRY_CODE' => $_SERVER['HTTP_CF_IPCOUNTRY'],
                'SERVER_ADDR' => $_SERVER['SERVER_ADDR'],
                'SERVER_NAME' => $_SERVER['SERVER_NAME'],
                'SERVER_ADDR' => $_SERVER['SERVER_ADDR'],
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
                'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                'user_id' => $data['user_id']??null

            ];
         
            $user_id = $data['user_id']??null;
            $user_agents = \DB::table('user_agents')
                ->updateOrInsert(['user_id'=>$user_id],$server);

            return response()->json([ 
                    "status"=>$status,
                    "code"=>$code,
                    "message"=> $message ,
                    'data'=> $data??$request->all(),
                    'token' => $token 
                 ]);   
        }else{
            return response()->json([ 
                    "status"=>$status,
                    "code"=>$code,
                    "message" => 'Invalid email or password',
                    'token' =>$token 

                 ]); 
        }
          
    }

     /* @method : Email Verification
    * @param : token_id
    * Response : jsonṭ
    * Return :token and email 
   */


    public function forgotPassword(Request $request)
    {  
        $email = $request->input('email');
        //Server side valiation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        $helper = new Helper;
       
        if ($validator->fails()) {
            $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }

        $user =   User::where('email',$email)->first();
        if($user==null){
            return Response::json(array(
                'status' => false,
                'code' => 201,
                'message' => "Oh no! The address you provided isn't in our system",
                'data'  =>  $request->all()
                )
            );
        }
        $user_data = $user;
        $enc = Crypt::encryptString($user->id);
     
        $links = url('api/v2/changePassword?token='.$enc);

        $email_content = array(
                        'receipent_email'   => $request->input('email'),
                        'subject'           => 'Your Sportsfight Account Password',
                        'name'              => $user->first_name, 
                        'greeting'          => 'Sportsfight',
                        'links'             => $links

                    );
        $helper = new Helper;
        $email_response = $helper->sendNotificationMail(
                                $email_content,
                                'forgot_password_link'
                            ); 
       
       return   response()->json(
                    [ 
                        "status"=>true,
                        "code"=> 200,
                        "message"=>"Reset password link has sent. Please check your email.",
                        'data' => $request->all()
                    ]
                );
    }

    public function changePassword(Request $request)
    {   
        $token = $request->token;
        return view('changePassword',compact('token'));        
    }
   
    public function emailVerification(Request $request)
    {
        $verification_code = $request->input('verification_code');
        $email    = $request->input('email');

        if (Hash::check($email, $verification_code)) {
           $user = User::where('email',$email)->get()->count();
           if($user>0)
           {
              User::where('email',$email)->update(['status'=>1]);  
           }else{
            echo "Verification link is Invalid or expire!"; exit();
                return response()->json([ "status"=>0,"message"=>"Verification link is Invalid!" ,'data' => '']);
           }
           echo "Email verified successfully."; exit();  
           return response()->json([ "status"=>1,"message"=>"Email verified successfully." ,'data' => '']);
        }else{
            echo "Verification link is Invalid!"; exit();
            return response()->json([ "status"=>0,"message"=>"Verification link is invalid!" ,'data' => '']);
        }
    }


    public function resetPassword(Request $request){

        $user_id =  $request->user_id;
        $old_password =  $request->old_password;
        $current_password =  $request->new_password;

        $messages = [
            'user_id.required' => 'User id is required', 
            'old_password.required' => 'Old password is required',
            'new_password.required' => 'New password is required'

        ];
        $validator = Validator::make($request->all(), [
                'user_id' => 'required',   
                 'old_password' => 'required',
                 'new_password' => 'required|min:6'
            ],$messages);  
         
        $user = User::where('id',$user_id)->first(); 
        
        // Return Error Message
        if ($validator->fails() || $user ==null) {
            $error_msg =[];        
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }  
                return Response::json(array( 
                    'status' => false,
                    "code"=> 201,
                    'message' => $error_msg[0]??'Opps! This user is not available'
                    )
                ); 
        } 

        $credentials = [
                        'email'=>$user->email,
                        'password'=>$old_password
                    ];

         $auth = Auth::attempt($credentials); 
        if($auth){
            $user->password = Hash::make($current_password);
            $user->save();
            return response()->json(
                [ 
                    "status"=>true,
                    'code'=>200,
                    "message"=>"Password reset successfully"
                ]);

        }else{
            return response()->json([ "status"=>false,'code'=>201,"message"=>"Old password do not match. Try again!"]);

        }
    }

    public function temporaryPassword(Request $request){

        $user_id =  $request->user_id;
        $user = User::where('id',$user_id)->first();
        if($user){
            return Response()->json([ "status"=>true,'code'=>200,"message"=>"Temporary Password sent"]);

        }else{
            return response()->json([ "status"=>false,'code'=>201,"message"=>"Email does not exist!"]);
        }
    }
   
    public function logout(Request $request){
        $user_id =  User::find($request->user_id);
        if($user_id){
            $user_id->login_status = false;
            $user_id->save();
            return response()->json([ "status"=>true,'code'=>200,"message"=>"Logout successfully"]);
        }else{
            return response()->json([ "status"=>false,'code'=>201,"message"=>"User does not"]); 
        }
    }

    public function deviceNotification(Request $request){

        $user_id =  User::find($request->user_id);
        $device_id = $request->device_id;

        $validator = Validator::make($request->all(), [
               'user_id' => 'required',
               'device_id' => 'required'
        ]);
        /** Return Error Message **/
        if ($validator->fails()) {
            $error_msg      =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                    
            return Response::json(array(
                'status' => false,
                'code'=>201,
                'message' => $error_msg[0]
                )
            );
        } 

        if($user_id){
            $user_id->device_id = $device_id;
            $user_id->save();
            return response()->json([ "status"=>true,'code'=>200,"message"=>"notification updated"]);
        }else{
            return response()->json([ "status"=>false,'code'=>201,"message"=>"something went wrong"]); 
        }
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
}
