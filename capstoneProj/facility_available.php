<?php require_once('./config.php') ?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php') ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities | <?= $_settings->info('name') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #60a5fa;
            --secondary: #6b7280;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --light-gray: #f9fafb;
            --medium-gray: #e5e7eb;
            --dark-gray: #1f2937;
            --text-color: #374151;
            --border-radius-sm: 0.375rem;
            --border-radius: 0.5rem;
            --border-radius-lg: 0.75rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(to bottom right, #ffffff, var(--light-gray));
            color: var(--text-color);
            line-height: 1.5;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Page Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .loader-content {
            text-align: center;
            color: var(--primary);
        }

        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(37, 99, 235, 0.1);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }

        .loader-text {
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: 1px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        /* Full-width Main Container */
        .main-container {
            width: 100%;
            margin: 0;
            padding: 2.5rem 0; /* Vertical padding only */
            padding-top: 5rem;
        }

        /* Full-width content container */
        .content-container {
            max-width: 100%;
            padding: 0 1.5rem; /* Mobile-first padding */
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            margin-bottom: 3.5rem;
            text-align: center;
            padding: 0 1rem; /* Add some padding on smaller screens */
        }

        .page-title {
            font-size: 2.25rem; /* Mobile-first size */
            font-weight: 800;
            color: var(--dark-gray);
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
            line-height: 1.2;
        }

        .page-subtitle {
            font-size: 1rem; /* Mobile-first size */
            color: var(--secondary);
            max-width: 1000px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Search Bar - Full width with constrained content */
        .search-container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto 4rem auto;
            padding: 0 1rem; /* Mobile-first padding */
        }

        .search-wrapper {
            position: relative;
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--medium-gray);
            transition: all 0.3s ease;
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-wrapper:hover {
            box-shadow: var(--shadow-lg);
        }

        .search-wrapper:focus-within {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        #search {
            background: transparent;
            border: none;
            padding: 1rem 1.5rem; /* Mobile-first padding */
            font-size: 1rem; /* Mobile-first size */
            color: var(--dark-gray);
            width: 100%;
            outline: none;
        }

        #search::placeholder {
            color: #9ca3af;
        }

        .search-icon {
            position: absolute;
            right: 1.5rem; /* Mobile-first position */
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
            font-size: 1.1rem; /* Mobile-first size */
        }

        /* Facility Grid - Mobile-first layout */
        #facility_list {
            display: grid;
            grid-template-columns: 1fr; /* Single column on mobile */
            gap: 1.5rem; /* Mobile-first gap */
            margin: 2rem 0;
            padding: 0 1rem; /* Mobile-first padding */
            width: 100%;
        }

        /* Facility Card */
        .book_facility {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            text-decoration: none;
            color: var(--dark-gray);
            display: flex;
            flex-direction: column;
            opacity: 0;
            transform: translateY(20px);
            animation: cardSlideIn 0.6s ease forwards;
            border: 1px solid var(--medium-gray);
        }

        @keyframes cardSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Staggered animation */
        .book_facility:nth-child(1) { animation-delay: 0.1s; }
        .book_facility:nth-child(2) { animation-delay: 0.15s; }
        .book_facility:nth-child(3) { animation-delay: 0.2s; }
        .book_facility:nth-child(4) { animation-delay: 0.25s; }
        .book_facility:nth-child(5) { animation-delay: 0.3s; }
        .book_facility:nth-child(6) { animation-delay: 0.35s; }
        .book_facility:nth-child(n+7) { animation-delay: 0.4s; }

        .book_facility:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
        }

        /* Image Container */
        .facility-image-container {
            position: relative;
            width: 100%;
            height: 200px; /* Mobile-first height */
            overflow: hidden;
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        }

        .facility-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .book_facility:hover .facility-thumbnail {
            transform: scale(1.08);
        }

        /* Image Overlay */
        .image-overlay-info {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0));
            padding: 1.25rem 1.5rem 1rem; /* Mobile-first padding */
            color: white;
            font-size: 1rem; /* Mobile-first size */
            font-weight: 600;
        }

        .overlay-price {
            font-size: 1.4rem; /* Mobile-first size */
            font-weight: 800;
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
        }

        .overlay-price span {
            font-size: 0.8em;
            font-weight: 600;
            opacity: 0.8;
        }

        .overlay-status {
            background: var(--success);
            padding: 0.4rem 1rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Category Badge */
        .facility-category {
            position: absolute;
            top: 1rem; /* Mobile-first position */
            right: 1rem; /* Mobile-first position */
            background: rgba(37, 99, 235, 0.9);
            color: white;
            padding: 0.5rem 1rem; /* Mobile-first padding */
            font-size: 0.75rem; /* Mobile-first size */
            font-weight: 600;
            border-radius: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(5px);
            z-index: 10;
        }

        /* Content */
        .facility-content {
            padding: 1.5rem; /* Mobile-first padding */
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .facility-name {
            font-size: 1.3rem; /* Mobile-first size */
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark-gray);
            line-height: 1.3;
        }

        .facility-description {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            font-size: 0.9rem; /* Mobile-first size */
            line-height: 1.6;
            flex-grow: 1;
        }

        .facility-features {
            font-size: 0.9rem; /* Mobile-first size */
            color: var(--secondary);
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .facility-features i {
            color: var(--primary);
            font-size: 1rem; /* Mobile-first size */
        }

        /* Action Button */
        .facility-action {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.25rem;
        }

        .view-details-btn {
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem; /* Mobile-first padding */
            border-radius: 2rem;
            font-size: 0.9rem; /* Mobile-first size */
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .book_facility:hover .view-details-btn {
            background: var(--primary-dark);
            box-shadow: var(--shadow);
        }

        .arrow-icon {
            transition: transform 0.3s ease;
        }

        .book_facility:hover .arrow-icon {
            transform: translateX(5px);
        }

        /* No Results */
        #noResult, #noResultSearch {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 1rem; /* Mobile-first padding */
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            display: none;
            border: 1px solid var(--medium-gray);
            margin: 0 1rem; /* Mobile-first margin */
        }

        #noResult i, #noResultSearch i {
            font-size: 3rem; /* Mobile-first size */
            color: var(--secondary);
            margin-bottom: 2rem;
            opacity: 0.6;
        }

        #noResult p, #noResultSearch p {
            font-size: 1.2rem; /* Mobile-first size */
            color: var(--secondary);
            font-weight: 500;
        }

        /* Tablet Breakpoint */
        @media (min-width: 768px) {
            .content-container, #facility_list {
                padding: 0 2rem;
            }

            .page-title {
                font-size: 2.5rem;
            }

            .page-subtitle {
                font-size: 1.15rem;
            }

            .search-container {
                padding: 0 2rem;
            }

            #search {
                padding: 1.25rem 2rem;
                font-size: 1.05rem;
            }

            .search-icon {
                right: 2rem;
                font-size: 1.2rem;
            }

            #facility_list {
                grid-template-columns: repeat(2, 1fr); /* 2 columns on tablet */
                gap: 2rem;
            }

            .facility-image-container {
                height: 240px;
            }

            .facility-content {
                padding: 1.75rem;
            }

            .facility-name {
                font-size: 1.5rem;
            }

            .facility-description {
                font-size: 1rem;
            }

            .image-overlay-info {
                padding: 1.5rem 2rem 1.25rem;
            }

            .overlay-price {
                font-size: 1.5rem;
            }

            .facility-category {
                top: 1.25rem;
                right: 1.25rem;
                padding: 0.6rem 1.25rem;
                font-size: 0.8rem;
            }

            .view-details-btn {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
            
            #noResult, #noResultSearch {
                margin: 0 2rem;
                padding: 4rem 2rem;
            }
            
            #noResult i, #noResultSearch i {
                font-size: 3.5rem;
            }
            
            #noResult p, #noResultSearch p {
                font-size: 1.3rem;
            }
        }

        /* Desktop Breakpoint */
        @media (min-width: 1024px) {
            .content-container, #facility_list {
                padding: 0 3rem;
            }

            .page-title {
                font-size: 3rem;
            }

            .page-subtitle {
                font-size: 1.25rem;
            }

            .search-container {
                padding: 0 3rem;
            }

            #facility_list {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); /* Responsive columns */
                gap: 2.5rem;
            }

            .facility-image-container {
                height: 280px;
            }

            .facility-content {
                padding: 2rem;
            }

            .facility-name {
                font-size: 1.75rem;
            }

            .facility-description {
                font-size: 1.05rem;
            }

            .image-overlay-info {
                padding: 1.75rem 2rem 1.25rem;
            }

            .overlay-price {
                font-size: 1.6rem;
            }

            .facility-category {
                top: 1.5rem;
                right: 1.5rem;
                padding: 0.7rem 1.5rem;
                font-size: 0.85rem;
            }
            
            #noResult, #noResultSearch {
                margin: 0 3rem;
                padding: 5rem;
            }
            
            #noResult i, #noResultSearch i {
                font-size: 4rem;
            }
            
            #noResult p, #noResultSearch p {
                font-size: 1.5rem;
            }
        }

        /* Large Desktop Breakpoint */
        @media (min-width: 1400px) {
            .content-container, #facility_list {
                padding: 0 4rem;
            }

            .page-title {
                font-size: 3.5rem;
            }

            .page-subtitle {
                font-size: 1.5rem;
            }

            .search-container {
                padding: 0 4rem;
            }

            #facility_list {
                grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            }

            .facility-image-container {
                height: 300px;
            }
            
            #noResult, #noResultSearch {
                margin: 0 4rem;
            }
        }

        /* Extra Large Screens */
        @media (min-width: 1600px) {
            .content-container, #facility_list {
                padding: 0 5rem;
            }
            
            .search-container {
                padding: 0 5rem;
            }
            
            #noResult, #noResultSearch {
                margin: 0 5rem;
            }
        }

        /* Small Mobile Breakpoint */
        @media (max-width: 360px) {
            .content-container, #facility_list {
                padding: 0 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .page-subtitle {
                font-size: 0.9rem;
            }

            .search-container {
                padding: 0 1rem;
            }

            #search {
                padding: 0.9rem 1.25rem;
                font-size: 0.95rem;
            }

            .search-icon {
                right: 1.25rem;
                font-size: 1rem;
            }

            .facility-image-container {
                height: 180px;
            }

            .facility-content {
                padding: 1.25rem;
            }

            .facility-name {
                font-size: 1.2rem;
            }

            .facility-description {
                font-size: 0.85rem;
            }

            .image-overlay-info {
                padding: 1rem 1.25rem 0.75rem;
                font-size: 0.9rem;
            }

            .overlay-price {
                font-size: 1.3rem;
            }

            .facility-category {
                padding: 0.4rem 0.8rem;
                font-size: 0.7rem;
            }

            .view-details-btn {
                padding: 0.7rem 1.25rem;
                font-size: 0.85rem;
            }
            
            #noResult, #noResultSearch {
                margin: 0 1rem;
                padding: 2rem 0.5rem;
            }
            
            #noResult i, #noResultSearch i {
                font-size: 2.5rem;
            }
            
            #noResult p, #noResultSearch p {
                font-size: 1.1rem;
            }
        }

        footer {
            display: none !important;
        }
    </style>
