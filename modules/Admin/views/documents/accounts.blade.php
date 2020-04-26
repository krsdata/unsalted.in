
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
                                        <span class="caption-subject font-red sbold uppercase">Bank Accounts</span>
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
                                            <form action="{{route('bankAccount')}}" method="get" id="filter_data">
                                             
                                            <div class="col-md-3">
                                                <input value="{{ (isset($_REQUEST['search']))?$_REQUEST['search']:''}}" placeholder="Search by  name" type="text" name="search" id="search" class="form-control" >
                                            </div>
                                            <div class="col-md-2">
                                                <input type="submit" value="Search" class="btn btn-primary form-control">
                                            </div>
                                           
                                        </form>
                                         <div class="col-md-2">
                                             <a href="{{ route('bankAccount') }}">   <input type="submit" value="Reset" class="btn btn-default form-control"> </a>
                                        </div>
                                       
                                        </div>
                                    </div>
                                     
                                    <table class="table table-striped table-hover table-bordered" id="">
                                        <thead>
                                            <tr>
                                                <th> Sno. </th>
                                                <th> User Details </th> 
                                                <th> Bank Name  </th> 
                                                <th> Account Holder Name </th> 
                                                <th> Account Number </th>
                                                <th> IFSC Code </th> 
                                                <th> Passbook Url </th>
                                                <th> Action </th>  
                                                <th>Created Date</th>  
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($documents as $key => $result)
                                            <tr>
                                                 <td>   {{ (($documents->currentpage()-1)*15)+(++$key) }} 
                                                </td>
                                                <td>  
                                                   Name: {{$result->user->first_name??''}} </br>
                                                   Email:   {{
                                                    $result->user->email??''
                                                }},<br>
                                                Phone:   {{
                                                    $result->user->phone??''
                                                }}
                                                 </td>  
                                                <td>  
                                                    {{$result->bank_name}} </td> 
                                              
                                                  <td>  
                                                    {{$result->account_name}} 
                                                  </td> 
                                                 <td>  
                                                    {{$result->account_number}} 
                                                  </td>

                                                  <td>  
                                                    {{$result->ifsc_code}} 
                                                  </td>
                                                   
                                                  <td>
                                                 @if($result->bank_passbook_url)   
                                                <a href="{{  $result->bank_passbook_url }}" target="_blank" >
                                                <img src="{{ $result->bank_passbook_url }}" width="100px" height="50px;"> </a>
                                                 @else
                                                 NA
                                                 @endif   
                                                  </td>
                                                <td> <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModal" onclick="getCategory('{{$result->id}}')" >Approve</button>
                                                    
                                                    @if($result->status)
                                                    <span class="glyphicon glyphicon-ok"></span>
                                                    @endif
                                                 </td>

                                                <td>
                                                        {!! Carbon\Carbon::parse($result->created_at)->format('d-m-Y'); !!}
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
        <input type="hidden" name="bank_doc_id" value="" id="bank_doc_id"> 
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
    
    function getCategory(bank_doc_id) {
        document.getElementById("bank_doc_id").value  = bank_doc_id; 
    }
</script>