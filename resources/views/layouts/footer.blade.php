<footer class="ftco-footer">
          <div class="footer-top">
            <div class="container">
              <div class="row">
                <div class="col-md-3 footer-about wow fadeInUp animated" style="visibility: visible; animation-name: fadeInUp;">
                  <h3 style="border-bottom: 2px solid #fff;">About us</h3>
                  <span style="width: 200px;display: block;border-bottom: 2px solid #fff;margin-top: -4px;"></span>
                  <p class="mt-20">
                    We are a young company always looking for new and creative ideas to help you with our products in your everyday work.
                  </p>
                  <p>© Company Inc.</p>
                      </div>
                <div class="col-md-4 offset-md-1 footer-contact wow fadeInDown animated footpara " style="visibility: visible; animation-name: fadeInDown;">
                  <h3 style="border-bottom: 2px solid #fff;">Contact</h3>
                  <span style="width: 98px;display: block;border-bottom: 2px solid #fff;margin-top: -4px;"></span>
                      <p><i class="fa fa-map-marker-alt"></i> Via Rossini 10, 10136 Turin Italy</p>
                      <p><i class="fa fa-phone"></i> Phone: (0039) 123 45 67 347</p>
                      <p><i class="fa fa-envelope"></i> Email: <a href="mailto:sportsfight@domain.com">sportsfight@domain.com</a></p>
                      <p><i class="fa fa-skype"></i> Skype: Sportsfight_online</p>
                      </div>
                      <div class="col-md-4 footer-links wow fadeInUp animated footpara" style="visibility: visible; animation-name: fadeInUp;">
                        <div class="row">
                          <div class="col">
                            <h3 style="border-bottom: 2px solid #fff;">Links</h3>
                            <span style="width: 70px;display: block;border-bottom: 2px solid #fff;margin-top: -4px;">
                              
                            </span>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <p><a class="scroll-link" href="#top-content">Home</a></p>
                            <p><a href="">Services</a></p>
                            
                            <p><a href="#">About</a></p>
                            <p><a href="#">Screenshot</a></p>
                            
                            
                          </div>
                          <div class="col-md-6">
                            <p><a href="#">How it works</a></p>
                           
                            <p><a href="#">Our Policy</a></p>
                            <p><a href="#">Terms & Conditions</a></p>
                          </div>
                        </div>
                      </div>
                </div>
            </div>
          </div>
          <div class="footer-bottom">
            <div class="container">
              <div class="row">
                  <div class="col footer-social">
                        <a href="#"><i class="fa fa-facebook-f"></i></a> 
              <a href="#"><i class="fa fa-twitter"></i></a> 
              <a href="#"><i class="fa fa-google-plus-g"></i></a> 
              <a href="#"><i class="fa fa-instagram"></i></a> 
              <a href="#"><i class="fa fa-pinterest"></i></a>
                      </div>
                </div>
            </div>
          </div></footer>
    
  

  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
    <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/>
    <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#e10000"/></svg></div>

  <script src="{{ URL::asset('webmedia/js/jquery.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/jquery-migrate-3.0.1.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/popper.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/bootstrap.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/jquery.easing.1.3.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/jquery.waypoints.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/jquery.stellar.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/owl.carousel.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/jquery.magnific-popup.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/aos.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/jquery.animateNumber.min.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/scrollax.min.js')}}"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
<!--   <script src="{{ URL::asset('webmedia/js/google-map.js')}}"></script>
 -->  <script src="{{ URL::asset('webmedia/js/plugins.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/main.js')}}"></script>
  <script src="{{ URL::asset('webmedia/js/main1.js')}}"></script>

  <script>
    $(document).ready(function(){
        // Add minus icon for collapse element which is open by default
        $(".collapse.show").each(function(){
          $(this).prev(".card-header").find(".fa").addClass("fa-minus").removeClass("fa-plus");
        });
        
        // Toggle plus minus icon on show hide of collapse element
        $(".collapse").on('show.bs.collapse', function(){
          $(this).prev(".card-header").find(".fa").removeClass("fa-plus").addClass("fa-minus");
        }).on('hide.bs.collapse', function(){
          $(this).prev(".card-header").find(".fa").removeClass("fa-minus").addClass("fa-plus");
        });
    });
  </script>
  <script type="text/javascript">
    $(document).ready(function(){
    $('.owl-carousel').owlCarousel({
        loop:true,
        autoplay:true,
        autoplayTimeout:1000,
        autoplayHoverPause:true,
        autoplaySpeed:5000 
        
        
    });
  });
  </script> 
  </body>
</html>
