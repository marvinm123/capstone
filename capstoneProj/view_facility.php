<?php
// Fetch facility data
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT f.*, c.name as category FROM `facility_list` f INNER JOIN category_list c ON f.category_id = c.id WHERE f.id = '{$_GET['id']}' ");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = stripslashes($v);
        }
    }
}
// Facility image
$image_src = validate_image(isset($image_path) ? $image_path : '');

// System cover image for header background
$cover_img = validate_image($_settings->info('cover_img') ?? '');
if (empty($cover_img)) {
    $cover_img = "path/to/default/cover.jpg"; // fallback image
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&display=swap');

    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --primary-light: #3b82f6;
        --secondary: #64748b;
        --accent: #0ea5e9;
        --success: #10b981;
        --warning: #f59e0b;
        --error: #ef4444;
        --dark: #0f172a;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e1;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #475569;
        --gray-700: #334155;
        --gray-800: #1e293b;
        --gray-900: #0f172a;
        --white: #ffffff;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --border-radius: 6px;
        --border-radius-lg: 8px;
        --border-radius-xl: 12px;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding-top: 10;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        color: var(--gray-800);
        line-height: 1.5;
    }

    /* Full-width background pattern */
    .background-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -2;
        background: 
            radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.02) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.02) 0%, transparent 50%),
            linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    }

    /* Full-width main container */
    .page-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 2rem;
        margin-top: 1%;
        width: 100%;
    }

    /* Full-width facility container */
    .facility-container {
        width: 100%;
        max-width: none;
        margin: 0;
        background: var(--white);
        border-radius: var(--border-radius-xl);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        animation: slideUp 0.5s ease-out forwards;
        border: 1px solid var(--gray-200);
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 500px;
    }

    @keyframes slideUp {
        from { 
            opacity: 0; 
            transform: translateY(15px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }

    /* Left side - Details section */
    .facility-details {
        padding: 2.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: var(--white);
        order: 1;
    }

    /* Right side - Image section */
    .image-container {
        position: relative;
        background: var(--gray-50);
        order: 2;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .facility-image {
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: relative;
        min-height: 500px;
    }

    .facility-image img {
    width: 100%;
    height: 100%;
    object-fit: contain cover; /* Changed from 'cover' to 'contain' */
    transition: transform 0.3s ease;
}
    .facility-image:hover img {
        transform: scale(1.02);
    }

    /* Image overlay badges */
    .image-overlay {
        position: absolute;
        top: 1.5rem;
        left: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        z-index: 10;
    }

    .image-badge {
        background: rgba(37, 99, 235, 0.9);
        color: var(--white);
        padding: 0.375rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        gap: 0.375rem;
        width: fit-content;
    }

    .category-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        background: var(--gray-100);
        color: var(--gray-700);
        padding: 0.5rem 1rem;
        border-radius: 16px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1rem;
        border: 1px solid var(--gray-200);
        width: fit-content;
    }

    .facility-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        color: var(--gray-900);
        line-height: 1.2;
    }

    .facility-description {
        font-size: 1.1rem;
        line-height: 1.6;
        color: var(--gray-600);
        margin-bottom: 2rem;
        text-align: left;
    }

    /* Features section */
    .features-section {
        margin-bottom: 2rem;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: var(--border-radius);
        border: 1px solid var(--gray-200);
        transition: all 0.2s ease;
    }

    .feature-item:hover {
        background: var(--white);
        box-shadow: var(--shadow);
        transform: translateY(-1px);
    }

    .feature-icon {
        width: 2.25rem;
        height: 2.25rem;
        background: var(--primary);
        color: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    .feature-text {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    /* Pricing section */
    .pricing-section {
        background: var(--gray-50);
        padding: 1.5rem;
        border-radius: var(--border-radius-lg);
        border: 1px solid var(--gray-200);
        margin-bottom: 2rem;
    }

    .pricing-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .price-container {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
    }

    .price {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--primary);
        font-family: 'Inter', sans-serif;
    }

    .price-unit {
        font-size: 1rem;
        color: var(--gray-500);
        font-weight: 500;
    }

    .availability-badge {
        background: var(--success);
        color: var(--white);
        padding: 0.5rem 1rem;
        border-radius: 16px;
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pricing-details {
        color: var(--gray-600);
        font-size: 0.875rem;
        line-height: 1.4;
        background: var(--white);
        padding: 1rem;
        border-radius: var(--border-radius);
        border: 1px solid var(--gray-200);
    }

    /* Book Now button */
    .book-button {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: var(--white);
        border: none;
        padding: 1rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: var(--border-radius-lg);
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        max-width: 280px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-family: 'Inter', sans-serif;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
    }

    .book-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .book-button:hover::before {
        left: 100%;
    }

    .book-button:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .book-button:active {
        transform: translateY(0);
    }

    .book-button:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    /* Loading spinner */
    .spinner-border {
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid var(--white);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .page-container {
            padding: 1.5rem;
        }
        
        .facility-details {
            padding: 2rem;
        }
        
        .facility-title {
            font-size: 2.25rem;
        }
    }

    @media (max-width: 968px) {
        .facility-container {
            grid-template-columns: 1fr;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .image-container {
            order: 1;
        }
        
        .facility-details {
            order: 2;
            padding: 1.5rem;
        }
        
        .facility-image {
            min-height: 300px;
        }
        
        .facility-title {
            font-size: 2rem;
        }
        
        .features-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .pricing-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }

    @media (max-width: 640px) {
        .page-container {
            padding: 1rem;
        }
        
        .facility-container {
            border-radius: var(--border-radius-lg);
        }
        
        .facility-details {
            padding: 1.25rem;
        }
        
        .facility-image {
            min-height: 250px;
        }
        
        .book-button {
            max-width: 100%;
            padding: 0.875rem 1.5rem;
        }
        
        .facility-title {
            font-size: 1.75rem;
        }
        
        .price {
            font-size: 2rem;
        }

        .image-overlay {
            top: 1rem;
            left: 1rem;
        }
    }

    @media (max-width: 480px) {
        .facility-title {
            font-size: 1.5rem;
        }
        
        .price {
            font-size: 1.75rem;
        }
        
        .facility-description {
            font-size: 1rem;
        }
    }

    /* Hide footer as requested */
    footer, .footer, #footer, [class*="footer-"], [id*="footer-"] {
        display: none !important;
    }
</style>
<br>
<br>
<br>
<!-- Background -->
<div class="background-container"></div>

<!-- Full-width page content -->
<div class="page-container">
    <div class="facility-container">
        <!-- Left side - Details -->

        <div class="image-container">
            <div class="facility-image">
                <img src="<?= $image_src ?>" alt="<?= isset($name) ? htmlspecialchars($name) : 'Facility Image' ?>" />
                <div class="image-overlay">
                    <span class="image-badge">
                        ⭐ Premium
                    </span>
                    <span class="image-badge">
                        ❄ Air Conditioned
                    </span>
                </div>
            </div>
        </div>
        <div class="facility-details">
            <span class="category-tag">
                🏢 <?= isset($category) ? htmlspecialchars($category) : 'Facility' ?>
            </span>
            <h1 class="facility-title"><?= isset($name) ? htmlspecialchars($name) : '' ?></h1>
            
            <p class="facility-description">
                <?= isset($description) ? strip_tags($description) : '' ?>
            </p>

            <!-- Features Section -->
            <div class="features-section">
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">👥</div>
                        <span class="feature-text">Professional Setting</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🖥️</div>
                        <span class="feature-text">Modern Equipment</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">❄</div>
                        <span class="feature-text">Air Conditioned</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">☕</div>
                        <span class="feature-text">Refreshments Available</span>
                    </div>
                </div>
            </div>
            
            <!-- Pricing Section -->
            <div class="pricing-section">
                <div class="pricing-header">
                    <div class="price-container">
                        <span class="price">₱<?= isset($price) ? number_format($price, 2) : '0.00' ?></span>
                        <span class="price-unit">/ hour</span>
                    </div>
                    <div class="availability-badge">
                        ✅ Available Now
                    </div>
                </div>
                <div class="pricing-details">
                    <strong>Includes:</strong> Professional setup, Air Conditioned facilities, modern amenities, and dedicated support. Minimum booking duration may apply. 
                    <br>
                    <strong>Note:</strong> You have to pay 50% of the booking fee upfront, and the remaining 50% before your scheduled date. 
                </div>
            </div>
            
            <button id="book_now" class="book-button">
                📅 Book Now
            </button>
        </div>
        
        <!-- Right side - Image -->
       
    </div>
</div>

<script>
$(function() {
    let isBookingInProgress = false;

    $('#book_now').off('click').on('click', function() {
        const button = $(this);

        // Prevent multiple clicks
        if (isBookingInProgress) return false;

        <?php
        $isClient = ($_settings->userdata('id') && $_settings->userdata('login_type') == 2) ? 'true' : 'false';
        ?>
        if (<?= $isClient ?>) {
            isBookingInProgress = true;
            button.addClass('loading').prop('disabled', true).text('Loading...');

            // Clean up any old modals
            $('.modal').modal('hide');
            setTimeout(() => {
                $('.modal').remove();
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            }, 200);

            if (window.modalTimeout) clearTimeout(window.modalTimeout);

            window.modalTimeout = setTimeout(function() {
                const url = "booking.php?fid=<?= $id ?>&t=" + new Date().getTime();
                const facilityTitle = $('.facility-title').first().text().trim() || 'Facility';

                $.ajax({
                    url: url,
                    method: 'GET',
                    timeout: 10000,
                    success: function(response) {
                        const modalId = 'bookingModal_' + Date.now();
const modalHtml = `
<div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-body" style="padding: 0;">
                <div style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); padding: 2rem; text-align: center; color: white;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                    <h3 style="margin: 0 0 0.5rem 0; font-weight: 600;">Book ${facilityTitle}</h3>
                    <p style="margin: 0; opacity: 0.9;">Ready to reserve your time slot?</p>
                </div>
                <div style="padding: 2rem;">
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <span style="font-weight: 500;">Select your preferred date</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <span style="font-weight: 500;">Choose available time slots</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: #10b981; font-size: 1.2rem;">✓</span>
                            <span style="font-weight: 500;">Confirm your booking</span>
                        </div>
                    </div>
                    
                    <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #ffc107;">
                        <div style="font-size: 0.9rem; color: #856404;">
                            <strong>⚠️ Payment Terms:</strong><br>
                            • 50% payment required upfront<br>
                            • Remaining 50% due before scheduled date<br>
                            • 24hr cancellation notice required
                        </div>
                    </div>
                    
                    <div id="bookingFormContainer" style="display: none;">
                        ${response}
                    </div>
                    
                    <button class="continue-booking-btn" 
                            style="width: 100%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 1rem; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: transform 0.2s;"
                            onmouseover="this.style.transform='translateY(-2px)'"
                            onmouseout="this.style.transform='translateY(0)'">
                        Continue to Calendar
                    </button>
                    <button type="button" class="close" data-dismiss="modal" 
                            style="position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; opacity: 0.5; background: none; border: none; cursor: pointer;"
                            onmouseover="this.style.opacity='1'"
                            onmouseout="this.style.opacity='0.5'">
                        &times;
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>`;

$('body').append(modalHtml);
const $modal = $('#' + modalId);

// Handle continue button click - shows the booking form
$modal.find('.continue-booking-btn').on('click', function() {
    // Hide the intro content and button
    $(this).closest('.modal-body').find('> div:last-child > div').first().slideUp(300);
    $(this).closest('.modal-body').find('> div:last-child > div').eq(1).slideUp(300);
    $(this).slideUp(300);
    
    // Show the booking form
    setTimeout(function() {
        $('#bookingFormContainer').slideDown(400);
    }, 300);
});

$modal.on('shown.bs.modal', function() {
    resetBookNowButton();
});

$modal.on('hide.bs.modal', function() {
    resetBookNowButton();
});

$modal.on('hidden.bs.modal', function() {
    $modal.remove();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
    resetBookNowButton();
});

$modal.modal('show');
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to load booking form. Please try again.');
                        console.error('AJAX Error:', status, error);
                        resetBookNowButton();
                    }
                });
            }, 300);

        } else {
            location.href = './login.php';
        }
    });

    function resetBookNowButton() {
        $('#book_now').removeClass('loading').prop('disabled', false).text('Book Now');
        isBookingInProgress = false;
    }

    $(document).on('click', '[data-dismiss="modal"]', function() {
        resetBookNowButton();
        setTimeout(() => {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
        }, 300);
    });

    $(document).on('keydown', function(e) {
        if (e.keyCode === 27 && $('.modal:visible').length > 0) {
            resetBookNowButton();
        }
    });

    $(window).on('beforeunload', function() {
        if (window.modalTimeout) {
            clearTimeout(window.modalTimeout);
        }
    });
});
</script>
