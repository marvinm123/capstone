<?php
// Get addresses for the chart
$addresses = [];
$qry = $conn->query("SELECT address FROM client_list WHERE delete_flag=0");
while ($row = $qry->fetch_assoc()) {
    $addresses[] = strtolower(trim(stripslashes($row['address'])));
}

// Get facility booking data
$facility_bookings = [];
$facility_qry = $conn->query("SELECT f.name as facility_name, COUNT(b.id) as booking_count 
                              FROM facility_list f 
                              LEFT JOIN booking_list b ON f.id = b.facility_id 
                              WHERE f.delete_flag = 0 
                              GROUP BY f.id, f.name 
                              ORDER BY booking_count DESC");
while ($row = $facility_qry->fetch_assoc()) {
    $facility_bookings[] = $row;
}

// Get booking status data
$status_data = [];
$status_qry = $conn->query("SELECT 
                              SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending,
                              SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as confirmed,
                              SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as done,
                              SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as cancelled
                            FROM booking_list");
$status_data = $status_qry->fetch_assoc();

// Get total counts
$total_categories = $conn->query("SELECT count(id) as total FROM category_list WHERE delete_flag = 0")->fetch_assoc()['total'];
$total_facilities = $conn->query("SELECT count(id) as total FROM facility_list WHERE delete_flag = 0")->fetch_assoc()['total'];
$total_clients = $conn->query("SELECT count(*) as total FROM client_list WHERE delete_flag = 0")->fetch_assoc()['total'];
$total_bookings = $conn->query("SELECT count(*) as total FROM booking_list")->fetch_assoc()['total'];
$pending_bookings = $conn->query("SELECT count(*) as total FROM booking_list WHERE status = 0")->fetch_assoc()['total'];
$confirmed_bookings = $conn->query("SELECT count(*) as total FROM booking_list WHERE status = 1")->fetch_assoc()['total'];

// COMPLETE REVENUE LOGIC (Same as Monthly Report)
$current_month = date('m');
$current_year = date('Y');

$revenue_qry = $conn->query("SELECT 
    -- CONFIRMED REVENUE: paid_amount when booking was made (this month) - ONLY for status=1
    COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$current_month' AND YEAR(bl.date_created) = '$current_year' THEN 
        bl.paid_amount
    ELSE 0 END), 0) as confirmed_revenue_booking_month,
    
    -- COMPLETED REVENUE: BOTH paid_amount AND remaining balance when booking is completed (based on completion month)
    COALESCE(SUM(CASE WHEN bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$current_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$current_year' THEN 
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
    COALESCE(SUM(CASE WHEN bl.status = 0 AND MONTH(bl.date_created) = '$current_month' AND YEAR(bl.date_created) = '$current_year' THEN 
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
    COALESCE(SUM(CASE WHEN bl.status = 1 AND MONTH(bl.date_created) = '$current_month' AND YEAR(bl.date_created) = '$current_year' THEN 
        CASE 
            WHEN bl.time_from IS NULL OR bl.time_to IS NULL THEN
                GREATEST((fl.price * (DATEDIFF(bl.date_to, bl.date_from) + 1)) - bl.paid_amount, 0)
            ELSE
                GREATEST((fl.price * (
                    (UNIX_TIMESTAMP(CONCAT(bl.date_to, ' ', bl.time_to)) - 
                     UNIX_TIMESTAMP(CONCAT(bl.date_from, ' ', bl.time_from))) / 3600
                )) - bl.paid_amount, 0)
        END
    ELSE 0 END), 0) as future_revenue
    FROM booking_list bl
    JOIN facility_list fl ON bl.facility_id = fl.id
    WHERE (MONTH(bl.date_created) = '$current_month' AND YEAR(bl.date_created) = '$current_year')
       OR (bl.status = 2 AND MONTH(COALESCE(bl.date_updated, bl.date_created)) = '$current_month' AND YEAR(COALESCE(bl.date_updated, bl.date_created)) = '$current_year')");
$revenue_data = $revenue_qry->fetch_assoc();

// Calculate total actual revenue for this month
$total_actual_revenue = ($revenue_data['confirmed_revenue_booking_month'] ?? 0) + ($revenue_data['completed_revenue_usage_month'] ?? 0);

// Get most popular category
$popular_category = $conn->query("SELECT c.name, COUNT(b.id) as bookings 
                                  FROM category_list c 
                                  LEFT JOIN facility_list f ON c.id = f.category_id 
                                  LEFT JOIN booking_list b ON f.id = b.facility_id 
                                  WHERE c.delete_flag = 0 
                                  GROUP BY c.id, c.name 
                                  ORDER BY bookings DESC 
                                  LIMIT 1")->fetch_assoc();

// Get booking trends by month
$monthly_bookings = [];
$monthly_qry = $conn->query("SELECT 
                             DATE_FORMAT(date_created, '%Y-%m') as month,
                             COUNT(*) as count
                             FROM booking_list 
                             GROUP BY DATE_FORMAT(date_created, '%Y-%m')
                             ORDER BY month DESC
                             LIMIT 6");
while ($row = $monthly_qry->fetch_assoc()) {
    $monthly_bookings[] = $row;
}
$monthly_bookings = array_reverse($monthly_bookings);

// Get recent bookings with calculated total amount based on actual hours
$recent_bookings = [];
$recent_qry = $conn->query("SELECT b.ref_code, b.date_created, b.status, 
                            c.firstname, c.lastname, f.name as facility_name,
                            b.paid_amount, f.price as facility_price,
                            b.date_from, b.date_to, b.time_from, b.time_to
                            FROM booking_list b
                            JOIN client_list c ON b.client_id = c.id
                            JOIN facility_list f ON b.facility_id = f.id
                            ORDER BY b.date_created DESC
                            LIMIT 5");
while ($row = $recent_qry->fetch_assoc()) {
    // Calculate total amount based on actual booked hours/days
    if ($row['time_from'] && $row['time_to']) {
        // Calculate hours for hourly bookings
        $start_datetime = strtotime($row['date_from'] . ' ' . $row['time_from']);
        $end_datetime = strtotime($row['date_to'] . ' ' . $row['time_to']);
        $hours_booked = max(1, ($end_datetime - $start_datetime) / 3600);
        $row['total_amount'] = $row['facility_price'] * $hours_booked;
    } else {
        // Calculate days for daily bookings
        $days_booked = (strtotime($row['date_to']) - strtotime($row['date_from'])) / (60 * 60 * 24) + 1;
        $row['total_amount'] = $row['facility_price'] * $days_booked;
    }
    $recent_bookings[] = $row;
}

// Get all bookings for calendar view with duration calculation
$calendar_bookings = [];
$calendar_qry = $conn->query("SELECT b.date_from, b.date_to, b.time_from, b.time_to, b.status, b.ref_code,
                              f.name as facility_name, f.id as facility_id, c.firstname, c.lastname,
                              cat.name as category_name, b.paid_amount
                              FROM booking_list b
                              JOIN client_list c ON b.client_id = c.id
                              JOIN facility_list f ON b.facility_id = f.id
                              JOIN category_list cat ON f.category_id = cat.id
                              WHERE b.status != 3
                              ORDER BY b.date_from DESC");
while ($row = $calendar_qry->fetch_assoc()) {
    // Calculate duration for display
    if ($row['time_from'] && $row['time_to']) {
        $start_datetime = strtotime($row['date_from'] . ' ' . $row['time_from']);
        $end_datetime = strtotime($row['date_to'] . ' ' . $row['time_to']);
        $hours_booked = max(1, ($end_datetime - $start_datetime) / 3600);
        $row['duration_display'] = round($hours_booked) . ' hour' . (round($hours_booked) > 1 ? 's' : '');
    } else {
        $days_booked = (strtotime($row['date_to']) - strtotime($row['date_from'])) / (60 * 60 * 24) + 1;
        $row['duration_display'] = $days_booked == 1 ? '' : $days_booked . ' days';
    }
    $calendar_bookings[] = $row;
}

// Get date events for calendar
$date_events = [];
$events_qry = $conn->query("SELECT * FROM date_events ORDER BY event_date DESC LIMIT 6");
while ($row = $events_qry->fetch_assoc()) {
    $date_events[] = $row;
}

// Calculate completion rate
$completion_rate = $total_bookings > 0 ? round(($status_data['done'] / $total_bookings) * 100, 1) : 0;
$cancellation_rate = $total_bookings > 0 ? round(($status_data['cancelled'] / $total_bookings) * 100, 1) : 0;

// Calculate success rate
$success_rate = $total_bookings > 0 
    ? round((($status_data['confirmed'] + $status_data['done']) / $total_bookings) * 100, 1) 
    : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Analytics Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    
    <style>
        :root {
    --primary: #667eea;
    --primary-dark: #5568d3;
    --secondary: #764ba2;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --text-primary: #0f172a;
    --text-secondary: #64748b;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    color: var(--text-primary);
    min-height: 100vh;
    padding: 2rem;
    padding-top: 5rem;
}

.dashboard-container {
    max-width: 1600px;
    margin: 0 auto;
}

/* Header Section with Title and Navigation */
.dashboard-header-section {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    padding:3rem 2rem;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    max-width: 1600px;
    margin-left: auto;
    margin-right: auto;
}

.dashboard-title {
    font-size: 2rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.admin-info {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    font-weight: 600;
    color: var(--text-secondary);
}

.admin-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

/* Navigation Bar */
.dashboard-nav {
    display: flex;
    justify-content: center;
    gap: 0.8rem;
    flex-wrap: wrap;
    max-width: 1600px;
    margin: 0 auto;
    background: white;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.nav-btn {
    padding: 0.75rem 1.3rem;
    border: none;
    border-radius: 10px;
    background: white;
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border: 2px solid #e2e8f0;
}

.nav-btn:hover {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    border-color: var(--primary);
}

.nav-btn.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.nav-btn i {
    font-size: 1rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    transition: width 0.3s ease;
}

.stat-card:hover::before {
    width: 8px;
}

.stat-card.primary::before { background: var(--primary); }
.stat-card.success::before { background: var(--success); }
.stat-card.warning::before { background: var(--warning); }
.stat-card.danger::before { background: var(--danger); }
.stat-card.info::before { background: var(--info); }
.stat-card.secondary::before { background: var(--secondary); }
.stat-card.purple::before { background: #8b5cf6; }

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
}

.stat-card.primary .stat-icon { background: linear-gradient(135deg, #667eea, #5568d3); }
.stat-card.success .stat-icon { background: linear-gradient(135deg, #10b981, #059669); }
.stat-card.warning .stat-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-card.danger .stat-icon { background: linear-gradient(135deg, #ef4444, #dc2626); }
.stat-card.info .stat-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.stat-card.secondary .stat-icon { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.stat-card.purple .stat-icon { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-detail {
    margin-top: 0.8rem;
    padding-top: 0.8rem;
    border-top: 1px solid #e2e8f0;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

/* Calendar Event Styles */
.fc-event {
    border: none !important;
    border-radius: 6px;
    padding: 3px 6px;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.fc-event:hover {
    opacity: 0.8;
    transform: scale(1.02);
}

.fc-event .event-duration {
    font-size: 0.6rem;
    opacity: 0.9;
    margin-top: 1px;
    display: block;
}

.fc-daygrid-event-dot {
    display: none !important;
    border: none !important;
}

.fc-event .fc-event-dot {
    display: none !important;
}

.fc-event.status-0 {
    background-color: #e2e8f0 !important;
    color: #1e293b !important;
    border-left: 4px solid #94a3b8 !important;
}

.fc-event.status-1 {
    background-color: #dbeafe !important;
    color: #1e40af !important;
    border-left: 4px solid #2563eb !important;
}

.fc-event.status-2 {
    background-color: #fef08a !important;
    color: #854d0e !important;
    border-left: 4px solid #ca8a04 !important;
}

.fc-list-event {
    border-left: 4px solid !important;
}

.fc-list-event.status-0 {
    border-left-color: #94a3b8 !important;
    background-color: #e2e8f0 !important;
}

.fc-list-event.status-1 {
    border-left-color: #2563eb !important;
    background-color: #dbeafe !important;
}

.fc-list-event.status-2 {
    border-left-color: #ca8a04 !important;
    background-color: #fef08a !important;
}

.fc-list-table .fc-list-event-time {
    display: none !important;
}

.fc-list-table .fc-list-event-gap {
    display: none !important;
}

.fc-list-event-time {
    display: none !important;
}

.fc-list-event-title {
    color: inherit !important;
    font-weight: 600;
}

.fc-event-title {
    color: inherit !important;
}

.fc-list-event-graphic {
    display: none !important;
}

.fc-list-event-dot {
    display: none !important;
}

.fc-h-event .fc-event-main {
    color: inherit !important;
}

.fc-daygrid-block-event .fc-event-time,
.fc-daygrid-block-event .fc-event-title {
    color: inherit !important;
}

.fc-event-main-custom {
    padding: 4px;
}

.fc-event-time-custom {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 2px;
}

/* Charts Section */
.charts-section {
    margin-top: 3rem;
    scroll-margin-top: 10rem;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 2rem;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.chart-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.chart-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.chart-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: var(--primary);
    margin-right: 1rem;
}

.chart-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary);
}

.chart-canvas-wrapper {
    height: 300px;
    position: relative;
}

/* Recent Activity */
.activity-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-top: 2rem;
    scroll-margin-top: 10rem;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.2s ease;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: #f8fafc;
}

.activity-info {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.3rem;
}

.activity-subtitle {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.activity-revenue {
    font-size: 0.85rem;
    font-weight: 600;
    margin-right: 1rem;
    padding: 0.3rem 0.8rem;
    border-radius: 8px;
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #e2e8f0; color: #1e293b; }
.status-confirmed { background: #dbeafe; color: #1e40af; }
.status-done { background: #fef08a; color: #854d0e; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

/* Calendar Overview Styles */
.calendar-overview-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-top: 2rem;
    scroll-margin-top: 10rem;
}

#bookingCalendar {
    margin-top: 1rem;
}

.fc {
    border-radius: 12px;
    overflow: hidden;
}

.fc .fc-toolbar-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary);
}

.fc .fc-button {
    background-color: var(--primary);
    border: none;
    padding: 8px 16px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.fc .fc-button:hover {
    background-color: var(--primary-dark);
    transform: translateY(-1px);
}

.fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: var(--primary-dark);
}

.fc .fc-daygrid-day.fc-day-today {
    background-color: rgba(79, 70, 229, 0.1);
}

.calendar-legend {
    display: flex;
    gap: 1.5rem;
    margin-top: 1.5rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 12px;
    flex-wrap: wrap;
}

.calendar-legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 500;
}

.calendar-legend-color {
    width: 20px;
    height: 12px;
    border-radius: 4px;
}

.calendar-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.calendar-stat-item {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 12px;
    text-align: center;
    border-left: 4px solid;
}

.calendar-stat-item.pending { border-left-color: #94a3b8; background: linear-gradient(135deg, #f8fafc, #e2e8f0); }
.calendar-stat-item.confirmed { border-left-color: #3b82f6; background: linear-gradient(135deg, #eff6ff, #dbeafe); }
.calendar-stat-item.completed { border-left-color: #eab308; background: linear-gradient(135deg, #fefce8, #fef08a); }

.calendar-stat-value {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-primary);
}

.calendar-stat-label {
    font-size: 0.8rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.3rem;
}

/* Event Overview Styles */
.event-overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.event-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border-left: 4px solid;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.event-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.event-card.event-notice {
    border-left-color: #8b5cf6;
    background: linear-gradient(135deg, #faf5ff, #e9d5ff);
}

.event-card.event-holiday {
    border-left-color: #28a745;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
}

.event-card.event-maintenance {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, #fee2e2, #fecaca);
}

.event-card.event-special {
    border-left-color: #06b6d4;
    background: linear-gradient(135deg, #cffafe, #a5f3fc);
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.event-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
}

.event-notice .event-icon { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.event-holiday .event-icon { background: linear-gradient(135deg, #28a745, #22c55e); }
.event-maintenance .event-icon { background: linear-gradient(135deg, #dc3545, #ef4444); }
.event-special .event-icon { background: linear-gradient(135deg, #06b6d4, #0ea5e9); }

.event-type-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    background: rgba(255, 255, 255, 0.9);
}

.event-notice .event-type-badge { color: #6b21a8; }
.event-holiday .event-type-badge { color: #166534; }
.event-maintenance .event-type-badge { color: #991b1b; }
.event-special .event-type-badge { color: #0e7490; }

.event-content {
    flex: 1;
}

.event-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.8rem;
    line-height: 1.3;
}

.event-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 0.8rem;
    font-weight: 500;
}

.event-description {
    font-size: 0.9rem;
    color: #475569;
    line-height: 1.5;
    background: rgba(255, 255, 255, 0.7);
    padding: 0.8rem;
    border-radius: 8px;
    border-left: 3px solid rgba(0, 0, 0, 0.1);
}

.no-events-message {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 2rem;
    color: #64748b;
}

.no-events-message i {
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-events-message h4 {
    font-size: 1.3rem;
    margin-bottom: 0.5rem;
    color: #475569;
}

.no-events-message p {
    font-size: 0.95rem;
    opacity: 0.8;
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    backdrop-filter: blur(4px);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-width: 500px;
    width: 90%;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: color 0.2s ease;
}

.modal-close:hover {
    color: var(--danger);
}

.modal-body {
    line-height: 1.6;
}

.booking-detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.8rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.booking-detail-label {
    font-weight: 600;
    color: var(--text-secondary);
}

.booking-detail-value {
    font-weight: 500;
    color: var(--text-primary);
    text-align: right;
}

.booking-status-badge {
    display: inline-block;
    padding: 0.4rem 1rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 1200px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    body {
        padding: 1rem;
        padding-top: 12rem;
    }

    .dashboard-header-section {
        padding: 1rem;
    }

    .dashboard-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
        margin-bottom: 1rem;
    }

    .dashboard-title {
        font-size: 1.5rem;
    }

    .dashboard-nav {
        gap: 0.5rem;
        padding: 0.8rem;
    }
    
    .nav-btn {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
        flex: 1 1 auto;
        min-width: 120px;
    }

    .nav-btn i {
        font-size: 0.9rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stat-value {
        font-size: 2rem;
    }

    .event-overview-grid {
        grid-template-columns: 1fr;
    }
    
    .event-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .event-type-badge {
        align-self: flex-start;
    }

    .charts-section {
        scroll-margin-top: 12rem;
    }

    .calendar-overview-card {
        scroll-margin-top: 12rem;
    }

    .activity-card {
        scroll-margin-top: 12rem;
    }

    .activity-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.8rem;
    }

    .activity-revenue {
        margin-right: 0;
    }
}

@media (max-width: 480px) {
    body {
        padding: 0.5rem;
        padding-top: 14rem;
    }

    .dashboard-title {
        font-size: 1.3rem;
    }

    .nav-btn {
        padding: 0.5rem 0.8rem;
        font-size: 0.8rem;
        min-width: 100px;
    }

    .stat-card {
        padding: 1.5rem;
    }

    .stat-value {
        font-size: 1.8rem;
    }

    .chart-card {
        padding: 1.5rem;
    }

    .section-title {
        font-size: 1.5rem;
    }
}
    </style>
</head>
<body>
    <!-- Header Section with Title and Navigation -->
    <div class="dashboard-header-section">
        <!-- Navigation Bar -->
        <div class="dashboard-nav">
            <button class="nav-btn" data-target="stats">
                <i class="fas fa-chart-bar"></i> Stats Overview
            </button>
            <button class="nav-btn" data-target="booking-status">
                <i class="fas fa-chart-pie"></i> Booking Status
            </button>
            <button class="nav-btn" data-target="facilities">
                <i class="fas fa-star"></i> Top Facilities
            </button>
            <button class="nav-btn" data-target="trends">
                <i class="fas fa-chart-line"></i> Booking Trends
            </button>
            <button class="nav-btn" data-target="calendar">
                <i class="fas fa-calendar-alt"></i> Booking Calendar
            </button>
            <button class="nav-btn" data-target="events">
                <i class="fas fa-calendar-star"></i> Event Overview
            </button>
            <button class="nav-btn" data-target="activity">
                <i class="fas fa-history"></i> Recent Activity
            </button>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Stats Grid - Row 1 -->
        <div class="stats-grid" id="stats">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= number_format($total_bookings) ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-detail">
                    <i class="fas fa-chart-line"></i> All time bookings
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= number_format($pending_bookings) ?></div>
                        <div class="stat-label">Pending Bookings</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-detail">
                    <i class="fas fa-exclamation-circle"></i> Awaiting confirmation
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= number_format($confirmed_bookings) ?></div>
                        <div class="stat-label">Confirmed</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-detail">
                    <i class="fas fa-thumbs-up"></i> Active bookings
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-value">₱<?= number_format($total_actual_revenue, 0) ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="stat-detail">
                    <i class="fas fa-coins"></i> From confirmed & completed
                </div>
            </div>
        </div>

        <!-- Stats Grid - Row 2 -->
        <div class="stats-grid">
            <div class="stat-card secondary">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= number_format($total_facilities) ?></div>
                        <div class="stat-label">Total Facilities</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-door-closed"></i>
                    </div>
                </div>
                <div class="stat-detail">
                    <i class="fas fa-building"></i> Available for booking
                </div>
            </div>

            <div class="stat-card primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= number_format($total_clients) ?></div>
                        <div class="stat-label">Registered Clients</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-detail">
                    <i class="fas fa-user-check"></i> Active accounts
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= $success_rate ?>%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
                <div class="stat-detail">
                    <i class="fas fa-check-double"></i> Confirmed + Completed
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?= $cancellation_rate ?>%</div>
                        <div class="stat-label">Cancellation Rate</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
                <div class="stat-detail">
                    <i class="fas fa-ban"></i> Cancelled bookings
                </div>
            </div>
        </div>

        <!-- Booking Status & Client Distribution Section -->
        <div class="charts-section" id="booking-status">
            <h2 class="section-title">Booking Status & Client Distribution</h2>
            
            <div class="charts-grid">
                <!-- Booking Status Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="chart-title">Booking Status</h3>
                    </div>
                    <div class="chart-canvas-wrapper">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Client Distribution Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="chart-title">Client Distribution</h3>
                    </div>
                    <div class="chart-canvas-wrapper">
                        <canvas id="clientChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Facilities Section -->
        <div class="charts-section" id="facilities">
            <h2 class="section-title">Top Performing Facilities</h2>

            <!-- Facility Performance Chart - Full Width -->
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="chart-title">Top Performing Facilities</h3>
                </div>
                <div class="chart-canvas-wrapper" style="height: 400px;">
                    <canvas id="facilityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Booking Trends Section -->
        <div class="charts-section" id="trends">
            <h2 class="section-title">Booking Trends</h2>

            <!-- Monthly Trends Chart - Full Width -->
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="chart-title">Booking Trends (Last 6 Months)</h3>
                </div>
                <div class="chart-canvas-wrapper" style="height: 350px;">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Booking Calendar Overview -->
        <div class="calendar-overview-card" id="calendar">
            <div class="chart-header">
                <div class="chart-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="chart-title">Booking Calendar Overview</h3>
            </div>
            
            <!-- Calendar Stats Summary -->
            <div class="calendar-stats">
                <div class="calendar-stat-item pending">
                    <div class="calendar-stat-value"><?= $status_data['pending'] ?></div>
                    <div class="calendar-stat-label">Pending</div>
                </div>
                <div class="calendar-stat-item confirmed">
                    <div class="calendar-stat-value"><?= $status_data['confirmed'] ?></div>
                    <div class="calendar-stat-label">Confirmed</div>
                </div>
                <div class="calendar-stat-item completed">
                    <div class="calendar-stat-value"><?= $status_data['done'] ?></div>
                    <div class="calendar-stat-label">Completed</div>
                </div>
            </div>

            <!-- Calendar -->
            <div id="bookingCalendar"></div>

            <!-- Legend -->
            <div class="calendar-legend">
                <div class="calendar-legend-item">
                    <div class="calendar-legend-color" style="background: #94a3b8;"></div>
                    <span>Pending</span>
                </div>
                <div class="calendar-legend-item">
                    <div class="calendar-legend-color" style="background: #3b82f6;"></div>
                    <span>Confirmed</span>
                </div>
                <div class="calendar-legend-item">
                    <div class="calendar-legend-color" style="background: #eab308;"></div>
                    <span>Completed</span>
                </div>
            </div>
        </div>

        <!-- Event Overview Section -->
        <div class="calendar-overview-card" style="margin-top: 2rem;" id="events">
            <div class="chart-header">
                <div class="chart-icon">
                    <i class="fas fa-calendar-star"></i>
                </div>
                <h3 class="chart-title">Event Overview</h3>
            </div>
            
            <div class="event-overview-grid">
                <?php if (empty($date_events)): ?>
                    <div class="no-events-message">
                        <i class="fas fa-calendar-plus fa-3x"></i>
                        <h4>No Events Scheduled</h4>
                        <p>There are no upcoming events in the calendar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($date_events as $event): 
                        $event_type_class = [
                            'notice' => 'event-notice',
                            'holiday' => 'event-holiday',
                            'maintenance' => 'event-maintenance',
                            'special_event' => 'event-special'
                        ][$event['event_type']];
                        
                        $event_type_text = [
                            'notice' => 'General Notice',
                            'holiday' => 'Holiday',
                            'maintenance' => 'Maintenance',
                            'special_event' => 'Special Event'
                        ][$event['event_type']];
                        
                        $event_icon = [
                            'notice' => 'fas fa-bullhorn',
                            'holiday' => 'fas fa-umbrella-beach',
                            'maintenance' => 'fas fa-tools',
                            'special_event' => 'fas fa-star'
                        ][$event['event_type']];
                    ?>
                    <div class="event-card <?= $event_type_class ?>">
                        <div class="event-header">
                            <div class="event-icon">
                                <i class="<?= $event_icon ?>"></i>
                            </div>
                            <div class="event-type-badge">
                                <?= $event_type_text ?>
                            </div>
                        </div>
                        <div class="event-content">
                            <h4 class="event-title"><?= htmlspecialchars($event['title']) ?></h4>
                            <div class="event-date">
                                <i class="fas fa-calendar-day"></i>
                                <?= date('F j, Y', strtotime($event['event_date'])) ?>
                            </div>
                            <?php if (!empty($event['description'])): ?>
                            <div class="event-description">
                                <?= htmlspecialchars($event['description']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-card" id="activity">
            <div class="chart-header">
                <div class="chart-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="chart-title">Recent Bookings</h3>
            </div>
            <?php foreach($recent_bookings as $booking): 
                $status_class = ['pending', 'confirmed', 'done', 'cancelled'][$booking['status']];
                $status_text = ['Pending', 'Confirmed', 'Completed', 'Cancelled'][$booking['status']];
                
                // Calculate display amount based on status
                switch($booking['status']) {
                    case 0: // Pending
                        $revenue_info = 'Total: ₱' . number_format($booking['total_amount'], 2);
                        break;
                    case 1: // Confirmed
                        $revenue_info = 'Paid: ₱' . number_format($booking['paid_amount'], 2) . ' | Total : ₱' . number_format($booking['total_amount'], 2);
                        break;
                    case 2: // Completed
                        $revenue_info = 'Total: ₱' . number_format($booking['total_amount'], 2);
                        break;
                    case 3: // Cancelled
                        $revenue_info = 'Refund: ₱' . number_format($booking['paid_amount'], 2);
                        break;
                }
            ?>
            <div class="activity-item">
                <div class="activity-info">
                    <div class="activity-title">
                        <?= $booking['ref_code'] ?> - <?= $booking['facility_name'] ?>
                    </div>
                    <div class="activity-subtitle">
                        <?= $booking['firstname'] ?> <?= $booking['lastname'] ?> • 
                        <?= date('M d, Y g:i A', strtotime($booking['date_created'])) ?>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span class="activity-revenue">
                        <?= $revenue_info ?>
                    </span>
                    <span class="status-badge status-<?= $status_class ?>">
                        <?= $status_text ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Booking Detail Modal -->
    <div class="modal-overlay" id="bookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-calendar-check"></i>
                    Booking Details
                </h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Modal content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Chart colors
        const colors = {
            primary: '#667eea',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6',
            secondary: '#8b5cf6'
        };

        // 1. Booking Status Chart (Doughnut)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [
                        <?= $status_data['pending'] ?>,
                        <?= $status_data['confirmed'] ?>,
                        <?= $status_data['done'] ?>,
                        <?= $status_data['cancelled'] ?>
                    ],
                    backgroundColor: [
                        colors.warning,
                        colors.info,
                        colors.success,
                        colors.danger
                    ],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });

        // 2. Client Distribution Chart (Pie)
        const addresses = <?php echo json_encode($addresses) ?>;
        const total = addresses.length;
        const residents = addresses.filter(addr => addr.includes('grande')).length;
        const nonResidents = total - residents;

        const clientCtx = document.getElementById('clientChart').getContext('2d');
        new Chart(clientCtx, {
            type: 'pie',
            data: {
                labels: ['Residents', 'Non-Residents'],
                datasets: [{
                    data: [residents, nonResidents],
                    backgroundColor: [colors.primary, colors.secondary],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });

        // 3. Facility Performance Chart (Bar)
        const facilities = <?php echo json_encode($facility_bookings) ?>;
        const topFacilities = facilities.slice(0, 8);

        const facilityCtx = document.getElementById('facilityChart').getContext('2d');
        new Chart(facilityCtx, {
            type: 'bar',
            data: {
                labels: topFacilities.map(f => f.facility_name),
                datasets: [{
                    label: 'Bookings',
                    data: topFacilities.map(f => parseInt(f.booking_count)),
                    backgroundColor: colors.primary,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 4. Monthly Trends Chart (Line)
        const monthlyData = <?php echo json_encode($monthly_bookings) ?>;

        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(m => m.month),
                datasets: [{
                    label: 'Bookings',
                    data: monthlyData.map(m => parseInt(m.count)),
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 5. Initialize Booking Calendar with NEW COLOR SCHEME
        const calendarBookings = <?php echo json_encode($calendar_bookings) ?>;
        const calendarEl = document.getElementById('bookingCalendar');
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            height: 'auto',
            
            // REMOVE "ALL DAY" TEXT FROM LIST VIEW
            views: {
                listMonth: {
                    listDaySideFormat: false // This removes the "All day" text
                }
            },
            
            events: calendarBookings.map(booking => {
                const statusClass = ['status-0', 'status-1', 'status-2', 'status-3'][booking.status];
                const statusText = ['Pending', 'Confirmed', 'Completed', 'Cancelled'][booking.status];
                
                return {
                    title: `${booking.ref_code} - ${booking.facility_name}`,
                    start: booking.date_from,
                    end: booking.date_to,
                    classNames: [statusClass],
                    extendedProps: {
                        refCode: booking.ref_code,
                        facility: booking.facility_name,
                        client: `${booking.firstname} ${booking.lastname}`,
                        status: statusText,
                        statusCode: booking.status,
                        paidAmount: booking.paid_amount,
                        category: booking.category_name,
                        timeFrom: booking.time_from,
                        timeTo: booking.time_to,
                        duration: booking.duration_display
                    }
                };
            }),
            eventClick: function(info) {
                showBookingModal(info.event);
            },
            eventDidMount: function(info) {
                const props = info.event.extendedProps;
                
                // Remove any existing dots
                const dots = info.el.querySelector('.fc-daygrid-event-dot');
                if (dots) {
                    dots.style.display = 'none';
                }
                
                // Only show duration if it's not empty (not whole day) and in month view
                if (props.duration && props.duration.trim() !== '' && info.view.type === 'dayGridMonth') {
                    const eventEl = info.el;
                    const titleEl = eventEl.querySelector('.fc-event-title');
                    if (titleEl) {
                        const durationEl = document.createElement('div');
                        durationEl.className = 'event-duration';
                        durationEl.textContent = props.duration;
                        durationEl.style.color = 'inherit';
                        eventEl.appendChild(durationEl);
                    }
                }
                
                // Enhanced tooltip
                info.el.title = `${props.refCode} - ${props.facility}\nClient: ${props.client}\nStatus: ${props.status}${props.duration && props.duration.trim() !== '' ? '\nDuration: ' + props.duration : ''}\nPaid: ₱${parseFloat(props.paidAmount).toFixed(2)}`;
            }
        });
        
        calendar.render();

        // Modal Functions
        function showBookingModal(event) {
            const props = event.extendedProps;
            const modal = document.getElementById('bookingModal');
            const modalBody = document.getElementById('modalBody');
            
            const statusBadgeClass = ['status-pending', 'status-confirmed', 'status-completed', 'status-cancelled'][props.statusCode];
            
            modalBody.innerHTML = `
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Reference Code:</span>
                    <span class="booking-detail-value">${props.refCode}</span>
                </div>
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Facility:</span>
                    <span class="booking-detail-value">${props.facility}</span>
                </div>
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Category:</span>
                    <span class="booking-detail-value">${props.category}</span>
                </div>
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Client:</span>
                    <span class="booking-detail-value">${props.client}</span>
                </div>
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Date:</span>
                    <span class="booking-detail-value">${event.startStr} ${event.endStr && event.endStr !== event.startStr ? ' to ' + event.endStr : ''}</span>
                </div>
                ${props.timeFrom && props.timeTo ? `
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Time:</span>
                    <span class="booking-detail-value">${formatTime(props.timeFrom)} - ${formatTime(props.timeTo)}</span>
                </div>
                ` : ''}
                ${props.duration && props.duration.trim() !== '' ? `
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Duration:</span>
                    <span class="booking-detail-value">${props.duration}</span>
                </div>
                ` : ''}
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Status:</span>
                    <span class="booking-detail-value">
                        <span class="booking-status-badge ${statusBadgeClass}">${props.status}</span>
                    </span>
                </div>
                <div class="booking-detail-row">
                    <span class="booking-detail-label">Paid Amount:</span>
                    <span class="booking-detail-value">₱${parseFloat(props.paidAmount).toFixed(2)}</span>
                </div>
            `;
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function formatTime(timeStr) {
            if (!timeStr) return 'N/A';
            const [hours, minutes] = timeStr.split(':');
            const hour = parseInt(hours);
            const period = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${period}`;
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Navigation functionality
      // Navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const navButtons = document.querySelectorAll('.nav-btn');
    const headerHeight = document.querySelector('.dashboard-header-section').offsetHeight;
    
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                // Remove active class from all buttons
                navButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Smooth scroll to target with offset for fixed header - CUSTOM FOR EACH SECTION
                let offsetTop;
                
                switch(targetId) {
                    case 'stats':
                        offsetTop = targetElement.offsetTop - headerHeight - 10;
                        break;
                    case 'booking-status':
                        offsetTop = targetElement.offsetTop - headerHeight + 40;
                        break;
                    case 'facilities':
                        offsetTop = targetElement.offsetTop - headerHeight + 85;
                        break;
                    case 'trends':
                        offsetTop = targetElement.offsetTop - headerHeight + 40;
                        break;
                    case 'calendar':
                        // For calendar, scroll to the actual calendar grid
                        const calendarGrid = document.querySelector('#bookingCalendar');
                        offsetTop = calendarGrid.offsetTop - headerHeight + 160;
                        break;
                    case 'events':
                        offsetTop = targetElement.offsetTop - headerHeight + 150;
                        break;
                    case 'activity':
                        offsetTop = targetElement.offsetTop - headerHeight - 10;
                        break;
                    default:
                        offsetTop = targetElement.offsetTop - headerHeight - 20;
                }

                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Highlight active section based on scroll position
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('[id]');
        let currentSection = '';
        const headerHeight = document.querySelector('.dashboard-header-section').offsetHeight;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - headerHeight - 50;
            const sectionHeight = section.clientHeight;
            if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
                currentSection = section.getAttribute('id');
            }
        });
        
        navButtons.forEach(button => {
            button.classList.remove('active');
            if (button.getAttribute('data-target') === currentSection) {
                button.classList.add('active');
            }
        });
    });
});
    </script>
</body>
</html>