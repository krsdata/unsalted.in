 <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="page-content-wrapper">
                <!-- BEGIN CONTENT BODY -->
                <div class="page-content">
                    <!-- BEGIN PAGE HEAD-->
                    <div class="page-head">
                        <!-- BEGIN PAGE TITLE -->
                        <div class="page-title">
                            <h1>My Dashboard
                                <small>user, group category and category</small>
                            </h1>
                        </div>
                        <!-- END PAGE TITLE -->
                      
                        <!-- END PAGE TOOLBAR -->
                    </div>
                    <!-- END PAGE HEAD-->
                    <!-- BEGIN PAGE BREADCRUMB -->
                    <ul class="page-breadcrumb breadcrumb">
                        <li>
                            Home
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span class="active">Dashboard</span>
                        </li>
                    </ul>
                    <!-- END PAGE BREADCRUMB -->
                    <!-- BEGIN PAGE BASE CONTENT -->
                    <div class="row">
                       
                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                    <div class="number">
                                        <h3 class="font-purple-soft">
                                            <span data-counter="counterup" data-value="276">{{$users_count}}</span>
                                        </h3>
                                        <small>Manage USERS</small>
                                    </div>
                                    <div class="icon">
                                        <i class="icon-user"></i>
                                    </div>
                                </div>
                                <div class="progress-info">
                                    <div class="progress">
                                        <span style="width: 57%;" class="progress-bar progress-bar-success purple-soft">
                                            <span class="sr-only">56% change</span>
                                        </span>
                                    </div>
                                     
                                </div>
                            </div>
                        </div>
    
                        

                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">{{$match}}</span>
                                        </h3>
                                        <small> Total Matches </small>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                </div>
                                <div class="progress-info">
                                    <div class="progress">
                                        <span style="width: {{$match}}%;" class="progress-bar progress-bar-success blue-sharp">
                                            <span class="sr-only">{{$banner}}% grow</span>
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">{{$contest_types or 0 }}</span>
                                        </h3>
                                        <small> Total Content Type  </small>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                </div> 
                                <div class="progress-info">
                                    <div class="progress">
                                        <span style="width: {{$contest_types}}%;" class="progress-bar progress-bar-success blue-sharp">
                                            <span class="sr-only">{{$contest_types}}% grow</span>
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">{{$banner or 0 }}</span>
                                        </h3>
                                        <small> Banners </small>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                </div> 
                                <div class="progress-info">
                                    <div class="progress">
                                        <span style="width: {{$banner}}%;" class="progress-bar progress-bar-success blue-sharp">
                                            <span class="sr-only">{{$banner}}% grow</span>
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>


                      <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">{{$match_3}}</span>
                                        </h3>
                                        <small> Live Matches </small>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                </div>
                                <div class="progress-info">
                                    <div class="progress">
                                        <span style="width: {{$match_3}}%;" class="progress-bar progress-bar-success blue-sharp">
                                            <span class="sr-only">{{$match_3}}% grow</span>
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                         <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">{{$match_2}}</span>
                                        </h3>
                                        <small> Completed Matches </small>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                </div>
                                <div class="progress-info">
                                    <div class="progress">
                                        <span style="width: {{$match_2}}%;" class="progress-bar progress-bar-success blue-sharp">
                                            <span class="sr-only">{{$match_2}}% grow</span>
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                         <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">{{$match_1}}</span>
                                        </h3>
                                        <small> Upcoming Matches </small>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                </div>
                                <div class="progress-info">
                                    <div class="progress">
                                        <span style="width: {{$match_1}}%;" class="progress-bar progress-bar-success blue-sharp">
                                            <span class="sr-only">{{$match_3}}% grow</span>
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>



                   
                   
                    
                    <!-- END PAGE BASE CONTENT -->
                
                 
                     

                </div>

                <div class="row">
                    <div class=" col-lg-12 col-md-12 col-sm-11 col-xs-12 " style=""><p class="alert alert-success  ">CRON JOB : 
                     @if(Session::has('flash_alert_notice'))
                              
                           <span class="alert alert-danger">  {{ Session::get('flash_alert_notice') }} </span>
                             
                        @endif
                 </p> </div>
                     
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                      <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">
                                                <a href="{{url('api/v2/getMatchDataFromApiAdmin')}}" target="_blank">All Match Status  </a>

                                            </span>
                                        </h3>
                                        <small> Update Match from Cron </small>
                                    </div>
                                  
                                </div>
                                <div class="progress-info">
                                    <div class="progress">
                                        <span style="width: {{$match_1}}%;" class="progress-bar progress-bar-success blue-sharp">
                                            <span class="sr-only">{{$match_3}}% grow</span>
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                    </div> 

                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                     <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">
                                                <a href="{{url('api/v2/updateMatchDataByStatusAdmin/3')}}">Live Match Status  </a>

                                            </span>
                                        </h3>
                                        <small> Update Match from Cron </small>
                                    </div>
                                   
                                </div>
                                 
                            </div>
                    </div>

                      <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                      <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">
                                                <a href="{{url('api/v2/updateMatchDataByStatusAdmin/2')}}">Completed Match   </a>

                                            </span>
                                        </h3>
                                        <small> Update Match from Cron </small>
                                    </div>
                                  
                                </div>
                                 
                            </div>
                    </div>

                      <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                            <div class="dashboard-stat2 bordered">
                                <div class="display">
                                      <div class="icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>
                                    <div class="number">
                                        <h3 class="font-blue-sharp">
                                            <span data-counter="counterup" data-value="567">
                                                <a href="{{url('api/v2/updateMatchDataByStatusAdmin/1')}}" >Upcoming Match   </a>

                                            </span>
                                        </h3>


                                        <small> Upcoming Match from Cron </small>
                                    </div>
                                     
                                </div>
                                 
                            </div>
                    </div>

                    
                </div>


                <!-- END CONTENT BODY -->
            </div>
            <!-- END CONTENT -->
            <!-- BEGIN QUICK SIDEBAR -->
            <a href="javascript:;" class="page-quick-sidebar-toggler">
                <i class="icon-login"></i>
            </a>
            <div class="page-quick-sidebar-wrapper" data-close-on-body-click="false">
                <div class="page-quick-sidebar">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="javascript:;" data-target="#quick_sidebar_tab_1" data-toggle="tab"> Users
                                <span class="badge badge-danger">0</span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;" data-target="#quick_sidebar_tab_2" data-toggle="tab"> Alerts
                                <span class="badge badge-success">7</span>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"> More
                                <i class="fa fa-angle-down"></i>
                            </a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="javascript:;" data-target="#quick_sidebar_tab_3" data-toggle="tab">
                                        <i class="icon-bell"></i> Alerts </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-target="#quick_sidebar_tab_3" data-toggle="tab">
                                        <i class="icon-info"></i> Notifications </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-target="#quick_sidebar_tab_3" data-toggle="tab">
                                        <i class="icon-speech"></i> Activities </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="javascript:;" data-target="#quick_sidebar_tab_3" data-toggle="tab">
                                        <i class="icon-settings"></i> Settings </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active page-quick-sidebar-chat" id="quick_sidebar_tab_1">
                            <div class="page-quick-sidebar-chat-users" data-rail-color="#ddd" data-wrapper-class="page-quick-sidebar-list">
                                <h3 class="list-heading">Staff</h3>
                                <ul class="media-list list-items">
                                    <li class="media">
                                        <div class="media-status">
                                            <span class="badge badge-success">0</span>
                                        </div>
                                        <img class="media-object" src="../assets/layouts/layout/img/avatar3.jpg" alt="...">
                                        <div class="media-body">
                                            <h4 class="media-heading">Admin</h4>
                                            <div class="media-heading-sub"> Super Admin </div>
                                        </div>
                                    </li>
                                     
                                </ul>
                                 
                            </div>
                          
                        </div>
                         
                    </div>
                </div>
            </div>
            <!-- END QUICK SIDEBAR -->
        </div>
        