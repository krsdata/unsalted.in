@extends('layouts.master') 
    @section('header')
    <h1>Dashboard</h1>
    @stop
    @section('content') 
      @include('partials.navigation')
      <!-- Left side column. contains the logo and sidebar -->
    
    <section class="content-wrap" style="background-image: url('{{url('webmedia/images/cricg.jpg')}}');" data-section="home" data-stellar-background-ratio="0.5" id="home-section">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-start" data-scrollax-parent="true" style="height: 499px;">
          <div class="col-md-12 ftco-animate text-center" data-scrollax=" properties: { translateY: '70%' }">
            
            <form action="#" class="search-location">
	        		<div class="row">
	        			<div class="col-lg align-items-end">
	        				
		                    <img  src="{{url('webmedia/images/download-android-new.png')}}" alt="android-new" style="width: 200px;">
		                    
	        			</div>
	        		</div>
	        	</form>
          </div>
        </div>
      </div>
    </section>
<style type="text/css">
  .page_title{
    margin-top: -110px;right: 10px;position: absolute;background: #fff;padding: 10px;border-radius: 10px;
    font-family: 'Raleway', sans-serif;
    
  }
  .divider-left{
    height: 4px;
    width: 70px;
    background: #dd2342;
    display: block;
    margin-top: 1px; 
  }
  .divider{
    background: #151515;
    height: 1px;
    width: 70px !important;
    margin-left: 0px;
    position: absolute;
    left: 70px;
    top: 50px;
  }
</style>

  <!--Section: Content-->
  <section  id="termscondition" data-aos="fade-up">
      <div class="container my-5">
           <div class="row justify-content-end">
				<div class="col-md-12 ">
  				<div class="heading-section text-center ftco-animate">
  				  <h2 class="mb-4 page_title" >404! Page Not Found</h2>
          </div>       	
				</div>
          <div class="col-md-12">     
            <div class="faq_content wow fadeIn animated" data-wow-delay="400ms">
              
              <h2 class="heading heading_space wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown; height: 300px">
                <span>Oops! Page is not found!</span>
                <span class="divider-left">
                  
                </span>
                <span class="divider">
                  
                </span>
            </h2>       
          </div>
          <div class="heading heading_space wow fadeInDown animated" style="padding: 10px">
          </div>
        </div>
		  </div>
    </div>
</section>

@stop