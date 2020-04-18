@extends('layouts.master') 
    @section('header')
    <h1>Dashboard</h1>
    @stop
    @section('content') 
      @include('partials.navigation')
      <!-- Left side column. contains the logo and sidebar -->
    


  <!--Section: Content-->
  <section  id="termscondition" data-aos="fade-up">
      <div class="container my-5">
           <div class="row justify-content-end">
					<div class="col-md-12 ">
						<div class="heading-section text-center ftco-animate">
							<span class="subheading">Change Password</span>
                        <h2 class="mb-4" style="text-decoration: underline">Change Password</h2></div>       	
				</div>
				<div class="col-md-12">
          
  <div class=" " style="border:1px solid #ccc; padding: 50px">
 
  @if (session('status'))
    <div class="alert alert-danger">
        {{ session('status') }} 
        <?php Session::pull('status'); ?>
    </div>
@endif

 
  @if (session('status_1'))
    <div class="alert alert-info">
        {{ session('status_1') }}
        <?php Session::pull('status_1'); ?>
    </div>
@endif
  <form method="POST" action="{{url('changePasswordToken')}}" accept-charset="UTF-8" class="" id="users_form"> 
      @csrf
     <div class="form-group ">
        <label class="control-label ">Password</label>
        <input type="text"  class="form-control" name="password"> 
    </div>
    <input type="hidden" name="token" value="{{$token??''}}">
     
     <div class="form-group ">
       <input type="submit" name="submit" value="sumit"   class=" btn btn-info">
     </div>
  </div>
</form>
                  
                                
        </div>
      </div>
		    </div>
        </div>
	</section>

@stop