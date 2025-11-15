<?php
require_once('./../../config.php');
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
        
        // Extract data for easier use
        $id = $booking_data['id'];
        $ref_code = $booking_data['ref_code'];
        $client = $booking_data['client'];
        $status = $booking_data['status'];
        $date_from = $booking_data['date_from'];
        $date_to = $booking_data['date_to'];
        $time_from = $booking_data['time_from'];
        $time_to = $booking_data['time_to'];
        $payment_proof = $booking_data['payment_proof'] ?? '';
        $paid_amount = $booking_data['paid_amount'] ?? 0;
        
        $facility_name = $facility_data['name'] ?? 'Unknown Facility';
        $facility_code = $facility_data['facility_code'] ?? '';
        $category = $facility_data['category'] ?? 'Unknown Category';
        $facility_price = floatval($facility_data['price'] ?? 0);
        
        // Calculate duration and amount
        if(empty($time_from) || empty($time_to)) {
            // All-day booking
            $start_date = new DateTime($date_from);
            $end_date = new DateTime($date_to);
            $end_date->modify('+1 day');
            $interval_days = $start_date->diff($end_date);
            $total_days = $interval_days->days;
            
            $total_amount = $facility_price * $total_days;
            $total_hours = $total_days * 24;
            $rate_display = "₱" . number_format($facility_price, 2) . " per day × " . $total_days . " day" . ($total_days > 1 ? "s" : "");
        } else {
            // Hourly booking
            $datetime_from = new DateTime($date_from . ' ' . $time_from);
            $datetime_to = new DateTime($date_to . ' ' . $time_to);
            $interval = $datetime_from->diff($datetime_to);
            
            $total_hours = ($interval->days * 24) + $interval->h + ($interval->i / 60);
            $total_hours = round($total_hours, 2);
            
            $rate_per_hour = $facility_price;
            $total_amount = $rate_per_hour * $total_hours;
            $rate_display = "₱" . number_format($rate_per_hour, 2) . " per hour × " . $total_hours . " hour(s)";
        }
        
        if($facility_price <= 0) {
            $total_amount = 0;
            $rate_display = "No rate set";
        }
    }
}
?>

