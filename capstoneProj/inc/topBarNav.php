<link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    /* Modern Professional Navbar Styles */
    #topNavBar {
        font-family: 'Comic Neue', cursive;
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    #topNavBar .container-fluid {
        max-width: 1400px;
        padding-left: 1rem;
        padding-right: 1rem;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    /* Logo and Brand */
    #topNavBar .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: #2c3e50 !important;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        white-space: nowrap;
        margin-bottom: 0;
    }

    #topNavBar .navbar-brand img {
        transition: all 0.3s ease;
        border-radius: 6px;
        flex-shrink: 0;
    }

    #topNavBar .navbar-brand:hover {
        transform: translateY(-2px);
    }

    #topNavBar .navbar-brand:hover img {
        transform: rotate(-5deg);
    }

    /* Navigation Container */
    .nav-container {
        display: flex;
        flex-wrap: nowrap;
        justify-content: center;
        align-items: center;
        gap: 0.3rem;
        flex: 1;
        margin: 0 1.5rem;
        order: 2;
        width: 100%;
    }

    /* Nav Items */
    #topNavBar .nav-link {
        color: #34495e !important;
        font-weight: 600;
        padding: 0.5rem 1rem !important;
        position: relative;
        transition: all 0.3s ease;
        border-radius: 8px;
        white-space: nowrap;
        font-size: 0.95rem;
    }

    #topNavBar .nav-link:before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #ff8800, #ff6600);
        transition: width 0.3s ease;
    }

    #topNavBar .nav-link:hover {
        color: #ff8800 !important;
        transform: translateY(-2px);
    }

    #topNavBar .nav-link:hover:before {
        width: 60%;
    }

    #topNavBar .nav-link.active {
        color: #ff6600 !important;
        font-weight: 700;
    }

    #topNavBar .nav-link.active:before {
        width: 60%;
    }

    /* Dropdown Menu for User Account */
    #topNavBar .dropdown-menu {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        padding: 0.5rem 0;
        margin-top: 0.5rem;
        transform-origin: top center;
        transition: all 0.2s ease;
        min-width: 220px;
    }

    #topNavBar .dropdown-item {
        padding: 0.6rem 1.5rem;
        color: #34495e !important;
        font-weight: 500;
        transition: all 0.2s ease;
        position: relative;
        white-space: normal;
        word-wrap: break-word;
    }

    #topNavBar .dropdown-item:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background: linear-gradient(to bottom, #ff8800, #ff6600);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    #topNavBar .dropdown-item:hover {
        background: rgba(255, 136, 0, 0.05) !important;
        color: #ff6600 !important;
        padding-left: 1.8rem;
    }

    #topNavBar .dropdown-item:hover:before {
        opacity: 1;
    }

    /* Logout Button Specific Styles */
    #topNavBar .dropdown-item.logout-item {
        color: #dc3545 !important;
    }

    #topNavBar .dropdown-item.logout-item:hover {
        background: rgba(220, 53, 69, 0.05) !important;
        color: #dc3545 !important;
    }

    #topNavBar .dropdown-item.logout-item:before {
        background: linear-gradient(to bottom, #dc3545, #c82333) !important;
    }

    /* Login/Register Buttons */
    .auth-buttons {
        display: flex;
        gap: 0.5rem;
        margin-top: 0;
        order: 1;
    }

    .auth-buttons a {
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        white-space: nowrap;
        font-size: 0.9rem;
    }

    .auth-buttons a:hover {
        transform: translateY(-2px);
    }

    .auth-buttons a:first-child {
        color: #34495e;
    }

    .auth-buttons a:last-child {
        background: linear-gradient(90deg, #ff8800, #ff6600);
        color: white !important;
        box-shadow: 0 4px 15px rgba(255, 136, 0, 0.3);
    }

    .auth-buttons a:last-child:hover {
        box-shadow: 0 6px 20px rgba(255, 136, 0, 0.4);
    }

    /* User Dropdown in Nav */
    .user-dropdown {
        margin-top: 0;
        order: 1;
    }

    .user-dropdown .dropdown-toggle {
        color: #34495e !important;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .user-dropdown .dropdown-toggle:hover {
        background: rgba(255, 136, 0, 0.05);
        color: #ff6600 !important;
    }

    /* Desktop - Large Screens (992px and up) - Login/Register next to brand */
    @media (min-width: 992px) {
        #topNavBar .container-fluid {
            flex-wrap: nowrap;
        }

        .nav-container {
            order: 1;
            width: auto;
            flex: 1;
        }

        .auth-buttons,
        .user-dropdown {
            order: 2;
        }
    }

    /* Desktop - Large Screens (1200px and up) */
    @media (min-width: 1200px) {
        #topNavBar .navbar-brand {
            font-size: 1.5rem;
        }
        
        #topNavBar .nav-link {
            font-size: 1rem;
            padding: 0.5rem 1.1rem !important;
        }
        
        .nav-container {
            gap: 0.4rem;
        }
    }

    /* Desktop - Medium Screens (992px to 1199px) */
    @media (max-width: 1199.98px) and (min-width: 992px) {
        #topNavBar .nav-link {
            padding: 0.5rem 0.85rem !important;
            font-size: 0.9rem;
        }
        
        #topNavBar .navbar-brand {
            font-size: 1.3rem;
        }
        
        .nav-container {
            gap: 0.25rem;
            margin: 0 1rem;
        }
        
        .auth-buttons a,
        .user-dropdown .dropdown-toggle {
            padding: 0.5rem 0.9rem;
            font-size: 0.85rem;
        }
    }

    /* Tablet (768px to 991px) */
    @media (max-width: 991.98px) and (min-width: 768px) {
        #topNavBar {
            padding: 0.6rem 0;
        }
        
        #topNavBar .container-fluid {
            flex-direction: column;
        }
        
        #topNavBar .navbar-brand {
            font-size: 1.3rem;
            margin-bottom: 0.6rem;
        }
        
        .nav-container {
            margin: 0;
            width: 100%;
            flex-wrap: wrap;
        }
        
        #topNavBar .nav-link {
            padding: 0.45rem 0.8rem !important;
            font-size: 0.85rem;
        }
        
        .nav-container {
            gap: 0.4rem;
        }
        
        .auth-buttons {
            margin-top: 0.5rem;
            order: 3;
        }
        
        .auth-buttons a,
        .user-dropdown .dropdown-toggle {
            font-size: 0.85rem;
            padding: 0.45rem 1rem;
        }
        
        .user-dropdown {
            margin-top: 0.5rem;
            order: 3;
        }
    }

    /* Mobile - Landscape (576px to 767px) */
    @media (max-width: 767.98px) and (min-width: 576px) {
        #topNavBar {
            padding: 0.5rem 0;
        }
        
        #topNavBar .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        #topNavBar .navbar-brand {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        #topNavBar .navbar-brand img {
            width: 28px;
            height: 28px;
        }
        
        #topNavBar .nav-link {
            padding: 0.4rem 0.7rem !important;
            font-size: 0.8rem;
        }
        
        .nav-container {
            gap: 0.3rem;
        }
        
        .auth-buttons {
            margin-top: 0.4rem;
            gap: 0.4rem;
        }
        
        .auth-buttons a,
        .user-dropdown .dropdown-toggle {
            font-size: 0.8rem;
            padding: 0.4rem 0.9rem;
        }
        
        .user-dropdown {
            margin-top: 0.4rem;
        }
    }

    /* Mobile - Portrait (up to 575px) */
    @media (max-width: 575.98px) {
        #topNavBar {
            padding: 0.4rem 0;
        }
        
        #topNavBar .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        #topNavBar .navbar-brand {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        #topNavBar .navbar-brand img {
            width: 26px;
            height: 26px;
        }
        
        #topNavBar .navbar-brand span {
            font-size: 1rem;
        }
        
        #topNavBar .nav-link {
            padding: 0.35rem 0.6rem !important;
            font-size: 0.75rem;
        }
        
        .nav-container {
            gap: 0.25rem;
        }
        
        .auth-buttons {
            margin-top: 0.4rem;
            gap: 0.3rem;
        }
        
        .auth-buttons a {
            font-size: 0.75rem;
            padding: 0.35rem 0.8rem;
        }
        
        .auth-buttons a i {
            display: none;
        }
        
        .user-dropdown {
            margin-top: 0.4rem;
        }
        
        .user-dropdown .dropdown-toggle {
            font-size: 0.75rem;
            padding: 0.35rem 0.8rem;
        }
        
        #topNavBar .dropdown-menu {
            min-width: 200px;
        }
        
        #topNavBar .dropdown-item {
            font-size: 0.8rem;
            padding: 0.5rem 1.2rem;
        }
    }

    /* Extra Small Mobile (up to 375px) */
    @media (max-width: 375px) {
        #topNavBar {
            padding: 0.35rem 0;
        }
        
        #topNavBar .navbar-brand {
            font-size: 0.9rem;
        }
        
        #topNavBar .navbar-brand img {
            width: 24px;
            height: 24px;
        }
        
        #topNavBar .navbar-brand span {
            font-size: 0.9rem;
        }
        
        #topNavBar .nav-link {
            padding: 0.3rem 0.5rem !important;
            font-size: 0.7rem;
        }
        
        .nav-container {
            gap: 0.2rem;
        }
        
        .auth-buttons a,
        .user-dropdown .dropdown-toggle {
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
        }
        
        #topNavBar .dropdown-item {
            font-size: 0.75rem;
            padding: 0.45rem 1rem;
        }
    }

    /* Very Small Mobile (up to 320px) */
    @media (max-width: 320px) {
        #topNavBar .navbar-brand {
            font-size: 0.85rem;
        }
        
        #topNavBar .navbar-brand img {
            width: 22px;
            height: 22px;
        }
        
        #topNavBar .nav-link {
            padding: 0.25rem 0.4rem !important;
            font-size: 0.65rem;
        }
        
        .auth-buttons a,
        .user-dropdown .dropdown-toggle {
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
        }
    }

    /* Collapsed State */
    #topNavBar.collapsed {
        transform: translateY(-100%);
        opacity: 0;
        pointer-events: none;
    }
