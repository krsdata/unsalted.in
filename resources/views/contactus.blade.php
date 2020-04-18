@extends('layouts.master') 
    @section('header')
    <h1>Dashboard</h1>
    @stop
    @section('content') 
      @include('partials.navigation')
      <!-- Left side column. contains the logo and sidebar -->
<section class="content-wrap " style="background-image: url({{url('webmedia/images/c0.jpg')}}); height: 500px" data-section="home" id="home-section">
      
</section> 


<section id="contact" class=" ftco-section contact-section ftco-no-pb" style="margin-bottom: 50px">
    <div class="container">

  
  	    <div class="row justify-content-center mb-5 pb-3">
	          <div class="col-md-12 heading-section text-center ftco-animate">
	             
	            <h2 class="mb-4">Contact Us</h2>
	             
	    </div></div>
    <div class="row padding-bottom">
      <div class="col-md-4 contact_address heading_space wow fadeInLeft animated animated" data-aos="fade-left" data-wow-delay="400ms" style="visibility: visible; animation-delay: 400ms; animation-name: fadeInLeft;">
        @if (session('status'))
    <div class="alert alert-info">
        {{ session('status') }}
        <?php Session::pull('status'); ?>
    </div>
@endif
        <h2 class="heading heading_space"><span>Get</span> in Touch <span class="divider-left"></span></h2>
        <p>Fantasy</p>
        <div class="address col-md-12">
          <i class="icon icon-map-pin border_radius"></i>
          <h4>Visit Us</h4>
          <p>www.sportsfight.in</p>
        </div>
        <div class="address second col-md-12">
          <i class="icon icon-envelope border_radius"></i>
          <h4>Email Us</h4>
          <p><a href="mailto:info@sportsfight.in">info@sportsfight.in</a></p>
        </div>
        <div class="address col-md-12">
          <i class="icon icon-phone border_radius"></i>
          <h4>Call Us</h4>
          <p>   Mo. No -  xxxxx-xxxx</p>
<p></p>
        </div>
      </div>
    <div class="col-md-8 wow fadeInRight animated animated" data-aos="fade-right" data-wow-delay="450ms" style="visibility: visible; animation-delay: 450ms; animation-name: fadeInRight;">
        <h2 class="heading heading_space"> <span>Contact</span> Form<span class="divider-left"></span></h2>
                
        <form method="POST" action="contactus" accept-charset="UTF-8" class="form-inline findus" id="contact-form"> 
                   @csrf                     
          <div class="row">
            <div class="col-md-12">
              <div id="result" style="overflow: hidden; display: none;"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 col-sm-4">
              <div class="form-group ">

                 <span class="label label-danger">Name:</span>
                  <input type="text" class="form-control " placeholder="Name" name="name" id="name" required="">
              </div>
            </div>
            <div class="col-md-4 col-sm-4">
              <div class="form-group ">

                 <span class="label label-danger">Email Address:</span>
                  <input value="" type="email" class="form-control " placeholder="Email" name="email" id="email" required="required">
              </div>
            </div>
                <div class="col-md-4 col-sm-4">
              <div class="form-group ">

                 <span class="label label-danger">Contact Number:</span>
                <input value="" type="number" class="form-control " placeholder="Mobile number" name="mobile" id="email" required="required">
              </div>
            </div>
            <div class="col-md-12">

                <span class="label label-danger">Your Message:</span>
                <textarea placeholder="Comment" name="comments" id="message" class="form-control"></textarea>
              <br><br>
              <button class="  btn btn-success" id="btn_submit">Submit</button>
            </div>
            
          </div>
        </form>
       
      </div>
    </div>
    
  </div>
</section>



@stop