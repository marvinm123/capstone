 <?php
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
$events_qry = $conn->query("SELECT * FROM date_events ORDER BY event_date DESC");
while ($row = $events_qry->fetch_assoc()) {
    $date_events[] = $row;
}

// Get booking status counts for stats
$status_counts_qry = $conn->query("SELECT 
    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as done
    FROM booking_list WHERE status != 3");
$status_counts = $status_counts_qry->fetch_assoc();
?>

<div class="card card-outline card-primary">
    <div class="card-header modern-header">
        <div class="header-content">
            <div class="header-icon-wrapper">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="header-text">
                <h3 class="header-title">Booking Calendar Overview</h3>
                <p class="header-subtitle">Manage and view all facility bookings and events</p>
            </div>
        </div>
        <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Calendar Stats Summary - Enhanced Design -->
        <div class="calendar-stats mb-4">
            <div class="calendar-stat-item pending">
                <div class="calendar-stat-value"><?= $status_counts['pending'] ?></div>
                <div class="calendar-stat-label">Pending</div>
            </div>
            <div class="calendar-stat-item confirmed">
                <div class="calendar-stat-value"><?= $status_counts['confirmed'] ?></div>
                <div class="calendar-stat-label">Confirmed</div>
            </div>
            <div class="calendar-stat-item completed">
                <div class="calendar-stat-value"><?= $status_counts['done'] ?></div>
                <div class="calendar-stat-label">Completed</div>
            </div>
            <div class="calendar-stat-item events">
                <div class="calendar-stat-value"><?= count($date_events) ?></div>
                <div class="calendar-stat-label">Date Events</div>
            </div>
        </div>

        <!-- Calendar Container with Enhanced Design -->
        <div class="calendar-overview-card">
            <div class="chart-header">
                <div class="chart-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="chart-title">Interactive Booking Calendar</h3>
            </div>
            
            <!-- Calendar -->
            <div id="bookingCalendar"></div>

            <!-- Enhanced Legend -->
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
                <div class="calendar-legend-item">
                    <div class="calendar-legend-color" style="background: #8b5cf6;"></div>
                    <span>General Notices</span>
                </div>
                <div class="calendar-legend-item">
                    <div class="calendar-legend-color" style="background: #28a745;"></div>
                    <span>Holidays</span>
                </div>
                <div class="calendar-legend-item">
                    <div class="calendar-legend-color" style="background: #dc3545;"></div>
                    <span>Maintenance</span>
                </div>
                <div class="calendar-legend-item">
                    <div class="calendar-legend-color" style="background: #06b6d4;"></div>
                    <span>Special Events</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading overlay for calendar actions -->
<div class="calendar-loading-overlay" id="calendarLoadingOverlay">
    <div class="calendar-loader">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    <div class="calendar-loading-text" id="calendarLoadingText">Processing...</div>
</div>

<!-- Modal for Booking Details -->
<div class="modal-overlay" id="bookingModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-calendar-check"></i>
                Booking Details
            </h3>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Modal content will be populated by JavaScript -->
        </div>
    </div>
</div>

<!-- Modal for Date Event Management -->
<div class="modal-overlay" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-calendar-plus"></i>
                <span id="eventModalTitle">Add Date Event</span>
            </h3>
        </div>
        <form id="eventForm">
            <div class="modal-body">
                <input type="hidden" name="id" id="event_id">
                <input type="hidden" name="event_date" id="event_date">
                
                <div class="form-group">
                    <label for="event_title" class="form-label">Event Title</label>
                    <input type="text" class="form-control" id="event_title" name="title" required placeholder="Enter event title">
                </div>
                
                <div class="form-group">
                    <label for="event_type" class="form-label">Event Type</label>
                    <select class="form-control" id="event_type" name="event_type" required>
                        <option value="notice">General Notice</option>
                        <option value="holiday">Holiday</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="special_event">Special Event</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="event_description" class="form-label">Description</label>
                    <textarea class="form-control" id="event_description" name="description" rows="4" placeholder="Enter event description (visible to clients)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;">
                    <i class="fas fa-trash mr-2"></i>Delete Event
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Save Event
                </button>
            </div>
        </form>
    </div>
</div>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--gray-bg);
        color: var(--text-main);
        font-size: 0.95rem;
        line-height: 1.6;
    }
/* Enhanced Header Design */
.modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    padding: 2rem 1.5rem !important;
    border-radius: 20px 20px 0 0 !important;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.header-icon-wrapper {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.header-text {
    flex: 1;
}

.header-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: white;
    margin: 0;
    letter-spacing: 0.5px;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-family: 'Segoe UI', system-ui, sans-serif;
}

.header-subtitle {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0.3rem 0 0 0;
    font-weight: 400;
}

