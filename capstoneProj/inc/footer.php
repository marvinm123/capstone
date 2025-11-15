
<script>
  $(document).ready(function(){
    $('#p_use').click(function(){
      uni_modal("Privacy Policy","policy.php","mid-large")
    })
     window.viewer_modal = function($src = ''){
      start_loader()
      var t = $src.split('.')
      t = t[1]
      if(t =='mp4'){
        var view = $("<video src='"+$src+"' controls autoplay></video>")
      }else{
        var view = $("<img src='"+$src+"' />")
      }
      $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
      $('#viewer_modal .modal-content').append(view)
      $('#viewer_modal').modal({
              show:true,
              backdrop:'static',
              keyboard:false,
              focus:true
            })
            end_loader()  

  }
    window.uni_modal = function($title = '' , $url='',$size=""){
        start_loader()
        $.ajax({
            url:$url,
            error:err=>{
                console.log()
                alert("An error occured")
            },
            success:function(resp){
                if(resp){
                    $('#uni_modal .modal-title').html($title)
                    $('#uni_modal .modal-body').html(resp)
                    if($size != ''){
                        $('#uni_modal .modal-dialog').addClass($size+'  modal-dialog-centered')
                    }else{
                        $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md modal-dialog-centered")
                    }
                    $('#uni_modal').modal({
                      show:true,
                      backdrop:'static',
                      keyboard:false,
                      focus:true
                    })
                    end_loader()
                }
            }
        })
    }
    window._conf = function($msg='',$func='',$params = []){
       $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
       $('#confirm_modal .modal-body').html($msg)
       $('#confirm_modal').modal('show')
    }
  })
</script>

<body class="d-flex flex-column min-vh-100">
  <!-- Your Navbar / Header -->

  <main class="flex-fill">
    <!-- Your Page Content Here -->
  </main>
<!-- Footer -->
<footer class="footer text-white pt-5" style="background-color: rgba(0, 0, 0, 0.5);">
  <div class="container">
    <div class="row text-center text-md-left">

      <!-- Contact Info -->
      <div class="col-md-4 mb-4">
        <h5 class="text-uppercase font-weight-bold mb-3">Contact Us</h5>
        <p><i class="fas fa-phone-alt mr-2"></i> +1 (123) 456-7890</p>
        <p><i class="fas fa-envelope mr-2"></i> info@yourfacility.com</p>
      </div>

      <!-- Address -->
      <div class="col-md-4 mb-4">
        <h5 class="text-uppercase font-weight-bold mb-3">Location</h5>
        <p><i class="fas fa-map-marker-alt mr-2"></i>3GJV+987, Catalunan Grande Rd,<br> Talomo, Davao City, <br>Davao del Sur, Philippines</p>
        <p><i class="fas fa-map-marker-alt mr-2"></i> 7.080923178586113, <br>125.54332173957216</p>
      </div>

      <!-- Google Maps Embed -->
      <div class="col-md-4 mb-4">
        <h5 class="text-uppercase font-weight-bold mb-3">Map</h5>
        <div style="width: 100%">
          <iframe
            width="100%"
            height="150"
            frameborder="0"
            style="border:0; border-radius: 10px;"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="https://www.google.com/maps?q=7.080923178586113,125.54325736655896&hl=es;z=14&output=embed">
          </iframe>
        </div>
      </div>

    </div>

    <hr class="bg-secondary">

    <div class="text-center pb-3">
      <small>&copy; <?= date('Y') ?> YourFacilityApp. All rights reserved.</small>
    </div>
  </div>
</footer>



   
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
      $.widget.bridge('uibutton', $.ui.button)
    </script>
   <!-- Bootstrap 4 -->
   <script src="<?php echo base_url ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 -->
    <script src="<?php echo base_url ?>plugins/select2/js/select2.full.min.js"></script>
    <!-- Summernote -->
    <script src="<?php echo base_url ?>plugins/summernote/summernote-bs4.min.js"></script>
    <script src="<?php echo base_url ?>plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url ?>plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo base_url ?>plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo base_url ?>plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <!-- overlayScrollbars -->
    <!-- <script src="<?php echo base_url ?>plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script> -->
    <!-- AdminLTE App -->
    <script src="<?php echo base_url ?>dist/js/adminlte.js"></script>
  