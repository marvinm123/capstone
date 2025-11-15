<?php require_once('./config.php') ?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php') ?>
<body class="hold-transition login-page">
<script>start_loader()</script>

<style>
  * {
    box-sizing: border-box;
  }

  html, body {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    overflow-x: hidden;
  }

  body {
    background: url('<?= validate_image($_settings->info('cover')) ?>') no-repeat center center fixed;
    background-size: cover;
    position: relative;
  }

  /* Background with blur effect */
  .background-blur {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('<?= validate_image($_settings->info('cover')) ?>') no-repeat center center fixed;
    background-size: cover;
    filter: blur(8px);
    z-index: -2;
  }

  /* Overlay for better contrast */
  .background-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 0, 0, 0.3));
    z-index: -1;
  }

  /* Main container - FIXED CENTERING */
  .login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    width: 100%;
  }

  /* Enhanced login box */
  .login-box {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 450px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    animation: slideUp 0.6s ease-out;
    margin: 0 auto;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Logo styling */
  #logo-img {
    width: 100px;
    height: 100px;
    object-fit: scale-down;
    object-position: center;
    margin-bottom: 20px;
    border-radius: 50%;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
  }

  #logo-img:hover {
    transform: scale(1.05);
  }

  /* Header styling */
  .card-header {
    border: none;
    background: transparent;
    padding: 0;
    margin-bottom: 30px;
  }

  .card-header .h1 {
    font-size: 2.2rem;
    background: linear-gradient(135deg, #007bff, #0056b3);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.5px;
  }

  .login-box-msg {
    font-size: 1.1rem;
    margin-bottom: 30px;
    color: #666;
    font-weight: 400;
  }

  /* Error message styling */
  .error-message {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 0.95rem;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
    animation: slideDown 0.4s ease-out;
    border: none;
    display: none;
  }

  /* Warning message styling (for unverified email) */
  .warning-message {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 0.95rem;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
    animation: slideDown 0.4s ease-out;
    border: none;
    display: none;
  }

  .warning-message a {
    color: white;
    text-decoration: underline;
    font-weight: 600;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Enhanced form controls */
  .input-group {
    margin-bottom: 20px;
    position: relative;
    display: flex;
    align-items: center;
  }

  .form-control {
    flex: 1;
    border: 2px solid #e1e5e9;
    border-radius: 12px;
    padding: 15px 55px 15px 20px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    height: 52px;
  }

  .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    background: rgba(255, 255, 255, 1);
    outline: none;
  }

  /* Error styling for form controls */
  .form-control.error {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
  }

  .input-group-append {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    z-index: 10;
    pointer-events: none;
  }

  .input-group-text {
    background: transparent;
    border: none;
    color: #007bff;
    padding: 0 18px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    border-radius: 0 12px 12px 0;
    pointer-events: auto;
  }

  .input-group-text.toggle-password {
    cursor: pointer;
    user-select: none;
  }

  .input-group-text.toggle-password:hover {
    color: #0056b3;
    background: rgba(0, 123, 255, 0.08);
  }

  .input-group-text.toggle-password:active {
    transform: scale(0.9);
    background: rgba(0, 123, 255, 0.15);
  }

  .input-group-text i {
    font-size: 1.1rem;
    pointer-events: none;
  }

  .form-control:focus ~ .input-group-append .input-group-text {
    color: #0056b3;
  }

  /* Enhanced button */
  .btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    border-radius: 12px;
    padding: 15px 30px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    position: relative;
    overflow: hidden;
    width: 100%;
  }

  .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
  }

  .btn-primary:hover::before {
    left: 100%;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
    background: linear-gradient(135deg, #0056b3, #007bff);
  }

  .btn-primary:active {
    transform: translateY(0);
  }

  /* Enhanced links */
  a {
    text-decoration: none;
    color: #007bff;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
  }

  a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 0;
    background: linear-gradient(135deg, #007bff, #0056b3);
    transition: width 0.3s ease;
  }

  a:hover::after {
    width: 100%;
  }

  a:hover {
    color: #0056b3;
    text-decoration: none;
  }

  /* Forgot password link styling */
  .forgot-password-link {
    display: inline-flex;
    align-items: center;
    font-size: 0.95rem;
    color: #007bff;
    margin-bottom: 20px;
  }

  .forgot-password-link i {
    margin-right: 5px;
    font-size: 0.9rem;
  }

  /* Row adjustments */
  .row.mb-3 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 25px;
    flex-wrap: wrap;
  }

  .col-6 {
    flex: 0 0 auto;
  }

  .text-left {
    text-align: left;
  }

  .text-right {
    text-align: right;
  }

  /* Create account section */
  .text-center.mt-2 {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e1e5e9;
  }

  /* Login links section */
  .login-links {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
  }

  .back-link {
    display: inline-flex;
    align-items: center;
    font-size: 0.95rem;
  }

  .back-link i {
    margin-right: 5px;
  }

  /* ==================== */
  /* RESPONSIVE DESIGN - FIXED */
  /* ==================== */

  /* Large devices (desktops, 992px and up) */
  @media (min-width: 992px) {
    .login-box {
      padding: 40px;
    }
  }

  /* Medium devices (tablets, 768px to 991px) */
  @media (max-width: 991px) {
    .login-box {
      padding: 35px 30px;
      max-width: 400px;
    }
    
    .card-header .h1 {
      font-size: 2rem;
    }
  }

  /* Small devices (landscape phones, 576px to 767px) */
  @media (max-width: 767px) {
    .login-container {
      padding: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .login-box {
      padding: 30px 25px;
      max-width: 380px;
      margin: 0 auto;
    }
    
    .card-header .h1 {
      font-size: 1.8rem;
    }
    
    .login-box-msg {
      font-size: 1rem;
      margin-bottom: 25px;
    }
    
    .form-control {
      padding: 14px 50px 14px 18px;
      font-size: 0.95rem;
      height: 50px;
    }
    
    .input-group-text {
      padding: 0 16px;
    }

    .input-group-text i {
      font-size: 1rem;
    }
    
    .btn-primary {
      padding: 14px 25px;
      font-size: 1rem;
    }
  }

  /* Extra small devices (portrait phones, less than 576px) - FIXED CENTERING */
  @media (max-width: 575px) {
    html, body {
      height: 100%;
      overflow: hidden;
    }
    
    body {
      background: url('<?= validate_image($_settings->info('cover')) ?>') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      margin: 0;
    }
    
    .background-blur {
      background: url('<?= validate_image($_settings->info('cover')) ?>') no-repeat center center fixed;
      background-size: cover;
    }
    
    .login-container {
      padding: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      width: 100%;
      margin: 0;
    }
    
    .login-box {
      padding: 25px 20px;
      max-width: 100%;
      border-radius: 16px;
      margin: 0 auto;
      width: 100%;
    }
    
    #logo-img {
      width: 80px;
      height: 80px;
      margin-bottom: 15px;
    }
    
    .card-header .h1 {
      font-size: 1.6rem;
    }
    
    .card-header {
      margin-bottom: 20px;
    }
    
    .login-box-msg {
      font-size: 0.95rem;
      margin-bottom: 20px;
    }
    
    .form-control {
      padding: 12px 48px 12px 16px;
      font-size: 16px; /* Prevents zoom on iOS */
      border-radius: 10px;
      height: 48px;
    }

    .input-group-text {
      padding: 0 14px;
      border-radius: 0 10px 10px 0;
    }

    .input-group-text i {
      font-size: 1rem;
    }
    
    .input-group {
      margin-bottom: 20px;
    }
    
    .btn-primary {
      padding: 14px 20px;
      font-size: 1rem;
      border-radius: 10px;
    }
    
    /* Stack the links on mobile */
    .login-links {
      flex-direction: column;
      text-align: center;
      gap: 12px;
    }
    
    .text-center.mt-2 {
      margin-top: 25px;
      padding-top: 15px;
    }
    
    .error-message, .warning-message {
      padding: 10px 15px;
      font-size: 0.9rem;
      margin-bottom: 15px;
    }
  }

  /* Very small devices (phones under 360px) */
  @media (max-width: 360px) {
    .login-box {
      padding: 20px 15px;
      border-radius: 14px;
    }
    
    #logo-img {
      width: 70px;
      height: 70px;
    }
    
    .card-header .h1 {
      font-size: 1.4rem;
    }
    
    .login-box-msg {
      font-size: 0.9rem;
    }
    
    .form-control {
      padding: 10px 44px 10px 14px;
      font-size: 14px;
      border-radius: 8px;
      height: 44px;
    }

    .input-group-text {
      padding: 0 12px;
      border-radius: 0 8px 8px 0;
    }

    .input-group-text i {
      font-size: 0.95rem;
    }
    
    .btn-primary {
      padding: 12px 18px;
      font-size: 0.95rem;
    }
  }

  /* Orientation specific adjustments */
  @media (max-height: 600px) and (orientation: landscape) {
    .login-container {
      padding: 10px;
      align-items: flex-start;
      min-height: auto;
      height: auto;
      padding-top: 20px;
      padding-bottom: 20px;
    }
    
    .login-box {
      margin: 10px auto;
      padding: 20px;
      max-width: 400px;
    }
    
    #logo-img {
      width: 60px;
      height: 60px;
      margin-bottom: 10px;
    }
    
    .card-header {
      margin-bottom: 15px;
    }
    
    .input-group {
      margin-bottom: 15px;
    }
  }

  /* High-resolution displays */
  @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .background-blur {
      filter: blur(4px); /* Reduce blur on retina displays */
    }
  }

  /* Loading animation */
  .login-box.loading {
    pointer-events: none;
    opacity: 0.7;
  }

  /* Success/Error message styling */
  .alert {
    border-radius: 12px;
    border: none;
    margin-bottom: 20px;
    animation: slideDown 0.3s ease-out;
  }

  /* Glass morphism effect enhancement */
  .login-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
    border-radius: 20px;
    pointer-events: none;
  }

  /* Loading overlay styles */
  .login-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    flex-direction: column;
  }

  .login-loading-overlay.active {
    opacity: 1;
    visibility: visible;
  }

  .login-loader {
    width: 60px;
    height: 60px;
    position: relative;
  }

  .login-loader .circle {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 4px solid transparent;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  .login-loader .circle:nth-child(2) {
    border: 4px solid transparent;
    border-top: 4px solid #0056b3;
    animation: spin 1.5s linear infinite reverse;
    width: 80%;
    height: 80%;
    top: 10%;
    left: 10%;
  }

  .login-loader .circle:nth-child(3) {
    border: 4px solid transparent;
    border-top: 4px solid #28a745;
    animation: spin 2s linear infinite;
    width: 60%;
    height: 60%;
    top: 20%;
    left: 20%;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  .login-loading-text {
    color: white;
    margin-top: 20px;
    font-size: 1.2rem;
    font-weight: 500;
    text-align: center;
    animation: pulse 1.5s infinite;
    padding: 0 15px;
  }

  @keyframes pulse {
    0% { opacity: 0.7; }
    50% { opacity: 1; }
    100% { opacity: 0.7; }
  }

  /* Mobile-specific loading text */
  @media (max-width: 575px) {
    .login-loader {
      width: 50px;
      height: 50px;
    }
    
    .login-loading-text {
      font-size: 1rem;
      margin-top: 15px;
    }
  }
</style>

<!-- Loading overlay -->
<div class="login-loading-overlay" id="loginLoadingOverlay">
  <div class="login-loader">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
  </div>
  <div class="login-loading-text">Logging in, please wait...</div>
</div>

<!-- Background elements -->
<div class="background-blur"></div>
<div class="background-overlay"></div>

<div class="login-container">
  <div class="login-box text-center">
    <?php if($_settings->chk_flashdata('success')): ?>
      <script>
        alert_toast("<?php echo $_settings->flashdata('success') ?>", 'success');
      </script>
    <?php endif; ?>

    <img src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo" class="img-thumbnail" id="logo-img">
    
    <div class="card-header">
      <span class="h1">Login</span>
    </div>

    <div class="card-body">
      <p class="login-box-msg">Sign in to access your account</p>

      <!-- Error message container -->
      <div class="error-message" id="errorMessage"></div>
      
      <!-- Warning message container for unverified email -->
      <div class="warning-message" id="warningMessage"></div>

      <form id="unified-login-frm" action="" method="post">
        <div class="input-group">
          <input type="text" class="form-control" name="login_identifier" id="loginIdentifier" autofocus placeholder="Username or Email" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>

        <div class="input-group">
          <input type="password" class="form-control" name="password" id="loginPassword" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text toggle-password" data-target="loginPassword">
              <span class="fas fa-eye-slash"></span>
            </div>
          </div>
        </div>

        <!-- Updated links section with Forgot Password -->
        <div class="login-links">
          <a href="<?= base_url ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Site
          </a>
          <a href="<?= base_url ?>forgot_password.php" class="forgot-password-link">
            <i class="fas fa-key"></i> Forgot Password?
          </a>
        </div>

        <div class="mb-3">
          <button type="submit" class="btn btn-primary" id="loginButton">Sign In</button>
        </div>

        <div class="text-center mt-2">
          <a href="<?= base_url.'register.php' ?>">Create a Client Account</a>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

<script>
  $(document).ready(function () {
    end_loader();
    
    // Add focus animations
    $('.form-control').on('focus', function() {
      $(this).parent().addClass('focused');
      // Remove error styling on focus
      $(this).removeClass('error');
    }).on('blur', function() {
      $(this).parent().removeClass('focused');
    });

    // Toggle password visibility
    $('.toggle-password').on('click', function() {
      const targetId = $(this).data('target');
      const input = $('#' + targetId);
      const icon = $(this).find('i');
      
      if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
      } else {
        input.attr('type', 'password');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
      }
    });

    // Function to show loading overlay
    function showLoginLoading() {
      $('#loginLoadingOverlay').addClass('active');
      $('body').css('overflow', 'hidden');
    }

    // Function to hide loading overlay
    function hideLoginLoading() {
      $('#loginLoadingOverlay').removeClass('active');
      $('body').css('overflow', 'auto');
    }

    // Function to show error message at top
    function showErrorMessage(message) {
      const errorDiv = $('#errorMessage');
      const warningDiv = $('#warningMessage');
      
      // Hide warning if showing error
      warningDiv.fadeOut(300);
      
      errorDiv.text(message).fadeIn(300);
      
      // Add error styling to form fields
      $('#loginIdentifier, #loginPassword').addClass('error');
      
      // Auto-hide after 5 seconds
      setTimeout(function() {
        errorDiv.fadeOut(300);
        $('#loginIdentifier, #loginPassword').removeClass('error');
      }, 5000);
    }

    // Function to show warning message with verification link
    function showWarningMessage(message, email) {
      const warningDiv = $('#warningMessage');
      const errorDiv = $('#errorMessage');
      
      // Hide error if showing warning
      errorDiv.fadeOut(300);
      
      warningDiv.html(message).fadeIn(300);
      
      // Add error styling to form fields
      $('#loginIdentifier, #loginPassword').addClass('error');
    }

    // Clear messages when user starts typing
    $('#loginIdentifier, #loginPassword').on('input', function() {
      if ($('#errorMessage').is(':visible')) {
        $('#errorMessage').fadeOut(300);
        $('#loginIdentifier, #loginPassword').removeClass('error');
      }
      if ($('#warningMessage').is(':visible')) {
        $('#warningMessage').fadeOut(300);
        $('#loginIdentifier, #loginPassword').removeClass('error');
      }
    });

    // Handle form submission
    $('#unified-login-frm').submit(function(e) {
      e.preventDefault();
      
      // Show the beautiful loading overlay
      showLoginLoading();
      
      // Hide any existing messages
      $('#errorMessage').fadeOut(300);
      $('#warningMessage').fadeOut(300);
      $('#loginIdentifier, #loginPassword').removeClass('error');
      
      // Debug: Check what data is being sent
      console.log('Form data:', $(this).serialize());
      
      $.ajax({
        url: "classes/Login.php?f=unified_login",
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        error: function(err) {
          console.log('AJAX Error:', err);
          // Hide loading overlay
          hideLoginLoading();
          // Use error display function
          showErrorMessage("Invalid password. Please try again.");
        },
        success: function(resp) {
          console.log('Server response:', resp);
          
          if (typeof resp === 'object' && resp.status === 'success') {
            // Keep the loading overlay visible during redirect
            $('.login-loading-text').text('Login successful! Redirecting...');
            
            // Redirect based on user type
            setTimeout(function() {
              if (resp.user_type === 'admin' || resp.user_type === 'staff') {
                location.href = 'admin/';
              } else if (resp.user_type === 'client') {
                location.href = './';
              } else if (resp.user_type === 'driver') {
                location.href = 'driver/';
              } else {
                location.href = './';
              }
            }, 1500);
          } else if (resp.status === 'unverified') {
            // Hide loading overlay and show warning
            hideLoginLoading();
            showWarningMessage(
              'Your email is not verified. <a href="verify.php?email=' + encodeURIComponent(resp.email) + '">Click here to verify</a>',
              resp.email
            );
          } else {
            // Hide loading overlay and show error
            hideLoginLoading();
            showErrorMessage(resp.msg || "Wrong password or username. Please try again.");
          }
        }
      });
    });

    // Fix for mobile centering and white space
    function fixMobileLayout() {
      const vh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty('--vh', `${vh}px`);
      
      // Ensure body covers full height
      document.body.style.minHeight = window.innerHeight + 'px';
      
      // Force re-center on resize
      $('.login-container').css({
        'min-height': window.innerHeight + 'px',
        'display': 'flex',
        'align-items': 'center',
        'justify-content': 'center'
      });
    }

    // Apply fixes on load and resize
    fixMobileLayout();
    window.addEventListener('resize', fixMobileLayout);
    window.addEventListener('orientationchange', function() {
      setTimeout(fixMobileLayout, 100);
    });
  });
</script>

</body>
</html>