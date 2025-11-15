<style>
  /* Mobile hamburger menu button - Semi-transparent */
  .mobile-menu-toggle {
    display: none;
    position: fixed;
    top: 70px;
    left: 15px;
    z-index: 9999;
    background: rgba(52, 58, 64, 0.85);
    backdrop-filter: blur(8px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
  }

  .mobile-menu-toggle:hover {
    background: rgba(73, 80, 87, 0.9);
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(0,0,0,0.4);
  }

  .mobile-menu-toggle:active {
    transform: scale(0.95);
  }

  .mobile-menu-toggle i {
    font-size: 1.4rem;
    transition: transform 0.2s ease;
  }

  body.sidebar-open .mobile-menu-toggle i {
    transform: rotate(90deg);
  }

  /* Sidebar overlay */
  .sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9998;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .sidebar-overlay.active {
    display: block;
    opacity: 1;
  }

  /* Main sidebar container */
  .main-sidebar {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    z-index: 9999;
    background: #343a40;
    overflow-y: auto;
    overflow-x: hidden;
    transition: transform 0.3s ease;
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.3);
    will-change: transform;
  }

  .sidebar {
    height: 100%;
    padding-top: 13px;
    background: #343a40;
  }

  .brand-link {
    background-color: rgb(163, 182, 201) !important;
    padding: 1.25rem 1rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 1rem;
    min-height: 90px;
  }

  .brand-image {
    width: 5rem;
    height: 5rem;
    object-fit: cover;
    background: transparent;
    border-radius: 50%;
    opacity: 1;
    border: none;
    flex-shrink: 0;
    display: block;
    padding: 0;
  }

  .brand-text {
    font-size: 1rem;
    font-weight: 600;
    color: white;
    flex: 1;
    line-height: 1.3;
    white-space: normal;
    word-wrap: break-word;
    text-align: left;
    overflow: hidden;
  }

  .nav-sidebar {
    padding: 1rem 0;
    list-style: none;
  }

  .nav-sidebar > .nav-item {
    margin-bottom: 6px;
  }

  .nav-header {
    padding: 1rem 1.25rem 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 1.5rem;
    position: relative;
  }

  .nav-header::after {
    content: "";
    position: absolute;
    bottom: -0.5rem;
    left: 1.25rem;
    right: 1.25rem;
    height: 1px;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.2), transparent);
  }

  .nav-item {
    margin: 0.25rem 0;
    position: relative;
  }

  .nav-link {
    display: flex;
    align-items: center;
    padding: 0.875rem 1.25rem;
    color: rgba(255, 255, 255, 0.9) !important;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
    margin: 0 0.75rem;
    border-radius: 0.75rem;
    line-height: 1.3;
    background: transparent;
  }

  .nav-link::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 0;
    height: 100%;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(29, 78, 216, 0.1));
    z-index: 0;
    transition: width 0.2s ease;
  }

  .nav-link:hover::before {
    width: 100%;
  }

  .nav-link:hover {
    color: white !important;
    transform: translateX(6px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
  }

  .nav-link.active {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(29, 78, 216, 0.1)) !important;
    color: white !important;
    font-weight: 600;
    border-left: 3px solid #3b82f6;
    margin-left: 0.75rem;
    border-radius: 0 0.75rem 0.75rem 0;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
  }

  .nav-link.active::before {
    width: 100%;
  }

  .nav-icon {
    margin-right: 0.875rem;
    font-size: 1.1rem;
    width: 1.5rem;
    text-align: center;
    color: #ffffff !important;
    transition: all 0.2s ease;
    position: relative;
    z-index: 1;
    flex-shrink: 0;
  }

  .nav-link:hover .nav-icon {
    transform: scale(1.1);
    color: rgb(182, 187, 196) !important;
  }

  .nav-link.active .nav-icon {
    color: rgb(226, 230, 236) !important;
  }

  .nav-link p {
    margin: 0;
    position: relative;
    z-index: 1;
  }

  .nav-notification-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    background-color: #ef4444;
    border-radius: 50%;
    margin-left: auto;
    box-shadow: 0 0 5px rgba(239, 68, 68, 0.7);
    animation: pulse 1.5s infinite;
    position: relative;
    top: 4px;
    flex-shrink: 0;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.1);
    }
  }

  .nav-treeview {
    padding-left: 0;
    margin-top: 10px;
    list-style: none;
  }

  .nav-treeview .nav-link {
    padding-left: 3rem;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7) !important;
  }

  /* DESKTOP VIEW - Sidebar always visible */
  @media (min-width: 992px) {
    .main-sidebar {
      transform: translateX(0) !important;
    }

    .mobile-menu-toggle {
      display: none !important;
    }

    .sidebar-overlay {
      display: none !important;
    }

    .content-wrapper,
    .main-footer {
      margin-left: 260px;
    }
  }

  /* MOBILE VIEW - Sidebar hidden by default */
  @media (max-width: 991px) {
    .mobile-menu-toggle {
      display: flex !important;
      align-items: center;
      justify-content: center;
    }

    .main-sidebar {
      transform: translateX(-100%) !important;
    }

    body.sidebar-open .main-sidebar {
      transform: translateX(0) !important;
    }

    body.sidebar-open .mobile-menu-toggle {
      left: 275px;
      background: rgba(73, 80, 87, 0.9);
    }

    .content-wrapper,
    .main-footer {
      margin-left: 0 !important;
    }

    /* Prevent body scroll when sidebar open */
    body.sidebar-open {
      overflow: hidden;
    }
  }

  /* Tablet adjustments */
  @media (min-width: 768px) and (max-width: 991px) {
    .brand-image {
      width: 4rem;
      height: 4rem;
    }

    .brand-text {
      font-size: 0.9rem;
    }

    .brand-link {
      padding: 1rem 0.8rem;
      gap: 0.8rem;
      min-height: 80px;
    }

    .nav-link {
      font-size: 0.875rem;
      padding: 0.875rem 1.1rem;
    }

    .nav-icon {
      font-size: 1.05rem;
    }

    .nav-header {
      font-size: 0.75rem;
      padding: 0.85rem 1rem 0.4rem;
    }
  }

  /* Small mobile */
  @media (max-width: 767px) {
    .brand-image {
      width: 3.5rem;
      height: 3.5rem;
    }

    .brand-text {
      font-size: 0.85rem;
    }

    .brand-link {
      padding: 0.9rem 0.75rem;
      gap: 0.75rem;
      min-height: 75px;
    }

    .nav-link {
      font-size: 0.85rem;
      padding: 0.8rem 1rem;
    }

    .nav-icon {
      font-size: 1rem;
      margin-right: 0.75rem;
    }

    .nav-header {
      font-size: 0.72rem;
      padding: 0.8rem 0.9rem 0.35rem;
    }
  }

  @media (max-width: 576px) {
    .main-sidebar {
      width: 280px;
      max-width: 85vw;
    }

    .brand-image {
      width: 3rem;
      height: 3rem;
    }

    .brand-text {
      font-size: 0.8rem;
    }

    .brand-link {
      padding: 0.85rem 0.65rem;
      gap: 0.7rem;
      min-height: 70px;
    }

    .nav-link {
      font-size: 0.825rem;
      padding: 0.75rem 0.9rem;
    }

    .nav-icon {
      font-size: 0.95rem;
    }

    .nav-header {
      font-size: 0.7rem;
      padding: 0.75rem 0.85rem 0.3rem;
    }

    .mobile-menu-toggle {
      width: 45px;
      height: 45px;
      top: 65px;
    }

    body.sidebar-open .mobile-menu-toggle {
      left: 295px;
    }
  }

  @media (max-width: 400px) {
    .main-sidebar {
      max-width: 90vw;
    }

    .brand-image {
      width: 2.75rem;
      height: 2.75rem;
    }

    .brand-text {
      font-size: 0.75rem;
    }

    .brand-link {
      padding: 0.8rem 0.6rem;
      gap: 0.65rem;
      min-height: 65px;
    }

    .nav-link {
      font-size: 0.8rem;
      padding: 0.7rem 0.85rem;
    }

    .nav-header {
      font-size: 0.68rem;
      padding: 0.7rem 0.8rem 0.3rem;
    }
  }

  @media (max-width: 320px) {
    .brand-image {
      width: 2.5rem;
      height: 2.5rem;
    }

    .brand-text {
      font-size: 0.7rem;
    }

    .brand-link {
      padding: 0.75rem 0.55rem;
      gap: 0.6rem;
      min-height: 60px;
    }

    .nav-link {
      padding: 0.65rem 0.75rem;
      font-size: 0.78rem;
    }

    .nav-header {
      font-size: 0.65rem;
      padding: 0.65rem 0.75rem 0.25rem;
    }
  }
