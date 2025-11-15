<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>White Background Layout</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        html,
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        #main-header {
            position: relative;
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 0;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #212529;
            padding: 2rem;
            animation: fadeInUp 1.2s ease forwards;
            max-width: 900px;
        }

        .scrollable-about {
            max-height: 60vh;
            overflow-y: hidden;
            padding-right: 15px;
            transition: overflow-y 0.2s ease;
            scrollbar-gutter: stable;
        }

        .scrollable-about:hover,
        .scrollable-about:active,
        .scrollable-about.scrolling {
            overflow-y: auto;
        }

        .scrollable-about::-webkit-scrollbar {
            width: 8px;
        }

        .scrollable-about::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        .scrollable-about::-webkit-scrollbar-thumb {
            background: rgba(255, 187, 51, 0.5);
            border-radius: 4px;
        }

        .scrollable-about::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 159, 0, 0.6);
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #212529;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .hero-content p {
            font-size: 1.2rem;
            color: #495057;
            text-shadow: none;
            margin-bottom: 2rem;
        }

        .hero-btn {
            display: inline-block;
            background: #ffbb33;
            color: #212529;
            font-weight: 600;
            padding: 0.75rem 2.5rem;
            border-radius: 50px;
            box-shadow: 0 6px 12px rgba(255, 187, 51, 0.5);
            text-decoration: none;
            transition: 0.3s ease;
        }

        .hero-btn:hover {
            background: #ff9f00;
            box-shadow: 0 8px 18px rgba(255, 159, 0, 0.6);
            color: #212529;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.2rem;
            }

            .hero-content p {
                font-size: 1rem;
            }
            
            .scrollable-about {
                max-height: 50vh;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .about-us h2 {
            color: #ffbb33;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .about-us p {
            font-size: 1.1rem;
            color: #343a40;
            line-height: 1.5;
            text-shadow: none;
        }

        footer {
            display: none !important;
        }
    </style>
</head>
<br>
<br>
<br>
<br>
<br>
<body>
    <!-- Header -->
  
        <div class="hero-overlay"></div>
        <div class="hero-content container">
            <div class="scrollable-about">
                <?php include "about.html"; ?>
            </div>
        </div>


    <script>
        $(document).scroll(function() {
            $('#topNavBar').removeClass('bg-transparent navbar-light navbar-dark bg-gradient-light text-light')
            if ($(window).scrollTop() === 0) {
                $('#topNavBar').addClass('navbar-dark bg-transparent text-light')
            } else {
                $('#topNavBar').addClass('navbar-light bg-gradient-light')
            }
        });
        
        $(function() {
            $(document).trigger('scroll');
            
            const $scrollableAbout = $('.scrollable-about');
            
            // Show scrollbar on hover
            $scrollableAbout.hover(
                function() {
                    $(this).addClass('scrolling');
                },
                function() {
                    $(this).removeClass('scrolling');
                }
            );
            
            // Keep scrollbar visible during scroll
            $scrollableAbout.on('scroll', function() {
                $(this).addClass('scrolling');
                clearTimeout($(this).data('scrollTimer'));
                $(this).data('scrollTimer', setTimeout(function() {
                    $scrollableAbout.removeClass('scrolling');
                }, 500));
            });
        });
    </script>
</body>
</html>