</head>

<body>
    <div class="page-loader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-text">Loading Facilities</div>
        </div>
    </div>
    <br>

    <div class="main-container">
        <div class="content-container">
            <header class="page-header">
                <h1 class="page-title">Discover Our Premium Facilities</h1>
                <p class="page-subtitle">
                    Explore our curated selection of professional and versatile facilities,
                    perfectly designed to support your events, bookings, and collaborations.
                </p>
            </header>

            <div class="search-container">
                <div class="search-wrapper">
                    <input type="search" id="search" placeholder="Search facilities by name, category, or description..." aria-label="Search Facilities">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <div id="facility_list">
                <?php
                $facilities = $conn->query("SELECT f.*, c.name as category FROM `facility_list` f INNER JOIN category_list c ON f.category_id = c.id WHERE f.delete_flag = 0 ORDER BY f.facility_code");
                if ($facilities->num_rows > 0) :
                    while ($row = $facilities->fetch_assoc()) :
                ?>
                        <a class="book_facility" href="./?p=view_facility&id=<?= $row['id'] ?>" data-id="<?= $row['id'] ?>">
                            <div class="facility-image-container">
                                <div class="facility-category"><?= htmlspecialchars($row['category']) ?></div>
                                <img src="<?= validate_image($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="facility-thumbnail" loading="lazy">
                                <div class="image-overlay-info">
                                    <div class="overlay-price">
                                        ₱<?= number_format($row['price'], 2) ?> <span>/ hour</span>
                                    </div>
                                   
                                </div>
                            </div>
                            <div class="facility-content">
                                <h3 class="facility-name"><?= htmlspecialchars($row['name']) ?></h3>
                                <p class="facility-description">
                                    <?= htmlspecialchars(strip_tags(substr($row['description'], 0, 180))) ?><?php if (strlen($row['description']) > 180) echo '...'; ?>
                                </p>
                                <div class="facility-features">
                                    <i class="fas fa-users"></i> Spacious & adaptable
                                </div>
                                <div class="facility-action">
                                    <div class="view-details-btn">
                                        <span>View Details</span>
                                        <i class="fas fa-arrow-right arrow-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php
                    endwhile;
                else :
                    ?>
                    <div id="noResult" style="display: block;">
                        <i class="fas fa-search-minus"></i>
                        <p>No facilities found yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div id="noResultSearch" style="display: none;">
                <i class="fas fa-search-minus"></i>
                <p>No facilities found matching your search criteria</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Hide loader when page is loaded
            setTimeout(function() {
                $('.page-loader').addClass('hidden');
            }, 800);

            // Search functionality
            $('#search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase().trim();
                let visibleCount = 0;

                $('#facility_list .book_facility').each(function() {
                    const facilityText = $(this).text().toLowerCase();
                    const isVisible = facilityText.includes(searchTerm);

                    $(this).toggle(isVisible);
                    if (isVisible) visibleCount++;
                });

                // Show/hide #noResultSearch based on search results
                $('#noResultSearch').toggle(visibleCount === 0);
                $('#noResult').toggle(false); // Ensure the initial no result is hidden
            });

            // If initially no facilities are loaded (PHP returns 0 rows), hide #noResultSearch
            // and ensure the main #noResult is shown.
            <?php if ($facilities->num_rows == 0) : ?>
                $('#noResult').show();
                $('#noResultSearch').hide(); // Ensure this is hidden if no initial facilities
            <?php else : ?>
                $('#noResult').hide(); // Ensure this is hidden if there are initial facilities
            <?php endif; ?>
        });
    </script>
</body>

</html>