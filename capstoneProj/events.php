<?php
$page = 'events';

// Fetch events from database
$events_qry = $conn->query("SELECT * FROM date_events WHERE event_date >= CURDATE() ORDER BY event_date ASC");
$upcoming_events = [];
while($row = $events_qry->fetch_assoc()){
    $upcoming_events[] = $row;
}

// Fetch past events
$past_events_qry = $conn->query("SELECT * FROM date_events WHERE event_date < CURDATE() ORDER BY event_date DESC LIMIT 6");
$past_events = [];
while($row = $past_events_qry->fetch_assoc()){
    $past_events[] = $row;
}

// For calendar - get all events (no date restriction for calendar view)
$calendar_events_qry = $conn->query("SELECT * FROM date_events ORDER BY event_date ASC");
$calendar_events = [];
while($row = $calendar_events_qry->fetch_assoc()){
    $calendar_events[] = $row;
}
?>
<br>
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />

<style>
    body{
          font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
/* Events Page Styles */
.events-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 0 3rem 0;
    text-align: center;
    margin-bottom: 3rem;
}

.events-hero h1 {
    font-size: 2.8rem;
    font-weight: 800;
    margin-bottom: 1rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.events-hero p {
    font-size: 1.1rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.events-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem 2rem 1.5rem;
}

.section-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2.5rem;
    text-align: center;
    position: relative;
    padding-bottom: 1rem;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 2px;
}

/* Calendar Styles */
.calendar-section {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 4rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.calendar-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

/* FullCalendar Customization */
#eventCalendar {
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
    background-color: rgba(102, 126, 234, 0.1);
}

/* Event Styles - Match Admin Colors */
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

/* Remove dots */
.fc-daygrid-event-dot {
    display: none !important;
}

