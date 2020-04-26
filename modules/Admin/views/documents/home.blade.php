
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
                                        <span class="caption-subject font-red sbold uppercase">{{ $heading }}s</span>
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
                                            <form action="{{route('documents')}}" method="get" id="filter_data">
                                             
                                            <div class="col-md-3">
                                                <input value="{{ (isset($_REQUEST['search']))?$_REQUEST['search']:''}}" placeholder="Search by  name" type="text" name="search" id="search" class="form-control" >
                                            </div>
                                            <div class="col-md-2">
                                                <input type="submit" value="Search" class="btn btn-primary form-control">
                                            </div>
                                           
                                        </form>
                                         <div class="col-md-2">
                                             <a href="{{ route('documents') }}">   <input type="submit" value="Reset" class="btn btn-default form-control"> </a>
                                        </div>
                                       
                                        </div>
                                    </div>
                                     
                                    <table class="table table-striped table-hover table-bordered" id="">
                                        <thead>
                                            <tr>
                                                <th> Sno. </th>
                                                <th> User Details </th> 
                                                <th> Document Type </th> 
                                                <th> Document Numebr</th> 
                                                <th> Image </th> 
                                                <th> Status </th> 
                                                <th>Created date</th> 
                                                <th>Action</th> 
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($documents as $key => $result)
                                            <tr>
                                                 <td>   {{ (($documents->currentpage()-1)*15)+(++$key) }} 
                                                </td>
                                                <td>  
                                                    @if(isset($result->user))
                                                    {{$result->user->first_name}}
                                                     | {{
                                                    $result->user->email
                                                }} 
                                                @endif
                                            </td>  
                                                <td>  {{$result->doc_type}} </td> 
                                                 <td>  {{$result->doc_number}} </td> 
                                                <td>
                                                  @if($result->doc_type=='adharcard')
                                                <a href="{{  $result->doc_url_front }}" target="_blank" >
                                                <img src="{{ $result->doc_url_front }}" width="100px" height="50px;"> </a>  

                                                  <a href="{{  $result->doc_url_back }}" target="_blank" >
                                                  <img src="{{ $result->doc_url_back }}" width="100px" height="50px;"> </a>

                                                @else
                                                  @if($result->doc_type=='pancard')
                                                 <a href="{{  $result->doc_url_front }}" target="_blank" >
                                                <img src="{{ $result->doc_url_front }}" width="100px" height="50px;"> </a> 
                                                  @else
                                                    NA
                                                  @endif

                                                @endif

                                                </td>
                                                <td> <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModal" onclick="getCategory('{{$result->id}}')" >Approve</button>
                                                    
                                                    @if($result->status)
                                                    <span class="glyphicon glyphicon-ok"></span>
                                                    @endif
                                                    <ion-icon name="close-circle-outline"></ion-icon>

                                                 </td>
                                                <td>
                                                        {!! Carbon\Carbon::parse($result->created_at)->format('d-m-Y'); !!}
                                                </td>
                                                    
                                                <td>  
                                                        {!! Form::open(array('class' => 'form-inline pull-left deletion-form', 'method' => 'DELETE',  'id'=>'deleteForm_'.$result->id, 'route' => array('documents.destroy', $result->id))) !!}
                                                        <button class='delbtn btn btn-danger btn-xs' type="submit" name="remove_levels" value="delete" id="{{$result->id}}"><i class="fa fa-fw fa-trash" title="Delete"></i></button>
                                                        
                                                         {!! Form::close() !!}

                                                    </td>
                                               
                                            </tr>
                                           @endforeach
                                            
                                        </tbody>
                                    </table>
                                     <div class="center" align="center">  {!! $documents->appends(['search' => isset($_GET['search'])?$_GET['search']:''])->render() !!}</div>
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
        
        <!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Are you sure want to approve?</h4>
      </div>
      <form method="post">
      <div class="modal-body">
        <input type="hidden" name="doc_id" value="" id="doc_id"> 
         <p> Verification Status : <b> Approved </b></p>    
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         <button type="submit" class="btn btn-danger"  > Save </button>
      </div>
      </form>
    </div>
  </div>
</div>


<script type="text/javascript">
    
    function getCategory(doc_id) {
        document.getElementById("doc_id").value  = doc_id; 
    }
</script>