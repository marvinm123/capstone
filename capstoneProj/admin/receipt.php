<?php
require_once('./../config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    $id = $_GET['id'];
    
    // Get booking data with all related information
    $booking_qry = $conn->query("SELECT 
        b.*, 
        CONCAT(c.lastname, ', ', c.firstname, ' ', COALESCE(c.middlename, '')) as client_name,
        c.email as client_email,
        c.contact as client_contact,
        c.address as client_address,
        f.name as facility_name,
        f.facility_code,
        f.price as facility_price,
        cat.name as category_name,
        COALESCE(b.paid_amount, 0) as paid_amount
        FROM `booking_list` b 
        INNER JOIN client_list c ON b.client_id = c.id 
        INNER JOIN facility_list f ON b.facility_id = f.id
        INNER JOIN category_list cat ON f.category_id = cat.id
        WHERE b.id = '{$id}'");
    
    if($booking_qry->num_rows > 0){
        $booking_data = $booking_qry->fetch_assoc();
        
        // Extract data
        $ref_code = $booking_data['ref_code'];
        $client_name = $booking_data['client_name'];
        $client_email = $booking_data['client_email'];
        $client_contact = $booking_data['client_contact'];
        $client_address = $booking_data['client_address'];
        $facility_name = $booking_data['facility_name'];
        $facility_code = $booking_data['facility_code'];
        $category_name = $booking_data['category_name'];
        $facility_price = floatval($booking_data['facility_price']);
        $paid_amount = floatval($booking_data['paid_amount']);
        $date_from = $booking_data['date_from'];
        $date_to = $booking_data['date_to'];
        $time_from = $booking_data['time_from'];
        $time_to = $booking_data['time_to'];
        $status = $booking_data['status'];
        
        // Calculate duration and amounts
        if(empty($time_from) || empty($time_to)) {
            // All-day booking
            $start_date = new DateTime($date_from);
            $end_date = new DateTime($date_to);
            $end_date->modify('+1 day');
            $interval_days = $start_date->diff($end_date);
            $total_days = $interval_days->days;
            $total_hours = $total_days * 24;
            $total_amount = $facility_price * $total_days;
            $rate_type = "Daily Rate";
            $rate_display = "₱" . number_format($facility_price, 2) . " × " . $total_days . " day(s)";
        } else {
            // Hourly booking
            $datetime_from = new DateTime($date_from . ' ' . $time_from);
            $datetime_to = new DateTime($date_to . ' ' . $time_to);
            $interval = $datetime_from->diff($datetime_to);
            $total_hours = ($interval->days * 24) + $interval->h + ($interval->i / 60);
            $total_hours = round($total_hours, 2);
            $total_amount = $facility_price * $total_hours;
            $rate_type = "Hourly Rate";
            $rate_display = "₱" . number_format($facility_price, 2) . " × " . $total_hours . " hour(s)";
        }
        
        // Calculate balance
        $balance = $total_amount - $paid_amount;
    }
}

// Get the logo path
$logo_path = validate_image($_settings->info('logo'));
?>

<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Receipt - <?= $ref_code ?? '' ?></title>
    <!-- Add logo as favicon for browser tab -->
    <link rel="icon" type="image/x-icon" href="<?php echo $logo_path; ?>">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #334155;
            line-height: 1.4;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
            position: relative;
        }
        
        .receipt-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #8b5cf6, #f59e0b);
        }
        
        .logo-title-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }
        
        .header-logo {
            max-height: 60px;
            max-width: 150px;
            object-fit: contain;
        }
        
        .receipt-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
            letter-spacing: 1px;
        }
        
        .receipt-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .receipt-ref {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .receipt-body {
            padding: 1.5rem 2rem;
        }
        
        .section {
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: #3b82f6;
            font-size: 0.9rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        
        .info-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 500;
        }
        
        .amount-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 0.75rem;
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 6px;
            border-left: 3px solid #10b981;
        }
        
        .amount-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0;
        }
        
        .amount-label {
            font-weight: 500;
            color: #475569;
            font-size: 0.9rem;
        }
        
        .amount-value {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }
        
        .total-amount {
            font-size: 1.1rem;
            color: #10b981;
            border-top: 1px solid #e2e8f0;
            padding-top: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .paid-amount {
            color: #059669;
            font-size: 1rem;
        }
        
        .balance-amount {
            color: #dc2626;
            font-size: 1rem;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-paid {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .receipt-footer {
            background: #f1f5f9;
            padding: 1.25rem 2rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-info {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        
        .footer-label {
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .footer-value {
            font-size: 0.8rem;
            color: #475569;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            text-decoration: none;
            border: none;
        }
        
        .btn-print {
            background: #3b82f6;
            color: white;
        }
        
        .btn-print:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        
        .btn-back {
            background: #6b7280;
            color: white;
        }
        
        .btn-back:hover {
            background: #4b5563;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 5rem;
            color: rgba(0, 0, 0, 0.03);
            font-weight: 900;
            z-index: -1;
            white-space: nowrap;
            pointer-events: none;
        }

        .logo-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.03;
            z-index: -1;
            pointer-events: none;
            max-width: 400px;
            max-height: 400px;
        }

        .notes-box {
            background: #fffbeb;
            padding: 0.75rem;
            border-radius: 6px;
            border-left: 3px solid #f59e0b;
            font-size: 0.8rem;
        }

        .notes-box p {
            margin-bottom: 0.4rem;
            color: #92400e;
        }

        .error-container {
            text-align: center;
            padding: 3rem 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 2rem auto;
        }

        .error-icon {
            font-size: 4rem;
            color: #ef4444;
            margin-bottom: 1.5rem;
        }

        .error-title {
            color: #ef4444;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .error-message {
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        /* PRINT STYLES - Optimized for one page with better readability */
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body {
                background: white !important;
                font-size: 11pt;
                line-height: 1.3;
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
            }
            
            .receipt-container {
                box-shadow: none !important;
                margin: 0 !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                width: 100%;
                height: auto;
                min-height: auto;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            
            .receipt-header {
                padding: 1rem 1.5rem !important;
                margin-bottom: 0.8rem;
            }
            
            .logo-title-container {
                gap: 0.8rem !important;
            }
            
            .header-logo {
                max-height: 50px !important;
                max-width: 130px !important;
            }
            
            .receipt-title {
                font-size: 1.5rem !important;
                margin-bottom: 0.2rem !important;
            }
            
            .receipt-subtitle {
                font-size: 0.8rem !important;
            }
            
            .receipt-ref {
                top: 0.8rem !important;
                right: 0.8rem !important;
                font-size: 0.75rem !important;
                padding: 0.3rem 0.6rem !important;
            }
            
            .receipt-body {
                padding: 1rem 1.5rem !important;
            }
            
            .section {
                margin-bottom: 1rem !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            
            .section-title {
                font-size: 0.9rem !important;
                margin-bottom: 0.6rem !important;
                padding-bottom: 0.4rem !important;
            }
            
            .info-grid {
                gap: 0.8rem !important;
                grid-template-columns: repeat(2, 1fr) !important;
            }
            
            .info-label {
                font-size: 0.7rem !important;
            }
            
            .info-value {
                font-size: 0.8rem !important;
            }
            
            .amount-grid {
                padding: 1rem !important;
                gap: 0.6rem !important;
                margin: 0.8rem 0 !important;
            }
            
            .amount-item {
                padding: 0.3rem 0 !important;
            }
            
            .amount-label, .amount-value {
                font-size: 0.8rem !important;
            }
            
            .total-amount, .paid-amount, .balance-amount {
                font-size: 0.9rem !important;
            }
            
            .receipt-footer {
                padding: 1rem 1.5rem !important;
                margin-top: 0.8rem;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            
            .footer-info {
                gap: 0.3rem !important;
            }
            
            .footer-label {
                font-size: 0.7rem !important;
            }
            
            .footer-value {
                font-size: 0.75rem !important;
            }
            
            .action-buttons {
                display: none !important;
            }
            
            .watermark, .logo-watermark {
                display: block !important;
                opacity: 0.03 !important;
            }
            
            .watermark {
                font-size: 4rem !important;
            }
            
            .logo-watermark {
                max-width: 300px !important;
                max-height: 300px !important;
            }
            
            .notes-box {
                padding: 0.6rem !important;
                font-size: 0.75rem !important;
                margin-top: 0.5rem !important;
            }

            .status-badge {
                padding: 0.3rem 0.6rem !important;
                font-size: 0.7rem !important;
            }

            /* Ensure proper spacing for one page */
            html, body {
                height: auto !important;
                overflow: visible !important;
            }

            /* Maintain readable font sizes */
            .section-title i {
                font-size: 0.8rem !important;
            }

            /* Prevent page breaks */
            .receipt-header,
            .amount-grid,
            .receipt-footer {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Single column for very small screens */
            @media (max-width: 500px) {
                .info-grid {
                    grid-template-columns: 1fr !important;
                    gap: 0.6rem !important;
                }
            }
        }
        
        @media (max-width: 768px) {
            .receipt-container {
                margin: 0.5rem;
                border-radius: 4px;
            }
            
            .logo-title-container {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .amount-grid {
                grid-template-columns: 1fr;
            }
            
            .receipt-footer {
                flex-direction: column;
                gap: 0.75rem;
                text-align: center;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: center;
            }
            
            .receipt-header {
                padding: 1rem;
            }
            
            .receipt-body {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .receipt-title {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 1.1rem;
            }
            
            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php if(isset($booking_data)): ?>
    <!-- Logo Watermark -->
    <img src="<?php echo $logo_path; ?>" class="logo-watermark" alt="Watermark">
    <div class="watermark">OFFICIAL RECEIPT</div>
    
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="receipt-ref">Ref: <?= htmlspecialchars($ref_code) ?></div>
            
            <!-- Logo and Title Container -->
            <div class="logo-title-container">
                <img src="<?php echo $logo_path; ?>" class="header-logo" alt="Company Logo">
                <h1 class="receipt-title">OFFICIAL RECEIPT</h1>
            </div>
            
            <p class="receipt-subtitle">Facility Booking Payment</p>
        </div>
        
        <div class="receipt-body">
            <!-- Client & Facility Information -->
            <div class="section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Booking Information
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Client Name</span>
                        <span class="info-value"><?= htmlspecialchars($client_name) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Contact Information</span>
                        <span class="info-value"><?= htmlspecialchars($client_contact) ?> | <?= htmlspecialchars($client_email) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Address</span>
                        <span class="info-value"><?= htmlspecialchars($client_address) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Facility</span>
                        <span class="info-value"><?= htmlspecialchars($facility_name) ?> (<?= htmlspecialchars($category_name) ?>)</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Facility Code</span>
                        <span class="info-value"><?= htmlspecialchars($facility_code) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Booking Period</span>
                        <span class="info-value">
                            <?= date("M d, Y", strtotime($date_from)) ?>
                            <?php if($date_from != $date_to): ?>
                                 - <?= date("M d, Y", strtotime($date_to)) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Time Schedule</span>
                        <span class="info-value">
                            <?php if(!empty($time_from) && !empty($time_to)): ?>
                                <?= date("g:i A", strtotime($time_from)) ?> - <?= date("g:i A", strtotime($time_to)) ?>
                            <?php else: ?>
                                All Day
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Duration</span>
                        <span class="info-value"><?= $total_hours ?> hour(s)</span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Breakdown -->
            <div class="section">
                <h3 class="section-title">
                    <i class="fas fa-receipt"></i>
                    Payment Details
                </h3>
                <div class="amount-grid">
                    <div class="amount-item">
                        <span class="amount-label">Facility Rate (<?= $rate_type ?>)</span>
                        <span class="amount-value"><?= $rate_display ?></span>
                    </div>
                    <div class="amount-item total-amount">
                        <span class="amount-label">Total Amount Due</span>
                        <span class="amount-value">₱<?= number_format($total_amount, 2) ?></span>
                    </div>
                    <div class="amount-item paid-amount">
                        <span class="amount-label">Amount Paid</span>
                        <span class="amount-value">₱<?= number_format($paid_amount, 2) ?></span>
                    </div>
                    <div class="amount-item balance-amount">
                        <span class="amount-label">Remaining Balance</span>
                        <span class="amount-value">₱<?= number_format($balance, 2) ?></span>
                    </div>
                </div>
                
                <div style="margin-top: 0.75rem;">
                    <?php if($paid_amount >= $total_amount): ?>
                        <span class="status-badge status-paid">
                            <i class="fas fa-check-circle"></i>
                            Fully Paid
                        </span>
                    <?php elseif($paid_amount > 0): ?>
                        <span class="status-badge status-pending">
                            <i class="fas fa-clock"></i>
                            Partial Payment (₱<?= number_format($balance, 2) ?> balance)
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-pending">
                            <i class="fas fa-exclamation-circle"></i>
                            Payment Pending
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Additional Notes -->
            <div class="section">
                <h3 class="section-title">
                    <i class="fas fa-sticky-note"></i>
                    Notes
                </h3>
                <div class="notes-box">
                    <p><strong>Important:</strong> Please present this receipt when accessing the facility.</p>
                    <p>• Keep this receipt for your records</p>
                    <p>• Receipt is valid only for the specified booking period</p>
                    <p>• For inquiries, contact facility administration</p>
                </div>
            </div>
        </div>
        
        <div class="receipt-footer">
            <div class="footer-info">
                <span class="footer-label">Issued Date</span>
                <span class="footer-value"><?= date("F d, Y") ?></span>
            </div>
            <div class="footer-info">
                <span class="footer-label">Receipt ID</span>
                <span class="footer-value"><?= htmlspecialchars($ref_code) ?></span>
            </div>
            <div class="action-buttons">
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Print Receipt
                </button>
              
        </div>
    </div>
    
    <script>
        // Enhanced print optimization
        function optimizeForPrint() {
            // Adjust sizes for print while maintaining readability
            document.body.style.fontSize = '11pt';
            document.body.style.lineHeight = '1.3';
        }

        // Auto-print if specified in URL
        <?php if(isset($_GET['print']) && $_GET['print'] == '1'): ?>
            window.onload = function() {
                optimizeForPrint();
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        <?php endif; ?>
        
        // Optimize before printing
        window.addEventListener('beforeprint', function() {
            optimizeForPrint();
        });

        // Restore after printing
        window.addEventListener('afterprint', function() {
            document.body.style.fontSize = '';
            document.body.style.lineHeight = '';
        });
        
        // Add keyboard shortcut for printing (Ctrl+P)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                optimizeForPrint();
                setTimeout(function() {
                    window.print();
                }, 100);
            }
        });

        // Dynamic Go Back function
        function goBack() {
            window.location.href = 'http://localhost/capstoneProj/admin/?page=booking_list';
        }
    </script>
    
    <?php else: ?>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="error-title">Receipt Not Found</h2>
        <p class="error-message">The requested receipt could not be found or is no longer available.</p>
        <button class="btn btn-back" onclick="goBack()">
            <i class="fas fa-arrow-left"></i>
            Go Back
        </button>
    </div>
    <?php endif; ?>
</body>
</html>