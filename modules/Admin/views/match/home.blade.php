
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

                                        <button type="button" class="btn pull-right btn-primary" data-toggle="modal" data-target="#changeDate" data-whatever="@" style="margin-right: 10px">Change Match Date</button> 
                                         
                                    </div>
                                     
                                </div>
                                  
                                    @if(Session::has('flash_alert_notice'))
                                         <div class="alert alert-success alert-dismissable" style="margin:10px">
                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                          <i class="icon fa fa-check"></i>  
                                         {{ Session::get('flash_alert_notice') }} 
                                         </div>
                                    @endif
                                <div class="portlet-body table-responsive">
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
                                                <th> Action</th> 
                                                <th> Status</th> 
                                                <th> Date </th> 
                                                <th> Prize Status</th>  
 
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($match as $key => $result)
                                            <tr>
                                              <td>
                                               

                                               {{ (($match->currentpage()-1)*15)+(++$key) }}</td>
                                                <td> {{$result->match_id}} </td>
                                                 <td> {{$result->title}} </td>
                                                 <td> <a class="btn btn-success" href="{{route('defaultContest.create')}}?match_id={{$result->match_id}}">
                                                    Add Contest
                                                 </a>
                                                  </td>
                                                 <td> <a class="btn btn-success" href="{{route('match.show',$result->id)}}?player={{$result->match_id}}">
                                                    View Players

                                                 </a>
                                                 
                                               </td>
                                               <td>    
    <style type="text/css">
      .dropdown-item{
        width: 200px;
        float: left;
      }
    </style>
    <div class="btn-group dropleft"> 
      <button class="btn btn-danger" type="button" data-toggle="dropdown">Action
      <span class="caret"></span></button>

      <div class="dropdown-menu">
        <a class="dropdown-item btn btn-primary" href="{{ route('match.show',$result->id)}}">View Details <i class="fa fa-eye" title="details"></i> </a>
        @if($result->status==2)
         <a class="dropdown-item btn btn-success" target="_blank" href=" {{url('api/v2/prizeDistribution?match_id='.$result->match_id)}}">
           Generate Prize
              </a> 
          @else
          <a class="dropdown-item btn btn-warning" href="#">Generate Prize - NA</a>
          @endif  
        <div class="dropdown-divider"></div>
        <a class="dropdown-item btn btn-info" href="{{route('triggerEmail','match_id='.$result->match_id)}}">Prize Email Trigger</a>
      </div>
    </div>


                                              </td> 
                                             

                                         <td> {{$result->status_str}} </td>
                                         <td> 
                                            {!!
                                                \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $result->date_start, 'UTC')
                                                ->setTimezone('Asia/Kolkata')
                                                ->format('d-m-y, H:i:s A')
                                            !!}
                                        </td>
                                         <td> 
                                            @if($result->current_status==1) 
                                             Prize Distributed 
                                            @else
                                             NA
                                            @endif
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
<div class="modal fade" id="changeDate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Change Match Date</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Start Date:</label>
            <input type="text" class="form-control form_datetime_start form_datetime" id="start_date" value="{{date('Y-m-d h:i')}}" readonly name="date_start">
          </div>
          <div class="form-group">
            <label for="recipient-name" class="col-form-label" >End Date:</label>
            <input type="text" class="form-control form_datetime_end form_datetime" id="end_date" value="{{date('Y-m-d h:i')}}" readonly name="date_end">
          </div>
           <div class="form-group">
            <label for="recipient-name" class="col-form-label">Match Id:</label>
            <input type="text" class="form-control" id="match_id"  name="match_id" >
          </div>
           <div class="form-group">
            <label for="recipient-name" class="col-form-label">Match Status:</label>
             <select class="form-control" name="status">
                <option value="">Select Status</option>
                <option value="1">Upcoming</option>
                <option value="2">Completed</option>
                <option value="3">Live</option>
                <option value="4">Cancelled</option>
             </select> 
          </div>
         <!--  <div class="form-group">
            <label for="message-text" class="col-form-label">Match Id:</label>
            <textarea class="form-control" id="message-text" ></textarea>
          </div> -->
           <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"> Save </button>
      </div>
        </form>
      </div>
     
    </div>
  </div>
</div>

<div class="modal fade" id="popMsg" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Email sent successfully</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div> 
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
</div>

<div class="modal fade" id="popMsg2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Prize distributed successfully!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div> 
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>
</div>
