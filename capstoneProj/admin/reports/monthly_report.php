<?php if($_settings->chk_flashdata('success')): ?>
<script>
	alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif;?>

<?php
// Get current month/year or from request
$selected_month = $_GET['month'] ?? date('m');
$selected_year = $_GET['year'] ?? date('Y');

// UPDATED REVENUE LOGIC:
// 1. When booking is CONFIRMED: Use paid_amount as confirmed revenue for booking month
// 2. When booking is COMPLETED: Include BOTH the initial paid_amount AND the remaining balance (facility_price - paid_amount)

$booking_stats_qry = $conn->query("SELECT 
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 0 THEN 1 END) as pending_bookings,
    COUNT(CASE WHEN status = 1 THEN 1 END) as confirmed_bookings,
    COUNT(CASE WHEN status = 2 THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN status = 3 THEN 1 END) as cancelled_bookings
    FROM booking_list 
    WHERE MONTH(date_created) = '$selected_month' AND YEAR(date_created) = '$selected_year'");
$booking_stats = $booking_stats_qry->fetch_assoc();

// UPDATED Revenue calculation - when booking is completed, include BOTH paid_amount and remaining balance
$revenue_qry = $conn->query("SELECT 
    -- CONFIRMED REVENUE: paid_amount when booking was made (this month) - ONLY for status=1
    COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
        bl.paid_amount
    ELSE 0 END), 0) as confirmed_revenue_booking_month,
    
    -- COMPLETED REVENUE: BOTH paid_amount AND remaining balance when booking is completed (based on completion month)
    COALESCE(SUM(CASE WHEN bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year' THEN 
        -- Include the initial paid_amount PLUS the remaining balance
        bl.paid_amount + 
        CASE 
            WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
            ELSE
                GREATEST((fl.price * (
                    (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                     UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                )) - bl.paid_amount, 0)
        END
    ELSE 0 END), 0) as completed_revenue_usage_month,
    
    -- PENDING REVENUE: Full potential revenue for pending bookings made this month
    COALESCE(SUM(CASE WHEN bl.status = 0 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
        CASE 
            WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)
            ELSE
                fl.price * (
                    (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                     UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                )
        END
    ELSE 0 END), 0) as pending_revenue,
    
    -- FUTURE REVENUE: Remaining amount (facility_price - paid_amount) for confirmed bookings
    COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
        CASE 
            WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
            ELSE
                GREATEST((fl.price * (
                    (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                     UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                )) - bl.paid_amount, 0)
        END
    ELSE 0 END), 0) as future_revenue,
    
    COUNT(DISTINCT bl.client_id) as unique_clients
    FROM booking_list bl
    JOIN facility_list fl ON bl.facility_id = fl.id
    WHERE (MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year')
       OR (bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year')");
$revenue_data = $revenue_qry->fetch_assoc();

$month_name = date('F', mktime(0, 0, 0, $selected_month, 1));

// Calculate total actual revenue for this month
$total_actual_revenue = ($revenue_data['confirmed_revenue_booking_month'] ?? 0) + ($revenue_data['completed_revenue_usage_month'] ?? 0);

// Calculate success rate
$success_rate = $booking_stats['total_bookings'] > 0 
    ? round((($booking_stats['confirmed_bookings'] + $booking_stats['completed_bookings']) / $booking_stats['total_bookings']) * 100, 1) 
    : 0;
?>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --gray-bg: #f8fafc;
        --gray-border: #e2e8f0;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        --radius: 0.75rem;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--gray-bg);
        color: var(--text-main);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .card-header h3.card-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .btn-flat.btn-primary {
        background: linear-gradient(90deg, var(--primary), var(--primary-hover));
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: var(--radius);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-flat.btn-info {
        background: linear-gradient(90deg, var(--info), #0891b2);
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: var(--radius);
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-flat.btn-default {
        background-color: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--gray-border);
        padding: 0.55rem 1.1rem;
        border-radius: var(--radius);
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .card.card-outline.card-primary.rounded-0.shadow,
    .card.card-outline {
        border-radius: var(--radius) !important;
        background-color: #ffffff;
        box-shadow: var(--shadow);
        border: none;
        padding: 1.25rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.8rem;
        border-radius: var(--radius);
        text-align: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.2);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .stat-card:hover::before {
        top: -30%;
        right: -30%;
    }

    .stat-card.success {
        background: linear-gradient(135deg, var(--success), #16a34a);
    }

    .stat-card.warning {
        background: linear-gradient(135deg, var(--warning), #d97706);
    }

    .stat-card.danger {
        background: linear-gradient(135deg, var(--danger), #dc2626);
    }

    .stat-card.info {
        background: linear-gradient(135deg, var(--info), #0891b2);
    }

    .stat-card.purple {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .stat-card h4 {
        margin: 0 0 0.8rem 0;
        font-size: 0.95rem;
        opacity: 0.9;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .form-control {
        padding: 12px 40px 12px 15px;
        height: 45px;
        line-height: 1.5;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 500;
        color: #1e293b;
        background-color: white;
        transition: all 0.3s ease;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 12px;
        vertical-align: middle;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .form-control:hover {
        border-color: #667eea;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #475569;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .form-control option {
        padding: 10px;
    }

    .row.align-items-end {
        display: flex;
        align-items: flex-end;
    }

    .table {
        border-collapse: separate !important;
        border-spacing: 0 0.5rem !important;
        width: 100%;
    }

    .table thead tr th {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: var(--text-main);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 16px 20px;
        border: none !important;
        letter-spacing: 0.03em;
        vertical-align: middle;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .table thead tr th:first-child {
        border-top-left-radius: var(--radius);
        border-bottom-left-radius: var(--radius);
    }

    .table thead tr th:last-child {
        border-top-right-radius: var(--radius);
        border-bottom-right-radius: var(--radius);
    }

    .table tbody tr {
        background-color: #ffffff;
        border-radius: var(--radius);
    }

    .table tbody tr td {
        padding: 16px 20px !important;
        vertical-align: middle !important;
        border: none !important;
        color: var(--text-muted);
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        border-top: 1px solid #f1f5f9;
    }

    .table tbody tr:hover td {
        background-color: #f8fafc;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    .badge {
        display: inline-block;
        padding: 0.4em 0.9em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .badge-success { 
        background: linear-gradient(135deg, var(--success), #16a34a); 
        color: #fff; 
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
    }
    .badge-danger { 
        background: linear-gradient(135deg, var(--danger), #dc2626); 
        color: #fff; 
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }
    .badge-warning { 
        background: linear-gradient(135deg, var(--warning), #d97706); 
        color: #fff; 
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }
    .badge-primary { 
        background: linear-gradient(135deg, var(--primary), var(--primary-hover)); 
        color: #fff; 
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
    }
    .badge-info { 
        background: linear-gradient(135deg, var(--info), #0891b2); 
        color: #fff; 
        box-shadow: 0 2px 8px rgba(6, 182, 212, 0.3);
    }

    .control-section {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.5rem;
        border-radius: var(--radius);
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .control-section .row {
        align-items: end;
    }

    .currency {
        color: var(--success);
        font-weight: 600;
    }

    .section-title {
        color: var(--primary);
        font-weight: 600;
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--primary-hover));
        border-radius: 2px;
    }

    .report-summary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 2rem;
        border-radius: var(--radius);
        margin-top: 2rem;
        border-left: 4px solid var(--primary);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--text-muted);
    }

    .dataTables_length,
    .dataTables_filter {
        display: none !important;
    }

    .dataTables_info,
    .dataTables_paginate {
        display: none !important;
    }

    .dataTables_scrollBody,
    .table-responsive {
        overflow-x: hidden !important;
    }

    .dataTables_wrapper {
        overflow: hidden !important;
    }

    .table-responsive::-webkit-scrollbar,
    .dataTables_scrollBody::-webkit-scrollbar,
    .dataTables_scrollHead::-webkit-scrollbar {
        display: none;
    }

    .table-responsive,
    .dataTables_scrollBody,
    .dataTables_scrollHead {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
        color: var(--primary);
    }

    .revenue-explanation {
        background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
        padding: 1.5rem;
        border-radius: var(--radius);
        border-left: 4px solid var(--info);
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    .revenue-flow-diagram {
        background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        padding: 1.5rem;
        border-radius: var(--radius);
        border-left: 4px solid #8b5cf6;
        margin-bottom: 1.5rem;
    }

    /* MODULAR PRINT STYLES */
    @media print {
        .no-print { 
            display: none !important; 
        }
        
        body { 
            background: white !important; 
            color: #333 !important;
            font-size: 10pt !important;
            line-height: 1.4 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .card { 
            box-shadow: none !important; 
            border: none !important;
            page-break-inside: avoid;
            margin-bottom: 15pt !important;
            padding: 0 !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
        
        .report-section {
            margin-bottom: 20pt !important;
            page-break-inside: avoid;
            border: 1px solid #ddd !important;
            border-radius: 4pt !important;
            padding: 12pt !important;
            background: white !important;
        }
        
        .report-section-header {
            background: #f5f5f5 !important;
            margin: -12pt -12pt 12pt -12pt !important;
            padding: 8pt 12pt !important;
            border-bottom: 1px solid #ddd !important;
            font-weight: 700 !important;
            font-size: 11pt !important;
            color: #333 !important;
        }
        
        .stat-card { 
            background: white !important; 
            color: #333 !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            margin-bottom: 8pt !important;
            page-break-inside: avoid;
            border-radius: 4pt !important;
            padding: 10pt !important;
            text-align: center;
        }
        
        .stat-card h4 {
            color: #666 !important;
            font-size: 8pt !important;
            padding-bottom: 4pt !important;
            margin-bottom: 4pt !important;
            font-weight: 600 !important;
            border-bottom: 1px solid #eee !important;
        }
        
        .stat-number {
            color: #000 !important;
            font-size: 18pt !important;
            font-weight: 700 !important;
        }
        
        .stat-card small {
            color: #999 !important;
            font-size: 7pt !important;
        }
        
        .stats-grid {
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 6pt !important;
            margin-bottom: 12pt !important;
        }
        
        .table-responsive {
            border: 1px solid #ddd !important;
            border-radius: 4pt !important;
            padding: 8pt !important;
            margin-bottom: 12pt !important;
            background: white !important;
            page-break-inside: avoid;
        }
        
        .table {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            page-break-inside: auto;
            margin-bottom: 0 !important;
            width: 100% !important;
        }
        
        .table thead tr th {
            background: #f5f5f5 !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
            font-size: 8pt !important;
            padding: 6pt 4pt !important;
            font-weight: 700 !important;
            text-align: center !important;
        }
        
        .table tbody tr td {
            background: white !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            font-size: 8pt !important;
            padding: 6pt 4pt !important;
        }
        
        .table tbody tr:hover td {
            background: white !important;
        }
        
        .section-title {
            background: white !important;
            color: #333 !important;
            font-size: 11pt !important;
            page-break-after: avoid;
            margin-top: 15pt !important;
            margin-bottom: 8pt !important;
            padding: 0 !important;
            border-radius: 0 !important;
            border-left: none !important;
            border-bottom: 2px solid #333 !important;
            padding-bottom: 4pt !important;
        }
        
        .section-title::after {
            display: none !important;
        }
        
        .section-title i {
            color: #666 !important;
        }
        
        .badge {
            background: #f5f5f5 !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            padding: 2pt 6pt !important;
            border-radius: 3pt !important;
            font-weight: 600 !important;
            font-size: 7pt !important;
        }
        
        .badge-success {
            background: #e8f5e9 !important;
            color: #2e7d32 !important;
            border-color: #a5d6a7 !important;
        }
        
        .badge-danger {
            background: #ffebee !important;
            color: #c62828 !important;
            border-color: #ef9a9a !important;
        }
        
        .badge-warning {
            background: #fff8e1 !important;
            color: #f57c00 !important;
            border-color: #ffe082 !important;
        }
        
        .badge-info {
            background: #e3f2fd !important;
            color: #1565c0 !important;
            border-color: #90caf9 !important;
        }
        
        .badge-primary {
            background: #e8eaf6 !important;
            color: #283593 !important;
            border-color: #9fa8da !important;
        }
        
        .report-summary, .revenue-explanation, .revenue-flow-diagram {
            background: white !important;
            border: 1px solid #ddd !important;
            border-radius: 4pt !important;
            padding: 10pt !important;
            margin-bottom: 12pt !important;
            page-break-inside: avoid;
        }
        
        .report-summary h4,
        .revenue-explanation h6,
        .revenue-flow-diagram h6 {
            color: #333 !important;
            border-bottom: 1px solid #ddd !important;
            padding-bottom: 4pt !important;
            margin-bottom: 8pt !important;
            font-size: 10pt !important;
        }
        
        h3, h4 {
            page-break-after: avoid;
        }
        
        tr {
            page-break-inside: avoid;
        }
        
        .card-header {
            background: white !important;
            color: #333 !important;
            border-bottom: 2px solid #333 !important;
            margin-bottom: 12pt !important;
            padding: 10pt 0 !important;
            border-radius: 0 !important;
        }
        
        .card-header h3 {
            color: #333 !important;
            margin: 0 !important;
            font-size: 14pt !important;
        }
        
        .card-header i {
            color: #666 !important;
        }
        
        .currency {
            color: #2e7d32 !important;
            font-weight: bold !important;
        }
        
        .container-fluid {
            margin-bottom: 15pt !important;
        }
        
        .row.mb-4 {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 6pt !important;
            margin-bottom: 12pt !important;
        }
        
        .table tbody tr[style*="gradient"] {
            background: #f5f5f5 !important;
            border: 1px solid #999 !important;
        }
        
        .table tbody tr[style*="gradient"] td {
            background: transparent !important;
            font-weight: 700 !important;
            color: #000 !important;
            border-color: #999 !important;
        }
        
        .container-fluid {
            page-break-inside: avoid;
        }
        
        @page {
            margin: 1.5cm;
            size: A4;
        }
        
        .empty-state {
            background: white !important;
            border: 1px dashed #ddd !important;
            border-radius: 4pt !important;
            padding: 15pt !important;
        }
        
        .empty-state i {
            color: #ccc !important;
        }
        
        .print-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 8pt !important;
            margin-bottom: 12pt !important;
        }
        
        .print-grid-4 {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 6pt !important;
            margin-bottom: 12pt !important;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 15pt;
            padding-bottom: 10pt;
            border-bottom: 2px solid #333;
        }
        
        .print-header h1 {
            font-size: 18pt;
            margin: 0 0 5pt 0;
            color: #333;
        }
        
        .print-header p {
            margin: 0;
            font-size: 10pt;
            color: #666;
        }
    }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .control-section .row { flex-direction: column; gap: 1rem; }
        .control-section .row > div { width: 100% !important; }
    }

    .card-tools {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .report-section {
        margin-bottom: 2rem;
        border: 1px solid var(--gray-border);
        border-radius: var(--radius);
        padding: 1.5rem;
        background: white;
        box-shadow: var(--shadow);
    }
    
    .report-section-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--gray-border);
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--text-main);
        border-radius: var(--radius) var(--radius) 0 0;
    }
    
    .print-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .print-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 992px) {
        .print-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .print-grid,
        .print-grid-4 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card card-outline card-primary rounded-0 shadow">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-line mr-2"></i>Enhanced Monthly Reports - <?php echo $month_name . ' ' . $selected_year; ?>
        </h3>
        <div class="card-tools">
            <button onclick="window.print()" class="btn btn-flat btn-primary">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Print Header (only visible in print) -->
        <div class="print-header" style="display: none;">
            <h1>Monthly Business Report</h1>
            <p><?php echo $month_name . ' ' . $selected_year; ?> | Generated on <?php echo date('F j, Y \a\t g:i A'); ?></p>
        </div>

        <!-- Control Section -->
        <div class="control-section no-print">
            <form method="GET" class="row">
                <input type="hidden" name="page" value="reports/monthly_report">
                <div class="col-md-3">
                    <label for="month" class="form-label"><strong>Month:</strong></label>
                    <select name="month" id="month" class="form-control">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo sprintf('%02d', $m); ?>" <?php echo $selected_month == sprintf('%02d', $m) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label"><strong>Year:</strong></label>
                    <select name="year" id="year" class="form-control">
                        <?php for($y = 2020; $y <= date('Y') + 2; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label><br>
                    <button type="submit" class="btn btn-flat btn-info">
                        <i class="fas fa-chart-bar"></i> Generate Report
                    </button>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label><br>
                    <button type="button" onclick="location.reload()" class="btn btn-flat btn-default">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </form>
        </div>

        <!-- Updated Revenue Logic Explanation -->
        <div class="revenue-explanation no-print">
            <h6 style="color: var(--info); margin-bottom: 0.5rem;"><i class="fas fa-info-circle mr-2"></i>Complete Revenue Calculation Logic</h6>
            <p style="margin: 0; line-height: 1.5;">
                <strong>Confirmed Revenue:</strong> Uses actual paid_amount when booking is confirmed • 
                <strong>Completed Revenue:</strong> Includes BOTH initial paid_amount AND remaining balance when booking is completed • 
                <strong>Future Revenue:</strong> Shows remaining balance (facility_price - paid_amount) for confirmed bookings
            </p>
        </div>

        <!-- Revenue Flow Diagram -->
        <div class="revenue-flow-diagram no-print">
            <h6 style="color: #8b5cf6; margin-bottom: 1rem;"><i class="fas fa-flow-chart mr-2"></i>Revenue Flow Example</h6>
            <div class="row">
                <div class="col-md-4">
                    <strong style="color: #8b5cf6;">Booking</strong><br>
                    <small>Client books a facility </small>
                </div>
                <div class="col-md-4">
                    <strong style="color: var(--info);">Confirmation (Paid Amount)</strong><br>
                    <small>Actual paid amount goes to booking date</small>
                </div>
                <div class="col-md-4">
                    <strong style="color: var(--success);">Completion (Full Amount)</strong><br>
                    <small>BOTH paid amount AND remaining balance</small>
                </div>
            </div>
        </div>

        <!-- Executive Summary Section -->
        <div class="report-section">
            <div class="report-section-header">
                <i class="fas fa-chart-bar mr-2"></i>Executive Summary
            </div>
            
            <!-- Key Metrics -->
            <div class="print-grid-4">
                <div class="stat-card">
                    <h4><i class="fas fa-calendar-check"></i> Total Bookings</h4>
                    <p class="stat-number"><?php echo number_format($booking_stats['total_bookings']); ?></p>
                </div>
                <div class="stat-card success">
                    <h4><i class="fas fa-peso-sign"></i> Total Revenue</h4>
                    <p class="stat-number">₱<?php echo number_format($total_actual_revenue, 0); ?></p>
                </div>
                <div class="stat-card info">
                    <h4><i class="fas fa-users"></i> Active Clients</h4>
                    <p class="stat-number"><?php echo number_format($revenue_data['unique_clients']); ?></p>
                </div>
                <div class="stat-card warning">
                    <h4><i class="fas fa-percentage"></i> Success Rate</h4>
                    <p class="stat-number"><?php echo $success_rate; ?>%</p>
                </div>
            </div>

            <!-- Revenue Breakdown -->
            <div class="print-grid-4">
                <div class="stat-card info">
                    <h4><i class="fas fa-calendar-plus"></i> Confirmed Revenue</h4>
                    <p class="stat-number">₱<?php echo number_format($revenue_data['confirmed_revenue_booking_month'] ?? 0, 0); ?></p>
                    <small style="opacity: 0.8;">Paid amount from confirmed</small>
                </div>
                <div class="stat-card success">
                    <h4><i class="fas fa-check-double"></i> Completed Revenue</h4>
                    <p class="stat-number">₱<?php echo number_format($revenue_data['completed_revenue_usage_month'] ?? 0, 0); ?></p>
                    <small style="opacity: 0.8;">Full amount from completed</small>
                </div>
                <div class="stat-card warning">
                    <h4><i class="fas fa-hourglass-half"></i> Pending Revenue</h4>
                    <p class="stat-number">₱<?php echo number_format($revenue_data['pending_revenue'] ?? 0, 0); ?></p>
                    <small style="opacity: 0.8;">Full potential from pending</small>
                </div>
                <div class="stat-card purple">
                    <h4><i class="fas fa-arrow-right"></i> Future Revenue</h4>
                    <p class="stat-number">₱<?php echo number_format($revenue_data['future_revenue'] ?? 0, 0); ?></p>
                    <small style="opacity: 0.8;">Remaining balance from confirmed</small>
                </div>
            </div>
        </div>

        <!-- Booking Status Section -->
        <div class="report-section">
            <div class="report-section-header">
                <i class="fas fa-tasks mr-2"></i>Booking Status Breakdown
            </div>
            
            <div class="print-grid-4">
                <div class="stat-card warning">
                    <h4><i class="fas fa-clock"></i> Pending</h4>
                    <p class="stat-number"><?php echo number_format($booking_stats['pending_bookings']); ?></p>
                </div>
                <div class="stat-card info">
                    <h4><i class="fas fa-check-circle"></i> Confirmed</h4>
                    <p class="stat-number"><?php echo number_format($booking_stats['confirmed_bookings']); ?></p>
                </div>
                <div class="stat-card success">
                    <h4><i class="fas fa-flag-checkered"></i> Completed</h4>
                    <p class="stat-number"><?php echo number_format($booking_stats['completed_bookings']); ?></p>
                </div>
                <div class="stat-card danger">
                    <h4><i class="fas fa-times-circle"></i> Cancelled</h4>
                    <p class="stat-number"><?php echo number_format($booking_stats['cancelled_bookings']); ?></p>
                </div>
            </div>
        </div>

        <!-- Facility Usage Section -->
        <div class="report-section">
            <div class="report-section-header">
                <i class="fas fa-building mr-2"></i>Facility Usage Statistics
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Facility Name</th>
                            <th width="12%">Category</th>
                            <th width="8%" class="text-center">Bookings</th>
                            <th width="12%" class="text-right">Confirmed Paid</th>
                            <th width="12%" class="text-right">Completed Total</th>
                            <th width="12%" class="text-right">Pending</th>
                            <th width="12%" class="text-right">Total</th>
                            <th width="7%" class="text-center">Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $total_facility_revenue = 0;
                        $facility_qry = $conn->query("SELECT 
                            fl.name as facility_name,
                            cl.name as category_name,
                            COUNT(CASE WHEN MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN bl.id END) as booking_count,
                            
                            COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                bl.paid_amount
                            ELSE 0 END), 0) as confirmed_paid_amount,
                            
                            COALESCE(SUM(CASE WHEN bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year' THEN 
                                bl.paid_amount + 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
                                    ELSE
                                        GREATEST((fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )) - bl.paid_amount, 0)
                                END
                            ELSE 0 END), 0) as completed_total_revenue,
                            
                            COALESCE(SUM(CASE WHEN bl.status = 0 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)
                                    ELSE
                                        fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )
                                END
                            ELSE 0 END), 0) as pending_revenue,
                            
                            fl.price as facility_price
                            FROM facility_list fl
                            LEFT JOIN category_list cl ON fl.category_id = cl.id
                            LEFT JOIN booking_list bl ON fl.id = bl.facility_id 
                                AND ((MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year')
                                     OR (bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year'))
                            WHERE fl.delete_flag = 0 AND fl.status = 1
                            GROUP BY fl.id, fl.name, cl.name, fl.price
                            HAVING COUNT(CASE WHEN MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN bl.id END) > 0
                            ORDER BY (COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                bl.paid_amount
                            ELSE 0 END), 0) + COALESCE(SUM(CASE WHEN bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year' THEN 
                                bl.paid_amount + 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
                                    ELSE
                                        GREATEST((fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )) - bl.paid_amount, 0)
                                END
                            ELSE 0 END), 0)) DESC, COUNT(CASE WHEN MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN bl.id END) DESC");
                            
                        if($facility_qry->num_rows > 0):
                            while($facility = $facility_qry->fetch_assoc()):
                                $facility_total_actual_revenue = $facility['confirmed_paid_amount'] + $facility['completed_total_revenue'];
                                $total_facility_revenue += $facility_total_actual_revenue;
                        ?>
                            <tr>
                                <td class="text-center"><strong><?php echo $i++; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($facility['facility_name']); ?></strong>
                                    <br><small class="text-muted">₱<?php echo number_format($facility['facility_price'], 2); ?> per booking</small>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($facility['category_name']); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary" style="font-size: 0.9rem;">
                                        <?php echo number_format($facility['booking_count']); ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <small class="currency">₱<?php echo number_format($facility['confirmed_paid_amount'], 2); ?></small>
                                    <br><small class="text-muted">From Confirmed</small>
                                </td>
                                <td class="text-right">
                                    <small class="currency">₱<?php echo number_format($facility['completed_total_revenue'], 2); ?></small>
                                    <br><small class="text-muted">Full Amount</small>
                                </td>
                                <td class="text-right">
                                    <small style="color: var(--warning); font-weight: 600;">₱<?php echo number_format($facility['pending_revenue'], 2); ?></small>
                                    <br><small class="text-muted">Full Potential</small>
                                </td>
                                <td class="text-right">
                                    <strong class="currency">₱<?php echo number_format($facility_total_actual_revenue, 2); ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    if($facility['booking_count'] >= 10) {
                                        echo '<span class="badge badge-success">High</span>';
                                    } elseif($facility['booking_count'] >= 5) {
                                        echo '<span class="badge badge-warning">Medium</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">Low</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        ?>
                            <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); font-weight: bold;">
                                <td colspan="7" class="text-right"><strong>TOTAL ACTUAL REVENUE:</strong></td>
                                <td class="text-right">
                                    <strong class="currency" style="font-size: 1.1rem;">₱<?php echo number_format($total_facility_revenue, 2); ?></strong>
                                </td>
                                <td></td>
                            </tr>
                        <?php 
                        else:
                        ?>
                            <tr>
                                <td colspan="9" class="text-center">No facility usage data found for the selected period.</td>
                            </tr>
                        <?php 
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Category Performance Section -->
        <div class="report-section">
            <div class="report-section-header">
                <i class="fas fa-chart-pie mr-2"></i>Category Performance
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Category</th>
                            <th width="10%" class="text-center">Bookings</th>
                            <th width="12%" class="text-right">Confirmed Paid</th>
                            <th width="12%" class="text-right">Completed Total</th>
                            <th width="12%" class="text-right">Pending</th>
                            <th width="12%" class="text-right">Future</th>
                            <th width="12%" class="text-right">Total Revenue</th>
                            <th width="5%" class="text-right">Avg Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $category_qry = $conn->query("SELECT 
                            cl.name as category_name,
                            COUNT(CASE WHEN MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN bl.id END) as total_bookings,
                            
                            COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                bl.paid_amount
                            ELSE 0 END), 0) as confirmed_paid_amount,
                            
                            COALESCE(SUM(CASE WHEN bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year' THEN 
                                bl.paid_amount + 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
                                    ELSE
                                        GREATEST((fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )) - bl.paid_amount, 0)
                                END
                            ELSE 0 END), 0) as completed_total_revenue,
                            
                            COALESCE(SUM(CASE WHEN bl.status = 0 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)
                                    ELSE
                                        fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )
                                END
                            ELSE 0 END), 0) as pending_revenue,
                            
                            COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
                                    ELSE
                                        GREATEST((fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )) - bl.paid_amount, 0)
                                END
                            ELSE 0 END), 0) as future_revenue,
                            
                            COALESCE(ROUND(AVG(fl.price), 2), 0) as avg_price
                            FROM category_list cl
                            JOIN facility_list fl ON cl.id = fl.category_id
                            LEFT JOIN booking_list bl ON fl.id = bl.facility_id 
                                AND ((MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year')
                                     OR (bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year'))
                            WHERE cl.delete_flag = 0 AND cl.status = 1
                            GROUP BY cl.id, cl.name
                            HAVING COUNT(CASE WHEN MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN bl.id END) > 0
                            ORDER BY 
                                (COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                    bl.paid_amount
                                ELSE 0 END), 0) + 
                                 COALESCE(SUM(CASE WHEN bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year' THEN 
                                    bl.paid_amount + 
                                    CASE 
                                        WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                            GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
                                        ELSE
                                            GREATEST((fl.price * (
                                                (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                                 UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                            )) - bl.paid_amount, 0)
                                    END
                                ELSE 0 END), 0)) DESC, 
                                COUNT(CASE WHEN MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN bl.id END) DESC");
                            
                        if($category_qry->num_rows > 0):
                            while($category = $category_qry->fetch_assoc()):
                                $category_total_revenue = $category['confirmed_paid_amount'] + $category['completed_total_revenue'];
                        ?>
                            <tr>
                                <td class="text-center"><strong><?php echo $i++; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary"><?php echo number_format($category['total_bookings'] ?: 0); ?></span>
                                </td>
                                <td class="text-right">
                                    <strong style="color: var(--info);">₱<?php echo number_format($category['confirmed_paid_amount'] ?: 0, 2); ?></strong>
                                </td>
                                <td class="text-right">
                                    <strong class="currency">₱<?php echo number_format($category['completed_total_revenue'] ?: 0, 2); ?></strong>
                                </td>
                                <td class="text-right">
                                    <strong style="color: var(--warning);">₱<?php echo number_format($category['pending_revenue'] ?: 0, 2); ?></strong>
                                </td>
                                <td class="text-right">
                                    <strong style="color: #8b5cf6;">₱<?php echo number_format($category['future_revenue'] ?: 0, 2); ?></strong>
                                </td>
                                <td class="text-right">
                                    <strong class="currency">₱<?php echo number_format($category_total_revenue, 2); ?></strong>
                                </td>
                                <td class="text-right">
                                    <strong>₱<?php echo number_format($category['avg_price'] ?: 0, 2); ?></strong>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <i class="fas fa-chart-pie"></i><br>
                                    <strong>No category data found</strong><br>
                                    <small>No bookings by category for <?php echo $month_name . ' ' . $selected_year; ?></small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Clients Activity Section -->
        <div class="report-section">
            <div class="report-section-header">
                <i class="fas fa-star mr-2"></i>Top Clients Activity
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Client Name</th>
                            <th width="20%">Email</th>
                            <th width="15%">Contact</th>
                            <th width="10%" class="text-center">Bookings</th>
                            <th width="12%" class="text-right">Actual Revenue</th>
                            <th width="13%" class="text-right">Potential Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $client_qry = $conn->query("SELECT 
                            CONCAT(cl.firstname, ' ', COALESCE(cl.middlename, ''), ' ', cl.lastname) as client_name,
                            cl.email,
                            cl.contact,
                            COUNT(CASE WHEN MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN bl.id END) as total_bookings,
                            
                            COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                bl.paid_amount
                            ELSE 0 END), 0) +
                            COALESCE(SUM(CASE WHEN bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year' THEN 
                                bl.paid_amount + 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
                                    ELSE
                                        GREATEST((fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )) - bl.paid_amount, 0)
                                END
                            ELSE 0 END), 0) as actual_revenue,
                            
                            COALESCE(SUM(CASE WHEN MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year' THEN 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)
                                    ELSE
                                        fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )
                                END
                            ELSE 0 END), 0) as potential_total
                            FROM client_list cl
                            JOIN booking_list bl ON cl.id = bl.client_id
                            JOIN facility_list fl ON bl.facility_id = fl.id
                            WHERE ((MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year')
                                   OR (bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year'))
                            GROUP BY cl.id
                            HAVING total_bookings > 0
                            ORDER BY actual_revenue DESC, potential_total DESC
                            LIMIT 15");
                            
                        if($client_qry->num_rows > 0):
                            while($client = $client_qry->fetch_assoc()):
                        ?>
                            <tr>
                                <td class="text-center">
                                    <strong><?php echo $i; ?></strong>
                                    <?php if($i <= 3): ?>
                                        <i class="fas fa-trophy ml-1" style="color: <?php echo $i == 1 ? '#ffd700' : ($i == 2 ? '#c0c0c0' : '#cd7f32'); ?>"></i>
                                    <?php endif; ?>
                                    <?php $i++; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars(trim($client['client_name'])); ?></strong>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($client['email']); ?></small>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($client['contact']); ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info" style="font-size: 0.85rem;">
                                        <?php echo number_format($client['total_bookings']); ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <strong class="currency">₱<?php echo number_format($client['actual_revenue'], 2); ?></strong>
                                </td>
                                <td class="text-right">
                                    <strong style="color: #8b5cf6;">₱<?php echo number_format($client['potential_total'], 2); ?></strong>
                                    <br><small class="text-muted">If all confirmed</small>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-users"></i><br>
                                    <strong>No client activity found</strong><br>
                                    <small>No client bookings for <?php echo $month_name . ' ' . $selected_year; ?></small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daily Booking Trends Section -->
        <div class="report-section">
            <div class="report-section-header">
                <i class="fas fa-calendar-alt mr-2"></i>Daily Booking Trends
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="30%">Date</th>
                            <th width="15%" class="text-center">Bookings</th>
                            <th width="15%" class="text-right">Confirmed Paid</th>
                            <th width="15%" class="text-right">Completed Total</th>
                            <th width="15%" class="text-right">Total Revenue</th>
                            <th width="10%" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $daily_qry = $conn->query("SELECT 
                            DATE(bl.date_created) as activity_date,
                            COUNT(*) as daily_bookings,
                            COALESCE(SUM(CASE WHEN bl.status = 1 THEN 
                                bl.paid_amount
                            ELSE 0 END), 0) as confirmed_paid,
                            COALESCE(SUM(CASE WHEN bl.status = 2 THEN 
                                bl.paid_amount + 
                                CASE 
                                    WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                                        GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
                                    ELSE
                                        GREATEST((fl.price * (
                                            (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                                             UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                                        )) - bl.paid_amount, 0)
                                END
                            ELSE 0 END), 0) as completed_total
                            FROM booking_list bl
                            JOIN facility_list fl ON bl.facility_id = fl.id
                            WHERE (MONTH(bl.date_created) = '$selected_month' AND YEAR(bl.date_created) = '$selected_year')
                               OR (bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$selected_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$selected_year')
                            GROUP BY DATE(bl.date_created)
                            ORDER BY activity_date DESC
                            LIMIT 10");
                            
                        $total_daily_bookings = 0;
                        $total_daily_revenue = 0;
                        
                        if($daily_qry->num_rows > 0):
                            while($daily = $daily_qry->fetch_assoc()):
                                $daily_total_revenue = $daily['confirmed_paid'] + $daily['completed_total'];
                                $total_daily_bookings += $daily['daily_bookings'];
                                $total_daily_revenue += $daily_total_revenue;
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('l, F j, Y', strtotime($daily['activity_date'])); ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary" style="font-size: 0.9rem;">
                                        <?php echo number_format($daily['daily_bookings']); ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <strong style="color: var(--info);">₱<?php echo number_format($daily['confirmed_paid'], 2); ?></strong>
                                </td>
                                <td class="text-right">
                                    <strong class="currency">₱<?php echo number_format($daily['completed_total'], 2); ?></strong>
                                </td>
                                <td class="text-right">
                                    <strong class="currency">₱<?php echo number_format($daily_total_revenue, 2); ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    if($daily_total_revenue > 5000) {
                                        echo '<span class="badge badge-success">Excellent</span>';
                                    } elseif($daily_total_revenue > 2000) {
                                        echo '<span class="badge badge-info">Good</span>';
                                    } elseif($daily_total_revenue > 0) {
                                        echo '<span class="badge badge-warning">Fair</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">Low</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        ?>
                            <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); font-weight: bold;">
                                <td><strong>TOTALS (Last 10 Days):</strong></td>
                                <td class="text-center">
                                    <span class="badge badge-success" style="font-size: 1rem;">
                                        <?php echo number_format($total_daily_bookings); ?>
                                    </span>
                                </td>
                                <td colspan="2" class="text-center">
                                    <strong>Combined Revenue:</strong>
                                </td>
                                <td class="text-right">
                                    <strong class="currency" style="font-size: 1.1rem;">₱<?php echo number_format($total_daily_revenue, 2); ?></strong>
                                </td>
                                <td></td>
                            </tr>
                        <?php 
                        else:
                        ?>
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="fas fa-calendar-times"></i><br>
                                    <strong>No daily data available</strong><br>
                                    <small>No booking activity for <?php echo $month_name . ' ' . $selected_year; ?></small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Key Performance Indicators Section -->
        <div class="report-section">
            <div class="report-section-header">
                <i class="fas fa-tachometer-alt mr-2"></i>Key Performance Indicators
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">              
                    <thead>
                        <tr>
                            <th width="40%">Metric</th>
                            <th width="30%" class="text-center">Value</th>
                            <th width="30%">Analysis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><i class="fas fa-thumbs-up mr-2 text-success"></i>Booking Success Rate</strong></td>
                            <td class="text-center">
                                <span class="badge <?php echo $success_rate >= 80 ? 'badge-success' : ($success_rate >= 60 ? 'badge-warning' : 'badge-danger'); ?>" style="font-size: 1rem;">
                                    <?php echo $success_rate; ?>%
                                </span>
                            </td>
                            <td>
                                <strong>
                                <?php 
                                if($success_rate >= 80) echo '<span class="text-success">Excellent performance</span>';
                                elseif($success_rate >= 60) echo '<span class="text-warning">Good performance</span>';
                                elseif($success_rate >= 40) echo '<span class="text-info">Fair performance</span>';
                                else echo '<span class="text-danger">Needs improvement</span>';
                                ?>
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><i class="fas fa-times-circle mr-2 text-danger"></i>Cancellation Rate</strong></td>
                            <td class="text-center">
                                <?php $cancel_rate = $booking_stats['total_bookings'] > 0 ? round(($booking_stats['cancelled_bookings'] / $booking_stats['total_bookings']) * 100, 1) : 0; ?>
                                <span class="badge <?php echo $cancel_rate <= 10 ? 'badge-success' : ($cancel_rate <= 20 ? 'badge-warning' : 'badge-danger'); ?>" style="font-size: 1rem;">
                                    <?php echo $cancel_rate; ?>%
                                </span>
                            </td>
                            <td>
                                <strong>
                                <?php 
                                if($cancel_rate <= 10) echo '<span class="text-success">Low cancellation - Good</span>';
                                elseif($cancel_rate <= 20) echo '<span class="text-warning">Moderate cancellation</span>';
                                else echo '<span class="text-danger">High cancellation - Review policies</span>';
                                ?>
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><i class="fas fa-chart-line mr-2 text-info"></i>Revenue per Booking</strong></td>
                            <td class="text-center">
                                <span class="badge badge-info" style="font-size: 1rem;">
                                    ₱<?php echo $booking_stats['confirmed_bookings'] + $booking_stats['completed_bookings'] > 0 ? number_format($total_actual_revenue / ($booking_stats['confirmed_bookings'] + $booking_stats['completed_bookings']), 2) : '0.00'; ?>
                                </span>
                            </td>
                            <td><strong>Average revenue per successful booking</strong></td>
                        </tr>
                        <tr>
                            <td><strong><i class="fas fa-user-tie mr-2 text-primary"></i>Revenue per Client</strong></td>
                            <td class="text-center">
                                <span class="badge badge-primary" style="font-size: 1rem;">
                                    ₱<?php echo $revenue_data['unique_clients'] > 0 ? number_format($total_actual_revenue / $revenue_data['unique_clients'], 2) : '0.00'; ?>
                                </span>
                            </td>
                            <td><strong>Average spending per active client</strong></td>
                        </tr>
                        <tr>
                            <td><strong><i class="fas fa-balance-scale mr-2" style="color: #8b5cf6;"></i>Revenue Realization Rate</strong></td>
                            <td class="text-center">
                                <?php 
                                $total_potential = ($revenue_data['pending_revenue'] ?? 0) + ($revenue_data['future_revenue'] ?? 0) + $total_actual_revenue;
                                $realization_rate = $total_potential > 0 ? round(($total_actual_revenue / $total_potential) * 100, 1) : 0;
                                ?>
                                <span class="badge <?php echo $realization_rate >= 70 ? 'badge-success' : ($realization_rate >= 50 ? 'badge-warning' : 'badge-danger'); ?>" style="font-size: 1rem;">
                                    <?php echo $realization_rate; ?>%
                                </span>
                            </td>
                            <td><strong>Actual revenue vs total potential</strong></td>
                        </tr>
                        <tr>
                            <td><strong><i class="fas fa-hourglass-half mr-2 text-warning"></i>Pending Conversion Potential</strong></td>
                            <td class="text-center">
                                <span class="badge badge-warning" style="font-size: 1rem;">
                                    ₱<?php echo number_format(($revenue_data['pending_revenue'] ?? 0) + ($revenue_data['future_revenue'] ?? 0), 2); ?>
                                </span>
                            </td>
                            <td><strong>Potential additional revenue if all converted</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Report Summary Section -->
        <div class="report-section">
            <div class="report-section-header">
                <i class="fas fa-clipboard-check mr-2"></i>Complete Revenue Report Summary
            </div>
            
            <div class="print-grid">
                <div>
                    <p><strong><i class="fas fa-calendar mr-2"></i>Report Period:</strong> <?php echo $month_name . ' ' . $selected_year; ?></p>
                    <p><strong><i class="fas fa-database mr-2"></i>Total Records:</strong> <?php echo number_format($booking_stats['total_bookings']); ?> bookings processed</p>
                    <p><strong><i class="fas fa-clock mr-2"></i>Generated:</strong> <?php echo date('F j, Y \a\t g:i A'); ?></p>
                    <p><strong><i class="fas fa-cog mr-2"></i>Revenue Logic:</strong> Complete revenue tracking system</p>
                </div>
                <div>
                    <p><strong><i class="fas fa-money-bill-wave mr-2 text-info"></i>Confirmed Revenue:</strong> 
                        <span class="currency" style="font-size: 1.1rem;">₱<?php echo number_format($revenue_data['confirmed_revenue_booking_month'] ?? 0, 2); ?></span>
                        <small class="text-muted">(Paid amount from confirmed)</small>
                    </p>
                    <p><strong><i class="fas fa-receipt mr-2 text-success"></i>Completed Revenue:</strong> 
                        <span class="currency" style="font-size: 1.1rem;">₱<?php echo number_format($revenue_data['completed_revenue_usage_month'] ?? 0, 2); ?></span>
                        <small class="text-muted">(Full amount from completed)</small>
                    </p>
                    <p><strong><i class="fas fa-chart-line mr-2 text-success"></i>Total Actual Revenue:</strong> 
                        <span class="currency" style="font-size: 1.2rem;">₱<?php echo number_format($total_actual_revenue, 2); ?></span>
                    </p>
                    <p><strong><i class="fas fa-hourglass-half mr-2 text-warning"></i>Pending Potential:</strong> 
                        <span style="color: var(--warning); font-weight: 600;">₱<?php echo number_format($revenue_data['pending_revenue'] ?? 0, 2); ?></span>
                        <small class="text-muted">(Full potential)</small>
                    </p>
                </div>
            </div>
            
            <?php if($booking_stats['total_bookings'] == 0): ?>
                <div style="background: #fff3cd; border: 2px solid #ffeaa7; padding: 1.5rem; border-radius: var(--radius); margin-top: 1.5rem;">
                    <h5 style="color: #856404; margin-bottom: 1rem;"><i class="fas fa-exclamation-triangle mr-2"></i>Important Note</h5>
                    <p style="color: #856404; margin-bottom: 0.5rem;"><strong>No booking data found for the selected period.</strong> This could indicate:</p>
                    <ul style="color: #856404; margin-bottom: 0;">
                        <li>No bookings were made during <?php echo $month_name . ' ' . $selected_year; ?></li>
                        <li>System was not operational during this period</li>
                        <li>Data may need verification or migration</li>
                        <li>Consider checking previous or subsequent months</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        // Initialize DataTables with custom settings
        $('.table').each(function() {
            $(this).find('th, td').addClass("align-middle px-2 py-1");
            
            if ($(this).find('tbody tr').length > 5) {
                $(this).DataTable({
                    "pageLength": 10,
                    "order": [], 
                    "responsive": true,
                    "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>t<"row"<"col-sm-6"i><"col-sm-6"p>>',
                    "language": {
                        "search": "Search records:",
                        "lengthMenu": "Show _MENU_ entries per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    }
                });
            }
        });
        
        // Print optimization
        window.addEventListener('beforeprint', function() {
            $('.no-print').hide();
            $('.print-header').show();
            $('body').addClass('printing');
        });
        
        window.addEventListener('afterprint', function() {
            $('.no-print').show();
            $('.print-header').hide();
            $('body').removeClass('printing');
        });
        
        // Smooth scroll for section titles
        $('.section-title').click(function() {
            $(this).parent().find('.table-responsive').toggle('slow');
        });
        
        // Auto-refresh notification for current month
        const currentMonth = new Date().getMonth() + 1;
        const currentYear = new Date().getFullYear();
        const selectedMonth = <?php echo $selected_month; ?>;
        const selectedYear = <?php echo $selected_year; ?>;
        
        if (selectedMonth == currentMonth && selectedYear == currentYear) {
            $('.card-title').append(' <small class="badge badge-success ml-2"><i class="fas fa-circle" style="animation: pulse 2s infinite;"></i> Live Data</small>');
        }
        
        // Enhanced tooltips for revenue explanation
        $('[data-toggle="tooltip"]').tooltip();
        
        // Add hover effects for revenue cards
        $('.stat-card').hover(function() {
            $(this).find('.stat-number').addClass('animated pulse');
        }, function() {
            $(this).find('.stat-number').removeClass('animated pulse');
        });
        
        // Revenue comparison alerts
        const totalRevenue = <?php echo $total_actual_revenue; ?>;
        const pendingRevenue = <?php echo $revenue_data['pending_revenue'] ?? 0; ?>;
        
        if (pendingRevenue > totalRevenue * 0.5) {
            console.log('High pending revenue potential detected!');
            $('.revenue-explanation').append('<div style="margin-top: 10px; color: var(--warning); font-weight: bold;"><i class="fas fa-exclamation-circle mr-1"></i>High pending revenue potential detected - Focus on conversion!</div>');
        }
    });

    // Enhanced print function with revenue summary
    function printReport() {
        window.print();
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            location.reload();
        }
    });
    
    // Add enhanced animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
        .printing .stat-card {
            background: #f8f9fa !important;
            color: #333 !important;
            box-shadow: none !important;
        }
        .animated.pulse {
            animation: pulse 1s infinite;
        }
    `;
    document.head.append(style);
    
</script>