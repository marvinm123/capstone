<?php
require_once('./config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    // Get booking data with client info
    $booking_qry = $conn->query("SELECT b.*, concat(c.lastname,', ', c.firstname,' ',c.middlename) as client 
                                 FROM `booking_list` b 
                                 INNER JOIN client_list c ON b.client_id = c.id 
                                 WHERE b.id = '{$_GET['id']}'");
    
    if($booking_qry->num_rows > 0){
        $booking_data = $booking_qry->fetch_assoc();
        
        // Get facility data with category info
        $facility_qry = $conn->query("SELECT f.*, c.name as category 
                                     FROM `facility_list` f 
                                     INNER JOIN category_list c ON f.category_id = c.id 
                                     WHERE f.id = '{$booking_data['facility_id']}'");
        
        $facility_data = [];
        if($facility_qry->num_rows > 0){
            $facility_data = $facility_qry->fetch_assoc();
        }
        
        // Extract booking data for easier use
        foreach($booking_data as $k => $v){
            $$k = $v;
        }
        
        // Extract facility data for easier use (don't overwrite existing variables)
        foreach($facility_data as $k => $v){
            if(!isset($$k))
                $$k = $v;
        }
        
        // Calculate duration and amount
        $facility_price = floatval($price ?? 0);
        
        if(empty($time_from) || empty($time_to)) {
            // All-day booking: calculate number of days
            $start_date = new DateTime($date_from);
            $end_date = new DateTime($date_to);
            $end_date->modify('+1 day'); // Include the end date
            $interval_days = $start_date->diff($end_date);
            $total_days = $interval_days->days;
            
            // For all-day bookings, use facility price as daily rate
            $total_amount = $facility_price * $total_days;
            $total_hours = $total_days * 24;
            $rate_display = "₱" . number_format($facility_price, 2) . " per day × " . $total_days . " day" . ($total_days > 1 ? "s" : "");
        } else {
            // Hourly booking: calculate actual hours
            $datetime_from = new DateTime($date_from . ' ' . $time_from);
            $datetime_to = new DateTime($date_to . ' ' . $time_to);
            $interval = $datetime_from->diff($datetime_to);
            
            $total_hours = ($interval->days * 24) + $interval->h + ($interval->i / 60);
            $total_hours = round($total_hours, 2);
            
            // Use facility price directly as hourly rate
            $rate_per_hour = $facility_price;
            $total_amount = $rate_per_hour * $total_hours;
            $rate_display = "₱" . number_format($rate_per_hour, 2) . " per hour × " . $total_hours . " hour(s)";
        }
        
        // Fallback if no price
        if($facility_price <= 0) {
            $total_amount = 0;
            $rate_display = "No rate set";
        }
    }
}
?>
<style>
    /* General modal footer display */
    #uni_modal .modal-footer {
        display: none;
    }

    /* Modal content styling */
    .modal-body-content {
        padding: 1.5rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: #374151;
        position: relative;
    }

    /* RECEIPT MODAL STYLES */
    .receipt-modal {
        display: none;
        position: fixed;
        z-index: 99999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
    }

    .receipt-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .receipt-modal-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .receipt-modal-title {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .receipt-modal-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }

    .receipt-modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .receipt-modal-body {
        padding: 0;
        max-height: calc(90vh - 120px);
        overflow-y: auto;
    }

    /* PRINT RECEIPT STYLES - Integrated from your receipt code */
    .print-receipt-section {
        background: white;
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

    .receipt-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
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

    .receipt-section {
        margin-bottom: 1.5rem;
    }

    .receipt-section-title {
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

    .receipt-section-title i {
        color: #3b82f6;
        font-size: 0.9rem;
    }

    .receipt-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .receipt-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .receipt-info-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .receipt-info-value {
        font-size: 0.9rem;
        color: #1e293b;
        font-weight: 500;
    }

    .receipt-amount-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 0.75rem;
        background: #f8fafc;
        padding: 1.25rem;
        border-radius: 6px;
        border-left: 3px solid #10b981;
    }

    .receipt-amount-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.4rem 0;
    }

    .receipt-amount-label {
        font-weight: 500;
        color: #475569;
        font-size: 0.9rem;
    }

    .receipt-amount-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
    }

    .receipt-total-amount {
        font-size: 1.1rem;
        color: #10b981;
        border-top: 1px solid #e2e8f0;
        padding-top: 0.75rem;
        margin-top: 0.5rem;
    }

    .receipt-paid-amount {
        color: #059669;
        font-size: 1rem;
    }

    .receipt-balance-amount {
        color: #dc2626;
        font-size: 1rem;
    }

    .receipt-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .receipt-status-paid {
        background: #dcfce7;
        color: #166534;
    }

    .receipt-status-pending {
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

    .receipt-footer-info {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .receipt-footer-label {
        font-size: 0.75rem;
        color: #64748b;
    }

    .receipt-footer-value {
        font-size: 0.8rem;
        color: #475569;
        font-weight: 500;
    }

    .receipt-print-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0.6rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.3s ease;
        font-size: 0.85rem;
    }

    .receipt-print-btn:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .receipt-notes-box {
        background: #fffbeb;
        padding: 0.75rem;
        border-radius: 6px;
        border-left: 3px solid #f59e0b;
        font-size: 0.8rem;
    }

    .receipt-notes-box p {
        margin-bottom: 0.4rem;
        color: #92400e;
    }

    /* FIXED PRINT STYLES */
    @media print {
        body * {
            visibility: hidden;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .receipt-modal,
        .receipt-modal * {
            visibility: visible;
        }
        
        .receipt-modal {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: auto !important;
            background: white !important;
            display: block !important;
            overflow: visible !important;
        }
        
        .receipt-modal-content {
            position: relative !important;
            top: 0 !important;
            left: 0 !important;
            transform: none !important;
            width: 100% !important;
            max-width: 100% !important;
            max-height: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .receipt-modal-header {
            display: none !important;
        }
        
        .print-receipt-section {
            box-shadow: none !important;
            border: none !important;
            margin: 0 !important;
            page-break-inside: avoid;
        }
        
        .receipt-print-btn {
            display: none !important;
        }
        
        /* Ensure colors print correctly */
        .receipt-header {
            background: #3b82f6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .receipt-amount-grid {
            background: #f8fafc !important;
            border-left: 3px solid #10b981 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .receipt-notes-box {
            background: #fffbeb !important;
            border-left: 3px solid #f59e0b !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .receipt-footer {
            background: #f1f5f9 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* Status badges */
        .receipt-status-paid {
            background: #dcfce7 !important;
            color: #166534 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .receipt-status-pending {
            background: #fef3c7 !important;
            color: #92400e !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    /* VIEW RECEIPT BUTTON */
    .btn-view-receipt {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-view-receipt:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    /* LOADING OVERLAY (Only for cancellation process) */
    .booking-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 99999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s ease;
    }

    .booking-loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* SPINNING LOADER */
    .booking-loader {
        width: 80px;
        height: 80px;
        position: relative;
        margin-bottom: 25px;
    }

    .booking-loader .circle {
        position: absolute;
        border: 4px solid transparent;
        border-radius: 50%;
    }

    .booking-loader .circle:nth-child(1) {
        width: 80px;
        height: 80px;
        border-top: 4px solid #007bff;
        animation: spin-fast 1s linear infinite;
    }

    .booking-loader .circle:nth-child(2) {
        width: 64px;
        height: 64px;
        top: 8px;
        left: 8px;
        border-top: 4px solid #0056b3;
        animation: spin-medium 1.5s linear infinite reverse;
    }

    .booking-loader .circle:nth-child(3) {
        width: 48px;
        height: 48px;
        top: 16px;
        left: 16px;
        border-top: 4px solid #28a745;
        animation: spin-slow 2s linear infinite;
    }

    /* KEYFRAME ANIMATIONS */
    @keyframes spin-fast {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes spin-medium {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(-360deg); }
    }

    @keyframes spin-slow {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .booking-loading-text {
        color: white;
        font-size: 1.3rem;
        font-weight: 600;
        text-align: center;
        margin-bottom: 8px;
        animation: pulse-text 1.5s ease-in-out infinite;
    }

    .booking-loading-subtext {
        color: rgba(255, 255, 255, 0.8);
        font-size: 1rem;
        text-align: center;
        animation: pulse-text 1.5s ease-in-out infinite;
        animation-delay: 0.3s;
    }

    @keyframes pulse-text {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }

    /* Section Styling */
    .detail-section {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .detail-section:last-child {
        margin-bottom: 0;
    }

    .section-header {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-header .fas {
        color: #2563eb;
        font-size: 1.5rem;
    }

    /* Special styling for pricing section */
    .pricing-section {
        border-left: 4px solid #10b981 !important;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    }

    .pricing-section .section-header .fas {
        color: #10b981;
    }

    /* Payment proof section styling */
    .payment-section {
        border-left: 4px solid #8b5cf6 !important;
        background: linear-gradient(135deg, #f3f4f6 0%, #f9fafb 100%);
    }

    .payment-section .section-header .fas {
        color: #8b5cf6;
    }

    /* Description List Styling */
    .detail-list dt {
        font-weight: 600;
        color: #1f2937;
        margin-top: 1rem;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .detail-list dd {
        margin-left: 0;
        padding-left: 1.5rem;
        font-size: 1rem;
        color: #374151;
        line-height: 1.4;
    }

    /* Total amount special styling */
    .total-amount {
        font-size: 1.5rem;
        color: #10b981;
        font-weight: 700;
    }

    .rate-breakdown {
        font-size: 0.9rem;
        color: #059669;
        margin-top: 0.5rem;
        font-weight: 500;
        padding-left: 1.5rem;
    }

    .duration-info {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 0.25rem;
        padding-left: 1.5rem;
    }

    /* Schedule Specific Styling */
    .schedule-display {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .schedule-display .date-info,
    .schedule-display .time-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
    }
    .schedule-display .time-info {
        color: #6b7280;
        font-size: 0.9rem;
    }
    .schedule-display .fas {
        color: #60a5fa;
        font-size: 1rem;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 1rem;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.025em;
        text-transform: uppercase;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .status-badge.pending {
        background-color: #e5e7eb;
        color: #6b7280;
    }

    .status-badge.confirmed {
        background-color: #60a5fa;
        color: white;
    }

    .status-badge.done {
        background-color: #10b981;
        color: white;
    }

    .status-badge.cancelled {
        background-color: #ef4444;
        color: white;
    }

    /* PAYMENT PROOF STYLING - SINGLE IMAGE */
    .payment-upload-container {
        margin-top: 1rem;
        padding: 1.5rem;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        background: #f9fafb;
        text-align: center;
        transition: all 0.3s ease;
    }

    .payment-upload-container.dragover {
        border-color: #8b5cf6;
        background: #f5f3ff;
    }

    .payment-upload-container.has-image {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .payment-upload-input {
        display: none;
    }

    .payment-upload-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: #8b5cf6;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-upload-button:hover {
        background: #7c3aed;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .payment-upload-button.replace {
        background: #f59e0b;
    }

    .payment-upload-button.replace:hover {
        background: #d97706;
    }

    .payment-upload-text {
        margin-top: 0.75rem;
        font-size: 0.9rem;
        color: #6b7280;
    }

    .payment-image-preview {
        margin-top: 1rem;
        text-align: left;
    }

    .payment-image-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .payment-image-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .payment-image-thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        transition: transform 0.3s ease;
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
    }

    .payment-image-thumbnail:hover {
        transform: scale(1.05);
    }

    .payment-image-thumbnail[src=""], .payment-image-thumbnail:not([src]) {
        background: #f3f4f6 url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIGZpbGw9Im5vbmUiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTMzIDhIMTNWNEgydjI2aDR2NEgzM3YtNFoiIGZpbGw9IiNEQ0RDRjciLz4KPHBhdGggZD0iIDE1IDIwVjM0IDMwIDMwSDI5VjIwWiIgZmlsbD0iI0E1QTVBNSIvPgo8L3N2Zz4=') no-repeat center;
        background-size: 40px;
    }

    .payment-image-info {
        flex: 1;
    }

    .payment-image-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .payment-image-size {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .payment-image-actions {
        display: flex;
        gap: 0.5rem;
    }

    .payment-action-btn {
        padding: 0.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .payment-action-btn.view {
        background: #3b82f6;
        color: white;
    }

    .payment-action-btn.delete {
        background: #ef4444;
        color: white;
    }

    .payment-action-btn:hover {
        transform: scale(1.1);
    }

    /* Upload Progress Styling */
    .upload-progress {
        width: 100%;
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        margin-top: 0.5rem;
        overflow: hidden;
    }

    .upload-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #8b5cf6, #a855f7);
        border-radius: 2px;
        transition: width 0.3s ease;
        width: 0%;
    }

    .upload-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.25rem;
        font-size: 0.85rem;
    }

    .upload-status-text {
        color: #6b7280;
    }

    .upload-status.uploading .upload-status-text {
        color: #8b5cf6;
    }

    .upload-status.success .upload-status-text {
        color: #10b981;
    }

    .upload-status.error .upload-status-text {
        color: #ef4444;
    }

    .cancel-upload-btn {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }

    .cancel-upload-btn:hover {
        background: #fee2e2;
        transform: scale(1.1);
    }

    /* Image Modal */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 99999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(5px);
    }

    .image-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 90%;
        max-height: 90%;
        border-radius: 8px;
        overflow: hidden;
    }

    .image-modal-image {
        width: 100%;
        height: auto;
        max-height: 80vh;
        object-fit: contain;
    }

    .image-modal-close {
        position: absolute;
        top: 15px;
        right: 25px;
        color: white;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
        z-index: 100000;
    }

    .image-modal-close:hover {
        color: #ccc;
    }

    .payment-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .payment-status.uploaded {
        background: #dcfce7;
        color: #166534;
    }

    .payment-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 2rem;
    }

    .btn-custom {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .btn-custom::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s ease;
    }

    .btn-custom:hover::before {
        left: 100%;
    }

    .btn-custom.btn-danger-outline {
        background-color: white;
        color: #ef4444;
        border: 2px solid #ef4444;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-custom.btn-danger-outline:hover {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        transform: translateY(-3px);
    }

    .btn-custom.btn-dark-outline {
        background-color: white;
        color: #1f2937;
        border: 2px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-custom.btn-dark-outline:hover {
        background: linear-gradient(135deg, #1f2937, #111827);
        color: white;
        box-shadow: 0 8px 25px rgba(31, 41, 55, 0.4);
        transform: translateY(-3px);
    }

    /* Loading state for content (only used during cancellation) */
    .modal-body-content.loading {
        pointer-events: none;
    }

    .modal-body-content.loading .detail-section {
        opacity: 0.5;
        transform: scale(0.98);
        transition: all 0.3s ease;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .modal-body-content {
            padding: 1rem;
        }
        .receipt-modal-content {
            width: 95%;
        }
        .receipt-header {
            padding: 1rem;
        }
        .receipt-body {
            padding: 1rem;
        }
        .receipt-info-grid {
            grid-template-columns: 1fr;
        }
        .receipt-amount-grid {
            grid-template-columns: 1fr;
        }
        .receipt-footer {
            flex-direction: column;
            gap: 0.75rem;
            text-align: center;
        }
        .section-header {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        .detail-list dt {
            font-size: 0.9rem;
            margin-top: 0.75rem;
        }
        .detail-list dd {
            font-size: 0.95rem;
            padding-left: 1rem;
        }
        .total-amount {
            font-size: 1.3rem;
        }
        .rate-breakdown {
            font-size: 0.85rem;
            padding-left: 1rem;
        }
        .duration-info {
            padding-left: 1rem;
        }
        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }
        .btn-custom {
            width: 100%;
        }
        .booking-loader {
            width: 60px;
            height: 60px;
        }
        .booking-loader .circle:nth-child(1) {
            width: 60px;
            height: 60px;
        }
        .booking-loader .circle:nth-child(2) {
            width: 45px;
            height: 45px;
            top: 7.5px;
            left: 7.5px;
        }
        .booking-loader .circle:nth-child(3) {
            width: 30px;
            height: 30px;
            top: 15px;
            left: 15px;
        }
        .booking-loading-text {
            font-size: 1.1rem;
        }
        .booking-loading-subtext {
            font-size: 0.9rem;
        }
        .payment-image-item {
            flex-direction: column;
            text-align: center;
        }
        .payment-image-thumbnail {
            width: 120px;
            height: 120px;
        }
    }
</style>

<!-- Loading overlay (Only shows during cancellation process) -->
<div class="booking-loading-overlay" id="bookingLoadingOverlay">
    <div class="booking-loader">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    <div class="booking-loading-text" id="loadingText">Cancelling Booking...</div>
    <div class="booking-loading-subtext" id="loadingSubtext">Please wait while we process your request</div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close">&times;</span>
    <div class="image-modal-content">
        <img class="image-modal-image" id="modalImage">
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="receipt-modal">
    <div class="receipt-modal-content">
        <div class="receipt-modal-header">
            <h3 class="receipt-modal-title">Official Receipt</h3>
            <button class="receipt-modal-close">&times;</button>
        </div>
        <div class="receipt-modal-body">
            <!-- Printable Receipt Section -->
            <div class="print-receipt-section">
                <div class="receipt-header">
                    <div class="receipt-ref">Ref: <?= isset($ref_code) ? htmlspecialchars($ref_code) : "N/A" ?></div>
                    <h1 class="receipt-title">OFFICIAL RECEIPT</h1>
                    <p class="receipt-subtitle">Facility Booking Payment</p>
                </div>
                
                <div class="receipt-body">
                    <!-- Client & Facility Information -->
                    <div class="receipt-section">
                        <h3 class="receipt-section-title">
                            <i class="fas fa-info-circle"></i>
                            Booking Information
                        </h3>
                        <div class="receipt-info-grid">
                            <div class="receipt-info-item">
                                <span class="receipt-info-label">Client Name</span>
                                <span class="receipt-info-value"><?= isset($client) ? htmlspecialchars($client) : "N/A" ?></span>
                            </div>
                            <div class="receipt-info-item">
                                <span class="receipt-info-label">Facility</span>
                                <span class="receipt-info-value"><?= isset($name) ? htmlspecialchars($name) : "N/A" ?> (<?= isset($category) ? htmlspecialchars($category) : "N/A" ?>)</span>
                            </div>
                            <div class="receipt-info-item">
                                <span class="receipt-info-label">Facility Code</span>
                                <span class="receipt-info-value"><?= isset($facility_code) ? htmlspecialchars($facility_code) : "N/A" ?></span>
                            </div>
                            <div class="receipt-info-item">
                                <span class="receipt-info-label">Booking Period</span>
                                <span class="receipt-info-value">
                                    <?php if(isset($date_from) && isset($date_to)): ?>
                                        <?= date("M d, Y", strtotime($date_from)) ?>
                                        <?php if($date_from != $date_to): ?>
                                             - <?= date("M d, Y", strtotime($date_to)) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="receipt-info-item">
                                <span class="receipt-info-label">Time Schedule</span>
                                <span class="receipt-info-value">
                                    <?php if(!empty($time_from) && !empty($time_to)): ?>
                                        <?= date("g:i A", strtotime($time_from)) ?> - <?= date("g:i A", strtotime($time_to)) ?>
                                    <?php else: ?>
                                        All Day
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="receipt-info-item">
                                <span class="receipt-info-label">Duration</span>
                                <span class="receipt-info-value"><?= isset($total_hours) ? $total_hours : "0" ?> hour(s)</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Breakdown -->
                    <div class="receipt-section">
                        <h3 class="receipt-section-title">
                            <i class="fas fa-receipt"></i>
                            Payment Details
                        </h3>
                        <div class="receipt-amount-grid">
                            <div class="receipt-amount-item">
                                <span class="receipt-amount-label">Facility Rate</span>
                                <span class="receipt-amount-value"><?= isset($rate_display) ? $rate_display : "No rate set" ?></span>
                            </div>
                            <div class="receipt-amount-item receipt-total-amount">
                                <span class="receipt-amount-label">Total Amount Due</span>
                                <span class="receipt-amount-value">₱<?= isset($total_amount) ? number_format($total_amount, 2) : "0.00" ?></span>
                            </div>
                            <?php 
                            $paid_amount = isset($paid_amount) ? floatval($paid_amount) : 0;
                            $balance = isset($total_amount) ? ($total_amount - $paid_amount) : 0;
                            ?>
                            <div class="receipt-amount-item receipt-paid-amount">
                                <span class="receipt-amount-label">Amount Paid</span>
                                <span class="receipt-amount-value">₱<?= number_format($paid_amount, 2) ?></span>
                            </div>
                            <div class="receipt-amount-item receipt-balance-amount">
                                <span class="receipt-amount-label">Remaining Balance</span>
                                <span class="receipt-amount-value">₱<?= number_format($balance, 2) ?></span>
                            </div>
                        </div>
                        
                        <div style="margin-top: 0.75rem;">
                            <?php if($paid_amount >= ($total_amount ?? 0)): ?>
                                <span class="receipt-status-badge receipt-status-paid">
                                    <i class="fas fa-check-circle"></i>
                                    Fully Paid
                                </span>
                            <?php elseif($paid_amount > 0): ?>
                                <span class="receipt-status-badge receipt-status-pending">
                                    <i class="fas fa-clock"></i>
                                    Partial Payment (₱<?= number_format($balance, 2) ?> balance)
                                </span>
                            <?php else: ?>
                                <span class="receipt-status-badge receipt-status-pending">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Payment Pending
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Additional Notes -->
                    <div class="receipt-section">
                        <h3 class="receipt-section-title">
                            <i class="fas fa-sticky-note"></i>
                            Notes
                        </h3>
                        <div class="receipt-notes-box">
                            <p><strong>Important:</strong> Please present this receipt when accessing the facility.</p>
                            <p>• Keep this receipt for your records</p>
                            <p>• Receipt is valid only for the specified booking period</p>
                            <p>• For inquiries, contact facility administration</p>
                        </div>
                    </div>
                </div>
                
                <div class="receipt-footer">
                    <div class="receipt-footer-info">
                        <span class="receipt-footer-label">Issued Date</span>
                        <span class="receipt-footer-value"><?= date("F d, Y") ?></span>
                    </div>
                    <div class="receipt-footer-info">
                        <span class="receipt-footer-label">Receipt ID</span>
                        <span class="receipt-footer-value"><?= isset($ref_code) ? htmlspecialchars($ref_code) : "N/A" ?></span>
                    </div>
                    <button class="receipt-print-btn" onclick="printReceipt()">
                        <i class="fas fa-print"></i>
                        Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-body-content" id="modalBodyContent">
    <!-- Original Booking Details Sections -->
    <div class="detail-section">
        <h3 class="section-header"><i class="fas fa-info-circle"></i> Facility Details</h3>
        <dl class="detail-list">
            <dt>Facility Code</dt>
            <dd><?= isset($facility_code) ? htmlspecialchars($facility_code) : "N/A" ?></dd>
            <dt>Name</dt>
            <dd><?= isset($name) ? htmlspecialchars($name) : "N/A" ?></dd>
            <dt>Category</dt>
            <dd><?= isset($category) ? htmlspecialchars($category) : "N/A" ?></dd>
        </dl>
    </div>

    <div class="detail-section">
        <h3 class="section-header"><i class="fas fa-clipboard-list"></i> Booking Details</h3>
        <dl class="detail-list">
            <dt>Reference Code</dt>
            <dd><?= isset($ref_code) ? htmlspecialchars($ref_code) : "N/A" ?></dd>
            <dt>Schedule</dt>
            <dd>
                <div class="schedule-display">
                    <span class="date-info">
                        <i class="fas fa-calendar"></i>
                        <?php
                            if(isset($date_from) && isset($date_to)){
                                if($date_from == $date_to){
                                    echo date("F d, Y", strtotime($date_from));
                                }else{
                                    echo date("F d, Y", strtotime($date_from))." - ".date("F d, Y", strtotime($date_to));
                                }
                            } else {
                                echo "N/A";
                            }
                        ?>
                    </span>
                    <span class="time-info">
                        <i class="fas fa-clock"></i>
                        <?php
                            if(!empty($time_from) && !empty($time_to)){
                                $time_from_formatted = date("g:i A", strtotime($time_from));
                                $time_to_formatted = date("g:i A", strtotime($time_to));
                                echo $time_from_formatted . " - " . $time_to_formatted;
                                if(isset($date_from) && isset($date_to) && $date_from != $date_to) {
                                    echo " (Daily)";
                                }
                            } else {
                                echo "All day";
                                if(isset($date_from) && isset($date_to) && $date_from != $date_to) {
                                    echo " (Multi-day)";
                                }
                            }
                            if(isset($total_hours)) {
                                echo " • " . $total_hours . " hours total";
                            }
                        ?>
                    </span>
                </div>
            </dd>
            <dt>Status</dt>
            <dd>
                <?php
                    if(isset($status)){
                        switch($status){
                            case 0:
                                echo "<span class='status-badge pending'><i class='fas fa-hourglass-half'></i> Pending</span>";
                                break;
                            case 1:
                                echo "<span class='status-badge confirmed'><i class='fas fa-check-circle'></i> Confirmed</span>";
                                break;
                            case 2:
                                echo "<span class='status-badge done'><i class='fas fa-calendar-check'></i> Done</span>";
                                break;
                            case 3:
                                echo "<span class='status-badge cancelled'><i class='fas fa-times-circle'></i> Cancelled</span>";
                                break;
                            default:
                                echo "<span class='status-badge pending'>Unknown</span>";
                                break;
                        }
                    } else {
                        echo "<span class='status-badge pending'>N/A</span>";
                    }
                ?>
            </dd>
        </dl>
    </div>

    <!-- PRICING SECTION -->
    <div class="detail-section pricing-section">
        <h3 class="section-header"><i class="fas fa-money-bill-wave"></i> Pricing & Duration</h3>
        <dl class="detail-list">
            <dt>Total Amount</dt>
            <dd>
                <span class="total-amount">₱<?= isset($total_amount) ? number_format($total_amount, 2) : "0.00" ?></span>
                <?php if(isset($rate_display) && $facility_price > 0): ?>
                <div class="rate-breakdown"><?= $rate_display ?></div>
                <?php endif; ?>
            </dd>
            <dt>Duration</dt>
            <dd>
                <?= isset($total_hours) ? $total_hours : "0" ?> Hour(s)
                <div class="duration-info">
                    <?php
                        if(isset($total_hours) && $total_hours >= 24) {
                            $days = floor($total_hours / 24);
                            $remaining_hours = $total_hours % 24;
                            echo $days . " day" . ($days > 1 ? "s" : "");
                            if($remaining_hours > 0) {
                                echo " + " . $remaining_hours . " hour" . ($remaining_hours != 1 ? "s" : "");
                            }
                        } else {
                            echo "Same day booking";
                        }
                    ?>
                </div>
            </dd>
        </dl>
    </div>

    <!-- SINGLE IMAGE PAYMENT PROOF SECTION -->
    <div class="detail-section payment-section">
        <h3 class="section-header"><i class="fas fa-receipt"></i> Payment Proof</h3>
        <dl class="detail-list">
            <dt>Payment Status</dt>
            <dd>
                <span id="paymentStatus" class="payment-status <?= !empty($payment_proof ?? '') ? 'uploaded' : 'pending' ?>">
                    <?php if(!empty($payment_proof ?? '')): ?>
                        <i class="fas fa-check-circle"></i> Proof Uploaded
                    <?php else: ?>
                        <i class="fas fa-clock"></i> Awaiting Payment Proof
                    <?php endif; ?>
                </span>
            </dd>
            
            <?php if(isset($status) && $status != 3): // Don't show upload for cancelled bookings ?>
            <dt>Upload Payment Proof</dt>
            <dd>
                <div class="payment-upload-container" id="paymentUploadContainer">
                    <input type="file" id="paymentProofInput" class="payment-upload-input" accept="image/*">
                    <button class="payment-upload-button" id="uploadButton" onclick="document.getElementById('paymentProofInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span id="uploadButtonText">Choose Image</span>
                    </button>
                    <div class="payment-upload-text">
                        <span id="uploadText">Drag and drop an image here or click to browse</span><br>
                        <small>Supported formats: JPG, PNG, GIF (Max 5MB per file)</small>
                    </div>
                </div>
                
                <div class="payment-image-preview" id="paymentImagePreview">
                    <!-- Current payment proof will be loaded here -->
                    <?php if(!empty($payment_proof ?? '')): 
                        // Ensure proper URL construction
                        $image_url = $payment_proof;
                        if(!str_starts_with($payment_proof, 'http')) {
                            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                            $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'];
                            $image_url = $base_url . '/' . ltrim($payment_proof, '/');
                        }
                    ?>
                        <div class="payment-image-item" id="currentPaymentProof">
                            <img src="<?= htmlspecialchars($image_url) ?>" alt="Payment Proof" class="payment-image-thumbnail" onclick="viewImage('<?= htmlspecialchars($image_url) ?>')" onerror="this.style.display='none'; this.nextElementSibling.querySelector('.payment-image-name').textContent='Image not found';">
                            <div class="payment-image-info">
                                <div class="payment-image-name">Current Payment Proof</div>
                                <div class="payment-image-size">Uploaded</div>
                            </div>
                            <div class="payment-image-actions">
                                <button class="payment-action-btn view" onclick="viewImage('<?= htmlspecialchars($image_url) ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="payment-action-btn delete" onclick="deletePaymentProof()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </dd>
            <?php endif; ?>
        </dl>
    </div>

   <div class="action-buttons">
    <!-- View Receipt Button - Only show for confirmed bookings -->
    <?php if(isset($status) && $status == 1): ?>
    <a href="receipt.php?id=<?= isset($id) ? $id : '' ?>" class="btn-custom btn-view-receipt" target="_blank">
        <i class="fas fa-receipt"></i> View Receipt
    </a>
    <?php endif; ?>
    
    <?php if(isset($status) && $status == 0): ?>
    <button class="btn-custom btn-danger-outline" type="button" id="cancel_booking">
        <i class="fas fa-ban"></i> Cancel Booking
    </button>
    <?php endif; ?>
    <button class="btn-custom btn-dark-outline" type="button" data-dismiss="modal">
        <i class="fa fa-times"></i> Close
    </button>
</div>

<script>
$(function(){
    let currentUploadXhr = null; // Track current upload request
    let hasPaymentProof = <?= !empty($payment_proof ?? '') ? 'true' : 'false' ?>;
    
    // FIXED PRINT FUNCTION
    window.printReceipt = function() {
        // Store original body overflow
        const originalBodyOverflow = document.body.style.overflow;
        
        // Show the receipt modal if it's not already visible
        if ($('#receiptModal').css('display') === 'none') {
            openReceiptModal();
        }
        
        // Wait a bit for the modal to be fully visible, then print
        setTimeout(function() {
            // Print the entire receipt modal
            window.print();
            
            // Restore body overflow after print dialog closes
            setTimeout(function() {
                document.body.style.overflow = originalBodyOverflow;
            }, 500);
        }, 500);
    };

    // RECEIPT MODAL FUNCTIONS
    window.openReceiptModal = function() {
        $('#receiptModal').fadeIn(300);
        // Prevent body scrolling when modal is open
        document.body.style.overflow = 'hidden';
    };

    window.closeReceiptModal = function() {
        $('#receiptModal').fadeOut(300);
        // Restore body scrolling
        document.body.style.overflow = '';
    };

    // Close receipt modal when clicking close button or outside
    $('.receipt-modal-close, #receiptModal').on('click', function(e) {
        if (e.target === this || $(e.target).hasClass('receipt-modal-close')) {
            closeReceiptModal();
        }
    });

    // Prevent modal close when clicking on receipt content
    $('.receipt-modal-content').on('click', function(e) {
        e.stopPropagation();
    });

    // View Receipt button click event
    $('#view_receipt').click(function(){
        openReceiptModal();
    });

    // LOADING FUNCTIONS (Only used for cancellation process)
    function showBookingLoading() {
        console.log('Showing booking loading overlay...');
        $('#bookingLoadingOverlay').addClass('active');
        $('#modalBodyContent').addClass('loading');
    }

    function hideBookingLoading() {
        console.log('Hiding booking loading overlay...');
        $('#bookingLoadingOverlay').removeClass('active');
        $('#modalBodyContent').removeClass('loading');
    }

    function updateLoadingText(mainText, subText = '') {
        $('#loadingText').text(mainText);
        $('#loadingSubtext').text(subText);
    }

    // UPDATE UI BASED ON PAYMENT PROOF STATUS
    function updatePaymentUI(hasProof) {
        hasPaymentProof = hasProof;
        
        if (hasProof) {
            $('#paymentStatus').removeClass('pending').addClass('uploaded')
                .html('<i class="fas fa-check-circle"></i> Proof Uploaded');
            $('#paymentUploadContainer').addClass('has-image');
            $('#uploadButtonText').text('Replace Image');
            $('#uploadText').text('Upload a new image to replace current one');
        } else {
            $('#paymentStatus').removeClass('uploaded').addClass('pending')
                .html('<i class="fas fa-clock"></i> Awaiting Payment Proof');
            $('#paymentUploadContainer').removeClass('has-image');
            $('#uploadButtonText').text('Choose Image');
            $('#uploadText').text('Drag and drop an image here or click to browse');
        }
    }

    // SINGLE FILE UPLOAD HANDLER
    function handleSingleFileUpload(file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert_toast('Please select only image files.', 'error');
            return;
        }
        
        // Validate file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            alert_toast('File size must be less than 5MB.', 'error');
            return;
        }

        // If there's already an image, ask for confirmation to replace
        if (hasPaymentProof) {
            if (!confirm('This will replace your current payment proof. Are you sure?')) {
                return;
            }
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            // Create or update the preview
            const previewContainer = $('#paymentImagePreview');
            const existingItem = $('#currentPaymentProof');
            
            let imageItem;
            if (existingItem.length > 0) {
                // Update existing item
                imageItem = existingItem;
                imageItem.find('.payment-image-thumbnail').attr('src', e.target.result);
                imageItem.find('.payment-image-name').text(file.name);
                imageItem.find('.payment-image-size').text('Uploading...');
            } else {
                // Create new item
                imageItem = $(`
                    <div class="payment-image-item" id="currentPaymentProof">
                        <img src="${e.target.result}" alt="${file.name}" class="payment-image-thumbnail" onclick="viewImage('${e.target.result}')">
                        <div class="payment-image-info">
                            <div class="payment-image-name">${file.name}</div>
                            <div class="payment-image-size">Uploading...</div>
                            <div class="upload-progress">
                                <div class="upload-progress-bar" id="uploadProgressBar"></div>
                            </div>
                            <div class="upload-status uploading" id="uploadStatus">
                                <span class="upload-status-text">Uploading...</span>
                                <button class="cancel-upload-btn" onclick="cancelCurrentUpload()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </div>
                        <div class="payment-image-actions">
                            <button class="payment-action-btn view" onclick="viewImage('${e.target.result}')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="payment-action-btn delete" onclick="cancelCurrentUpload()" id="actionButton">
                                <i class="fas fa-ban"></i>
                            </button>
                        </div>
                    </div>
                `);
                previewContainer.html(imageItem);
            }
            
            // Add progress bar if it doesn't exist
            if (imageItem.find('.upload-progress').length === 0) {
                imageItem.find('.payment-image-info').append(`
                    <div class="upload-progress">
                        <div class="upload-progress-bar" id="uploadProgressBar"></div>
                    </div>
                    <div class="upload-status uploading" id="uploadStatus">
                        <span class="upload-status-text">Uploading...</span>
                        <button class="cancel-upload-btn" onclick="cancelCurrentUpload()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                `);
            }
            
            // Upload the image
            uploadSingleImageToServer(file);
        };
        reader.readAsDataURL(file);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function uploadSingleImageToServer(file) {
        const formData = new FormData();
        formData.append('payment_proof', file);
        formData.append('booking_id', '<?= isset($id) ? $id : "" ?>');
        formData.append('action', 'upload_payment_proof');
        
        // Create XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();
        currentUploadXhr = xhr;
        
        // Progress tracking
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentage = (e.loaded / e.total) * 100;
                $('#uploadProgressBar').css('width', percentage + '%');
                
                if (percentage < 100) {
                    $('#uploadStatus .upload-status-text').text(`Uploading ${Math.round(percentage)}%...`);
                } else {
                    $('#uploadStatus .upload-status-text').text('Processing...');
                }
            }
        });
        
        // Handle completion
        xhr.addEventListener('load', function() {
            currentUploadXhr = null;
            
            try {
                const resp = JSON.parse(xhr.responseText);
                if (resp.status === 'success') {
                    // Success - update UI
                    const newImageUrl = resp.image_url || resp.payment_proof_url;
                    
                    $('#uploadStatus').removeClass('uploading').addClass('success');
                    $('#uploadStatus .upload-status-text').text('Upload complete!');
                    $('#uploadStatus .cancel-upload-btn').remove();
                    
                    // Update the image source with the new URL and handle loading
                    const $thumbnail = $('#currentPaymentProof .payment-image-thumbnail');
                    $thumbnail.attr('src', newImageUrl);
                    $thumbnail.attr('onclick', `viewImage('${newImageUrl}')`);
                    
                    // Handle image load errors
                    $thumbnail.on('error', function() {
                        console.log('Image failed to load:', newImageUrl);
                        $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIGZpbGw9Im5vbmUiIHZpZXdCb3g9IjAgMCA4MCA4MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjZjNmNGY2IiBzdHJva2U9IiNlNWU3ZWIiIHN0cm9rZS13aWR0aD0iMiIvPgo8cGF0aCBkPSJtMjAgMjBoNDB2NDBIMjBWMjBabTgtOHY4aDI0di04SDI4WiIgZmlsbD0iI2Q5ZGNlMCIvPgo8L3N2Zz4=');
                        $('#currentPaymentProof .payment-image-name').text('Image not available');
                    });
                    
                    // Test if image loads successfully
                    $thumbnail.on('load', function() {
                        console.log('Image loaded successfully:', newImageUrl);
                        $('#currentPaymentProof .payment-image-name').text('Payment Proof');
                        $('#currentPaymentProof .payment-image-size').text(formatFileSize(file.size));
                    });
                    
                    // Update action buttons
                    $('#actionButton').removeClass('payment-action-btn delete')
                        .addClass('payment-action-btn delete')
                        .attr('onclick', 'deletePaymentProof()')
                        .html('<i class="fas fa-trash"></i>');
                    
                    $('#currentPaymentProof .view').attr('onclick', `viewImage('${newImageUrl}')`);
                    
                    updatePaymentUI(true);
                    alert_toast('Payment proof uploaded successfully!', 'success');
                    
                    // Hide upload status after 3 seconds
                    setTimeout(() => {
                        $('#uploadStatus').fadeOut();
                        $('.upload-progress').fadeOut();
                    }, 3000);
                    
                } else {
                    throw new Error(resp.message || 'Upload failed');
                }
            } catch (e) {
                $('#uploadStatus').removeClass('uploading').addClass('error');
                $('#uploadStatus .upload-status-text').text('Upload failed!');
                alert_toast('Error uploading payment proof: ' + e.message, 'error');
                
                // Remove the failed upload item after 3 seconds if it was a new upload
                if (!hasPaymentProof) {
                    setTimeout(() => {
                        $('#currentPaymentProof').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 3000);
                }
            }
        });
        
        // Handle errors
        xhr.addEventListener('error', function() {
            currentUploadXhr = null;
            $('#uploadStatus').removeClass('uploading').addClass('error');
            $('#uploadStatus .upload-status-text').text('Upload failed!');
            alert_toast('Error uploading payment proof.', 'error');
        });
        
        // Handle abort
        xhr.addEventListener('abort', function() {
            currentUploadXhr = null;
            console.log('Upload cancelled');
        });
        
        // Start upload
        xhr.open('POST', _base_url_ + "classes/Master.php?f=upload_payment_proof");
        xhr.send(formData);
    }

    // CANCEL CURRENT UPLOAD
    window.cancelCurrentUpload = function() {
        if (currentUploadXhr) {
            currentUploadXhr.abort();
            currentUploadXhr = null;
            alert_toast('Upload cancelled.', 'info');
        }
        
        // If this was a new upload (no existing proof), remove the preview
        if (!hasPaymentProof) {
            $('#currentPaymentProof').fadeOut(300, function() {
                $(this).remove();
            });
        } else {
            // If replacing existing, reload the page to restore original state
            setTimeout(() => {
                location.reload();
            }, 500);
        }
    };

    // DELETE PAYMENT PROOF
    window.deletePaymentProof = function() {
        if (!confirm('Are you sure you want to delete the current payment proof?')) {
            return;
        }
        
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=delete_payment_proof",
            method: "POST",
            data: {
                booking_id: '<?= isset($id) ? $id : "" ?>',
                action: 'delete_payment_proof'
            },
            dataType: "json",
            success: function(resp) {
                if (resp.status === 'success') {
                    // Remove the image preview
                    $('#currentPaymentProof').fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    updatePaymentUI(false);
                    alert_toast('Payment proof deleted successfully!', 'success');
                } else {
                    alert_toast('Error deleting payment proof: ' + (resp.message || 'Unknown error'), 'error');
                }
            },
            error: function() {
                alert_toast('Error deleting payment proof.', 'error');
            }
        });
    };

    // File input change handler
    $('#paymentProofInput').on('change', function(e) {
        if (e.target.files.length > 0) {
            handleSingleFileUpload(e.target.files[0]);
        }
        // Reset input value so same file can be selected again
        e.target.value = '';
    });

    // Drag and drop functionality
    const uploadContainer = $('#paymentUploadContainer');
    
    uploadContainer.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });
    
    uploadContainer.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });
    
    uploadContainer.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            // Only handle the first file for single image upload
            handleSingleFileUpload(files[0]);
        }
    });

    // Image viewing modal functions
    window.viewImage = function(imageSrc) {
        $('#modalImage').attr('src', imageSrc);
        $('#imageModal').fadeIn(300);
    };

    // Close image modal
    $('.image-modal-close, #imageModal').on('click', function(e) {
        if (e.target === this) {
            $('#imageModal').fadeOut(300);
        }
    });

    // Prevent modal close when clicking on image
    $('.image-modal-image').on('click', function(e) {
        e.stopPropagation();
    });

    // CANCEL BOOKING CLICK EVENT
    $('#cancel_booking').click(function(){
        console.log('Cancel booking clicked');
        _conf("Are you sure to cancel your facility booking [Ref. Code: <b><?= isset($ref_code) ? htmlspecialchars($ref_code) : "" ?></b>]?", "cancel_booking_action", ["<?= isset($id) ? $id : "" ?>"])
    });

    // ENHANCED CANCEL BOOKING ACTION
    window.cancel_booking_action = function($id) {
        console.log('Starting cancellation process...');
        
        // IMMEDIATELY CLOSE ALL CONFIRMATION DIALOGS
        setTimeout(function() {
            // Close SweetAlert2 dialogs
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            // Close SweetAlert1 dialogs
            if (typeof swal !== 'undefined') {
                swal.close();
            }
            // Close custom confirmation dialogs
            $('.swal2-container, .swal-overlay, .confirm-dialog').remove();
            $('.modal.fade.show').not('#uni_modal').modal('hide');
            
            // Show the loading overlay (now visible without confirmation blocking)
            showBookingLoading();
            updateLoadingText('Cancelling Booking...', 'Please wait while we process your request');
        }, 100);
        
        // AJAX request
        setTimeout(function() {
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=update_booking_status",
                method: "POST",
                data: {id: $id, status: 3},
                dataType: "json",
                error: function(err) {
                    console.log('AJAX Error:', err);
                    hideBookingLoading();
                    alert_toast("An error occurred while canceling the booking.", 'error');
                },
                success: function(resp) {
                    console.log('AJAX Success:', resp);
                    if (typeof resp === 'object' && resp.status === 'success') {
                        // Update loading text to show success
                        updateLoadingText('Booking Cancelled Successfully!', 'Refreshing page...');
                        
                        // Show success state briefly
                        setTimeout(function() {
                            alert_toast("Booking successfully cancelled.", 'success');
                            
                            // Reload page after showing success
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }, 1500);
                    } else {
                        hideBookingLoading();
                        alert_toast("An error occurred while canceling the booking.", 'error');
                    }
                }
            });
        }, 300);
    };

    // Initialize UI based on current payment proof status
    updatePaymentUI(hasPaymentProof);

    console.log('Booking modal with receipt functionality loaded successfully!');
});
</script>