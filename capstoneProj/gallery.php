<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: 'Inter', sans-serif;
            color: #212529;
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
            color: #2563eb;
        }

        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(37, 99, 235, 0.1);
            border-top: 4px solid #2563eb;
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

        .gallery-section {
            padding: 40px 20px;
            text-align: center;
            background-color: #ffffff;
            min-height: 100vh;
        }

        .gallery-section h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            color: #2563eb;
            font-weight: 700;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
            cursor: pointer;
            aspect-ratio: 4/3;
            background-color: #f8f9fa;
        }

        .gallery-item:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        footer {
            display: none !important;
        }

        /* Image Modal Styles - No text overlay */
        .image-modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            align-items: center;
            justify-content: center;
            opacity: 1;
        }

        #enlargedImage {
            max-width: 90%;
            max-height: 90%;
            display: block;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.6);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        #enlargedImage.zoomed {
            transform: scale(1.05);
        }

        body.modal-open {
            overflow: hidden;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .gallery-section h2 {
                font-size: 2rem;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .gallery-section h2 {
                font-size: 1.8rem;
            }
            
            .gallery-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-loader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-text">Loading Gallery</div>
        </div>
    </div>
<br>
<br>
    <section class="gallery-section">
        <h2>Gallery</h2>
        <div class="gallery-grid">
            <?php
            $json = __DIR__ . '/uploads/gallery/gallery.json';
            $gallery = file_exists($json) ? json_decode(file_get_contents($json), true) : [];

            if (!empty($gallery)):
                foreach ($gallery as $imgPath):
                    $fullPath = 'uploads/gallery/' . basename($imgPath);
            ?>
                <div class="gallery-item" onclick="openImageModal('<?php echo htmlspecialchars($fullPath); ?>')">
                    <img src="<?php echo htmlspecialchars($fullPath); ?>" alt="" loading="lazy">
                </div>
            <?php
                endforeach;
            else:
            ?>
                <p>No images found in gallery.</p>
            <?php endif; ?>
        </div>
    </section>

    <div id="imageModalOverlay" class="image-modal-overlay" onclick="closeImageModal(event)">
        <img id="enlargedImage" src="" alt="" onclick="handleEnlargedImageClick(event)">
    </div>

    <script>
        // Hide loader when page is loaded
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.querySelector('.page-loader').classList.add('hidden');
            }, 800);
        });

        const modalOverlay = document.getElementById('imageModalOverlay');
        const enlargedImage = document.getElementById('enlargedImage');
        let currentImageIndex = 0;
        let galleryImages = [];

        // Preload all gallery images for faster switching
        function preloadImages() {
            galleryImages = Array.from(document.querySelectorAll('.gallery-item img')).map(img => img.src);
        }

        function openImageModal(imageUrl) {
            // Find the index of the clicked image
            currentImageIndex = galleryImages.indexOf(imageUrl);
            
            // Show the modal immediately with no delay
            modalOverlay.style.display = 'flex';
            document.body.classList.add('modal-open');
            
            // Set the image source
            enlargedImage.src = imageUrl;
            
            // Remove zoom class if it exists
            enlargedImage.classList.remove('zoomed');
            
            // Preload images if not already done
            if (galleryImages.length === 0) {
                preloadImages();
            }
        }

        function closeImageModal(event) {
            if (event.target === modalOverlay) {
                // Close immediately with no delay
                modalOverlay.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        }

        function handleEnlargedImageClick(event) {
            event.stopPropagation();
            // Toggle zoom effect
            enlargedImage.classList.toggle('zoomed');
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(event) {
            if (!modalOverlay.style.display || modalOverlay.style.display === 'none') return;
            
            if (event.key === 'Escape' || event.keyCode === 27) {
                closeImageModal(event);
            }
            
            if (event.key === 'ArrowRight' || event.keyCode === 39) {
                // Next image
                currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
                enlargedImage.src = galleryImages[currentImageIndex];
                enlargedImage.classList.remove('zoomed');
                event.preventDefault();
            }
            
            if (event.key === 'ArrowLeft' || event.keyCode === 37) {
                // Previous image
                currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
                enlargedImage.src = galleryImages[currentImageIndex];
                enlargedImage.classList.remove('zoomed');
                event.preventDefault();
            }
        });
    </script>
</body>
</html>