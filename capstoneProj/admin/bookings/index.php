<?php
// PHPMailer configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';
require 'includes/PHPMailer/src/Exception.php';

// Initialize message variables
$success_message = '';
$error_message = '';

// Enhanced email sending function (removed date range functionality)
function sendBookingNotification($email, $refCode, $facilityName, $status, $clientName, $bookingCreatedDate = '', $timeRange = '')
{
    $mail = new PHPMailer(true);

    try {
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'marvinbaylosis03@gmail.com';
        $mail->Password = 'tetiodszkbcuhoqe';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('marvinbaylosis03@gmail.com', 'Facility Booking System');
        $mail->addAddress($email);

        // Set email content based on status
        $statusText = '';
        $statusColor = '';
        $emailMessage = '';
        switch ($status) {
            case 1:
                $statusText = 'Confirmed';
                $statusColor = '#22c55e';
                $emailMessage = 'Great news! Your facility booking has been confirmed. Please make note of your booking details below.';
                break;
            case 2:
                $statusText = 'Completed';
                $statusColor = '#3b82f6';
                $emailMessage = 'Your facility booking has been successfully completed. Thank you for using our services!';
                break;
            case 3:
                $statusText = 'Cancelled';
                $statusColor = '#ef4444';
                $emailMessage = 'We regret to inform you that your facility booking has been cancelled. If this was unexpected, please contact our support team for assistance.';
                break;
            default:
                $statusText = 'Updated';
                $statusColor = '#6b7280';
                $emailMessage = 'Your booking status has been updated. Please review the details below.';
        }

        $mail->isHTML(true);
        $mail->Subject = "Booking {$statusText} - {$refCode}";
        $mail->Body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: {$statusColor}; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .details, .booking-created {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            color: white;
            background-color: {$statusColor};
        }
        .automated-notice {
            font-size: 0.8rem;
            color: #6b7280;
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Booking Status Update</h2>
        </div>
        <div class='content'>
            <p>Dear {$clientName},</p>
            <p>{$emailMessage}</p>
            
            <div class='details'>
                <h3>Booking Details:</h3>
                <p><strong>Reference Code:</strong> {$refCode}</p>
                <p><strong>Facility:</strong> {$facilityName}</p>
                <p><strong>Status:</strong> <span class='status-badge'>{$statusText}</span></p>
                
                " . (!empty($timeRange) ? "
                <div class='booking-detail'>
                    <strong>Time Slot:</strong> {$timeRange}
                </div>
                " : "") . "
                
                " . (!empty($bookingCreatedDate) ? "
                <div class='booking-created'>
                    <strong>Booking Created:</strong> {$bookingCreatedDate}
                </div>
                " : "") . "
            </div>
            
            " . ($status == 3 ? "
            <div style='background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 15px 0;'>
                <p><strong>Cancellation Notice:</strong></p>
                <p>If you have any questions about this cancellation or need to make a new booking, please contact our support team.</p>
                <p><strong>Contact Information:</strong><br>
                Email: marvinbaylosis03@gmail.com<br>
                Phone: (123) 456-7890</p>
            </div>
            " : "") . "
            
            <p>Thank you for using our facility booking system.</p>
            <p>Best regards,<br>Sports Complex Management Team</p>
            
            <div class='automated-notice'>
                <p>This is an automated message. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Handle status update and email notification
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['new_status'])) {
    // Start loading animation
    $actionType = '';
    switch ($_POST['new_status']) {
        case 1: $actionType = 'confirming'; break;
        case 2: $actionType = 'completing'; break;
        case 3: $actionType = 'cancelling'; break;
        default: $actionType = 'processing';
    }
    
    echo '<div id="loading-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.95);z-index:9999;display:flex;justify-content:center;align-items:center;flex-direction:column;">
            <div class="loading-spinner" style="width:50px;height:50px;border:5px solid #f3f3f3;border-top:5px solid #3498db;border-radius:50%;animation:spin 1s linear infinite"></div>
            <p style="margin-top:20px;font-size:18px;color:#333;">' . ucfirst($actionType) . ' booking...</p>
            <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>
          </div>';
    ob_flush();
    flush();
    
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];

    $update_stmt = $conn->prepare("UPDATE booking_list SET status = ? WHERE id = ?");
    $update_stmt->bind_param("ii", $new_status, $booking_id);

    if ($update_stmt->execute()) {
        $booking_query = $conn->prepare("
            SELECT b.ref_code, b.date_created, b.time_from, b.time_to,
                   concat(c.lastname, ', ', c.firstname, ' ', COALESCE(c.middlename, '')) as client_name,
                   c.email as client_email,
                   f.name as facility_name
            FROM booking_list b 
            INNER JOIN client_list c ON b.client_id = c.id 
            INNER JOIN facility_list f ON b.facility_id = f.id 
            WHERE b.id = ?
        ");
        $booking_query->bind_param("i", $booking_id);
        $booking_query->execute();
        $booking_result = $booking_query->get_result();

        if ($booking_data = $booking_result->fetch_assoc()) {
            $email_sent = false;

            // Prepare time range
            $timeRange = '';
            if (!empty($booking_data['time_from']) && !empty($booking_data['time_to'])) {
                $time_from = date("g:i A", strtotime($booking_data['time_from']));
                $time_to = date("g:i A", strtotime($booking_data['time_to']));
                $timeRange = $time_from . " - " . $time_to;
            }

            // Prepare created date
            $bookingCreatedDate = '';
            if (!empty($booking_data['date_created'])) {
                $bookingCreatedDate = date("M j, Y g:i A", strtotime($booking_data['date_created']));
            }

            if (!empty($booking_data['client_email'])) {
                $email_sent = sendBookingNotification(
                    $booking_data['client_email'],
                    $booking_data['ref_code'],
                    $booking_data['facility_name'],
                    $new_status,
                    $booking_data['client_name'],
                    $bookingCreatedDate,
                    $timeRange
                );
            }

            switch ($new_status) {
                case 1:
                    $success_message = $email_sent
                        ? "Booking confirmed successfully! ✅ Confirmation email sent to client."
                        : "Booking confirmed, but email notification might still be sending due to a poor connection. ⚠️";
                    break;
                case 2:
                    $success_message = $email_sent
                        ? "Booking marked as completed! ✅ Completion email sent to client."
                        : "Booking marked as completed successfully! ✅";
                    break;
                case 3:
                    $success_message = $email_sent
                        ? "Booking cancelled successfully! ❌ Cancellation email sent to client."
                        : "Booking cancelled, but email notification might still be sending due to a poor connection. ⚠️";
                    break;
                default:
                    $success_message = "Booking status updated successfully! ✅";
            }

            if (!$email_sent && !empty($booking_data['client_email'])) {
                $success_message .= " (Email delivery failed - please check email configuration)";
            } elseif (empty($booking_data['client_email'])) {
                $success_message .= " (No email address found for client)";
            }
        }
    } else {
        $error_message = "Failed to update booking status. Please try again.";
    }
    
    // Add JavaScript to remove loading overlay
    echo '<script>document.getElementById("loading-overlay").remove();</script>';
}
?>
<?php
// Display success/error messages with beautiful notifications
if (!empty($success_message)) {
    echo "<div class='notification-modal-overlay' id='notification-overlay'>
            <div class='notification-modal success-modal'>
                <div class='notification-modal-header'>
                    <div class='notification-modal-icon'>
                        <i class='fas fa-check-circle'></i>
                    </div>
                    <h3 class='notification-modal-title'>Success</h3>
                </div>
                <div class='notification-modal-body'>
                    <div class='notification-modal-content'>
                        <div class='notification-message'>" . $success_message . "</div>
                        <div class='system-notice'>
                            Catalunan Grande Sports Complex
                        </div>
                    </div>
                </div>
                <div class='notification-modal-footer'>
                    <button class='notification-modal-btn notification-modal-btn-close' onclick='closeNotificationModal()'>
                        <i class='fas fa-times'></i> Close
                    </button>
                </div>
            </div>
          </div>
          <script>
            // Show modal immediately
            document.getElementById('notification-overlay').classList.add('active');
            
            function closeNotificationModal() {
                const overlay = document.getElementById('notification-overlay');
                overlay.classList.remove('active');
                setTimeout(function() {
                    overlay.remove();
                }, 300);
            }
          </script>";
}

if (!empty($error_message)) {
    echo "<div class='notification-modal-overlay' id='notification-overlay'>
            <div class='notification-modal error-modal'>
                <div class='notification-modal-header'>
                    <div class='notification-modal-icon'>
                        <i class='fas fa-exclamation-circle'></i>
                    </div>
                    <h3 class='notification-modal-title'>Error</h3>
                </div>
                <div class='notification-modal-body'>
                    <div class='notification-modal-content'>
                        <div class='notification-message'>" . $error_message . "</div>
                        <div class='system-notice'>
                            Catalunan Grande Sports Complex
                        </div>
                    </div>
                </div>
                <div class='notification-modal-footer'>
                    <button class='notification-modal-btn notification-modal-btn-close' onclick='closeNotificationModal()'>
                        <i class='fas fa-times'></i> Close
                    </button>
                </div>
            </div>
          </div>
          <script>
            // Show modal immediately
            document.getElementById('notification-overlay').classList.add('active');
            
            function closeNotificationModal() {
                const overlay = document.getElementById('notification-overlay');
                overlay.classList.remove('active');
                setTimeout(function() {
                    overlay.remove();
                }, 300);
            }
          </script>";
}
?>
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap');
    
    /* Cleaned CSS for Booking System */
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #ffffff;
        color: #1e293b;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .card-header h3.card-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0;
    }

    .btn-flat.btn-primary {
        background: linear-gradient(90deg, #2563eb, #1d4ed8);
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-flat.btn-default {
        background-color: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        padding: 0.55rem 1.1rem;
        border-radius: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .card.card-outline.card-primary.rounded-0.shadow {
        border-radius: 0.75rem !important;
        background-color: #ffffff;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        padding: 1.25rem;
        border: none;
    }

    /* Table Container with Scrollbar */
    .container-fluid {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding: 0 !important;
    }

    .table {
        border-collapse: separate !important;
        border-spacing: 0 1rem !important;
        width: 100%;
        min-width: 1200px; /* Ensures horizontal scroll on smaller screens */
        margin-bottom: 0;
        table-layout: fixed;
    }
    
    /* Fix column widths */
    .table colgroup col {
        width: auto !important;
    }
    
    .table colgroup col:nth-child(1) { width: 5% !important; }
    .table colgroup col:nth-child(2) { width: 15% !important; }
    .table colgroup col:nth-child(3) { width: 15% !important; }
    .table colgroup col:nth-child(4) { width: 15% !important; }
    .table colgroup col:nth-child(5) { width: 20% !important; }
    .table colgroup col:nth-child(6) { width: 15% !important; }
    .table colgroup col:nth-child(7) { width: 15% !important; }
    
    /* Fix DataTables wrapper */
    .dataTables_wrapper {
        overflow-x: visible;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 0 0.5rem;
    }
    
    /* Ensure table scrolls properly */
    .dataTables_scroll {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    
    .dataTables_scrollBody {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Hide the bottom scrollbar on DataTables */
    .dataTables_scrollFoot {
        display: none !important;
    }
    
    .dataTables_wrapper .dataTables_scroll .dataTables_scrollFoot {
        display: none !important;
    }
    
    .dataTables_scrollHead {
        overflow: hidden !important;
    }
    
    /* Hide any extra scrollbars */
    .dataTables_wrapper .row:last-child {
        overflow: visible !important;
    }
    
    .card-body {
        overflow: visible !important;
    }
    
    /* Only show scrollbar on the table body */
    .container-fluid {
        overflow: visible !important;
    }
    
    /* Style the main scrollbar if needed */
    .dataTables_scrollBody::-webkit-scrollbar {
        height: 8px;
    }
    
    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Remove horizontal scrollbar from bottom area */
    .dataTables_wrapper > .row:last-child,
    .dataTables_info,
    .dataTables_paginate {
        overflow-x: visible !important;
    }

    .table thead tr th {
        background-color: #eef2ff;
        color: #1e293b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 12px 16px;
        border: none !important;
        letter-spacing: 0.03em;
        vertical-align: middle;
    }

    .table tbody tr {
        background-color: #ffffff;
        border-radius: 0.75rem;
    }

    .table tbody tr td {
        padding: 14px 18px !important;
        vertical-align: middle !important;
        border: none !important;
        color: #64748b;
        background-color: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border-radius: 0.75rem;
    }

    .truncate-1 {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0;
    }

    .badge {
        display: inline-block;
        padding: 0.35em 0.75em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        vertical-align: middle;
    }

    .badge-success {
        background-color: #22c55e;
        color: #fff;
    }

    .badge-danger {
        background-color: #ef4444;
        color: #fff;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: #fff;
    }

    .badge-primary {
        background-color: #2563eb;
        color: #fff;
    }

    .badge-warning {
        background-color: #facc15;
        color: #1e293b;
    }

    .dropdown-menu a.dropdown-item {
        font-size: 0.9rem;
        padding: 0.5rem 1.2rem;
        color: #1e293b;
        border-radius: 0.5rem;
        text-decoration: none;
        display: block;
    }

    .ml-2 {
        margin-left: 0.5rem;
    }

    /* Enhanced Notification Styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 350px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        overflow: hidden;
        transform: translateX(400px);
        transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        opacity: 0;
    }

    .notification.show {
        transform: translateX(0);
        opacity: 1;
    }

    .notification-content {
        display: flex;
        align-items: center;
        padding: 16px;
    }

    .notification-icon {
        font-size: 24px;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .success-notification {
        border-left: 4px solid #22c55e;
    }

    .success-notification .notification-icon {
        color: #22c55e;
    }

    .error-notification {
        border-left: 4px solid #ef4444;
    }

    .error-notification .notification-icon {
        color: #ef4444;
    }

    .notification-message {
        flex: 1;
        font-weight: 500;
        line-height: 1.4;
    }

    .notification-progress {
        height: 4px;
        width: 100%;
        background: rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .notification-progress::after {
        content: '';
        position: absolute;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    .success-notification .notification-progress::after {
        background: #22c55e;
        animation: progress 5s linear forwards;
    }

    .error-notification .notification-progress::after {
        background: #ef4444;
        animation: progress 5s linear forwards;
    }

    @keyframes progress {
        from { transform: translateX(-100%); }
        to { transform: translateX(0); }
    }

    .btn {
        padding: 0.5rem 2rem;
        border-radius: 0.75rem;
        border: none;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(90deg, #2563eb, #1d4ed8);
        color: white;
    }

    .btn-success {
        background: linear-gradient(90deg, #22c55e, #16a34a);
        color: white;
    }

    .btn-danger {
        background: linear-gradient(90deg, #ef4444, #dc2626);
        color: white;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .btn-flat {
        box-shadow: none;
    }

    .btn-flat:hover {
        transform: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Form styling */
    form {
        display: inline-block;
    }

    /* Action buttons styling */
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    /* Hover effects for table rows */
    .table tbody tr:hover td {
        background-color: #f8fafc !important;
        transition: background-color 0.2s ease;
    }

    /* Loading states */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    #uni_modal .modal-footer {
        display: none;
    }

    .booking-details dl {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 0;
    }

    .booking-details dt,
    .booking-details dd {
        width: 50%;
        margin-bottom: 0.75rem;
    }

    .booking-details dt {
        font-weight: 600;
        color: #374151;
    }

    .booking-details dd {
        margin-left: 0;
        padding-left: 1rem;
        color: #4b5563;
    }

    fieldset.border-bottom {
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    legend.h5.text-muted {
        font-weight: 700;
        font-size: 1.25rem;
        color: #6b7280;
        padding-bottom: 0.25rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #d1d5db;
        width: auto;
    }

    .badge {
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.4em 1em;
        border-radius: 9999px;
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .btn-group-bottom {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }

    .btn-flat.bg-gradient-primary {
        background: linear-gradient(90deg, #3b82f6, #2563eb);
        color: #fff;
    }

    .btn-flat.bg-gradient-primary:hover {
        background: linear-gradient(90deg, #2563eb, #1e40af);
    }

    .btn-flat.bg-gradient-success {
        background: linear-gradient(90deg, #22c55e, #16a34a);
        color: #fff;
    }

    .btn-flat.bg-gradient-success:hover {
        background: linear-gradient(90deg, #16a34a, #15803d);
    }

    .btn-flat.bg-gradient-danger {
        background: linear-gradient(90deg, #ef4444, #b91c1c);
        color: #fff;
    }

    .btn-flat.bg-gradient-danger:hover {
        background: linear-gradient(90deg, #b91c1c, #7f1d1d);
    }

    .btn-flat.bg-gradient-dark {
        background: #374151;
        color: #fff;
    }

    .btn-flat.bg-gradient-dark:hover {
        background: #1f2937;
    }

    /* Custom Confirmation Modal Styles */
    .custom-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1060;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .custom-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .custom-modal {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 480px;
        overflow: hidden;
        transform: translateY(-30px) scale(0.95);
        transition: transform 0.3s ease;
    }
    
    .custom-modal-overlay.active .custom-modal {
        transform: translateY(0) scale(1);
    }
    
    .custom-modal-header {
        padding: 24px 24px 16px;
        display: flex;
        align-items: center;
        background: linear-gradient(to right, #2563eb, #3b82f6);
        color: white;
    }
    
    .custom-modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        font-size: 24px;
        background: rgba(255, 255, 255, 0.2);
    }
    
    .custom-modal-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }
    
    .custom-modal-body {
        padding: 24px;
        color: #4b5563;
        line-height: 1.6;
        background: #f8fafc;
    }
    
    .custom-modal-content {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .confirm-message {
        font-size: 1.1rem;
        margin-bottom: 20px;
        text-align: center;
        color: #1f2937;
        font-weight: 500;
    }
    
    .booking-details {
        background: #f1f5f9;
        border-radius: 10px;
        padding: 16px;
        margin: 20px 0;
    }
    
    .detail-row {
        display: flex;
        margin-bottom: 12px;
        align-items: flex-start;
    }
    
    .detail-label {
        font-weight: 600;
        min-width: 100px;
        color: #374151;
    }
    
    .detail-value {
        flex: 1;
        color: #4b5563;
    }
    
    .email-notice {
        background: #e0f2fe;
        border-left: 4px solid #0ea5e9;
        padding: 12px 16px;
        border-radius: 8px;
        margin: 20px 0;
        display: flex;
        align-items: center;
    }
    
    .email-notice i {
        color: #0ea5e9;
        margin-right: 10px;
        font-size: 1.2rem;
    }
    
    .custom-modal-footer {
        padding: 20px 24px;
        background: white;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-top: 1px solid #e5e7eb;
    }
    
    .custom-modal-btn {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        font-size: 1rem;
        min-width: 100px;
    }
    
    .custom-modal-btn-confirm {
        background: linear-gradient(to right, #22c55e, #16a34a);
        color: white;
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
    }
    
    .custom-modal-btn-cancel {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    
    .custom-modal-btn-confirm:hover {
        background: linear-gradient(to right, #16a34a, #15803d);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
    }
    
    .custom-modal-btn-cancel:hover {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }
    
    .system-notice {
        text-align: center;
        margin-top: 16px;
        font-size: 0.85rem;
        color: #94a3b8;
        font-style: italic;
    }
    
    .warning-text {
        color: #dc2626;
        font-weight: 600;
        margin-top: 10px;
        padding: 10px;
        background: #fef2f2;
        border-radius: 6px;
        border-left: 4px solid #dc2626;
    }
    
    /* Date Range Filter Styles */
    .date-range-filter {
        background: #f8fafc;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
    }
    
    .date-range-controls {
        display: flex;
        gap: 15px;
        align-items: end;
        flex-wrap: wrap;
    }
    
    .date-input-group {
        flex: 1;
        min-width: 150px;
    }
    
    .date-input-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #374151;
        font-size: 0.9rem;
    }
    
    .date-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    
    .date-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .date-range-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-filter {
        background: linear-gradient(90deg, #2563eb, #1d4ed8);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-filter:hover {
        background: linear-gradient(90deg, #1d4ed8, #1e40af);
    }
    
    .btn-clear {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-clear:hover {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }
    
    .filter-summary {
        margin-top: 10px;
        padding: 10px;
        background: #e0f2fe;
        border-radius: 6px;
        border-left: 4px solid #0ea5e9;
        display: none;
    }
    
    .filter-summary.active {
        display: block;
    }
    
    .filter-summary p {
        margin: 0;
        color: #0369a1;
        font-weight: 500;
        font-size: 0.9rem;
    }

    @media (max-width: 576px) {
        .booking-details dt,
        .booking-details dd {
            width: 100%;
        }

        .btn-group-bottom {
            justify-content: center;
        }
        
        .notification {
            width: calc(100% - 40px);
            right: 20px;
            left: 20px;
        }
        
        .custom-modal {
            width: 95%;
            margin: 0 10px;
        }
        
        .detail-row {
            flex-direction: column;
        }
        
        .detail-label {
            min-width: auto;
            margin-bottom: 5px;
        }
        
        .date-range-controls {
            flex-direction: column;
        }
        
        .date-input-group {
            min-width: 100%;
        }
        
        .date-range-actions {
            width: 100%;
            justify-content: stretch;
        }
        
        .btn-filter, .btn-clear {
            flex: 1;
            justify-content: center;
        }
    }

    .notification-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .notification-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .notification-modal {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 480px;
        overflow: hidden;
        transform: translateY(-30px) scale(0.95);
        transition: transform 0.3s ease;
    }
    
    .notification-modal-overlay.active .notification-modal {
        transform: translateY(0) scale(1);
    }
    
    .notification-modal-header {
        padding: 24px 24px 16px;
        display: flex;
        align-items: center;
        color: white;
    }
    
    .success-modal .notification-modal-header {
        background: linear-gradient(to right, #22c55e, #16a34a);
    }
    
    .error-modal .notification-modal-header {
        background: linear-gradient(to right, #ef4444, #dc2626);
    }
    
    .notification-modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        font-size: 24px;
        background: rgba(255, 255, 255, 0.2);
    }
    
    .notification-modal-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }
    
    .notification-modal-body {
        padding: 24px;
        background: #f8fafc;
    }
    
    .notification-modal-content {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        text-align: center;
    }
    
    .notification-message {
        font-size: 1.1rem;
        margin-bottom: 20px;
        color: #1f2937;
        font-weight: 500;
        line-height: 1.6;
    }
    
    .notification-modal-footer {
        padding: 20px 24px;
        background: white;
        display: flex;
        justify-content: center;
        border-top: 1px solid #e5e7eb;
    }
    
    .notification-modal-btn {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        font-size: 1rem;
        min-width: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .notification-modal-btn-close {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    
    .notification-modal-btn-close:hover {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    
    @media (max-width: 576px) {
        .notification-modal {
            width: 95%;
            margin: 0 10px;
        }
        
        .notification-modal-header {
            padding: 20px 20px 12px;
        }
        
        .notification-modal-body {
            padding: 20px;
        }
        
        .notification-modal-content {
            padding: 16px;
        }
        
        .notification-modal-title {
            font-size: 1.25rem;
        }
        
        .notification-message {
            font-size: 1rem;
        }
    }
</style>


<div class="card card-outline card-primary shadow rounded-0">
    <div class="card-header">
        <h3 class="card-title">Booking List</h3>
    </div>

    <div class="card-body">
        <!-- Simple Date Range Filter -->
        <div class="date-range-filter">
            <div class="date-range-controls">
                <div class="date-input-group">
                    <label for="start_date">From Date</label>
                    <input type="date" id="start_date" class="date-input">
                </div>
                <div class="date-input-group">
                    <label for="end_date">To Date</label>
                    <input type="date" id="end_date" class="date-input">
                </div>
                <div class="date-range-actions">
                    <button type="button" class="btn-filter" id="applyFilter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn-clear" id="clearFilter">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
            <div class="filter-summary" id="filterSummary">
                <p id="filterText"></p>
            </div>
        </div>

        <div class="container-fluid p-0">
            <table id="booking_table" class="table table-bordered table-hover">
                <colgroup>
                    <col width="5%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                    <col width="20%">
                    <col width="15%">
                    <col width="15%">
                </colgroup>
                <thead class="thead-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Date Booked</th>
                        <th class="text-center">Ref. Code</th>
                        <th class="text-center">Facility</th>
                        <th class="text-center">Client</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $bookings = $conn->query("
                        SELECT b.*, 
                               CONCAT(c.lastname, ', ', c.firstname, ' ', COALESCE(c.middlename, '')) AS client, 
                               c.email AS client_email,
                               f.name AS facility, 
                               cc.name AS category 
                        FROM `booking_list` b 
                        INNER JOIN client_list c ON b.client_id = c.id 
                        INNER JOIN facility_list f ON b.facility_id = f.id 
                        INNER JOIN category_list cc ON f.category_id = cc.id 
                        ORDER BY UNIX_TIMESTAMP(b.date_created) DESC
                    ");

                    while ($row = $bookings->fetch_assoc()):
                        $is_new = (strtotime($row['date_created']) > strtotime('-1 day'));
                    ?>
                        <tr>
                            <td class="text-center"><?= $i++ ?></td>
                            <td data-order="<?= strtotime($row['date_created']) ?>">
                                <?php echo date("F j, Y, g:i A", strtotime($row['date_created'])) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['ref_code']) ?>
                                <?php if ($is_new): ?>
                                    <span class="badge badge-danger ml-2">New</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <p class="truncate-1 m-0" title="<?= htmlspecialchars($row['facility']) ?>"><?= htmlspecialchars($row['facility']) ?></p>
                                <small class="text-muted"><?= htmlspecialchars($row['category']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['client']) ?><br>
                                <small class="text-muted"><i class="fa fa-envelope"></i> <?= htmlspecialchars($row['client_email']) ?></small>
                            </td>
                            <td class="text-center">
                                <?php
                                switch ($row['status']) {
                                    case 0:
                                        echo "<span class='badge badge-secondary px-3 rounded-pill'>Pending</span>";
                                        break;
                                    case 1:
                                        echo "<span class='badge badge-primary px-3 rounded-pill'>Confirmed</span>";
                                        break;
                                    case 2:
                                        echo "<span class='badge badge-warning px-3 rounded-pill'>Done</span>";
                                        break;
                                    case 3:
                                        echo "<span class='badge badge-danger px-3 rounded-pill'>Cancelled</span>";
                                        break;
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <div class="dropdown" style="display:inline-block;">
                                    <button class="btn btn-flat btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-cog"></i> Actions
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item view_data" href="javascript:void(0)" data-id="<?= $row['id'] ?>">
                                            <i class="fa fa-eye text-primary"></i> View Details
                                        </a>
                                        
                                        <?php if ($row['status'] == 0): ?>
                                            <form method="POST" class="dropdown-item p-0">
                                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="new_status" value="1">
                                                <button type="button" class="dropdown-item text-success" 
                                                    onclick="showCustomConfirm('confirm', '<?= htmlspecialchars($row['ref_code']) ?>', '<?= htmlspecialchars($row['client']) ?>', '<?= htmlspecialchars($row['facility']) ?>', this)">
                                                    <i class="fa fa-check text-success"></i> Confirm
                                                </button>
                                            </form>

                                            <form method="POST" class="dropdown-item p-0">
                                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="new_status" value="3">
                                                <button type="button" class="dropdown-item text-danger" 
                                                    onclick="showCustomConfirm('cancel', '<?= htmlspecialchars($row['ref_code']) ?>', '<?= htmlspecialchars($row['client']) ?>', '<?= htmlspecialchars($row['facility']) ?>', this)">
                                                    <i class="fa fa-times text-danger"></i> Cancel
                                                </button>
                                            </form>

                                        <?php elseif ($row['status'] == 1): ?>
                                            <form method="POST" class="dropdown-item p-0">
                                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="new_status" value="2">
                                                <button type="button" class="dropdown-item text-warning" 
                                                    onclick="showCustomConfirm('complete', '<?= htmlspecialchars($row['ref_code']) ?>', '<?= htmlspecialchars($row['client']) ?>', '<?= htmlspecialchars($row['facility']) ?>', this)">
                                                    <i class="fa fa-check-circle text-warning"></i> Complete
                                                </button>
                                            </form>

                                            <form method="POST" class="dropdown-item p-0">
                                                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="new_status" value="3">
                                                <button type="button" class="dropdown-item text-danger" 
                                                    onclick="showCustomConfirm('cancel_confirmed', '<?= htmlspecialchars($row['ref_code']) ?>', '<?= htmlspecialchars($row['client']) ?>', '<?= htmlspecialchars($row['facility']) ?>', this)">
                                                    <i class="fa fa-times text-danger"></i> Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div class="custom-modal-overlay" id="customConfirmModal">
    <div class="custom-modal">
        <div class="custom-modal-header">
            <div class="custom-modal-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3 class="custom-modal-title" id="modalTitle">Confirm Booking</h3>
        </div>
        <div class="custom-modal-body">
            <div class="custom-modal-content">
                <div class="confirm-message" id="modalMessage">
                    Are you sure you want to confirm this booking?
                </div>
                
                <div class="booking-details">
                    <div class="detail-row">
                        <span class="detail-label">Reference:</span>
                        <span class="detail-value" id="infoRefCode"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Client:</span>
                        <span class="detail-value" id="infoClient"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Facility:</span>
                        <span class="detail-value" id="infoFacility"></span>
                    </div>
                </div>
                
                <div class="email-notice" id="emailNotice">
                    <i class="fas fa-envelope"></i>
                    <span>A confirmation email will be sent to the client.</span>
                </div>
                
                <div class="warning-text" id="warningText" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="warningMessage"></span>
                </div>
            </div>
            
            <div class="system-notice">
                Catalunan Grande Sports Complex
            </div>
        </div>
        <div class="custom-modal-footer">
            <button class="custom-modal-btn custom-modal-btn-cancel" id="modalCancelBtn">Cancel</button>
            <button class="custom-modal-btn custom-modal-btn-confirm" id="modalConfirmBtn">Confirm</button>
        </div>
    </div>
</div>

<script>
$(function() {
    $('.table th, .table td').addClass("align-middle px-2 py-1");

    // Initialize DataTable with responsive disabled and scrollX enabled
    var table = $('#booking_table').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": false,  // Disable responsive to show all columns
        "scrollX": true,      // Enable horizontal scrolling
        "scrollCollapse": true
    });

    $('#booking_table').on('click', '.view_data', function() {
        uni_modal("Booking Details", "bookings/view_booking.php?id=" + $(this).attr('data-id'));
    });
    
    // Date Range Filter
    $('#applyFilter').on('click', function() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert('Start date cannot be after end date!');
            return;
        }
        
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var rowDate = new Date(data[1]);
                var rowTimestamp = rowDate.getTime();
                
                var startTimestamp = startDate ? new Date(startDate).getTime() : null;
                var endTimestamp = endDate ? new Date(endDate + 'T23:59:59').getTime() : null;
                
                if (!startDate && !endDate) {
                    return true;
                }
                
                if (startDate && endDate) {
                    return rowTimestamp >= startTimestamp && rowTimestamp <= endTimestamp;
                } else if (startDate) {
                    return rowTimestamp >= startTimestamp;
                } else if (endDate) {
                    return rowTimestamp <= endTimestamp;
                }
                
                return true;
            }
        );
        
        table.draw();
        updateFilterSummary();
    });
    
    $('#clearFilter').on('click', function() {
        $('#start_date').val('');
        $('#end_date').val('');
        $.fn.dataTable.ext.search.pop();
        table.draw();
        updateFilterSummary();
    });
    
    function updateFilterSummary() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const summary = $('#filterSummary');
        const filterText = $('#filterText');
        
        if (startDate || endDate) {
            let text = 'Showing bookings ';
            
            if (startDate && endDate) {
                text += `from <strong>${formatDate(startDate)}</strong> to <strong>${formatDate(endDate)}</strong>`;
            } else if (startDate) {
                text += `from <strong>${formatDate(startDate)}</strong> onwards`;
            } else if (endDate) {
                text += `up to <strong>${formatDate(endDate)}</strong>`;
            }
            
            filterText.html(text);
            summary.addClass('active');
        } else {
            summary.removeClass('active');
        }
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    
    $('#modalCancelBtn').on('click', function() {
        $('#customConfirmModal').removeClass('active');
        if (window.lastClickedButton) {
            window.lastClickedButton.disabled = false;
        }
    });
    
    $('#modalConfirmBtn').on('click', function() {
        if (window.currentForm) {
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.95);z-index:9999;display:flex;justify-content:center;align-items:center;flex-direction:column;';
            
            const spinner = document.createElement('div');
            spinner.className = 'loading-spinner';
            spinner.style.cssText = 'width:50px;height:50px;border:5px solid #f3f3f3;border-top:5px solid #3498db;border-radius:50%;animation:spin 1s linear infinite;';
            
            const actionText = document.createElement('p');
            actionText.style.cssText = 'margin-top:20px;font-size:18px;color:#333;';
            
            const action = window.currentForm.querySelector('input[name="new_status"]').value;
            switch(action) {
                case '1': actionText.textContent = 'Confirming booking...'; break;
                case '2': actionText.textContent = 'Completing booking...'; break;
                case '3': actionText.textContent = 'Cancelling booking...'; break;
                default: actionText.textContent = 'Processing...';
            }
            
            const style = document.createElement('style');
            style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
            
            overlay.appendChild(spinner);
            overlay.appendChild(actionText);
            document.body.appendChild(overlay);
            document.body.appendChild(style);
            
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'update_status';
            hiddenInput.value = '1';
            window.currentForm.appendChild(hiddenInput);
            
            window.currentForm.submit();
        }
        
        $('#customConfirmModal').removeClass('active');
    });
});

function showCustomConfirm(actionType, refCode, client, facility, button) {
    window.lastClickedButton = button;
    window.currentForm = button.closest('form');
    button.disabled = true;
    
    const modal = $('#customConfirmModal');
    const icon = $('.custom-modal-icon');
    const title = $('#modalTitle');
    const message = $('#modalMessage');
    const emailNotice = $('#emailNotice');
    const warningText = $('#warningText');
    const warningMessage = $('#warningMessage');
    
    $('#infoRefCode').text(refCode);
    $('#infoClient').text(client);
    $('#infoFacility').text(facility);
    warningText.hide();
    
    switch(actionType) {
        case 'confirm':
            icon.html('<i class="fas fa-calendar-check"></i>');
            title.text('Confirm Booking');
            message.text('Are you sure you want to confirm this booking?');
            $('.custom-modal-header').css('background', 'linear-gradient(to right, #2563eb, #3b82f6)');
            $('.custom-modal-btn-confirm').css('background', 'linear-gradient(to right, #22c55e, #16a34a)');
            emailNotice.html('<i class="fas fa-envelope"></i><span>A confirmation email will be sent to the client.</span>').show();
            break;
            
        case 'cancel':
            icon.html('<i class="fas fa-calendar-times"></i>');
            title.text('Cancel Booking');
            message.text('Are you sure you want to cancel this booking?');
            $('.custom-modal-header').css('background', 'linear-gradient(to right, #ef4444, #f87171)');
            $('.custom-modal-btn-confirm').css('background', 'linear-gradient(to right, #ef4444, #dc2626)');
            emailNotice.html('<i class="fas fa-envelope"></i><span>A cancellation email will be sent to the client.</span>').show();
            break;
            
        case 'complete':
            icon.html('<i class="fas fa-check-circle"></i>');
            title.text('Complete Booking');
            message.text('Are you sure you want to mark this booking as completed?');
            $('.custom-modal-header').css('background', 'linear-gradient(to right, #f59e0b, #fbbf24)');
            $('.custom-modal-btn-confirm').css('background', 'linear-gradient(to right, #f59e0b, #d97706)');
            emailNotice.html('<i class="fas fa-envelope"></i><span>A completion email will be sent to the client.</span>').show();
            break;
            
        case 'cancel_confirmed':
            icon.html('<i class="fas fa-exclamation-triangle"></i>');
            title.text('Cancel Confirmed Booking');
            message.text('Are you sure you want to cancel this confirmed booking?');
            $('.custom-modal-header').css('background', 'linear-gradient(to right, #ef4444, #f87171)');
            $('.custom-modal-btn-confirm').css('background', 'linear-gradient(to right, #ef4444, #dc2626)');
            emailNotice.html('<i class="fas fa-envelope"></i><span>A cancellation email will be sent to the client.</span>').show();
            warningMessage.html('Warning: This booking is already confirmed. Cancelling may cause inconvenience to the client.');
            warningText.show();
            break;
    }
    
    modal.addClass('active');
    return false;
}
</script>