.fc-event .fc-event-dot {
    display: none !important;
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
    justify-content: center;
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

/* Modern Timeline Design for Events */
.events-timeline {
    position: relative;
    padding-left: 40px;
    margin: 2rem 0;
}

.events-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.timeline-event {
    position: relative;
    margin-bottom: 2.5rem;
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.timeline-event:hover {
    transform: translateX(8px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.timeline-event::before {
    content: '';
    position: absolute;
    left: -38px;
    top: 24px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    border: 4px solid #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.timeline-event.notice::before { border-color: #8b5cf6; }
.timeline-event.holiday::before { border-color: #10b981; }
.timeline-event.maintenance::before { border-color: #ef4444; }
.timeline-event.special_event::before { border-color: #06b6d4; }

.timeline-event-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.timeline-event-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #64748b;
    font-weight: 600;
}

.timeline-event-date i {
    color: #667eea;
}

.timeline-event-type {
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.timeline-event-type.notice { 
    background: linear-gradient(135deg, #e9d5ff, #d8b4fe);
    color: #6b21a8; 
}
.timeline-event-type.holiday { 
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #166534; 
}
.timeline-event-type.maintenance { 
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b; 
}
.timeline-event-type.special_event { 
    background: linear-gradient(135deg, #cffafe, #a5f3fc);
    color: #0e7490; 
}

.timeline-event-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.timeline-event-description {
    color: #64748b;
    line-height: 1.6;
    font-size: 0.95rem;
}

.timeline-event-status {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.timeline-event-status.upcoming {
    color: #1e40af;
}

.timeline-event-status.coming-soon {
    color: #ea580c;
}

.timeline-event-status.past {
    color: #64748b;
}

/* Empty State */
.empty-events {
    text-align: center;
    padding: 5rem 3rem;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 20px;
    border: 2px dashed #cbd5e1;
    margin: 2rem 0;
}

.empty-events i {
    font-size: 4.5rem;
    color: #94a3b8;
    margin-bottom: 1.8rem;
    opacity: 0.7;
}

.empty-events h3 {
    color: #475569;
    margin-bottom: 1.2rem;
    font-size: 1.5rem;
    font-weight: 600;
}

.empty-events p {
    color: #64748b;
    max-width: 400px;
    margin: 0 auto;
    font-size: 1rem;
    line-height: 1.6;
}

/* Section Spacing */
.upcoming-events {
    margin-bottom: 4rem;
}

.past-events {
    margin-top: 4rem;
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

.event-detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.8rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.event-detail-label {
    font-weight: 600;
    color: #64748b;
}

.event-detail-value {
    font-weight: 500;
    color: #0f172a;
    text-align: right;
}

.event-type-badge {
    display: inline-block;
    padding: 0.4rem 1rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.type-notice {
    background: #e9d5ff;
    color: #6b21a8;
}

.type-holiday {
    background: #dcfce7;
    color: #166534;
}

.type-maintenance {
    background: #fee2e2;
    color: #991b1b;
}

.type-special_event {
    background: #cffafe;
    color: #0e7490;
}

.btn-close-modal {
    margin-top: 1.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #5568d3);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-close-modal:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Hide Footer */
footer {
    display: none !important;
}

/* Responsive */
@media (max-width: 768px) {
    .events-hero {
        padding: 3rem 0 2rem 0;
    }
    
    .events-hero h1 {
        font-size: 2.2rem;
    }
    
    .events-hero p {
        font-size: 1rem;
        padding: 0 1rem;
    }
    
    .events-container {
        padding: 0 1rem 1.5rem 1rem;
    }
    
    .calendar-section {
        padding: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .events-timeline {
        padding-left: 30px;
    }
    
    .events-timeline::before {
        left: 6px;
    }
    
    .timeline-event::before {
        left: -30px;
        width: 12px;
        height: 12px;
        border-width: 3px;
    }
    
    .timeline-event {
        padding: 1.2rem;
    }
    
    .timeline-event-header {
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .timeline-event-title {
        font-size: 1.1rem;
    }
    
    .section-title {
        font-size: 1.8rem;
        margin-bottom: 2rem;
    }
    
    .empty-events {
        padding: 3rem 2rem;
        margin: 1rem 0;
    }
    
    .calendar-legend {
        gap: 1rem;
    }
}
</style>

<br>
<br>
<br>

<div class="events-container">
    <!-- Calendar Section -->
    <section class="calendar-section">
        <div class="calendar-header">
            <h2 class="calendar-title">Event Calendar</h2>
        </div>
        
        <div id="eventCalendar"></div>
        
        <!-- Calendar Legend -->
        <div class="calendar-legend">
            <div class="calendar-legend-item">
                <div class="calendar-legend-color" style="background: #8b5cf6;"></div>
                <span>General Notices</span>
            </div>
            <div class="calendar-legend-item">
                <div class="calendar-legend-color" style="background: #10b981;"></div>
                <span>Holidays</span>
            </div>
            <div class="calendar-legend-item">
                <div class="calendar-legend-color" style="background: #ef4444;"></div>
                <span>Maintenance</span>
            </div>
            <div class="calendar-legend-item">
                <div class="calendar-legend-color" style="background: #06b6d4;"></div>
                <span>Special Events</span>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="upcoming-events">
        <h2 class="section-title">Upcoming Events</h2>
        
        <?php if(count($upcoming_events) > 0): ?>
            <div class="events-timeline">
                <?php foreach($upcoming_events as $event): ?>
                    <?php
                    $event_date = new DateTime($event['event_date']);
                    $today = new DateTime();
                    $is_upcoming = $event_date >= $today;
                    $days_diff = $today->diff($event_date)->days;
                    $status_class = $is_upcoming ? ($days_diff <= 7 ? 'coming-soon' : 'upcoming') : 'past';
                    ?>
                    <div class="timeline-event <?= $event['event_type'] ?>">
                        <div class="timeline-event-header">
                            <div class="timeline-event-date">
                                <i class="fas fa-calendar"></i>
                                <?= $event_date->format('F j, Y') ?>
                            </div>
                            <span class="timeline-event-type <?= $event['event_type'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $event['event_type'])) ?>
                            </span>
                        </div>
                        <h3 class="timeline-event-title"><?= htmlspecialchars($event['title']) ?></h3>
                        <?php if(!empty($event['description'])): ?>
                            <p class="timeline-event-description"><?= htmlspecialchars($event['description']) ?></p>
                        <?php else: ?>
                            <p class="timeline-event-description" style="color: #94a3b8; font-style: italic;">No description provided</p>
                        <?php endif; ?>
                        <div class="timeline-event-status <?= $status_class ?>">
                            <i class="fas fa-<?= $status_class === 'coming-soon' ? 'exclamation-circle' : ($status_class === 'upcoming' ? 'clock' : 'check-circle') ?>"></i>
                            <?= $status_class === 'coming-soon' ? 'Coming Soon!' : ($status_class === 'upcoming' ? 'Upcoming' : 'Completed') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-events">
                <i class="fas fa-calendar-plus"></i>
                <h3>No Upcoming Events</h3>
                <p>We're currently planning new events and announcements. Check back soon for updates about our facility schedules and special occasions.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Past Events Section -->
    <?php if(count($past_events) > 0): ?>
    <section class="past-events">
        <h2 class="section-title">Recent Events</h2>
        <div class="events-timeline">
            <?php foreach($past_events as $event): ?>
                <?php
                $event_date = new DateTime($event['event_date']);
                ?>
                <div class="timeline-event <?= $event['event_type'] ?>">
                    <div class="timeline-event-header">
                        <div class="timeline-event-date">
                            <i class="fas fa-calendar"></i>
                            <?= $event_date->format('F j, Y') ?>
                        </div>
                        <span class="timeline-event-type <?= $event['event_type'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $event['event_type'])) ?>
                        </span>
                    </div>
                    <h3 class="timeline-event-title"><?= htmlspecialchars($event['title']) ?></h3>
                    <?php if(!empty($event['description'])): ?>
                        <p class="timeline-event-description"><?= htmlspecialchars($event['description']) ?></p>
                    <?php else: ?>
                        <p class="timeline-event-description" style="color: #94a3b8; font-style: italic;">No description provided</p>
                    <?php endif; ?>
                    <div class="timeline-event-status past">
                        <i class="fas fa-check-circle"></i>
                        Completed
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- Modal for Event Details -->
<div class="modal-overlay" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-calendar-day"></i>
                Event Details
            </h3>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Modal content will be populated by JavaScript -->
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEvents = <?php echo json_encode($calendar_events); ?>;
    const calendarEl = document.getElementById('eventCalendar');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        height: 'auto',
        
        events: calendarEvents.map(event => {
            return {
                id: 'event_' + event.id,
                title: event.title,
                start: event.event_date,
                allDay: true,
                classNames: [event.event_type + '-event'],
                extendedProps: {
                    eventId: event.id,
                    description: event.description,
                    eventType: event.event_type
                }
            };
        }),
        
        eventClick: function(info) {
            showEventModal(info.event);
        },
        
        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            info.el.title = `${props.eventType.toUpperCase()}: ${info.event.title}\n${props.description || 'No description'}`;
        }
    });
    
    calendar.render();

    function showEventModal(event) {
        const props = event.extendedProps;
        const modal = document.getElementById('eventModal');
        const modalBody = document.getElementById('modalBody');
        
        const typeBadgeClass = 'type-' + props.eventType;
        const eventDate = new Date(event.startStr);
        
        modalBody.innerHTML = `
            <div class="event-detail-row">
                <span class="event-detail-label">Event Type:</span>
                <span class="event-detail-value">
                    <span class="event-type-badge ${typeBadgeClass}">
                        ${props.eventType.replace('_', ' ').toUpperCase()}
                    </span>
                </span>
            </div>
            <div class="event-detail-row">
                <span class="event-detail-label">Title:</span>
                <span class="event-detail-value">${event.title}</span>
            </div>
            <div class="event-detail-row">
                <span class="event-detail-label">Date:</span>
                <span class="event-detail-value">${eventDate.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                })}</span>
            </div>
            <div class="event-detail-row" style="border-bottom: none;">
                <span class="event-detail-label">Description:</span>
            </div>
            <div style="padding: 0.8rem 0; color: #475569;">
                ${props.description || '<em style="color: #94a3b8;">No description provided</em>'}
            </div>
            <button class="btn-close-modal" onclick="closeModal()">
                <i class="fas fa-times"></i> Close
            </button>
        `;
        
        modal.style.display = 'block';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                modal.classList.add('show');
            });
        });
    }

    window.closeModal = function() {
        const modal = document.getElementById('eventModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('eventModal');
        if (event.target === modal) {
            closeModal();
        }
    });
});
</script>
<style>
    /* Event Styles - Much Darker Colors with Black Text */
.fc-event {
    border: none !important;
    border-radius: 6px;
    padding: 3px 6px;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    color: #000000 !important;
}

.fc-event:hover {
    opacity: 0.8;
    transform: scale(1.02);
}

.fc-event.notice-event {
    background-color: #8b5cf6 !important; /* Much darker purple */
    color: #000000 !important;
    border-left: 4px solid #5b21b6 !important; /* Much darker border */
}

.fc-event.holiday-event {
    background-color: #16a34a !important; /* Much darker green */
    color: #000000 !important;
    border-left: 4px solid #166534 !important; /* Much darker border */
}

.fc-event.maintenance-event {
    background-color: #dc2626 !important; /* Much darker red */
    color: #000000 !important;
    border-left: 4px solid #991b1b !important; /* Much darker border */
}

.fc-event.special_event-event {
    background-color: #0891b2 !important; /* Much darker cyan */
    color: #000000 !important;
    border-left: 4px solid #0e7490 !important; /* Much darker border */
}

/* Calendar Legend - Much Darker Colors */
.calendar-legend-color[style*="background: #6d28d9;"] {
    background: #5b21b6 !important; /* Much darker purple */
}

.calendar-legend-color[style*="background: #15803d;"] {
    background: #166534 !important; /* Much darker green */
}

.calendar-legend-color[style*="background: #b91c1c;"] {
    background: #991b1b !important; /* Much darker red */
}

.calendar-legend-color[style*="background: #0d9488;"] {
    background: #0e7490 !important; /* Much darker cyan */
}

/* Update timeline event type colors to match much darker scheme */
.timeline-event-type.notice { 
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: #ece9e9ff !important; 
}
.timeline-event-type.holiday { 
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #ece9e9ff !important; 
}
.timeline-event-type.maintenance { 
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #ece9e9ff !important; 
}
.timeline-event-type.special_event { 
    background: linear-gradient(135deg, #0891b2, #0e7490);
    color: #ece9e9ff !important; 
}

/* Update modal type badges */
.type-notice {
    background: #8b5cf6;
    color: #000000 !important;
}

.type-holiday {
    background: #16a34a;
    color: #ece9e9ff !important;
}

.type-maintenance {
    background: #dc2626;
    color: #ece9e9ff !important;
}

.type-special_event {
    background: #0891b2;
    color: #ece9e9ff !important;
}

/* Update timeline event dot colors to match */
.timeline-event.notice::before { border-color: #5b21b6; }
.timeline-event.holiday::before { border-color: #166534; }
.timeline-event.maintenance::before { border-color: #991b1b; }
.timeline-event.special_event::before { border-color: #0e7490; }
</style>