<style>
    #uni_modal .modal-dialog {
        max-width: 900px;
        width: 95%;
    }
    
    #uni_modal .modal-content {
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header-custom {
        padding: 1.25rem 1.75rem;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        border-bottom: none;
    }
    
    .modal-header-custom h5 {
        font-weight: 600;
        font-size: 1.35rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }
    
    .modal-header-custom .ref-code {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        margin-left: auto;
        font-weight: 500;
    }
    
    .modal-body {
        padding: 1.75rem;
        background: #fafbfc;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .info-card {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        border-left: 4px solid #3b82f6;
    }
    
    .info-card h6 {
        margin: 0 0 0.75rem 0;
        font-size: 0.85rem;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-card .content {
        font-size: 1.05rem;
        color: #1e293b;
        font-weight: 500;
    }
    
    .info-card .subtext {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 0.5rem;
    }
    
    .amount-card {
        border-left-color: #10b981 !important;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    }
    
    .amount-card .content {
        font-size: 1.6rem;
        color: #10b981;
        font-weight: 700;
    }
    
    .payment-card {
        border-left-color: #8b5cf6 !important;
        background: linear-gradient(135deg, #f5f3ff 0%, #faf5ff 100%);
        grid-column: 1 / -1;
    }
    .payment-card {
        border-left-color: #6b7280 !important;
        background: white;
        border: 1px solid #e5e7eb;
        grid-column: 1 / -1;
    }

    .payment-proof-display {
        padding: 1.5rem;
        border: 1px solid #f3f4f6;
        border-radius: 8px;
        background: #fafafa;
        margin-top: 1rem;
    }

    .payment-image-item {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        gap: 1.5rem;
    }

    .payment-image-thumbnail {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        border: 1px solid #d1d5db;
        transition: all 0.2s ease;
    }

    .payment-image-thumbnail:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .payment-image-info {
        flex: 1;
    }

    .payment-image-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .payment-image-size {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.75rem;
    }

    .payment-image-actions {
        display: flex;
        gap: 0.75rem;
    }

    .payment-action-btn {
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0.6rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s ease;
    }

    .payment-action-btn:hover {
        background: #2563eb;
    }

    .payment-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .payment-status.uploaded {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .payment-status.pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    /* No Payment Proof State */
    .no-payment-proof {
        text-align: center;
        padding: 2rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #d1d5db;
        margin-top: 1rem;
    }

    .no-payment-proof i {
        font-size: 3rem;
        color: #9ca3af;
        margin-bottom: 1rem;
    }

    .no-payment-proof .title {
        font-size: 1.1rem;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .no-payment-proof .subtitle {
        font-size: 0.875rem;
        color: #9ca3af;
    }
    .receipt-card {
        border-left-color: #f59e0b !important;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        grid-column: 1 / -1;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-0 { background: #e2e8f0; color: #475569; }
    .status-1 { background: #dbeafe; color: #1d4ed8; }
    .status-2 { background: #fef3c7; color: #92400e; }
    .status-3 { background: #fee2e2; color: #b91c1c; }

    .payment-proof-display {
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: white;
        margin-top: 1rem;
    }

    .payment-image-item {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
    }

    .payment-image-thumbnail {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid #e5e7eb;
    }

    .amount-input-group {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    .amount-input-wrapper {
        position: relative;
        flex: 1;
    }

    .amount-input-wrapper .currency-symbol {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-weight: 600;
        z-index: 2;
        font-size: 1.1rem;
    }

    .amount-input {
        width: 100%;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        background: white;
        padding: 0.85rem 0.85rem 0.85rem 2.5rem;
    }

    .amount-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .save-amount-btn {
        background: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        padding: 0.85rem 1.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .save-amount-btn:hover {
        background: #059669;
    }

    .amount-saved-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        background: #dcfce7;
        color: #166534;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 0.75rem;
    }

    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .btn-action {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
    }
    
    .btn-close {
        background: #f1f5f9;
        color: #64748b;
    }
    
    .btn-receipt {
        background: #10b981;
        color: white;
    }
    
    .btn-confirm {
        background: #3b82f6;
        color: white;
    }
    
    .btn-done {
        background: #10b981;
        color: white;
    }
    
    .btn-cancel {
        background: #ef4444;
        color: white;
    }

    .image-modal {
        display: none;
        position: fixed;
        z-index: 999999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
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
        display: block;
    }

    .image-modal-close {
        position: absolute;
        top: 15px;
        right: 25px;
        color: white;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1000000;
        background: rgba(0, 0, 0, 0.5);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .modal-body {
            padding: 1.25rem;
        }
        
        .payment-image-item {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-action {
            width: 100%;
            justify-content: center;
        }
        
        .amount-input-group {
            flex-direction: column;
        }
        
        .save-amount-btn {
            width: 100%;
            justify-content: center;
        }
    }
    
</style>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close">&times;</span>
    <div class="image-modal-content">
        <img class="image-modal-image" id="modalImage">
    </div>
</div>

<div class="compact-modal">
    <div class="modal-header-custom">
        <h5><i class="fas fa-calendar-check"></i> Booking Details</h5>
        <span class="ref-code"><?= htmlspecialchars($ref_code ?? '') ?></span>
    </div>
    
    <div class="modal-body">
        <div class="info-grid">
            <div class="info-card">
                <h6><i class="fas fa-user"></i> Client</h6>
                <div class="content"><?= htmlspecialchars($client ?? 'Unknown Client') ?></div>
            </div>
            
            <div class="info-card">
                <h6><i class="fas fa-info-circle"></i> Status</h6>
                <div class="content">
                    <?php 
                        $status_class = "status-".$status;
                        switch($status){
                            case 0:
                                echo "<span class='status-badge $status_class'><i class='fas fa-clock'></i> Pending</span>";
                                break;
                            case 1:
                                echo "<span class='status-badge $status_class'><i class='fas fa-check-circle'></i> Confirmed</span>";
                                break;
                            case 2:
                                echo "<span class='status-badge $status_class'><i class='fas fa-check-double'></i> Done</span>";
                                break;
                            case 3:
                                echo "<span class='status-badge $status_class'><i class='fas fa-times-circle'></i> Cancelled</span>";
                                break;
                            default:
                                echo "<span class='status-badge status-0'><i class='fas fa-question-circle'></i> Unknown</span>";
                        }
                    ?>
                </div>
            </div>
            
            <div class="info-card">
                <h6><i class="fas fa-building"></i> Facility</h6>
                <div class="content"><?= htmlspecialchars($facility_name) ?></div>
                <div class="subtext"><?= htmlspecialchars($category) ?> (<?= htmlspecialchars($facility_code) ?>)</div>
            </div>
            
            <div class="info-card">
                <h6><i class="fas fa-calendar-alt"></i> Schedule</h6>
                <div class="content">
                    <?php 
                        if($date_from == $date_to){
                            echo date("M d, Y", strtotime($date_from));
                        } else {
                            echo date("M d, Y", strtotime($date_from)) . " - " . date("M d, Y", strtotime($date_to));
                        }
                    ?>
                </div>
                <div class="subtext">
                    <?php
                        if(!empty($time_from) && !empty($time_to)) {
                            $time_from_formatted = date("g:i A", strtotime($time_from));
                            $time_to_formatted = date("g:i A", strtotime($time_to));
                            echo $time_from_formatted . " - " . $time_to_formatted;
                            if($date_from != $date_to) {
                                echo " (Daily)";
                            }
                        } else {
                            echo "All day";
                            if($date_from != $date_to) {
                                echo " (Multi-day)";
                            }
                        }
                        echo " • " . $total_hours . " hours total";
                    ?>
                </div>
            </div>
            
            <!-- Total Amount Card -->
            <div class="info-card amount-card">
                <h6><i class="fas fa-money-bill-wave"></i> Total Amount</h6>
                <div class="content">
                    ₱<?= number_format($total_amount, 2) ?>
                </div>
                <?php if(isset($rate_display) && $facility_price > 0): ?>
                <div class="rate-info">
                    <?= $rate_display ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Duration Summary Card -->
            <div class="info-card">
                <h6><i class="fas fa-hourglass-half"></i> Duration</h6>
                <div class="content"><?= $total_hours ?> Hour(s)</div>
                <div class="subtext">
                    <?php
                        if($total_hours >= 24) {
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
            </div>

            <!-- Payment Proof Card -->
         <div class="info-card payment-card">
    <h6><i class="fas fa-file-invoice"></i> Payment Proof</h6>
    <div class="content">
        <?php
            $has_payment_proof = !empty($payment_proof);
            if($has_payment_proof) {
                echo "<span class='payment-status uploaded'><i class='fas fa-check-circle'></i> Proof Uploaded</span>";
            } else {
                echo "<span class='payment-status pending'><i class='fas fa-clock'></i> Awaiting Payment Proof</span>";
            }
        ?>
    </div>
    
    <?php if($has_payment_proof): ?>
    <div class="payment-proof-display">
        <div class="payment-image-item">
            <?php 
                $base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
                $final_image_path = $base_url . '/' . ltrim($payment_proof, '/');
            ?>
            <img src="<?= $final_image_path ?>" alt="Payment Proof" class="payment-image-thumbnail" onclick="viewImage('<?= $final_image_path ?>')" 
                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiBmaWxsPSIjRjhGOUZBIi8+CjxwYXRoIGQ9Ik02MCA4MUM3MC45MjQ5IDgxIDgwIDcxLjkyNDkgODAgNjFDODAgNTAuMDc1MSA3MC45MjQ5IDQxIDYwIDQxQzQ5LjA3NTEgNDEgNDAgNTAuMDc1MSA0MCA2MUM0MCA3MS45MjQ5IDQ5LjA3NTEgODEgNjAgODFaIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CjxwYXRoIGQ9Ik00MCA4MUw2MCA2MUw1NCA1NEw0OCA0OCIgc3Ryb2tlPSIjOUNBM0FGIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4K'; this.title='Image not found: <?= htmlspecialchars($payment_proof) ?>';">
            <div class="payment-image-info">
                <div class="payment-image-name">Payment Receipt</div>
                <div class="payment-image-size">Uploaded by client</div>
                <div class="subtext" style="font-size: 0.8rem; color: #6b7280;">
                    Click the image to view in full size
                </div>
            </div>
            <div class="payment-image-actions">
                <button class="payment-action-btn" onclick="viewImage('<?= $final_image_path ?>')">
                    <i class="fas fa-expand"></i> View Full Size
                </button>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="no-payment-proof">
        <i class="fas fa-receipt"></i>
        <div class="title">No Payment Proof Uploaded</div>
        <div class="subtitle">Client has not submitted payment confirmation yet</div>
    </div>
    <?php endif; ?>
</div>

            <!-- Receipt Amount Card - ALWAYS EDITABLE -->
            <div class="info-card receipt-card">
                <h6><i class="fas fa-file-invoice-dollar"></i> Receipt Amount</h6>
                <div class="content">
                    <?php if($paid_amount > 0): ?>
                        <div style="font-size: 1.5rem; color: #f59e0b; font-weight: 700; margin-bottom: 0.5rem;">
                            ₱<?= number_format($paid_amount, 2) ?>
                        </div>
                        <div class="amount-saved-badge">
                            <i class="fas fa-check-circle"></i> Amount saved for receipt
                        </div>
                    <?php else: ?>
                        <div style="color: #64748b; margin-bottom: 0.5rem;">
                            No amount set for receipt yet
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="amount-input-group">
                    <div class="amount-input-wrapper">
                        <span class="currency-symbol">₱</span>
                        <input type="number" 
                               id="receiptAmount" 
                               class="amount-input" 
                               placeholder="0.00" 
                               step="0.01" 
                               min="0" 
                               max="<?= $total_amount * 2 ?>" 
                               value="<?= $paid_amount > 0 ? number_format($paid_amount, 2, '.', '') : '' ?>">
                    </div>
                    <button type="button" class="save-amount-btn" id="saveAmountBtn">
                        <i class="fas fa-save"></i> Save Amount
                    </button>
                </div>
                <div class="subtext" style="margin-top: 0.5rem;">
                    Enter the actual amount paid for receipt generation. You can change this anytime.
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
           
            
            <?php if($paid_amount > 0): ?>
            <button class="btn-action btn-receipt" type="button" onclick="generateReceipt()">
                <i class="fas fa-receipt"></i> Generate Receipt
            </button>
            <?php endif; ?>
            
            <button class="btn-action btn-close" type="button" data-dismiss="modal">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<script>
    $(function(){
        // Image viewing modal functions
        window.viewImage = function(imageSrc) {
            $('#modalImage').attr('src', imageSrc);
            $('#imageModal').fadeIn(300);
            $('body').css('overflow', 'hidden');
        };

        // Close image modal function
        function closeImageModal() {
            $('#imageModal').fadeOut(300);
            $('body').css('overflow', '');
        }

        // Close image modal on X button click
        $(document).on('click', '.image-modal-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeImageModal();
        });

        // Close image modal on background click
        $(document).on('click', '#imageModal', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Save receipt amount - SIMPLIFIED VERSION
        $('#saveAmountBtn').on('click', function() {
            const amount = parseFloat($('#receiptAmount').val()) || 0;
            const bookingId = "<?= $id ?? '' ?>";
            
            if (amount <= 0) {
                alert_toast("Please enter a valid amount greater than 0.", 'error');
                return;
            }

            // Show loading state
            const saveBtn = $('#saveAmountBtn');
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_receipt_amount",
                method: "POST",
                data: {
                    id: bookingId,
                    amount: amount
                },
                dataType: "json",
                error: err => {
                    console.error(err);
                    alert_toast("An error occurred while saving the amount.", 'error');
                    saveBtn.prop('disabled', false).html(originalText);
                },
                success: function(resp){
                    if(resp && resp.status === 'success'){
                        alert_toast("Receipt amount saved successfully!", 'success');
                        
                        // Update UI without reloading
                        saveBtn.html('<i class="fas fa-check"></i> Saved');
                        
                        // Show receipt button if it wasn't there before
                        if($('.btn-receipt').length === 0 && amount > 0) {
                            $('.action-buttons').prepend('<button class="btn-action btn-receipt" type="button" onclick="generateReceipt()"><i class="fas fa-receipt"></i> Generate Receipt</button>');
                        }
                        
                        // Re-enable button after a short delay
                        setTimeout(() => {
                            saveBtn.prop('disabled', false).html(originalText);
                        }, 2000);
                        
                    } else {
                        alert_toast(resp?.message || "An error occurred.", 'error');
                        saveBtn.prop('disabled', false).html(originalText);
                    }
                }
            });
        });

        // Update booking status
        $('.update_booking').on('click', function(){
            const status = $(this).data('status');
            let actionText = "Update";
            switch(status){
                case 1: actionText = "Confirm"; break;
                case 2: actionText = "Mark as Done"; break;
                case 3: actionText = "Cancel"; break;
            }
            const refCode = "<?= htmlspecialchars($ref_code ?? '') ?>";
            _conf(`Are you sure to ${actionText} this facility booking [Ref. Code: <b>${refCode}</b>]?`, "update_booking", ["<?= $id ?? '' ?>", status]);
        });

        // Add error handling for broken images
        $('.payment-image-thumbnail').on('error', function() {
            $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik00MCA1NEM0Ni42Mjc0IDU0IDUyIDQ4LjYyNzQgNTIgNDJDNTIgMzUuMzcyNiA0Ni42Mjc0IDMwIDQwIDMwQzMzLjM3MjYgMzAgMjggMzUuMzcyNiAyOCA0MkMyOCA0OC42Mjc0IDMzLjM3MjYgNTQgNDAgNTRaIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CjxwYXRoIGQ9Ik0yOCA1NEw0MCA0MkwzNiAzOEwzMiAzNCIgc3Ryb2tlPSIjOUNBM0FGIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4=');
            $(this).attr('title', 'Image not found or failed to load');
        });
    });

    function generateReceipt() {
        const bookingId = "<?= $id ?? '' ?>";
        if(bookingId) {
            window.open(`receipt.php?id=${bookingId}`, '_blank');
        }
    }

    function update_booking(id, status){
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=update_booking_status",
            method: "POST",
            data: {id: id, status: status},
            dataType: "json",
            error: err => {
                console.error(err);
                alert_toast("An error occurred.", 'error');
                end_loader();
            },
            success: function(resp){
                if(resp && resp.status === 'success'){
                    location.reload();
                } else {
                    alert_toast("An error occurred.", 'error');
                    end_loader();
                }
            }
        });
    }
    
</script>