</style>

<nav class="navbar fixed-top" id="topNavBar">
    <div class="container-fluid">
        <!-- Logo/Brand -->
        <a class="navbar-brand" href="./">
            <img src="<?php echo validate_image($_settings->info('logo')) ?>" width="30" height="30" class="d-inline-block align-top" alt="" loading="lazy">
            <span class="ms-2"><?php echo $_settings->info('short_name') ?></span>
        </a>

        <!-- Auth Buttons or User Dropdown (Shows next to brand on desktop) -->
        <?php if($_settings->userdata('id') > 0 && $_settings->userdata('login_type') == 2): // Only client is logged in ?>
        <div class="user-dropdown dropdown">
            <a class="dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= $_settings->userdata('email') ?>
            </a>
            <div class="dropdown-menu dropdown-menu-center" aria-labelledby="navbarDropdownMenuLink">
                <a class="dropdown-item" href="./?p=booking_list"><i class="fas fa-calendar-check me-2"></i>My Bookings</a>
                <a class="dropdown-item" href="./?p=manage_account"><i class="fas fa-user-cog me-2"></i>Manage Account</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item logout-item" href="./classes/Login.php?f=logout_client"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </div>
        </div>
        <?php else: // No user (or admin/driver) is logged in, show login/register ?>
        <div class="auth-buttons">
            <a href="./login.php" class="btn btn-link text-decoration-none"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
            <a href="./register.php" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Register</a>
        </div>
        <?php endif; ?>

        <!-- Navigation Links (Center on desktop, below on mobile) -->
        <div class="nav-container">
            <a class="nav-link <?= isset($page) && $page == 'home'? 'active' : '' ?>" href="./">Home</a>
            <a class="nav-link <?= isset($page) && $page == 'facility_available'? 'active' : '' ?>" href="./?p=facility_available">Facilities</a>
            <a class="nav-link <?= isset($page) && $page == 'gallery'? 'active' : '' ?>" href="./?p=gallery">Gallery</a>
            <a class="nav-link <?= isset($page) && $page == 'events'? 'active' : '' ?>" href="./?p=events">Events</a>
            <a class="nav-link <?= isset($page) && $page == 'about'? 'active' : '' ?>" href="./?p=about">About Us</a>
        </div>
    </div>
</nav>

<script>
    $(function() {
        function checkScroll() {
            if ($(window).scrollTop() > 50) {
                $('#topNavBar').addClass('collapsed');
            } else {
                $('#topNavBar').removeClass('collapsed');
            }
        }

        // Initial check
        checkScroll();
        
        // Check on scroll with debounce for performance
        let scrollTimeout;
        $(window).scroll(function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(checkScroll, 20);
        });

        // Add smooth scrolling to all links
        $("a[href^='#']").on('click', function(event) {
            if (this.hash !== "") {
                event.preventDefault();
                $('html, body').animate({
                    scrollTop: $(this.hash).offset().top - 100
                }, 800);
            }
        });
    });
</script>