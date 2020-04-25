@extends('layouts.master')
    @section('content')  
      @include('partials.navigation')
      <!-- Left side column. contains the logo and sidebar --> 
    <section class="content-wrap" style="background-image: url('{{url('webmedia/images/cricg.jpg')}}');" data-section="home" data-stellar-background-ratio="0.5" id="home-section">
      <div class="overlay"></div>
      <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-start" data-scrollax-parent="true" style="height: 499px;">
          <div class="col-md-12 ftco-animate text-center" data-scrollax=" properties: { translateY: '70%' }">
            <h1 class="mb-5" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }"><a href="{{url('/')}}">Home </a> | About Us</h1>
            
            <form action="https://sportsfight.in/public/upload/apk/sportsfight.apk" class="search-location">
	        		<div class="row">
	        			<div class="col-lg align-items-end">
	        		<input type="image" src="{{url('webmedia/images/download-android-new.png')}}" alt="About Us" align="center" style="height: 60px; margin-top: -30px">
		                   
		                    
	        			</div>
	        		</div>
	        	</form>
          </div>
        </div>
      </div>
    </section>
 
 
    <!--Section: Content-->
    <section class="px-md-5 mx-md-5 text-center dark-grey-text about" id="workflow-section">

        <div class="container-fluid my-5 py-5 z-depth-1">

      <!--Grid row-->
      <div class="row">

        <!--Grid column-->
        <div class="col-md-6 mb-4 mb-md-0 heading-section">

          <span class="subheading">About us </span>
            <h2 class="mb-4">Who We are?</h2>

          <p class="text-muted">We drive one of the biggest virtual yet fancy sports platform. Also, we help you set-up your fan base by keeping a watch over shared posts in the feed. Not just this, but here you can enhance your performance by playing more to reach the next best level along with exciting cash rewards. Here you can create a team choosing your favourite players which help you gain more coins in any contest. Sportfight Fantasy League is a stage that permits you to play virtually opting amongst real-life players and earn points using your game expertise and knowledge.
          </p>
 
         
        </div>
        <!--Grid column-->

        <!--Grid column-->
        <div class="col-md-5 mb-4 mb-md-0">

          <img src="{{url('webmedia/images/screens1.png')}}" class="img-fluid" alt="">

        </div>
        <!--Grid column-->

      </div> 
      </div>


    </section>
    <!--Section: Content-->
        <!-- services -->
        <section class="ftco-section ftco-services-2" id="services-section">
            <div class="container">
                <div class="row justify-content-center pb-5">
          <div class="col-md-12 heading-section text-center ftco-animate">
            <span class="subheading">Features</span>
            <h2 class="mb-4 wow animated bounceInLeft ">Our features</h2>
            <p>Sportsfight fantasy league</p>
          </div>
        </div>
        <div class="row">
            <div class="col-md d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services text-center d-block rainbow">
              <div class="icon justify-content-center align-items-center d-flex"><span class="flaticon-pin"></span></div>
              <div class="media-body">
                <h3 class="heading mb-3">Easy to Join Contest</h3>
                <p>Sportsfight Fantasy League allows you to participate in the fantasy sports where you
can quickly level up your performance choosing your dream players. As you are just a
step away from the contest, So, buy the entry ticket and get yourself enrolled for the
upcoming contest just in a few clicks.
</p>
              </div>
            </div>      
          </div>
          <div class="col-md d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services text-center d-block mt-lg-5 pt-lg-4">
              <div class="icon justify-content-center align-items-center d-flex"><span class="flaticon-detective"></span></div>
              <div class="media-body">
                <h3 class="heading mb-3">High speedy app</h3>
                <p>Download the Sportsfight app to access exciting features easily. Also, the app is super
easy to use as it fastens the speed so download it to win your cash rewards a few
clicks away. Besides this, get instant notifications, offers and promotions in regards to
your fantasy sport, upcoming contests, dream player, and so forth.</p>
              </div>
            </div>      
          </div>
          <div class="col-md d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services text-center d-block">
              <div class="icon justify-content-center align-items-center d-flex"><span class="flaticon-house"></span></div>
              <div class="media-body">
                <h3 class="heading mb-3">Full protection</h3>
                <p>We understand safety measures, and that is why we make each participant register
with their verified email address or phone number. Or you can log with your current
Facebook or Google account as well. It will help check the user’s details for
undertaking further procedures. Do not worry, as your details are safe with us and we
do not share it with any third party without your consent</p>
              </div>
            </div>      
          </div>
          <div class="col-md d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services text-center d-block mt-lg-5 pt-lg-4">
              <div class="icon justify-content-center align-items-center d-flex"><span class="flaticon-purse"></span></div>
              <div class="media-body">
                <h3 class="heading mb-3">Easy to Withdraw</h3>
                <p>Sportsfight Fantasy League is a dream come true for the users, as they get an excellent
chance to choose their ideal players alongside you can even earn points by winning
the contest or a bonus by inviting a friend. After winning users switch to withdraw the
earnings, with an easy withdrawal request procedure. As soon as the withdrawal
request gets approved, your registered bank account will be verified to transfer the
earnings into your account.</p>
              </div>
            </div>      
          </div>
        </div>
            </div>
        </section>

   





       <!-- about works -->

    <section class="ftco-section ftco-services-2" style="background:linear-gradient(to right,#ffffff,#00bade)">
            <div class="container">
                <div class="row">
          <div class="col-md-4 heading-section ftco-animate">
            <span class="subheading">Steps</span>
            <h2 class="mb-4">Our Steps</h2>
           
            <div class="media block-6 services text-center d-block pt-md-5 mt-md-5">
              <div class="icon justify-content-center align-items-center d-flex"><span>1</span></div>
              <div class="media-body p-md-3">
                <h3 class="heading mb-3">Check Out Contest For the League</h3>
                <p class="mb-5">You are allowed to check over the participating teams based on the previous match
listings, and you can also check the entry ticket amount.</p>
                <hr>
              </div>
            </div>
          </div>
          <div class="col-md-4 d-flex align-self-stretch ftco-animate mt-lg-5">
            <div class="media block-6 services text-center d-block mt-lg-5 pt-md-5 pt-lg-4">
              <div class="icon justify-content-center align-items-center d-flex"><span>2</span></div>
              <div class="media-body p-md-3">
                <h3 class="heading mb-3">Create your Best Team</h3>
                <p class="mb-5"  style="color: #000">Sportsfight gives you an opportunity to organize the best team, choosing from real-life
players and get paid for your knowledge & expertise by winning the cash rewards.
</p>
                <hr>
              </div>
            </div>      
          </div>
          <div class="col-md-4 d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services text-center d-block">
              <div class="icon justify-content-center align-items-center d-flex"><span>3</span></div>
              <div class="media-body p-md-3">
                <h3 class="heading mb-3">Pay Small And Win Big</h3>
                <p class="mb-5" style="color: #000">Pay small and win big is the concept of winning a considerable amount by taking part in
the contest with a small token of entry amount. Not just this, but also all the participants
are getting rewarded based on their ranks.</p>
                <hr>
              </div>
            </div>      
          </div>
        </div>
            </div>
            
        </section>  
        <!-- Screenshot -->
 
   
    <div class="jumbotron jumbotron-fluid" style="margin: 0px">
          <div class="container center">
            <h1>Reach us at info@sportsfight.in </h1>
            
          </div>
      </div>

@stop