/* Enhanced Calendar Styles */
.calendar-overview-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-top: 1rem;
}

.calendar-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.calendar-stat-item {
    padding: 1.5rem;
    background: white;
    border-radius: 16px;
    text-align: center;
    border-left: 4px solid;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.calendar-stat-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.calendar-stat-item.pending { 
    border-left-color: #94a3b8;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}
.calendar-stat-item.confirmed { 
    border-left-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}
.calendar-stat-item.completed { 
    border-left-color: #eab308;
    background: linear-gradient(135deg, #fefce8, #fef08a);
}
.calendar-stat-item.events { 
    border-left-color: #8b5cf6;
    background: linear-gradient(135deg, #faf5ff, #e9d5ff);
}

.calendar-stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.calendar-stat-label {
    font-size: 0.9rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.chart-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.chart-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #667eea;
    margin-right: 1rem;
}

.chart-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    font-family: 'Plus Jakarta Sans', sans-serif;
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

/* Remove blue dots */
.fc-daygrid-event-dot {
    display: none !important;
    border: none !important;
}

.fc-event .fc-event-dot {
    display: none !important;
}

/* Updated colors */
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

/* Event type colors */
.fc-event.notice-event {
    background-color: #e9d5ff !important;
    color: #6b21a8 !important;
    border-left: 4px solid #7c3aed !important;
}

.fc-event.holiday-event {
    background-color: #dcfce7 !important;
    color: #166534 !important;
    border-left: 4px solid #16a34a !important;
}

.fc-event.maintenance-event {
    background-color: #fee2e2 !important;
    color: #991b1b !important;
    border-left: 4px solid #dc2626 !important;
}

.fc-event.special_event-event {
    background-color: #cffafe !important;
    color: #0e7490 !important;
    border-left: 4px solid #06b6d4 !important;
}

/* Calendar list view styles */
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

/* Remove "All day" text from list view */
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

/* Calendar Header Enhancement */
.fc {
    border-radius: 12px;
    overflow: hidden;
}

.fc .fc-toolbar-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #0f172a;
}

.fc .fc-button {
    background-color: #667eea;
    border: none;
    padding: 8px 16px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.fc .fc-button:hover {
    background-color: #5568d3;
    transform: translateY(-1px);
}

.fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: #5568d3;
}

.fc .fc-daygrid-day.fc-day-today {
    background-color: rgba(79, 70, 229, 0.1);
}

/* Disabled date styling */
.fc .fc-daygrid-day.date-has-events {
    cursor: default;
    background-color: rgba(148, 163, 184, 0.05);
}

.fc .fc-daygrid-day.past-date {
    cursor: default;
    background-color: rgba(148, 163, 184, 0.03);
    opacity: 0.6;
}

/* Fix cursor for all calendar interactions */
.fc-daygrid-day {
    cursor: pointer;
}

.fc-daygrid-day.date-has-events.has-booking {
    cursor: default;
}

.fc-daygrid-day.past-date {
    cursor: default;
}

.fc-event {
    cursor: pointer !important;
}

/* Calendar Legend */
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
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.show {
    opacity: 1;
}

.modal-overlay.closing {
    opacity: 0;
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
    cursor: default;
    opacity: 0;
    transform: translate(-50%, -60%);
    transition: all 0.3s ease;
}

.modal-overlay.show .modal-content {
    opacity: 1;
    transform: translate(-50%, -50%);
}

.modal-overlay.closing .modal-content {
    opacity: 0;
    transform: translate(-50%, -60%);
}

/* Loading overlay for calendar actions */
.calendar-loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    backdrop-filter: blur(4px);
}

.calendar-loading-overlay.active {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.calendar-loader {
    width: 60px;
    height: 60px;
    position: relative;
}

.calendar-loader .circle {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 4px solid transparent;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.calendar-loader .circle:nth-child(2) {
    border: 4px solid transparent;
    border-top: 4px solid #764ba2;
    animation: spin 1.5s linear infinite reverse;
    width: 80%;
    height: 80%;
    top: 10%;
    left: 10%;
}

.calendar-loader .circle:nth-child(3) {
    border: 4px solid transparent;
    border-top: 4px solid #8b5cf6;
    animation: spin 2s linear infinite;
    width: 60%;
    height: 60%;
    top: 20%;
    left: 20%;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.calendar-loading-text {
    color: white;
    margin-top: 20px;
    font-size: 1.2rem;
    font-weight: 500;
    text-align: center;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { opacity: 0.7; }
    50% { opacity: 1; }
    100% { opacity: 0.7; }
}

.modal-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
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
    color: #64748b;
}

.booking-detail-value {
    font-weight: 500;
    color: #0f172a;
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

.status-pending {
    background: #e2e8f0;
    color: #1e293b;
}

.status-confirmed {
    background: #dbeafe;
    color: #1e40af;
}

.status-completed {
    background: #fef08a;
    color: #854d0e;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

.form-control {
    width: 100%;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    background: white;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

select.form-control {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Cpath fill='%23667eea' d='M8 11L3 6h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 14px;
    padding-right: 3rem;
    cursor: pointer;
    height: auto;
    min-height: 45px;
    line-height: 1.5;
    color: #0f172a;
}

select.form-control:hover {
    border-color: #667eea;
}

select.form-control option {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    color: #0f172a;
    background: white;
    line-height: 1.5;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #5568d3);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

@media (max-width: 768px) {
    .header-content {
        gap: 1rem;
    }
    
    .header-icon-wrapper {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .header-title {
        font-size: 1.4rem;
    }
    
    .header-subtitle {
        font-size: 0.85rem;
    }
    
    .calendar-stats {
        grid-template-columns: 1fr;
    }
    
    .calendar-legend {
        gap: 1rem;
    }
    
    .modal-content {
        width: 95%;
        padding: 1.5rem;
    }
    
    .chart-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .modal-footer {
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .modal-footer .btn {
        flex: 1;
        justify-content: center;
        min-width: 120px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarBookings = <?php echo json_encode($calendar_bookings) ?>;
    const dateEvents = <?php echo json_encode($date_events) ?>;
    const calendarEl = document.getElementById('bookingCalendar');
    let selectedDateForEvent = null;
    let editingEvent = null;
    
    // Build a map of dates that have bookings or events
    const datesWithBookings = new Set();
    const datesWithEvents = new Set();
    
    calendarBookings.forEach(booking => {
        // Add all dates in the booking range
        const start = new Date(booking.date_from);
        const end = new Date(booking.date_to);
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            datesWithBookings.add(d.toISOString().split('T')[0]);
        }
    });
    
    dateEvents.forEach(event => {
        datesWithEvents.add(event.event_date);
    });
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        height: 'auto',
        
        views: {
            listMonth: {
                listDaySideFormat: false
            }
        },
        
        events: [
            // Bookings
            ...calendarBookings.map(booking => {
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
                        duration: booking.duration_display,
                        type: 'booking'
                    }
                };
            }),
            // Date Events
            ...dateEvents.map(event => {
                return {
                    id: 'event_' + event.id,
                    title: event.title,
                    start: event.event_date,
                    allDay: true,
                    classNames: [event.event_type + '-event'],
                    extendedProps: {
                        eventId: event.id,
                        description: event.description,
                        eventType: event.event_type,
                        type: 'date_event'
                    }
                };
            })
        ],
        
        eventClick: function(info) {
            if (info.event.extendedProps.type === 'booking') {
                showBookingModal(info.event);
            } else if (info.event.extendedProps.type === 'date_event') {
                showEventModal(info.event);
            }
        },
        
        dateClick: function(info) {
            const clickedDate = info.dateStr;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const clickedDateObj = new Date(clickedDate);
            
            // Block past dates
            if (clickedDateObj < today) {
                return;
            }
            
            // If date has an event, show that event for editing
            if (datesWithEvents.has(clickedDate)) {
                // Find the event for this date
                const eventForDate = dateEvents.find(e => e.event_date === clickedDate);
                if (eventForDate) {
                    // Create a mock event object to pass to showEventModal
                    const mockEvent = {
                        startStr: eventForDate.event_date,
                        title: eventForDate.title,
                        extendedProps: {
                            eventId: eventForDate.id,
                            description: eventForDate.description,
                            eventType: eventForDate.event_type,
                            type: 'date_event'
                        }
                    };
                    showEventModal(mockEvent);
                }
                return;
            }
            
            // If date has bookings, do nothing (ignore the click)
            if (datesWithBookings.has(clickedDate)) {
                return;
            }
            
            // If date is available, show event modal for creating new event
            selectedDateForEvent = clickedDate;
            showEventModal(null, clickedDate);
        },
        
        dayCellDidMount: function(info) {
            const dateStr = info.date.toISOString().split('T')[0];
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const cellDate = new Date(dateStr);
            
            // Mark past dates
            if (cellDate < today) {
                info.el.classList.add('past-date');
            }
            
            // Add visual indicator and cursor styling
            if (datesWithBookings.has(dateStr)) {
                info.el.classList.add('date-has-events', 'has-booking');
            } else if (datesWithEvents.has(dateStr)) {
                info.el.classList.add('date-has-events');
            }
        },
        
        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            
            // Remove any existing dots
            const dots = info.el.querySelector('.fc-daygrid-event-dot');
            if (dots) {
                dots.style.display = 'none';
            }
            
            // Only show duration if it's not empty (not whole day) and in month view
            if (props.duration && props.duration.trim() !== '' && info.view.type === 'dayGridMonth' && props.type === 'booking') {
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
            if (props.type === 'booking') {
                info.el.title = `${props.refCode} - ${props.facility}\nClient: ${props.client}\nStatus: ${props.status}${props.duration && props.duration.trim() !== '' ? '\nDuration: ' + props.duration : ''}\nPaid: ₱${parseFloat(props.paidAmount).toFixed(2)}`;
            } else if (props.type === 'date_event') {
                info.el.title = `${props.eventType.toUpperCase()}: ${info.event.title}\n${props.description || 'No description'}`;
            }
        }
    });
    
    calendar.render();

    // Helper functions for loading overlay
    function showCalendarLoading(message) {
        document.getElementById('calendarLoadingText').textContent = message || 'Processing...';
        document.getElementById('calendarLoadingOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function hideCalendarLoading() {
        document.getElementById('calendarLoadingOverlay').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

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
        // Trigger animation smoothly
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                modal.classList.add('show');
            });
        });
    }

    function showEventModal(event, date = null) {
        const modal = document.getElementById('eventModal');
        const form = document.getElementById('eventForm');
        const deleteBtn = document.getElementById('deleteEventBtn');
        const modalTitle = document.getElementById('eventModalTitle');
        
        if (event) {
            // Editing existing event
            editingEvent = event;
            const props = event.extendedProps;
            
            modalTitle.textContent = 'Edit Date Event';
            document.getElementById('event_id').value = props.eventId;
            document.getElementById('event_date').value = event.startStr;
            document.getElementById('event_title').value = event.title;
            document.getElementById('event_type').value = props.eventType;
            document.getElementById('event_description').value = props.description || '';
            
            deleteBtn.style.display = 'flex';
        } else {
            // Creating new event
            editingEvent = null;
            modalTitle.textContent = 'Add Date Event';
            document.getElementById('event_id').value = '';
            document.getElementById('event_date').value = date || selectedDateForEvent;
            document.getElementById('event_title').value = '';
            document.getElementById('event_type').value = 'notice';
            document.getElementById('event_description').value = '';
            
            deleteBtn.style.display = 'none';
        }
        
        modal.style.display = 'block';
        // Trigger animation smoothly
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                modal.classList.add('show');
            });
        });
    }

    // Event Form Submission
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const isEditing = document.getElementById('event_id').value !== '';
        
        // Close the modal first
        closeEventModal();
        
        // Show loading after modal closes (add delay)
        setTimeout(() => {
            showCalendarLoading(isEditing ? 'Updating event...' : 'Creating event...');
        }, 350);
        
        fetch('../classes/Master.php?f=save_date_event', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update loading message with longer delay
                setTimeout(() => {
                    document.getElementById('calendarLoadingText').textContent = 'Success! Refreshing...';
                }, 800);
                
                // Reload after longer delay (total 2 seconds from success)
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                hideCalendarLoading();
                alert('Error: ' + (data.msg || data.err || 'Unknown error'));
            }
        })
        .catch(error => {
            hideCalendarLoading();
            console.error('Error:', error);
            alert('Request failed: ' + error);
        });
    });

    // Delete Event
    document.getElementById('deleteEventBtn').addEventListener('click', function() {
        const eventId = document.getElementById('event_id').value;
        
        // Close modal first
        closeEventModal();
        
        // Show loading after modal closes (add delay)
        setTimeout(() => {
            showCalendarLoading('Deleting event...');
        }, 350);
        
        fetch('../classes/Master.php?f=delete_date_event', {
            method: 'POST',
            body: new URLSearchParams({ id: eventId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update loading message with longer delay
                setTimeout(() => {
                    document.getElementById('calendarLoadingText').textContent = 'Deleted! Refreshing...';
                }, 800);
                
                // Reload after longer delay (total 2 seconds from success)
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                hideCalendarLoading();
                alert('Error: ' + (data.msg || data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            hideCalendarLoading();
            console.error('Error:', error);
            alert('Error deleting event: ' + error);
        });
    });

    function closeModal() {
        const modal = document.getElementById('bookingModal');
        modal.classList.add('closing');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('closing');
        }, 300);
    }

    function closeEventModal() {
        const modal = document.getElementById('eventModal');
        modal.classList.add('closing');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('closing');
        }, 300);
    }

    function formatTime(timeStr) {
        if (!timeStr) return 'N/A';
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const period = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${period}`;
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        const bookingModal = document.getElementById('bookingModal');
        const eventModal = document.getElementById('eventModal');
        
        if (event.target === bookingModal) {
            closeModal();
        }
        if (event.target === eventModal) {
            closeEventModal();
        }
    });
});
</script>