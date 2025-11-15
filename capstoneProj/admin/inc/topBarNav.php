<style>
  /* FORCE navbar to adjust with sidebar - AGGRESSIVE FIX */
  .main-header.navbar {
    height: 60px !important;
    min-height: 60px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: #343a40;
    padding: 0.5rem 1rem;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 1050 !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out !important;
    width: 100% !important;
    margin-left: 0 !important;
  }

  /* FORCE: Navbar adjusts when sidebar is open on desktop */
  @media (min-width: 992px) {
    body:not(.sidebar-collapse) .main-header.navbar {
      left: 260px !important;
      width: calc(100% - 260px) !important;
    }
    
    body.sidebar-collapse .main-header.navbar,
    body.sidebar-mini.sidebar-collapse .main-header.navbar {
      left: 0 !important;
      width: 100% !important;
    }
  }

  /* ENHANCED: Hide hamburger button on mobile - more specific selectors */
  @media (max-width: 991px) {
    .nav-link[data-widget="pushmenu"],
    #sidebarToggle,
    li.nav-item:has(.nav-link[data-widget="pushmenu"]),
    li.nav-item:has(#sidebarToggle) {
      display: none !important;
      visibility: hidden !important;
      opacity: 0 !important;
      pointer-events: none !important;
    }
    
    .main-header.navbar {
      left: 0 !important;
      width: 100% !important;
    }
  }

  .navbar-nav .nav-link {
    color: #f8f9fa !important;
    font-weight: 500;
    transition: color 0.2s ease-in-out;
  }

  .navbar-nav .nav-link:hover {
    color: #ced4da !important;
  }

  .nav-link[data-widget="pushmenu"] i {
    font-size: 1.25rem;
    color: #f8f9fa !important;
    margin-right: 0.5rem;
  }

  .navbar-nav.ml-auto {
    display: flex !important;
    align-items: center;
    justify-content: flex-end;
    width: auto;
    margin-left: auto !important;
  }

  .nav-profile-btn {
    border: none;
    border-radius: 50px;
    padding: 0.4rem 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #fff !important;
    white-space: nowrap;
    background-color: rgba(255, 255, 255, 0.1);
    transition: background-color 0.2s ease;
  }

  .nav-profile-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff !important;
  }

  .user-info {
    font-weight: 600;
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #f8f9fa;
    text-align: center;
  }

  .dropdown-menu {
    border-radius: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    font-size: 0.875rem;
    right: 0;
    left: auto;
    border: none;
    margin-top: 0.5rem;
  }

  .dropdown-item {
    transition: background-color 0.2s ease;
    padding: 0.6rem 1.2rem;
  }

  .dropdown-item:hover {
    background-color: #f8f9fa;
    color: #007bff;
  }

  .dropdown-item i {
    width: 20px;
    text-align: center;
  }

  /* Site name/title */
  .nav-item.d-none.d-sm-inline-block .nav-link {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    font-weight: 600;
  }

  /* FORCE: Content wrapper positioning - works for ALL pages */
  .content-wrapper,
  .wrapper .content-wrapper,
  body > .wrapper > .content-wrapper {
    margin-top: 80px !important;
    transition: margin-left 0.3s ease-in-out !important;
    margin-left: 0 !important;
    padding-top: 1.5rem !important;
  }

  @media (min-width: 992px) {
    body:not(.sidebar-collapse) .content-wrapper,
    body:not(.sidebar-collapse) .wrapper .content-wrapper,
    body:not(.sidebar-collapse) body > .wrapper > .content-wrapper {
      margin-left: 260px !important;
    }
    
    body.sidebar-collapse .content-wrapper,
    body.sidebar-collapse .wrapper .content-wrapper,
    body.sidebar-collapse body > .wrapper > .content-wrapper,
    body.sidebar-mini.sidebar-collapse .content-wrapper {
      margin-left: 0 !important;
    }
  }

  /* FORCE: Footer positioning */
  .main-footer,
  footer,
  .wrapper .main-footer {
    transition: margin-left 0.3s ease-in-out !important;
    margin-left: 0 !important;
  }

  @media (min-width: 992px) {
    body:not(.sidebar-collapse) .main-footer,
    body:not(.sidebar-collapse) footer,
    body:not(.sidebar-collapse) .wrapper .main-footer {
      margin-left: 260px !important;
    }
    
    body.sidebar-collapse .main-footer,
    body.sidebar-collapse footer,
    body.sidebar-collapse .wrapper .main-footer,
    body.sidebar-mini.sidebar-collapse .main-footer {
      margin-left: 0 !important;
    }
  }

  /* Responsive adjustments */
  @media (max-width: 991px) {
    .main-header.navbar {
      padding: 0.5rem 0.75rem !important;
      left: 0 !important;
      width: 100% !important;
    }

    .content-wrapper,
    .wrapper .content-wrapper {
      margin-left: 0 !important;
      padding-top: 2rem !important; /* Extra padding on mobile */
    }

    .main-footer,
    .wrapper .main-footer {
      margin-left: 0 !important;
    }

    .user-info {
      max-width: 120px;
    }
  }

  @media (max-width: 768px) {
    .nav-item.d-none.d-sm-inline-block {
      display: none !important;
    }

    .main-header.navbar {
      height: 56px !important;
      min-height: 56px !important;
      padding: 0.4rem 0.6rem !important;
    }

    .content-wrapper,
    .wrapper .content-wrapper {
      margin-top: 76px !important;
      padding-top: 2.5rem !important; /* Increased padding for small screens */
    }

    .user-info {
      max-width: 100px;
      font-size: 0.85rem;
    }

    .nav-profile-btn {
      padding: 0.3rem 0.75rem;
      gap: 0.3rem;
    }

    .dropdown-toggle::after {
      display: none;
    }
  }

  @media (max-width: 576px) {
    .main-header.navbar {
      padding: 0.3rem 0.5rem !important;
      height: 52px !important;
      min-height: 52px !important;
    }

    .content-wrapper,
    .wrapper .content-wrapper {
      margin-top: 72px !important;
      padding-top: 2.5rem !important; /* Extra padding for very small screens */
    }

    .user-info {
      max-width: 80px;
      font-size: 0.8rem;
    }

    .navbar-nav .nav-link {
      padding: 0.4rem 0.6rem;
    }

    .nav-profile-btn {
      padding: 0.25rem 0.6rem;
      gap: 0.25rem;
    }

    .dropdown-menu {
      font-size: 0.8rem;
      min-width: 150px;
    }
  }

  @media (max-width: 400px) {
    .main-header.navbar {
      height: 50px !important;
      min-height: 50px !important;
    }

    .content-wrapper,
    .wrapper .content-wrapper {
      margin-top: 50px !important;
      padding-top: 2rem !important;
    }

    .user-info {
      max-width: 60px;
      font-size: 0.75rem;
    }
  }

  /* FLOATING SHORTCUT BUTTONS FIX - Only apply on small screens, NOT on PC */
  @media (max-width: 991px) {
    .dashboard-header-section {
      margin-top: 60px !important;
      padding-top: 0.25rem !important;
    }
  }

  @media (max-width: 768px) {
    .dashboard-header-section {
      margin-top: 56px !important;
      padding-top: 0.25rem !important;
    }
  }

  @media (max-width: 576px) {
    .dashboard-header-section {
      margin-top: 52px !important;
      padding-top: 0.25rem !important;
    }
  }

  @media (max-width: 400px) {
    .dashboard-header-section {
      margin-top: 50px !important;
      padding-top: 0.25rem !important;
    }
  }