</style>

<!-- Mobile Hamburger Menu Button -->
<button class="mobile-menu-toggle" id="mobileMenuToggle" type="button">
  <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="<?php echo base_url ?>admin" class="brand-link bg-gradient-primary text-sm text-light">
    <img src="<?php echo validate_image($_settings->info('logo')) ?>" alt="Store Logo" class="brand-image img-circle elevation-3 bg-gradient-light">
    <span class="brand-text font-weight-light"><?php echo $_settings->info('short_name') ?></span>
  </a>

  <div class="sidebar">
    <nav class="mt-4">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <li class="nav-item">
          <a href="./" class="nav-link nav-home">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?php echo base_url ?>admin/?page=facilities" class="nav-link nav-facilities">
            <i class="nav-icon fas fa-door-closed"></i>
            <p>Facility List</p>
            <?php if ($_settings->userdata('type') == 2 && isset($_SESSION['new_facility'])): ?>
              <span class="nav-notification-dot"></span>
            <?php endif; ?>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?php echo base_url ?>admin/?page=clients" class="nav-link nav-clients">
            <i class="nav-icon fas fa-users"></i>
            <p>Registered Clients</p>
            <?php if (isset($_SESSION['new_client'])): ?>
              <span class="nav-notification-dot"></span>
            <?php endif; ?>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?php echo base_url ?>admin/?page=bookings" class="nav-link nav-bookings">
            <i class="nav-icon fas fa-tasks"></i>
            <p>Booking List</p>
            <?php if (isset($_SESSION['new_booking'])): ?>
              <span class="nav-notification-dot"></span>
            <?php endif; ?>
          </a>
        </li>

        <?php if ($_settings->userdata('type') == 1): ?>
          <li class="nav-header">Maintenance</li>
          
          <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=calendar/calendar_overview" class="nav-link nav-calendar_overview">
              <i class="nav-icon fas fa-calendar-alt"></i>
              <p>Calendar Overview</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=reports/monthly_report" class="nav-link nav-reports_monthly_report">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>Monthly Report</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=categories" class="nav-link nav-categories">
              <i class="nav-icon fas fa-th-list"></i>
              <p>Category List</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=user/list" class="nav-link nav-user_list">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>User List</p>
            </a>
          </li>
          
          <li class="nav-item">
            <a href="<?php echo base_url ?>admin/?page=system_info" class="nav-link nav-system_info">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Settings</p>
            </a>
          </li>
        <?php endif; ?>

      </ul>
    </nav>
  </div>
