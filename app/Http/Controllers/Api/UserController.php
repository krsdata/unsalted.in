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



class UserController extends BaseController
{
   
    public function __construct(Request $request) {

        if ($request->header('Content-Type') != "application/json")  {
            $request->headers->set('Content-Type', 'application/json');
        }
    } 

    public function registration(Request $request)
    {   
        $input['first_name']    = $request->input('first_name');
        $input['last_name']     = $request->input('last_name'); 
        $input['email']         = $request->input('email'); 
        $input['password']      = Hash::make($request->input('password'));
        $input['role_type']     = 3; //$request->input('role_type'); ;
        $input['user_type']     = $request->input('user_type');
        $input['provider_id']   = $request->input('provider_id'); 

        $user = User::firstOrNew(['provider_id'=>$request->input('provider_id')]);
       
        if($request->input('user_id')){
            $u = $this->updateProfile($request,$user);
            return $u;
        } 

        if($input['user_type']=='googleauth' || $input['user_type']=='facebookauth' ){
                //Server side valiation
                $validator = Validator::make($request->all(), [
                   'first_name' => 'required',
                   'email' => 'required'
                ]);

        }else{
            //Server side valiation
            $validator = Validator::make($request->all(), [
               'first_name' => 'required',
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
                'message' => $error_msg[0],
                'data'  =>  $request->all()
                )
            );
        } 
         
        $helper = new Helper;
        /** --Create USER-- **/
        $user = User::create($input); 

        $subject = "Welcome to Plug11! Verify your email address to get started";
        $email_content = [
                'receipent_email'=> $request->input('email'),
                'subject'=>$subject,
                'greeting'=> 'PLUG11',
                'first_name'=> $request->input('first_name')
                ];

       $verification_email = $helper->sendMailFrontEnd($email_content,'verification_link');
        
        
        $notification = new Notification;
        $notification->addNotification('user_register',$user->id,$user->id,'User register','');
       
        return response()->json(
                            [ 
                                "status"=>1,
                                "code"=>200,
                                "message"=>"Thank you for registration",
                                'data'=>$user
                            ]
                        );
    }


    public function updateProfile(Request $request)
    {     

        $user = User::find($request->user_id); 

        if(!$request->user_id && (User::find($request->user_id))==null)
        {
            return Response::json(array(
                'status' => 0,
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
                    'status' => 0,
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
                    'status' => 0,
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
            $status = 1;
            $code  = 200;
            $message ="Profile updated successfully";
        }catch(\Exception $e){
            $status = 0;
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
                    'status' => 0,
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
       // echo "Email:".$request->email;
        $input = $request->all();
       // print_r ($input);
        $validator = Validator::make($request->all(), [
                    'email' => 'required',
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
                   $usermodel = User::where($credentials)->first();

                    $status = true;
                    $code = 200;
                    $message = "login successfully"; 
                      
                }else{ 
                   $user = new User;
                   
                    $user->first_name    = $request->get('first_name');
                    $user->last_name     = $request->get('last_name'); 
                    $user->email         = $request->get('email'); 
                    $user->role_type     = 3;//$request->input('role_type'); ;
                    $user->user_type     = $request->get('user_type');
                    $user->provider_id   = $request->get('provider_id'); 
                    $user->password   = "";
                    
                     

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

                 if (User::where($credentials)->first() ){
                   $usermodel = User::where($credentials)->first();
                   
                    $status = true;
                    $code = 200;
                    $message = "login successfully"; 
                      
                }else{   
                    $user = new User;
                   
                    $user->first_name    = $request->get('first_name');
                    $user->last_name     = $request->get('last_name'); 
                    $user->email         = $request->get('email'); 
                    $user->role_type     = 3;//$request->input('role_type'); ;
                    $user->user_type     = $request->get('user_type');
                    $user->provider_id   = $request->get('provider_id'); 
                    $user->password   = "";
                    

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
            $data['first_name'] = $usermodel->first_name;
            $data['last_name'] = $usermodel->last_name;
            $data['user_email'] = $usermodel->email;
            $data['user_id'] = $usermodel->id;
            $data['mobile_number'] = $usermodel->phone;
     
        }
       
        
        return response()->json([ 
                    "status"=>$status,
                    "code"=>$code,
                    "message"=> $message ,
                    'data' => $data
                 ]);   
    }

}