</style>

<nav class="main-header navbar navbar-expand navbar-dark text-sm shadow-sm">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button" id="sidebarToggle">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?php echo base_url ?>" class="nav-link fw-semibold">
        <?php echo (!isMobileDevice()) ? htmlspecialchars($_settings->info('name')) : htmlspecialchars($_settings->info('short_name')); ?> 
      </a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
      <a 
        class="nav-link nav-profile-btn dropdown-toggle" 
        href="#" 
        id="userDropdown" 
        role="button" 
        data-toggle="dropdown" 
        aria-haspopup="true" 
        aria-expanded="false"
      >
        <i class="fas fa-user-circle"></i>
        <span class="user-info">
          <?php echo htmlspecialchars(ucwords($_settings->userdata('firstname').' '.$_settings->userdata('lastname'))); ?>
        </span>
      </a>
      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
        <a class="dropdown-item" href="<?php echo base_url.'admin/?page=user'; ?>">
          <i class="fa fa-user"></i> My Account
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="<?php echo base_url.'/classes/Login.php?f=logout'; ?>">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </li>
  </ul>
</nav>

<script>
  // Override alert_toast to block all toast messages
  function alert_toast(msg, type) {
    // Do nothing to block success messages
  }

  $(document).ready(function() {
    // Override AdminLTE's pushmenu
    $('[data-widget="pushmenu"]').removeAttr('data-widget');
    
    // Hamburger button click handler - only works on desktop
    $('#sidebarToggle').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      if ($(window).width() >= 992) {
        // Desktop only: Toggle sidebar collapse
        $('body').toggleClass('sidebar-collapse');
      }
      // Do nothing on mobile since button is hidden
      
      return false;
    });

    // Handle window resize
    let resizeTimer;
    $(window).on('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        if ($(window).width() >= 992) {
          $('body').removeClass('sidebar-open');
          $('#sidebarOverlay').removeClass('active');
        }
      }, 250);
    });
  });
</script>
<script src="<?php echo base_url ?>plugins/adminlte/js/adminlte.min.js"></script>