</aside>

<script>
$(document).ready(function() {
  // Set active menu item
  var page = '<?php echo isset($_GET['page']) ? $_GET['page'] : 'home' ?>';
  var s = '<?php echo isset($_GET['s']) ? $_GET['s'] : '' ?>';
  page = page.replace(/\//g, "_");
  if (s !== '') {
    page = page + '_' + s;
  }

  var $target = $('.nav-link.nav-' + page);
  if ($target.length > 0) {
    $target.addClass('active');
  }

  // Mobile menu toggle
  $('#mobileMenuToggle').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    $('body').toggleClass('sidebar-open');
    $('#sidebarOverlay').toggleClass('active');
    
    var $icon = $(this).find('i');
    if ($('body').hasClass('sidebar-open')) {
      $icon.removeClass('fa-bars').addClass('fa-times');
    } else {
      $icon.removeClass('fa-times').addClass('fa-bars');
    }
  });

  // Close on overlay click
  $('#sidebarOverlay').on('click', function(e) {
    e.preventDefault();
    $('body').removeClass('sidebar-open');
    $(this).removeClass('active');
    $('#mobileMenuToggle i').removeClass('fa-times').addClass('fa-bars');
  });

  // Close when clicking a link on mobile
  $('.nav-link').on('click', function() {
    if ($(window).width() < 992) {
      setTimeout(function() {
        $('body').removeClass('sidebar-open');
        $('#sidebarOverlay').removeClass('active');
        $('#mobileMenuToggle i').removeClass('fa-times').addClass('fa-bars');
      }, 200);
    }
  });

  // Close on window resize to desktop
  var resizeTimer;
  $(window).on('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      if ($(window).width() >= 992) {
        $('body').removeClass('sidebar-open');
        $('#sidebarOverlay').removeClass('active');
        $('#mobileMenuToggle i').removeClass('fa-times').addClass('fa-bars');
      }
    }, 250);
  });

  // Disable navbar hamburger on mobile
  $('[data-widget="pushmenu"]').on('click', function(e) {
    if ($(window).width() < 992) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  });
});
</script>

<?php
if (isset($_SESSION['new_facility']) && $_settings->userdata('type') == 2) unset($_SESSION['new_facility']);
if (isset($_SESSION['new_client'])) unset($_SESSION['new_client']);
if (isset($_SESSION['new_booking'])) unset($_SESSION['new_booking']);
?>