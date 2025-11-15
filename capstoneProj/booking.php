<?php
require_once('./config.php');

if (isset($_GET['action']) && $_GET['action'] === 'load_bookings' && isset($_GET['fid']) && isset($_GET['date'])) {
    $fid = intval($_GET['fid']);
    $date = $conn->real_escape_string($_GET['date']);

    // First, get the category of the selected facility
    $categoryQuery = $conn->query("SELECT category_id FROM facility_list WHERE id = {$fid}");
    $categoryResult = $categoryQuery->fetch_assoc();
    $categoryId = $categoryResult['category_id'];

    // Get all facilities in the same category
    $facilitiesQuery = $conn->query("SELECT id FROM facility_list WHERE category_id = {$categoryId}");
    $facilityIds = [];
    while ($facilityRow = $facilitiesQuery->fetch_assoc()) {
        $facilityIds[] = $facilityRow['id'];
    }

    // Convert array to comma-separated string for SQL IN clause
    $facilityIdsStr = implode(',', $facilityIds);

    // Load bookings for ALL facilities in the same category (not cancelled) for the selected date
    $res = $conn->query("SELECT time_from, time_to, status FROM booking_list 
                        WHERE facility_id IN ({$facilityIdsStr}) 
                        AND status != 3 
                        AND date_from = '{$date}'");

    $events = [];
    $bookedTimeSlots = [];

    while ($row = $res->fetch_assoc()) {
        $events[] = [
            'start' => $row['time_from'],
            'end' => $row['time_to'],
            'display' => 'background',
            'color' => '#dc3545',
            'title' => 'Booked (Unavailable)'
        ];
        
        // Create array of all booked time slots (hour by hour)
        $startHour = intval(substr($row['time_from'], 0, 2));
        $endHour = intval(substr($row['time_to'], 0, 2));
        
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            $bookedTimeSlots[] = sprintf("%02d:00", $hour);
        }
    }

    // Remove duplicates
    $bookedTimeSlots = array_unique($bookedTimeSlots);

    // Check if the date is fully booked (all time slots from 8:00 to 21:00 are booked)
    $allPossibleSlots = [];
    for ($hour = 8; $hour <= 21; $hour++) {
        $allPossibleSlots[] = sprintf("%02d:00", $hour);
    }
    
    $fullyBooked = count(array_diff($allPossibleSlots, $bookedTimeSlots)) === 0;

    // Load date events for the selected date
    $dateEvents = [];
    $eventsRes = $conn->query("SELECT * FROM date_events WHERE event_date = '{$date}'");
    while ($eventRow = $eventsRes->fetch_assoc()) {
        $dateEvents[] = [
            'id' => $eventRow['id'],
            'title' => $eventRow['title'],
            'description' => $eventRow['description'],
            'event_type' => $eventRow['event_type'],
            'color' => $eventRow['color']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode([
        'events' => $events,
        'bookedTimeSlots' => $bookedTimeSlots,
        'fullyBooked' => $fullyBooked,
        'dateEvents' => $dateEvents
    ]);
    exit;
}

// Function to check if times conflict with existing bookings in the same category
function checkCategoryBookingConflict($conn, $facilityId, $date, $timeFrom, $timeTo, $excludeBookingId = null)
{
    // Get the category of the facility
    $categoryQuery = $conn->query("SELECT category_id FROM facility_list WHERE id = {$facilityId}");
    $categoryResult = $categoryQuery->fetch_assoc();
    $categoryId = $categoryResult['category_id'];

    // Get all facilities in the same category
    $facilitiesQuery = $conn->query("SELECT id FROM facility_list WHERE category_id = {$categoryId}");
    $facilityIds = [];
    while ($facilityRow = $facilitiesQuery->fetch_assoc()) {
        $facilityIds[] = $facilityRow['id'];
    }

    $facilityIdsStr = implode(',', $facilityIds);

    // Check for conflicts with any booking in the same category
    $conflictQuery = "SELECT id FROM booking_list 
                     WHERE facility_id IN ({$facilityIdsStr}) 
                     AND status != 3 
                     AND date_from = '{$date}'
                     AND (
                         (time_from <= '{$timeFrom}' AND time_to >= '{$timeFrom}') OR
                         (time_from <= '{$timeTo}' AND time_to >= '{$timeTo}') OR
                         (time_from >= '{$timeFrom}' AND time_to <= '{$timeTo}')
                     )";

    if ($excludeBookingId) {
        $conflictQuery .= " AND id != {$excludeBookingId}";
    }

    $result = $conn->query($conflictQuery);
    return $result->num_rows > 0;
}
?>

<!-- Trigger Button -->
<button class="btn btn-primary booking-trigger-btn" id="openBookingBtn" style="display: block; margin: 0 auto;">
    <i class="fas fa-calendar-plus"></i>
    Book Facility
</button>

<!-- Enhanced Loading Overlay for Booking -->
<div class="booking-loading-overlay" id="bookingLoadingOverlay">
    <div class="booking-loader">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    <div class="booking-loading-text" id="bookingLoadingText">Processing your booking...</div>
</div>

<!-- Modal Structure -->
<div class="modal-overlay" id="booking-modal">
    <div class="modal-window">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-calendar-alt"></i>
                Facility Booking
            </h3>
            <button class="modal-close" id="close-modal">&times;</button>
        </div>

        <form action="" id="booking-form">
            <input type="hidden" name="id" value="<?= isset($id) ? $id : '' ?>">
            <input type="hidden" name="facility_id" value="<?= isset($_GET['fid']) ? $_GET['fid'] : (isset($facility_id) ? $facility_id : '') ?>">
            <input type="hidden" name="date_from" id="selected_date" value="">
            <input type="hidden" name="date_to" id="selected_date_end" value="">

            <div class="form-section">
                <!-- Category Info Display -->
                <div class="form-group">
                    <div class="category-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Note: Booking times will be blocked for all facilities in this category</span>
                    </div>
                </div>

                <!-- Two-Column Layout -->
                <div class="booking-layout-container">
                    <!-- Left Column - Calendar -->
                    <div class="booking-left-column">
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fas fa-calendar"></i>
                                Select Date
                            </label>
                            <div id="booking-calendar"></div>
                        </div>
                    </div>

                    <!-- Right Column - Time Selection -->
                    <div class="booking-right-column">
                        <!-- Date Display -->
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fas fa-calendar-day"></i>
                                Selected Date
                            </label>
                            <div id="selected-date-display" class="selected-date-box">
                                Please select a date from the calendar
                            </div>
                        </div>

                        <!-- Date Events Display -->
                        <div id="date-events-container" class="date-events-wrapper" style="display: none;">
                            <div class="date-events-header">
                                <h4><i class="fas fa-info-circle"></i> Date Notices</h4>
                            </div>
                            <div id="date-events-content" class="date-events-content"></div>
                        </div>

                        <!-- Time Range Selector -->
                        <div class="form-group">
                            <label class="control-label">
                                <i class="fas fa-clock"></i> Time Range
                            </label>
                            <div class="time-range-container">
                                <select name="time_from" id="time_from" class="form-control time-select" required>
                                    <option value="">From</option>
                                    <?php
                                    for ($hour = 8; $hour <= 21; $hour++) {
                                        $time = sprintf("%02d:00", $hour);
                                        $display_time = date("g:i A", strtotime($time));
                                        echo "<option value='$time'>$display_time</option>";
                                    }
                                    ?>
                                </select>
                                <span class="time-separator">to</span>
                                <select name="time_to" id="time_to" class="form-control time-select" required>
                                    <option value="">To</option>
                                    <?php
                                    for ($hour = 9; $hour <= 22; $hour++) {
                                        $time = sprintf("%02d:00", $hour);
                                        $display_time = date("g:i A", strtotime($time));
                                        echo "<option value='$time'>$display_time</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Booked Time Slots Display -->
                        <div id="time-slots-container" class="time-slots-wrapper">
                            <div class="time-slots-header">
                                <h4><i class="fas fa-calendar-day"></i> Booked Time Slots (Unavailable)</h4>
                            </div>
                            <div id="time-slots-grid" class="time-slots-grid"></div>
                            <div id="no-bookings-message" class="no-bookings-message" style="display: none;">
                                <i class="fas fa-check-circle"></i> No bookings for this date - All times available
                            </div>
                        </div>

                        <!-- Legend Section -->
                        <div class="legend-section">
                            <h4 class="legend-title">
                                <i class="fas fa-info-circle"></i>
                                Time Slot Information
                            </h4>
                            <div class="legend-grid">
                                <div class="legend-item">
                                    <span class="legend-color booked-color"></span>
                                    <span class="legend-text">Booked Times (Red - Unavailable)</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color selected-color"></span>
                                    <span class="legend-text">Your Selection</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color fully-booked-color"></span>
                                    <span class="legend-text">Fully Booked Date</span>
                                </div>
                                <div class="legend-item">
                                    <span class="legend-color event-color"></span>
                                    <span class="legend-text">Date Events/Notices</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary submit-btn">
                    <i class="fas fa-check"></i>
                    Confirm Booking
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<!-- Enhanced Styles -->
<style>
    /* Modern Color Scheme */
    :root {
        --primary-color: #4f46e5;
        --primary-hover: #3730a3;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --info-color: #06b6d4;
        --event-color:  #f59e0b;
        --light-bg: #f8fafc;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --border-radius: 12px;
        --border-radius-lg: 16px;
    }

    /* Enhanced Event Blocked Modal - Matching Fully Booked Design */
    .event-blocked-modal-large {
        background: white;
        border-radius: var(--border-radius-lg);
        width: 600px;
        max-width: 90vw;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: var(--shadow-lg);
        animation: slideIn 0.3s ease;
    }

    .event-blocked-modal-large .event-modal-header {
        background: linear-gradient(135deg, var(--warning-color), #d97706);
        padding: 20px 24px;
        border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }

    .event-details-section {
        margin: 0;
        text-align: left;
    }

    .event-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .event-items-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 20px;
    }

    .event-detail-card {
        background: white;
        border: 2px solid var(--border-color);
        border-left: 4px solid var(--event-color);
        border-radius: var(--border-radius);
        padding: 16px;
        box-shadow: var(--shadow-md);
        transition: all 0.2s ease;
    }

    .event-detail-card:hover {
        transform: translateX(4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .event-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 12px;
        margin-bottom: 8px;
    }

    .event-title {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 1.05rem;
        flex: 1;
    }

    .event-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .event-badge-holiday {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fbbf24;
    }

    .event-badge-maintenance {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .event-badge-special_event {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .event-badge-notice {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    .event-description {
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.5;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid var(--border-color);
    }

    .event-note-box {
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border: 1px solid #7dd3fc;
        border-radius: var(--border-radius);
        padding: 16px;
        display: flex;
        gap: 12px;
        align-items: start;
        color: #0c4a6e;
    }

    .event-note-box i {
        font-size: 1.3rem;
        color: var(--info-color);
        margin-top: 2px;
    }

    .event-modal-footer {
        padding: 20px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: center;
    }

    .event-select-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-md);
    }

    .event-select-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: linear-gradient(135deg, var(--primary-hover), #312e81);
    }

    /* Fully Booked Modal - Similar Design */
    .fully-booked-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        animation: fadeIn 0.3s ease;
    }

    .fully-booked-modal {
        background: white;
        border-radius: var(--border-radius-lg);
        width: 500px;
        max-width: 90vw;
        box-shadow: var(--shadow-lg);
        animation: slideIn 0.3s ease;
    }

    .fully-booked-header {
        background: linear-gradient(135deg, var(--danger-color), #dc2626);
        padding: 20px 24px;
        border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }

    .fully-booked-title {
        font-size: 1.4rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .fully-booked-body {
        padding: 32px 24px;
        text-align: center;
    }

    .fully-booked-icon {
        font-size: 4rem;
        color: var(--danger-color);
        margin-bottom: 20px;
    }

    .fully-booked-message {
        color: var(--text-primary);
        font-size: 1.1rem;
        margin-bottom: 12px;
        font-weight: 600;
    }

    .fully-booked-submessage {
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .fully-booked-footer {
        padding: 20px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: center;
    }

    /* Date Events Styles */
    .date-events-wrapper {
        border: 2px solid var(--event-color);
        border-radius: var(--border-radius);
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        margin-bottom: 16px;
        overflow: hidden;
    }

    .date-events-header {
        background: var(--event-color);
        padding: 12px 16px;
        color: white;
    }

    .date-events-header h4 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }

    .date-events-content {
        padding: 16px;
    }

    .date-event-item {
        background: white;
        border-radius: var(--border-radius);
        padding: 12px;
        margin-bottom: 8px;
        border-left: 4px solid var(--event-color);
        box-shadow: var(--shadow-md);
    }

    .date-event-item:last-child {
        margin-bottom: 0;
    }

    .event-type-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .event-type-holiday {
        background: #fef3c7;
        color: #92400e;
    }

    .event-type-maintenance {
        background: #fee2e2;
        color: #991b1b;
    }

    .event-type-special_event {
        background: #d1fae5;
        color: #065f46;
    }

    .event-type-notice {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Enhanced Loading Overlay for Booking */
    .booking-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        backdrop-filter: blur(4px);
    }

    .booking-loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .booking-loader {
        width: 60px;
        height: 60px;
        position: relative;
        margin-bottom: 20px;
    }

    .booking-loader .circle {
        position: absolute;
        width: 100%;
        height: 100%;
        border: 4px solid transparent;
        border-top: 4px solid #4f46e5;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .booking-loader .circle:nth-child(2) {
        border: 4px solid transparent;
        border-top: 4px solid #10b981;
        animation: spin 1.5s linear infinite reverse;
        width: 80%;
        height: 80%;
        top: 10%;
        left: 10%;
    }

    .booking-loader .circle:nth-child(3) {
        border: 4px solid transparent;
        border-top: 4px solid #f59e0b;
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

    .booking-loading-text {
        color: white;
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

    /* Fixed Calendar Styling */
    #booking-calendar {
        margin-top: 10px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        min-height: 300px;
        height: auto !important;
    }

    .fc {
        height: auto !important;
    }

    .fc .fc-view-harness {
        height: auto !important;
    }

    .fc .fc-daygrid {
        height: auto !important;
    }

    .fc .fc-toolbar-title {
        font-size: 1.2rem;
        color: var(--text-primary);
    }

    .fc .fc-button {
        background-color: var(--primary-color);
        border: none;
        padding: 6px 12px;
        font-size: 0.9rem;
    }

    .fc .fc-button:hover {
        background-color: var(--primary-hover);
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background-color: var(--primary-hover);
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(79, 70, 229, 0.1);
    }

    .fc .fc-daygrid-day-top {
        padding: 4px;
    }

    .fc .fc-daygrid-day-number {
        font-weight: 600;
        position: relative;
        z-index: 2;
    }

    .fc .fc-daygrid-day.fc-day-disabled {
        background-color: #f8f9fa;
    }

    /* Date Event Styling in Calendar */
    .fc .fc-daygrid-day.has-event {
        background-color: rgba(139, 92, 246, 0.1) !important;
        position: relative;
    }

    .fc .fc-daygrid-day.has-event .fc-daygrid-day-number {
        color: #7c3aed !important;
        font-weight: bold !important;
    }

    .fc .fc-daygrid-day.has-event::before {
        content: '●';
        position: absolute;
        top: 2px;
        right: 4px;
        color: #8b5cf6;
        font-size: 12px;
        z-index: 3;
    }

    /* Enhanced Event Blocked Date Styling */
    .fc .fc-daygrid-day.event-blocked-date {
        background-color: #fef3c7 !important;
        position: relative;
        cursor: not-allowed !important;
    }

    .fc .fc-daygrid-day.event-blocked-date .fc-daygrid-day-number {
        color: #92400e !important;
        font-weight: bold !important;
    }

    .fc .fc-daygrid-day.event-blocked-date::before {
        content: '🚫';
        position: absolute;
        top: 2px;
        right: 2px;
        font-size: 10px;
        z-index: 3;
    }

    .fc .fc-daygrid-day.event-blocked-date::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(245, 158, 11, 0.3);
        pointer-events: none;
        z-index: 1;
    }

    /* Enhanced Fully Booked Date Styling */
    .fc .fc-daygrid-day.fully-booked-date {
        background-color: #fee2e2 !important;
        position: relative;
        cursor: not-allowed !important;
    }

    .fc .fc-daygrid-day.fully-booked-date .fc-daygrid-day-number {
        color: #b91c1c !important;
        font-weight: bold !important;
    }

    .fc .fc-daygrid-day.fully-booked-date::before {
        content: 'FULL';
        position: absolute;
        top: 2px;
        right: 2px;
        background-color: #dc2626;
        color: white;
        font-size: 8px;
        padding: 1px 3px;
        border-radius: 3px;
        font-weight: bold;
        z-index: 3;
    }

    .fc .fc-daygrid-day.fully-booked-date::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(220, 53, 69, 0.3);
        pointer-events: none;
        z-index: 1;
    }

    /* Partially booked dates */
    .fc .fc-daygrid-day.partially-booked-date {
        background-color: #fef3c7 !important;
        position: relative;
    }

    .fc .fc-daygrid-day.partially-booked-date .fc-daygrid-day-number {
        color: #92400e !important;
        font-weight: 600;
    }

    .fc .fc-daygrid-day.partially-booked-date::before {
        content: '●';
        position: absolute;
        top: 2px;
        right: 4px;
        color: #f59e0b;
        font-size: 12px;
        z-index: 3;
    }

    /* Trigger Button Enhancement */
    .booking-trigger-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
        border: none;
        padding: 12px 24px;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-md);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .booking-trigger-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: linear-gradient(135deg, var(--primary-hover), #312e81);
    }

    /* Modal Enhancements */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 100vw;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1050;
        animation: fadeIn 0.3s ease;
    }

    .modal-window {
        background: white;
        border-radius: var(--border-radius-lg);
        width: 900px;
        max-width: 95vw;
        max-height: 95vh;
        overflow-y: auto;
        box-shadow: var(--shadow-lg);
        position: relative;
        animation: slideIn 0.3s ease;
    }

    .modal-header {
        padding: 24px 24px 0 24px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        margin: 0;
        color: var(--text-primary);
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        border: none;
        background: none;
        color: var(--text-secondary);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .modal-close:hover {
        background: var(--light-bg);
        color: var(--danger-color);
    }

    .form-section {
        padding: 0 24px 24px 24px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .control-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    /* Category Info */
    .category-info {
        background: #e0f2fe;
        border: 1px solid #81d4fa;
        border-radius: var(--border-radius);
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #0277bd;
        font-size: 0.9rem;
    }

    /* Layout Container */
    .booking-layout-container {
        display: flex;
        gap: 24px;
        margin-bottom: 24px;
    }

    /* Left Column (Calendar) */
    .booking-left-column {
        flex: 1;
        min-width: 400px;
    }

    /* Right Column (Time Selection) */
    .booking-right-column {
        flex: 1.2;
        min-width: 400px;
        background: #f9fafb;
        border-radius: var(--border-radius-lg);
        padding: 20px;
        box-shadow: var(--shadow-md);
    }

    /* Selected Date Display */
    .selected-date-box {
        background: white;
        border: 2px solid var(--primary-color);
        border-radius: var(--border-radius);
        padding: 12px 16px;
        font-weight: 600;
        color: var(--primary-color);
        text-align: center;
    }

    /* Time Range Selector */
    .time-range-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .time-select {
        background-color: white;
        border: 2px solid var(--border-color);
        transition: all 0.2s ease;
    }

    .time-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .time-select option:disabled {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .time-separator {
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.9rem;
    }

    /* Time Slots Display */
    .time-slots-wrapper {
        margin: 24px 0;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        background: white;
    }

    .time-slots-header {
        background: var(--light-bg);
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
    }

    .time-slots-header h4 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-primary);
        font-size: 1rem;
    }

    .time-slots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 8px;
        padding: 16px;
    }

    .time-slot {
        padding: 10px 8px;
        text-align: center;
        border-radius: var(--border-radius);
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }

    .time-slot.booked {
        background-color: #fee2e2;
        color: #b91c1c;
        border-color: #fca5a5;
        cursor: not-allowed;
    }

    .no-bookings-message {
        padding: 20px;
        text-align: center;
        background: #f0fdf4;
        border: 1px solid #86efac;
        color: #166534;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 16px;
        border-radius: var(--border-radius);
    }

    /* Legend Section */
    .legend-section {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: 16px;
        margin-top: 16px;
    }

    .legend-title {
        margin: 0 0 12px 0;
        color: var(--text-primary);
        font-size: 0.95rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .legend-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }

    .booked-color {
        background: var(--danger-color);
    }

    .selected-color {
        background: var(--primary-color);
    }

    .fully-booked-color {
        background: #fee2e2;
        border: 1px solid #fca5a5;
    }

    .event-color {
        background: var(--event-color);
    }

    .legend-text {
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Submit Button */
    .submit-btn {
        width: 100%;
        background: linear-gradient(135deg, var(--success-color), #059669);
        border: none;
        padding: 14px 24px;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .submit-btn:hover::before {
        left: 100%;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: linear-gradient(135deg, #059669, #047857);
    }

    /* Error Messages */
    .alert {
        padding: 12px 16px;
        border-radius: var(--border-radius);
        margin-bottom: 16px;
        border-left: 4px solid;
        font-weight: 500;
        animation: slideDown 0.4s ease-out;
    }

    .alert-danger {
        background-color: #fef2f2;
        border-left-color: var(--danger-color);
        color: #991b1b;
    }

    .alert-success {
        background-color: #f0fdf4;
        border-left-color: var(--success-color);
        color: #166534;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .modal-window {
            width: 95vw;
            margin: 10px;
        }

        .booking-layout-container {
            flex-direction: column;
        }

        .booking-left-column,
        .booking-right-column {
            min-width: 100%;
        }

        .time-slots-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        }

        .time-range-container {
            flex-direction: column;
            align-items: stretch;
        }

        .time-separator {
            text-align: center;
            margin: 8px 0;
        }

        .event-blocked-modal,
        .fully-booked-modal {
            width: 95vw;
        }
    }
</style>

<!-- Required Libraries -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<script>
    $(document).ready(function() {

    const modal = $('#booking-modal');
    let selectedDate = null;
    let bookedTimes = [];
    let calendar = null;
    let fullyBookedDates = new Set();
    let eventBlockedDates = new Set();
    let dateEvents = new Map();

    // Function to show booking loading overlay
    function showBookingLoading(message = 'Processing your booking...') {
        $('#bookingLoadingText').text(message);
        $('#bookingLoadingOverlay').addClass('active');
        $('body').css('overflow', 'hidden');
    }

    // Function to hide booking loading overlay
    function hideBookingLoading() {
        $('#bookingLoadingOverlay').removeClass('active');
        $('body').css('overflow', 'auto');
    }

    // Enhanced event blocking modal - same design as fully booked
    function showEventBlockedModal(date, events) {
        const formattedDate = new Date(date + 'T12:00:00').toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Create event details HTML
        const eventDetailsHtml = events.map(event => `
            <div class="event-detail-card">
                <div class="event-card-header">
                    <span class="event-title">${event.title}</span>
                    <span class="event-badge event-badge-${event.event_type}">
                        ${event.event_type.replace('_', ' ').toUpperCase()}
                    </span>
                </div>
                ${event.description ? `
                    <div class="event-description">
                        ${event.description}
                    </div>
                ` : ''}
            </div>
        `).join('');
        
        // Create modal matching fully-booked design
        const modalHtml = `
            <div class="fully-booked-overlay" id="eventBlockedOverlay">
                <div class="event-blocked-modal-large">
                    <div class="event-modal-header">
                        <div class="fully-booked-title">
                            <i class="fas fa-calendar-times"></i>
                            Date Not Available
                        </div>
                        <button class="event-modal-close" id="closeEventModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="fully-booked-body">
                        <i class="fas fa-ban fully-booked-icon" style="color: var(--warning-color);"></i>
                        <div class="fully-booked-message">
                            ${formattedDate}
                        </div>
                        <div class="fully-booked-submessage" style="margin-bottom: 24px;">
                            This date has scheduled events and cannot be booked.
                        </div>
                        
                        <div class="event-details-section">
                            <h4 class="event-section-title">
                                <i class="fas fa-info-circle"></i>
                                Event Details:
                            </h4>
                            <div class="event-items-container">
                                ${eventDetailsHtml}
                            </div>
                        </div>
                        
                        <div class="event-note-box">
                            <i class="fas fa-lightbulb"></i>
                            <div>
                                Please select another available date for your booking.
                            </div>
                        </div>
                    </div>
                    
                    <div class="fully-booked-footer">
                        <button class="event-select-btn" id="selectDifferentDate">
                            <i class="fas fa-calendar-day"></i>
                            Select Different Date
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#eventBlockedOverlay').remove();
        
        // Add new modal to body
        $('body').append(modalHtml);
        
        // Show the modal with animation
        setTimeout(() => {
            $('#eventBlockedOverlay').css('display', 'flex');
        }, 10);
        
        // Close button handler
        $('#closeEventModal, #selectDifferentDate').click(function() {
            $('#eventBlockedOverlay').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Click outside to close
        $('#eventBlockedOverlay').click(function(e) {
            if ($(e.target).is('#eventBlockedOverlay')) {
                $(this).fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }

    // Enhanced fully booked modal
    function showFullyBookedModal(date) {
        const formattedDate = new Date(date + 'T12:00:00').toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const modalHtml = `
            <div class="fully-booked-overlay" id="fullyBookedOverlay">
                <div class="fully-booked-modal">
                    <div class="fully-booked-header">
                        <div class="fully-booked-title">
                            <i class="fas fa-ban"></i>
                            Fully Booked
                        </div>
                        <button class="event-modal-close" id="closeFullyBookedModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="fully-booked-body">
                        <i class="fas fa-calendar-times fully-booked-icon"></i>
                        <div class="fully-booked-message">
                            ${formattedDate}
                        </div>
                        <div class="fully-booked-submessage">
                            All time slots for this date are fully booked. Please select a different date with available time slots.
                        </div>
                    </div>
                    
                    <div class="fully-booked-footer">
                        <button class="event-select-btn" id="selectDifferentDateFull">
                            <i class="fas fa-calendar-day"></i>
                            Select Different Date
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#fullyBookedOverlay').remove();
        $('body').append(modalHtml);
        
        setTimeout(() => {
            $('#fullyBookedOverlay').css('display', 'flex');
        }, 10);
        
        $('#closeFullyBookedModal, #selectDifferentDateFull').click(function() {
            $('#fullyBookedOverlay').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        $('#fullyBookedOverlay').click(function(e) {
            if ($(e.target).is('#fullyBookedOverlay')) {
                $(this).fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }

    $('#openBookingBtn').click(function() {
        modal.fadeIn(300);
        setTimeout(() => {
            initCalendar();
        }, 100);
    });

    $('#close-modal').click(function() {
        modal.fadeOut(300);
    });

    function initCalendar() {
        const calendarEl = document.getElementById('booking-calendar');

        if (calendar) {
            calendar.destroy();
        }

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            height: 'auto',
            selectable: true,
            dateClick: function(info) {
                const clickedDate = new Date(info.dateStr + 'T12:00:00');
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Check if date is in the past
                if (clickedDate < today) {
                    showPastDateWarning();
                    return;
                }

                const dateStr = formatDateForComparison(clickedDate);
                
                // Priority 1: Check if date has an event (blocked)
                if (eventBlockedDates.has(dateStr)) {
                    const events = dateEvents.get(dateStr) || [];
                    showEventBlockedModal(dateStr, events);
                    return;
                }
                
                // Priority 2: Check if date is fully booked
                if (fullyBookedDates.has(dateStr)) {
                    showFullyBookedModal(dateStr);
                    return;
                }

                handleDateSelection(info.dateStr);
            },
            validRange: {
                start: new Date()
            },
            dayCellClassNames: function(arg) {
                const dateStr = formatDateForComparison(arg.date);
                const classes = [];
                
                // Priority to event-blocked dates
                if (eventBlockedDates.has(dateStr)) {
                    classes.push('event-blocked-date');
                } else if (fullyBookedDates.has(dateStr)) {
                    classes.push('fully-booked-date');
                }
                
                if (dateEvents.has(dateStr) && dateEvents.get(dateStr).length > 0) {
                    classes.push('has-event');
                }
                
                return classes;
            },
            datesSet: function(info) {
                preloadFullyBookedDates(info.start, info.end);
                preloadDateEvents(info.start, info.end);
                
                setTimeout(() => {
                    if (calendar) {
                        calendar.render();
                    }
                }, 200);
            }
        });

        calendar.render();
        
        setTimeout(() => {
            calendar.updateSize();
            calendar.render();
        }, 200);
    }

    // Enhanced past date warning
    function showPastDateWarning() {
        const modalHtml = `
            <div class="fully-booked-overlay" id="pastDateOverlay">
                <div class="fully-booked-modal">
                    <div class="event-modal-header" style="background: linear-gradient(135deg, var(--info-color), #0891b2);">
                        <div class="event-modal-title">
                            <i class="fas fa-clock"></i>
                            Date Selection
                        </div>
                        <button class="event-modal-close" id="closePastDateModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="fully-booked-body">
                        <i class="fas fa-calendar-times fully-booked-icon" style="color: var(--info-color);"></i>
                        <div class="fully-booked-message">
                            Cannot Select Past Dates
                        </div>
                        <div class="fully-booked-submessage">
                            You have selected a date that has already passed. Please choose a current or future date for your booking.
                        </div>
                    </div>
                    
                    <div class="fully-booked-footer">
                        <button class="event-select-btn" id="closePastDateBtn">
                            <i class="fas fa-check"></i>
                            OK
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#pastDateOverlay').remove();
        $('body').append(modalHtml);
        
        setTimeout(() => {
            $('#pastDateOverlay').css('display', 'flex');
        }, 10);
        
        $('#closePastDateModal, #closePastDateBtn').click(function() {
            $('#pastDateOverlay').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        $('#pastDateOverlay').click(function(e) {
            if ($(e.target).is('#pastDateOverlay')) {
                $(this).fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }

    function formatDateForComparison(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function preloadFullyBookedDates(startDate, endDate) {
        const facilityId = $('input[name="facility_id"]').val();
        if (!facilityId) return;

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const currentDate = new Date(startDate);
        const datesToCheck = [];

        while (currentDate <= endDate) {
            if (currentDate >= today) {
                const dateStr = formatDateForComparison(currentDate);
                datesToCheck.push(dateStr);
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }

        if (datesToCheck.length > 0) {
            checkMultipleDatesFullyBooked(facilityId, datesToCheck);
        }
    }

    function preloadDateEvents(startDate, endDate) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const currentDate = new Date(startDate);
        const datesToCheck = [];

        while (currentDate <= endDate) {
            if (currentDate >= today) {
                const dateStr = formatDateForComparison(currentDate);
                datesToCheck.push(dateStr);
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }

        if (datesToCheck.length > 0) {
            loadDateEventsForDates(datesToCheck);
        }
    }

    function loadDateEventsForDates(dates) {
        const facilityId = $('input[name="facility_id"]').val();
        if (!facilityId) return;

        dates.forEach(date => {
            $.ajax({
                url: 'booking.php',
                data: {
                    action: 'load_bookings',
                    fid: facilityId,
                    date: date
                },
                dataType: 'json',
                success: function(response) {
                    if (response.dateEvents && response.dateEvents.length > 0) {
                        dateEvents.set(date, response.dateEvents);
                        eventBlockedDates.add(date);
                    } else {
                        dateEvents.set(date, []);
                        eventBlockedDates.delete(date);
                    }
                    forceCalendarCellUpdate();
                }
            });
        });
    }

    function checkMultipleDatesFullyBooked(facilityId, dates) {
        dates.forEach(date => {
            $.ajax({
                url: 'booking.php',
                data: {
                    action: 'load_bookings',
                    fid: facilityId,
                    date: date
                },
                dataType: 'json',
                success: function(response) {
                    // Priority to event-blocked dates
                    if (response.dateEvents && response.dateEvents.length > 0) {
                        dateEvents.set(date, response.dateEvents);
                        eventBlockedDates.add(date);
                        fullyBookedDates.delete(date); // Remove from fully booked if it's event-blocked
                    } else {
                        eventBlockedDates.delete(date);
                        
                        if (response.fullyBooked) {
                            fullyBookedDates.add(date);
                        } else {
                            fullyBookedDates.delete(date);
                        }
                    }
                    forceCalendarCellUpdate();
                },
                error: function() {
                    // Continue even if there's an error
                }
            });
        });
    }

    function forceCalendarCellUpdate() {
        if (!calendar) return;
        
        setTimeout(() => {
            const calendarEl = calendar.el;
            const dateCells = calendarEl.querySelectorAll('[data-date]');
            
            dateCells.forEach(cell => {
                const cellDate = cell.getAttribute('data-date');
                if (cellDate) {
                    cell.classList.remove('fully-booked-date', 'has-event', 'event-blocked-date');
                    
                    if (eventBlockedDates.has(cellDate)) {
                        cell.classList.add('event-blocked-date');
                    } else if (fullyBookedDates.has(cellDate)) {
                        cell.classList.add('fully-booked-date');
                    }
                    
                    if (dateEvents.has(cellDate) && dateEvents.get(cellDate).length > 0) {
                        cell.classList.add('has-event');
                    }
                }
            });
        }, 100);
    }

    function handleDateSelection(date) {
        // Double-check if date is event-blocked before allowing selection
        if (eventBlockedDates.has(date)) {
            const events = dateEvents.get(date) || [];
            showEventBlockedModal(date, events);
            return;
        }
        
        // Check if date is fully booked
        if (fullyBookedDates.has(date)) {
            showFullyBookedModal(date);
            return;
        }

        selectedDate = date;
        $('#selected_date').val(date);
        $('#selected_date_end').val(date);

        const formattedDate = new Date(date + 'T12:00:00').toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        $('#selected-date-display').text(formattedDate);

        loadBookedTimes(date);
    }

    function loadBookedTimes(date) {
        const facilityId = $('input[name="facility_id"]').val();

        $.ajax({
            url: 'booking.php',
            data: {
                action: 'load_bookings',
                fid: facilityId,
                date: date
            },
            dataType: 'json',
            success: function(response) {
                bookedTimes = response.events;
                
                if (response.dateEvents && response.dateEvents.length > 0) {
                    eventBlockedDates.add(date);
                    dateEvents.set(date, response.dateEvents);
                } else {
                    eventBlockedDates.delete(date);
                    
                    if (response.fullyBooked) {
                        fullyBookedDates.add(date);
                    } else {
                        fullyBookedDates.delete(date);
                    }
                }

                if (calendar) {
                    calendar.render();
                    setTimeout(() => {
                        forceCalendarCellUpdate();
                    }, 50);
                }

                renderBookedTimeSlots();
                renderDateEvents();
                updateTimeDropdowns();

                if (bookedTimes.length === 0) {
                    $('#no-bookings-message').show();
                    $('#time-slots-grid').hide();
                } else {
                    $('#no-bookings-message').hide();
                    $('#time-slots-grid').show();
                }
            },
            error: function(err) {
                console.error(err);
                showErrorModal('Failed to load booked times. Please try again.');
            }
        });
    }

    // Enhanced error modal
    function showErrorModal(message) {
        const modalHtml = `
            <div class="fully-booked-overlay" id="errorOverlay">
                <div class="fully-booked-modal">
                    <div class="fully-booked-header">
                        <div class="fully-booked-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error
                        </div>
                        <button class="event-modal-close" id="closeErrorModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="fully-booked-body">
                        <i class="fas fa-times-circle fully-booked-icon"></i>
                        <div class="fully-booked-message">
                            Something Went Wrong
                        </div>
                        <div class="fully-booked-submessage">
                            ${message}
                        </div>
                    </div>
                    
                    <div class="fully-booked-footer">
                        <button class="event-select-btn" id="closeErrorBtn">
                            <i class="fas fa-check"></i>
                            OK
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#errorOverlay').remove();
        $('body').append(modalHtml);
        
        setTimeout(() => {
            $('#errorOverlay').css('display', 'flex');
        }, 10);
        
        $('#closeErrorModal, #closeErrorBtn').click(function() {
            $('#errorOverlay').fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        $('#errorOverlay').click(function(e) {
            if ($(e.target).is('#errorOverlay')) {
                $(this).fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }

    function renderDateEvents() {
        const container = $('#date-events-container');
        const content = $('#date-events-content');
        content.empty();

        if (!selectedDate) {
            container.hide();
            return;
        }

        const events = dateEvents.get(selectedDate) || [];
        
        if (events.length === 0) {
            container.hide();
            return;
        }

        events.forEach(event => {
            const eventItem = $('<div>').addClass('date-event-item');
            
            const typeBadge = $('<span>').addClass('event-type-badge event-type-' + event.event_type)
                .text(event.event_type.replace('_', ' ').toUpperCase());
            
            const title = $('<div>').addClass('event-title')
                .css('font-weight', 'bold')
                .text(event.title);
            
            const description = $('<div>').addClass('event-description')
                .css('margin-top', '8px')
                .css('font-size', '0.9rem')
                .text(event.description || 'No description provided.');
            
            eventItem.append(typeBadge, title, description);
            content.append(eventItem);
        });

        container.show();
    }

    function renderBookedTimeSlots() {
        const container = $('#time-slots-grid');
        container.empty();

        if (!selectedDate) {
            container.html('<div class="no-date-selected">Please select a date first</div>');
            return;
        }

        bookedTimes.forEach(event => {
            const startTime = event.start;
            const endTime = event.end;
            const displayTime = `${formatTime(startTime)} - ${formatTime(endTime)}`;

            const slot = $('<div>').addClass('time-slot booked')
                .attr('data-start', startTime)
                .attr('data-end', endTime)
                .text(displayTime)
                .attr('title', 'This time slot is already booked and unavailable');

            container.append(slot);
        });
    }

    function updateTimeDropdowns() {
        const timeFromSelect = $('#time_from');
        const timeToSelect = $('#time_to');

        const currentFrom = timeFromSelect.val();
        const currentTo = timeToSelect.val();

        timeFromSelect.find('option').prop('disabled', false);
        timeToSelect.find('option').prop('disabled', false);

        const blockedStartTimes = new Set();
        
        bookedTimes.forEach(event => {
            const startHour = parseInt(event.start.split(':')[0]);
            const endHour = parseInt(event.end.split(':')[0]);
            
            for (let hour = startHour; hour < endHour; hour++) {
                blockedStartTimes.add(hour + ':00');
            }
        });

        timeFromSelect.find('option').each(function() {
            const time = $(this).val();
            if (time && blockedStartTimes.has(time)) {
                $(this).prop('disabled', true);
            }
        });

        if (currentFrom && timeFromSelect.find(`option[value="${currentFrom}"]:disabled`).length) {
            timeFromSelect.val('');
        }
        if (currentTo && timeToSelect.find(`option[value="${currentTo}"]:disabled`).length) {
            timeToSelect.val('');
        }

        timeFromSelect.off('change').on('change', function() {
            const fromTime = $(this).val();

            if (fromTime) {
                const fromHour = parseInt(fromTime.split(':')[0]);
                
                timeToSelect.find('option').prop('disabled', false);

                timeToSelect.find('option').each(function() {
                    const toTime = $(this).val();
                    if (toTime && toTime <= fromTime) {
                        $(this).prop('disabled', true);
                    }
                });

                let maxEndTime = '22:00';
                
                bookedTimes.forEach(event => {
                    const eventStartHour = parseInt(event.start.split(':')[0]);
                    if (eventStartHour > fromHour) {
                        if (event.start < maxEndTime) {
                            maxEndTime = event.start;
                        }
                    }
                });

                timeToSelect.find('option').each(function() {
                    const toTime = $(this).val();
                    if (toTime && toTime > maxEndTime) {
                        $(this).prop('disabled', true);
                    }
                });

                if (timeToSelect.val() && (timeToSelect.val() <= fromTime || timeToSelect.val() > maxEndTime)) {
                    timeToSelect.val('');
                }
            }
        });

        timeToSelect.off('change').on('change', function() {
            const toTime = $(this).val();
            const fromTime = timeFromSelect.val();

            if (fromTime && toTime && toTime <= fromTime) {
                $(this).val('');
                showErrorModal('End time must be after start time.');
            }
        });
    }

    function formatTime(timeStr) {
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const period = hour >= 12 ? 'PM' : 'AM';
        const displayHours = hour % 12 || 12;
        return `${displayHours}:${minutes} ${period}`;
    }

    $('#booking-form').submit(function(e) {
        e.preventDefault();
        const _this = $(this);
        $('.err-msg').remove();

        const date = $('#selected_date').val();
        const timeFrom = $('#time_from').val();
        const timeTo = $('#time_to').val();

        // Check if selected date has an event
        if (eventBlockedDates.has(date)) {
            const events = dateEvents.get(date) || [];
            const eventTitles = events.map(e => e.title).join(', ');
            const el = $('<div>').addClass("alert alert-danger err-msg").html(`
                <i class="fas fa-exclamation-triangle"></i>
                This date is blocked due to: ${eventTitles}. Please select another date.
            `);
            _this.prepend(el);
            el.show('slow');
            return;
        }
        
        // Check if selected date is fully booked
        if (fullyBookedDates.has(date)) {
            const el = $('<div>').addClass("alert alert-danger err-msg").html(`
                <i class="fas fa-exclamation-triangle"></i>
                This date is fully booked. Please select another date with available time slots.
            `);
            _this.prepend(el);
            el.show('slow');
            return;
        }

        if (!date || !timeFrom || !timeTo) {
            const el = $('<div>').addClass("alert alert-danger err-msg").html(`
                <i class="fas fa-exclamation-triangle"></i>
                Please select a date and time range.
            `);
            _this.prepend(el);
            el.show('slow');
            return;
        }

        if (timeTo <= timeFrom) {
            const el = $('<div>').addClass("alert alert-danger err-msg").html(`
                <i class="fas fa-exclamation-triangle"></i>
                End time must be after start time.
            `);
            _this.prepend(el);
            el.show('slow');
            return;
        }

        const fromHour = parseInt(timeFrom.split(':')[0]);
        const toHour = parseInt(timeTo.split(':')[0]);
        
        let hasConflict = false;
        bookedTimes.forEach(event => {
            const eventStartHour = parseInt(event.start.split(':')[0]);
            const eventEndHour = parseInt(event.end.split(':')[0]);
            
            if (!(toHour <= eventStartHour || fromHour >= eventEndHour)) {
                hasConflict = true;
            }
        });

        if (hasConflict) {
            const el = $('<div>').addClass("alert alert-danger err-msg").html(`
                <i class="fas fa-exclamation-triangle"></i>
                The selected time slot conflicts with an existing booking. Please choose a different time.
            `);
            _this.prepend(el);
            el.show('slow');
            return;
        }

        showBookingLoading('Processing your booking...');

        $.ajax({
            url: _base_url_ + "classes/Master.php?f=save_booking",
            data: new FormData(_this[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            dataType: 'json',
            error: err => {
                console.error(err);
                hideBookingLoading();
                showErrorModal('An error occurred while processing your booking. Please try again.');
            },
            success: function(resp) {
                if (typeof resp === 'object' && resp.status === 'success') {
                    $('#bookingLoadingText').text('Booking confirmed! Redirecting...');
                    
                    const successEl = $('<div>').addClass("alert alert-success").html(`
                        <i class="fas fa-check-circle"></i>
                        Booking submitted successfully! Redirecting...
                    `);
                    _this.prepend(successEl);
                    successEl.show('slow');

                    setTimeout(() => {
                        location.href = './?p=booking_list';
                    }, 2000);
                } else if (resp.status === 'failed' && !!resp.msg) {
                    hideBookingLoading();
                    const el = $('<div>').addClass("alert alert-danger err-msg").html(`
                        <i class="fas fa-exclamation-triangle"></i>
                        ${resp.msg}
                    `);
                    _this.prepend(el);
                    el.show('slow');
                } else {
                    hideBookingLoading();
                    showErrorModal('An error occurred while processing your booking.');
                    console.log(resp);
                }
                $("html, body").scrollTop(0);
            }
        });
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('#booking-modal')) {
            $('#booking-modal').fadeOut(300);
        }
    });

    $(document).keyup(function(e) {
        if (e.keyCode === 27) {
            $('#booking-modal').fadeOut(300);
            $('.event-blocked-overlay, .fully-booked-overlay').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
});
</script>