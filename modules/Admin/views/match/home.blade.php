
            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
             <div class="page-content-wrapper">
                <!-- BEGIN CONTENT BODY -->
                <div class="page-content">
                    <!-- BEGIN PAGE HEAD-->
                    
                    <!-- END PAGE HEAD-->
                    <!-- BEGIN PAGE BREADCRUMB -->
                   @include('packages::partials.breadcrumb')

                    <!-- END PAGE BREADCRUMB -->
                    <!-- BEGIN PAGE BASE CONTENT -->
                    <div class="row">
                        <div class="col-md-12">
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet light portlet-fit bordered">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="icon-settings font-red"></i>
                                        <span class="caption-subject font-red sbold uppercase">All Matches </span>
                                    </div>
                                     <div class="col-md-12 pull-right">
                                        <div class=" pull-right">
                                            <div   class="input-group"> 
                                                <a href="{{ route('match')}}?status=3">
                                                    <button  class="btn btn-success"><i class="fa fa-plus-circle"></i> Live </button> 
                                                </a> 
                                            </div>
                                        </div>
                                        <div class=" pull-right">
                                            <div   class="input-group"> 
                                                <a href="{{ route('match')}}?status=2">
                                                    <button  class="btn btn-success"><i class="fa fa-plus-circle"></i> Completed </button> 
                                                </a> 
                                            </div>
                                        </div>
                                        <div class=" pull-right">
                                            <div   class="input-group"> 
                                                <a href="{{ route('match')}}?status=1">
                                                    <button  class="btn btn-success"><i class="fa fa-plus-circle"></i> Upcoming </button> 
                                                </a> 
                                            </div>
                                        </div>
                                         
                                    </div>
                                     
                                </div>
                                  
                                    @if(Session::has('flash_alert_notice'))
                                         <div class="alert alert-success alert-dismissable" style="margin:10px">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                          <i class="icon fa fa-check"></i>  
                                         {{ Session::get('flash_alert_notice') }} 
                                         </div>
                                    @endif
                                <div class="portlet-body">
                                    <div class="table-toolbar">
                                        <div class="row">
                                            <form action="{{route('match')}}" method="get" id="filter_data">
                                             
                                            <div class="col-md-3">
                                                <input value="{{ (isset($_REQUEST['search']))?$_REQUEST['search']:''}}" placeholder="Search " type="text" name="search" id="search" class="form-control" >
                                            </div>
                                              <div class="col-md-3">
                                                <select class="form-control" name="status">
                                                    <option value="1" @if(isset($_REQUEST['status']) && $_REQUEST['status']==1) selected @endif>Upcoming</option>
                                                     <option value="2" <?php if(isset($_REQUEST['status']) && $_REQUEST['status']==2) { echo "selected"; }  ?>> Completed</option>
                                                      <option value="3" @if(isset($_REQUEST['status']) && $_REQUEST['status']==3) selected @endif>Live</option>
                                                      <option value="4" @if(isset($_REQUEST['status']) && $_REQUEST['status']==4) selected @endif>Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="submit" value="Search" class="btn btn-primary form-control">
                                            </div>
                                           
                                        </form>
                                         <div class="col-md-2">
                                             <a href="{{ route('match') }}">   <input type="submit" value="Reset" class="btn btn-default form-control"> </a>
                                        </div>
                                       
                                        </div>
                                    </div>
                                     
                                    <table class="table table-striped table-hover table-bordered" id="contact">
                                        <thead>
                                            <tr>
                                                 <th>Sno.</th>
                                                <th> Match Id </th>
                                                <th> Match Between </th> 
                                                <th> Add Contest</th> 
                                                <th> Player List </th> 
                                                <th> Status</th> 
                                                <th> Start Date</th> 
                                                 <th> Distribute Prize</th>

                                                 <th> View Details  </th> 
                                                <th>  Cron run at</th>  
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($match as $key => $result)
                                            <tr>
                                              <td> {{ (($match->currentpage()-1)*15)+(++$key) }}</td>
                                                <td> {{$result->match_id}} </td>
                                                 <td> {{$result->title}} </td>
                                                 <td> <a class="btn btn-success" href="{{route('defaultContest.create')}}?match_id={{$result->match_id}}">
                                                    Add Contest
                                                 </a>
                                                  </td>
                                                 <td> <a class="btn btn-success" href="{{route('match.show',$result->id)}}?player={{$result->match_id}}">
                                                    View Players
                                                 </a></td>
                                                 <td> {{$result->status_str}} </td>
                                                 <td> 
                                                    {!!
                                                        \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $result->date_start, 'UTC')
                                                        ->setTimezone('Asia/Kolkata')
                                                        ->format('d-m-y, H:i:s A')
                                                    !!}
                                                </td>
                                                 <td> 
                                                    @if($result->status==2)
                                                   <a class="btn btn-success" target="_blank" href=" {{url('api/v2/prizeDistribution?match_id='.$result->match_id)}}">
                                                     Distribute Prize
                                                        </a> 
                                                    @else
                                                     Pending
                                                    @endif
                                                    </td>
                                                    <td>  <a href="{{ route('match.show',$result->id)}}">
                                                            <i class="fa fa-eye" title="details"></i> 
                                                        </a> </td> 
                                                     
                                                    <td> 

                                                        {!!
                                                        \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $result->created_at, 'UTC')
                                                        ->setTimezone('Asia/Kolkata')
                                                        ->format('H:i:s A')
                                                    !!}
                                        </td> 
                                               
                                            </tr>
                                           @endforeach
                                            
                                        </tbody>
                                    </table>
                                    <span>
                                      Showing {{($match->currentpage()-1)*$match->perpage()+1}} to {{$match->currentpage()*$match->perpage()}}
                                    of  {{$match->total()}} entries
                                     <div class="center" align="center">  {!! $match->appends(['search' => isset($_GET['search'])?$_GET['search']:'','status' => isset($_GET['status'])?$_GET['status']:''])->render() !!}</div>
                                </div>
                            </div>
                            <!-- END EXAMPLE TABLE PORTLET-->
                        </div>
                    </div>
                    <!-- END PAGE BASE CONTENT -->
                </div>
                <!-- END CONTENT BODY -->
            </div>
            
            
            <!-- END QUICK SIDEBAR -->
        </div>
        
        
     <div id="responsive" class="modal fade" tabindex="-1" data-width="300">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Contact Group</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Contact Group Name</h4>
                        <p>
                            <input type="text" class="col-md-12 form-control" name="contact_group" id="contact_group"> </p>
                            <input type="hidden" name="contacts_id" value="">
                    </div>
                </div> 
            </div>
            <div class="modal-footer">
            <span id="error_msg"></span>
                <button type="button" data-dismiss="modal" class="btn dark btn-outline">Close</button>
                <button type="button" class="btn red" id="csave"  onclick="createGroup('{{url("admin/createGroup")}}','save')" >Save</button>
            </div>
        </div>
    </div>
</div>