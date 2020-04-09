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
            <h1 class="mb-5" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">Terms and Conditions</h1>
            
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


  <!--Section: Content-->
  <section  id="termscondition" data-aos="fade-up">
      <div class="container my-5">
           <div class="row justify-content-end">
					<div class="col-md-12 ">
						<div class="heading-section text-center ftco-animate">
							<span class="subheading">Terms & Conditions</span>
                        <h2 class="mb-4" style="text-decoration: underline">Terms and Conditions</h2></div>       	
				</div>
				<div class="col-md-12">
          
          <div class="faq_content wow fadeIn animated" data-wow-delay="400ms">
                            <p style="color: #00bade;"><strong>T&amp;C</strong><strong>&nbsp;AND DISCLAIMER</strong></p>

<p>All sales are final as Sportsfight &amp; Advisory offers free 2-day evaluation to ensure that our products and services will meet your needs without the need to purchase, there will be ABSOLUTELY NO REFUNDS and CANCELLATIONS.</p>

<p>Before deciding to subscribe to our services, please make sure to take our 2 day free trial, the evaluation version that we provide. We do not offer refunds on subscriptions that have already been taken. We believe in our services and support and even give two-day free trial that without any exception we have NO REFUND POLICY.</p>
<br>
 
<p>Before deciding to subscribe to our services, please make sure to take our 2 day free trial, the evaluation version that we provide. We do not offer refunds on subscriptions that have already been taken. We believe in our services and support and even give two-day free trial that without any exception we have NO REFUND POLICY.</p>

                     
        </div>
      </div>
		    </div>
        </div>
	</section>

@stop