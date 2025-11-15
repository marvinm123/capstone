<?php if(isset($_SESSION['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<br>
<br>
<br>

<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
  <!-- Header Section -->
  <div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h1 class="page-title mb-1">
          <i class="fas fa-calendar-check me-2"></i> My Bookings
        </h1>
        <p class="page-subtitle mb-0">View and manage your facility booking history</p>
      </div>
      <div class="total-badge">
        <i class="fas fa-list-check me-2"></i>
        <?php 
          $total_bookings = $conn->query("SELECT COUNT(*) as total FROM `booking_list` WHERE client_id = '{$_settings->userdata('id')}'")->fetch_assoc()['total'];
          echo $total_bookings . ' Booking' . ($total_bookings != 1 ? 's' : '');
        ?>
      </div>
    </div>
  </div>

  <!-- Stats Cards - Modular Grid -->
  <div class="stats-grid mb-4">
    <!-- Pending -->
    <div class="stat-module stat-pending">
      <div class="stat-icon">
        <i class="fas fa-clock"></i>
      </div>
      <div class="stat-content">
        <div class="stat-count">
          <?php 
            $pending = $conn->query("SELECT COUNT(*) as total FROM `booking_list` WHERE client_id = '{$_settings->userdata('id')}' AND status = 0")->fetch_assoc()['total'];
            echo $pending;
          ?>
        </div>
        <div class="stat-label">Pending</div>
      </div>
    </div>
    
    <!-- Confirmed -->
    <div class="stat-module stat-confirmed">
      <div class="stat-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-content">
        <div class="stat-count">
          <?php 
            $confirmed = $conn->query("SELECT COUNT(*) as total FROM `booking_list` WHERE client_id = '{$_settings->userdata('id')}' AND status = 1")->fetch_assoc()['total'];
            echo $confirmed;
          ?>
        </div>
        <div class="stat-label">Confirmed</div>
      </div>
    </div>
    
    <!-- Completed -->
    <div class="stat-module stat-completed">
      <div class="stat-icon">
        <i class="fas fa-calendar-check"></i>
      </div>
      <div class="stat-content">
        <div class="stat-count">
          <?php 
            $completed = $conn->query("SELECT COUNT(*) as total FROM `booking_list` WHERE client_id = '{$_settings->userdata('id')}' AND status = 2")->fetch_assoc()['total'];
            echo $completed;
          ?>
        </div>
        <div class="stat-label">Completed</div>
      </div>
    </div>
    
    <!-- Cancelled -->
    <div class="stat-module stat-cancelled">
      <div class="stat-icon">
        <i class="fas fa-times-circle"></i>
      </div>
      <div class="stat-content">
        <div class="stat-count">
          <?php 
            $cancelled = $conn->query("SELECT COUNT(*) as total FROM `booking_list` WHERE client_id = '{$_settings->userdata('id')}' AND status = 3")->fetch_assoc()['total'];
            echo $cancelled;
          ?>
        </div>
        <div class="stat-label">Cancelled</div>
      </div>
    </div>
  </div>

  <!-- Main Card -->
  <div class="main-card">
    <div class="card-header-custom">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="card-title-custom mb-0">
          <i class="fas fa-history me-2"></i>Booking History
        </h5>
        <div class="search-box">
          <i class="fas fa-search search-icon"></i>
          <input type="text" id="searchInput" class="search-input" placeholder="Search bookings...">
        </div>
      </div>
    </div>
    
    <div class="card-body-custom">
      <!-- Desktop Table View -->
      <div class="desktop-view">
        <div class="table-responsive">
          <table class="table booking-table" id="bookingTable">
            <thead>
              <tr>
                <th class="text-center" style="width: 60px;">#</th>
                <th style="width: 140px;">Booked On</th>
                <th style="width: 130px;">Reference</th>
                <th style="width: 220px;">Facility Details</th>
                <th style="width: 180px;">Booking Schedule</th>
                <th class="text-center" style="width: 120px;">Status</th>
                <th class="text-center" style="width: 80px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $i = 1;
              $qry = $conn->query("SELECT b.*,f.name as facility, c.name as category FROM `booking_list` b 
                INNER JOIN facility_list f ON b.facility_id = f.id 
                INNER JOIN category_list c ON f.category_id = c.id 
                WHERE b.client_id = '{$_settings->userdata('id')}' 
                ORDER BY unix_timestamp(b.date_created) DESC");
              while($row = $qry->fetch_assoc()):
                if(!empty($row['time_from']) && !empty($row['time_to'])) {
                  $time_from = date("g:i A", strtotime($row['time_from']));
                  $time_to = date("g:i A", strtotime($row['time_to']));
                  $time_display = "$time_from - $time_to";
                } else {
                  $time_display = "All day";
                }
              ?>
              <tr class="booking-row">
                <td class="text-center fw-bold"><?= $i++; ?></td>
                <td>
                  <div class="d-flex flex-column">
                    <?php if(!empty($row['date_created'])): ?>
                      <span class="fw-semibold"><?= date("M j, Y", strtotime($row['date_created'])) ?></span>
                      <small class="text-muted"><?= date("g:i A", strtotime($row['date_created'])) ?></small>
                    <?php else: ?>
                      <span class="text-muted">N/A</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <span class="ref-badge"><?= htmlspecialchars($row['ref_code'] ?? 'N/A') ?></span>
                </td>
                <td>
                  <div class="facility-info">
                    <div class="facility-icon">
                      <i class="fas fa-building"></i>
                    </div>
                    <div>
                      <h6 class="facility-name"><?= htmlspecialchars($row['facility'] ?? 'N/A') ?></h6>
                      <span class="facility-category"><?= htmlspecialchars($row['category'] ?? 'N/A') ?></span>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="d-flex flex-column">
                    <?php if(!empty($row['date_from']) && !empty($row['date_to'])): ?>
                      <?php if($row['date_from'] == $row['date_to']): ?>
                        <span class="schedule-date"><?= date("M j, Y", strtotime($row['date_from'])) ?></span>
                        <small class="schedule-time"><?= $time_display ?></small>
                      <?php else: ?>
                        <span class="schedule-date"><?= date("M j, Y", strtotime($row['date_from'])) ?> to <?= date("M j, Y", strtotime($row['date_to'])) ?></span>
                        <small class="schedule-time"><?= $time_display ?> daily</small>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted">Date not set</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="text-center">
                  <?php 
                    switch($row['status'] ?? 0){
                      case 0:
                        echo "<span class='status-badge status-pending'><i class='fas fa-clock'></i> Pending</span>";
                        break;
                      case 1:
                        echo "<span class='status-badge status-confirmed'><i class='fas fa-check-circle'></i> Confirmed</span>";
                        break;
                      case 2:
                        echo "<span class='status-badge status-completed'><i class='fas fa-calendar-check'></i> Completed</span>";
                        break;
                      case 3:
                        echo "<span class='status-badge status-cancelled'><i class='fas fa-times-circle'></i> Cancelled</span>";
                        break;
                    }
                  ?>
                </td>
                <td class="text-center">
                  <button type="button" class="action-btn view_data" data-id="<?= $row['id'] ?>" title="View Details">
                    <i class="fas fa-eye"></i>
                  </button>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile Card View -->
      <div class="mobile-view">
        <?php 
        $qry->data_seek(0);
        while($row = $qry->fetch_assoc()):
          if(!empty($row['time_from']) && !empty($row['time_to'])) {
            $time_from = date("g:i A", strtotime($row['time_from']));
            $time_to = date("g:i A", strtotime($row['time_to']));
            $time_display = "$time_from - $time_to";
          } else {
            $time_display = "All day";
          }
        ?>
        <div class="booking-card" data-searchable="<?= strtolower(htmlspecialchars($row['ref_code'] ?? '') . ' ' . htmlspecialchars($row['facility'] ?? '') . ' ' . htmlspecialchars($row['category'] ?? '')) ?>">
          <div class="booking-card-header">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="ref-badge-mobile"><?= htmlspecialchars($row['ref_code'] ?? 'N/A') ?></span>
              <?php 
                switch($row['status'] ?? 0){
                  case 0:
                    echo "<span class='status-badge-mobile status-pending'><i class='fas fa-clock'></i> Pending</span>";
                    break;
                  case 1:
                    echo "<span class='status-badge-mobile status-confirmed'><i class='fas fa-check-circle'></i> Confirmed</span>";
                    break;
                  case 2:
                    echo "<span class='status-badge-mobile status-completed'><i class='fas fa-calendar-check'></i> Completed</span>";
                    break;
                  case 3:
                    echo "<span class='status-badge-mobile status-cancelled'><i class='fas fa-times-circle'></i> Cancelled</span>";
                    break;
                }
              ?>
            </div>
            <h6 class="booking-card-title">
              <i class="fas fa-building me-2"></i><?= htmlspecialchars($row['facility'] ?? 'N/A') ?>
            </h6>
            <p class="booking-card-category"><?= htmlspecialchars($row['category'] ?? 'N/A') ?></p>
          </div>
          
          <div class="booking-card-body">
            <div class="info-item">
              <i class="fas fa-calendar"></i>
              <div>
                <small class="info-label">Booking Schedule</small>
                <?php if(!empty($row['date_from']) && !empty($row['date_to'])): ?>
                  <?php if($row['date_from'] == $row['date_to']): ?>
                    <div class="info-value"><?= date("M j, Y", strtotime($row['date_from'])) ?></div>
                    <small class="info-time"><?= $time_display ?></small>
                  <?php else: ?>
                    <div class="info-value"><?= date("M j, Y", strtotime($row['date_from'])) ?> to <?= date("M j, Y", strtotime($row['date_to'])) ?></div>
                    <small class="info-time"><?= $time_display ?> daily</small>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="info-value text-muted">Date not set</div>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="info-item">
              <i class="fas fa-clock"></i>
              <div>
                <small class="info-label">Booked On</small>
                <?php if(!empty($row['date_created'])): ?>
                  <div class="info-value"><?= date("M j, Y", strtotime($row['date_created'])) ?></div>
                  <small class="info-time"><?= date("g:i A", strtotime($row['date_created'])) ?></small>
                <?php else: ?>
                  <div class="info-value text-muted">N/A</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div class="booking-card-footer">
            <button type="button" class="view-btn view_data" data-id="<?= $row['id'] ?>">
              <i class="fas fa-eye me-2"></i>View Details
            </button>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
    
    <div class="card-footer-custom">
      <div class="footer-info">
        Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> bookings
      </div>
    </div>
  </div>
</div>

<!-- QR Code Modal -->
<div id="qrCodeModal" class="qr-modal">
  <div class="qr-modal-content">
    <button class="qr-modal-close">&times;</button>
    <div class="qr-modal-header">
      <i class="fas fa-qrcode me-2"></i>
      <h4>Payment Information</h4>
    </div>
    <div class="qr-modal-body">
      <div class="qr-code-wrapper">
        <img src="uploads/clients/321312.jpg" alt="QR Code" class="qr-code-img">
        <p class="qr-text">Scan this QR code to make payment</p>
      </div>
      <div class="contact-section">
        <div class="contact-item">
          <div class="contact-icon-wrapper">
            <i class="fas fa-phone-alt"></i>
          </div>
          <div>
            <small class="contact-label">Contact Number</small>
            <div class="contact-value">09606073283</div>
          </div>
        </div>
        <p class="contact-help">
          <i class="fas fa-info-circle me-1"></i>
          For payment verification and inquiries, please contact the number above.
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Floating QR Button -->
<button class="qr-float-btn" title="Payment QR Code">
  <i class="fas fa-qrcode"></i>
</button>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

:root {
  --primary: #3b82f6;
  --primary-dark: #2563eb;
  --pending: #6b7280;
  --confirmed: #3b82f6;
  --completed: #eab308;
  --cancelled: #ef4444;
  --success: #10b981;
  --bg-body: #f8fafc;
  --bg-card: #ffffff;
  --text-primary: #1e293b;
  --text-secondary: #64748b;
  --text-muted: #94a3b8;
  --border: #e2e8f0;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
  --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
}

* {
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background-color: var(--bg-body);
  color: var(--text-primary);
  line-height: 1.6;
}

/* Page Header */
.page-header {
  margin-bottom: 1.5rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.page-title i {
  color: var(--primary);
}

.page-subtitle {
  color: var(--text-secondary);
  font-size: 0.95rem;
}

.total-badge {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  padding: 0.625rem 1.25rem;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Stats Grid - Modular Layout */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
}

.stat-module {
  background: var(--bg-card);
  border-radius: var(--radius-md);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-module::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  transition: width 0.3s ease;
}

.stat-module:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
}

.stat-module:hover::before {
  width: 100%;
  opacity: 0.05;
}

.stat-pending::before { background: var(--pending); }
.stat-confirmed::before { background: var(--confirmed); }
.stat-completed::before { background: var(--completed); }
.stat-cancelled::before { background: var(--cancelled); }

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

.stat-pending .stat-icon { background: var(--pending); }
.stat-confirmed .stat-icon { background: var(--confirmed); }
.stat-completed .stat-icon { background: var(--completed); }
.stat-cancelled .stat-icon { background: var(--cancelled); }

.stat-content {
  flex: 1;
}

.stat-count {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--text-secondary);
  font-weight: 500;
}

/* Main Card */
.main-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  overflow: hidden;
}

.card-header-custom {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border);
  background: var(--bg-card);
}

.card-title-custom {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.card-title-custom i {
  color: var(--text-muted);
}

.search-box {
  position: relative;
  width: 100%;
  max-width: 300px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.625rem 1rem 0.625rem 2.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  transition: all 0.2s;
  background: var(--bg-body);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: white;
}

.card-body-custom {
  padding: 0;
}

/* Desktop Table */
.desktop-view {
  display: block;
}

.mobile-view {
  display: none;
}

.booking-table {
  width: 100%;
  margin: 0;
}

.booking-table thead th {
  background: var(--bg-body);
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 1rem 0.75rem;
  border-bottom: 1px solid var(--border);
}

.booking-table tbody td {
  padding: 1rem 0.75rem;
  vertical-align: middle;
  border-top: 1px solid var(--border);
}

.booking-row {
  transition: background-color 0.2s;
}

.booking-row:hover {
  background-color: rgba(59, 130, 246, 0.02);
}

.ref-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  background: var(--bg-body);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-family: 'Courier New', monospace;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.facility-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.facility-icon {
  width: 40px;
  height: 40px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  flex-shrink: 0;
}

.facility-name {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.facility-category {
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.schedule-date {
  font-weight: 500;
  color: var(--text-primary);
}

.schedule-time {
  color: var(--text-muted);
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  border-radius: var(--radius-sm);
  font-size: 0.8125rem;
  font-weight: 600;
  color: white;
}

.status-badge i {
  font-size: 0.75rem;
}

.status-pending { background: var(--pending); }
.status-confirmed { background: var(--confirmed); }
.status-completed { background: var(--completed); }
.status-cancelled { background: var(--cancelled); }

.action-btn {
  width: 36px;
  height: 36px;
  border: none;
  background: rgba(59, 130, 246, 0.1);
  color: var(--primary);
  border-radius: var(--radius-sm);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  background: var(--primary);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

/* Mobile Booking Cards */
.booking-card {
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
  padding: 1rem 1.5rem;
  transition: background-color 0.2s;
}

.booking-card:hover {
  background-color: rgba(59, 130, 246, 0.02);
}

.booking-card:last-child {
  border-bottom: none;
}

.booking-card-header {
  margin-bottom: 1rem;
}

.ref-badge-mobile {
  display: inline-block;
  padding: 0.375rem 0.625rem;
  background: var(--bg-body);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-family: 'Courier New', monospace;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-primary);
}

.status-badge-mobile {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.625rem;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.status-badge-mobile i {
  font-size: 0.625rem;
}

.booking-card-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0.5rem 0 0.25rem 0;
}

.booking-card-title i {
  color: var(--primary);
}

.booking-card-category {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  margin: 0;
}

.booking-card-body {
  margin-bottom: 1rem;
}

.info-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--bg-body);
  border-radius: var(--radius-sm);
  margin-bottom: 0.625rem;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-item > i {
  color: var(--text-muted);
  font-size: 1rem;
  margin-top: 0.125rem;
  flex-shrink: 0;
}

.info-label {
  display: block;
  color: var(--text-muted);
  font-size: 0.75rem;
  margin-bottom: 0.125rem;
}

.info-value {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 0.875rem;
}

.info-time {
  color: var(--text-muted);
  font-size: 0.8125rem;
}

.booking-card-footer {
  margin-top: 1rem;
}

.view-btn {
  width: 100%;
  padding: 0.75rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.view-btn:hover {
  background: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

/* Card Footer */
.card-footer-custom {
  padding: 1rem 1.5rem;
  background: var(--bg-body);
  border-top: 1px solid var(--border);
}

.footer-info {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.footer-info span {
  font-weight: 600;
  color: var(--text-primary);
}

/* QR Code Modal */
.qr-modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from { 
    transform: translate(-50%, -45%);
    opacity: 0;
  }
  to { 
    transform: translate(-50%, -50%);
    opacity: 1;
  }
}

.qr-modal-content {
  background: var(--bg-card);
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 90%;
  max-width: 480px;
  border-radius: var(--radius-lg);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideIn 0.3s ease;
  overflow: hidden;
}

.qr-modal-header {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  padding: 1.5rem;
  text-align: center;
  position: relative;
}

.qr-modal-header h4 {
  margin: 0;
  font-weight: 600;
  font-size: 1.25rem;
}

.qr-modal-close {
  position: absolute;
  right: 1rem;
  top: 1rem;
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  z-index: 1;
}

.qr-modal-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.qr-modal-body {
  padding: 2rem;
}

.qr-code-wrapper {
  text-align: center;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
  border-radius: var(--radius-md);
}

.qr-code-img {
  max-width: 100%;
  width: 280px;
  height: auto;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  background: white;
  padding: 1rem;
}

.qr-text {
  margin-top: 1rem;
  font-size: 0.95rem;
  color: var(--text-secondary);
  font-weight: 500;
  margin-bottom: 0;
}

.contact-section {
  background: white;
  border-radius: var(--radius-md);
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: var(--radius-md);
  margin-bottom: 1rem;
  border: 2px solid var(--primary);
}

.contact-icon-wrapper {
  width: 56px;
  height: 56px;
  background: white;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--primary);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
  flex-shrink: 0;
}

.contact-label {
  display: block;
  color: var(--text-muted);
  font-size: 0.75rem;
  margin-bottom: 0.125rem;
}

.contact-value {
  font-size: 1.35rem;
  font-weight: 600;
  color: var(--text-primary);
  letter-spacing: 0.5px;
}

.contact-help {
  text-align: center;
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: var(--radius-sm);
  line-height: 1.5;
}

/* Floating QR Button */
.qr-float-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
  }
  50% {
    box-shadow: 0 8px 32px rgba(59, 130, 246, 0.6);
  }
}

.qr-float-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 12px 32px rgba(59, 130, 246, 0.6);
  animation: none;
}

.qr-float-btn:active {
  transform: scale(0.95);
}

/* Responsive Design */
@media (max-width: 991.98px) {
  .desktop-view {
    display: none;
  }
  
  .mobile-view {
    display: block;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .total-badge {
    font-size: 0.8125rem;
    padding: 0.5rem 1rem;
  }
}

@media (max-width: 767.98px) {
  .container-fluid {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
  
  .card-header-custom {
    padding: 1rem;
  }
  
  .search-box {
    max-width: 100%;
  }
  
  .stat-module {
    padding: 1rem;
  }
  
  .stat-icon {
    width: 42px;
    height: 42px;
    font-size: 1.25rem;
  }
  
  .stat-count {
    font-size: 1.5rem;
  }
  
  .stat-label {
    font-size: 0.8125rem;
  }
}

@media (max-width: 575.98px) {
  .page-header {
    margin-bottom: 1rem;
  }
  
  .page-title {
    font-size: 1.25rem;
  }
  
  .page-subtitle {
    font-size: 0.875rem;
  }
  
  .total-badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.875rem;
  }
  
  .stats-grid {
    gap: 0.75rem;
  }
  
  .stat-module {
    padding: 0.875rem;
    gap: 0.75rem;
  }
  
  .stat-icon {
    width: 38px;
    height: 38px;
    font-size: 1.125rem;
  }
  
  .stat-count {
    font-size: 1.375rem;
  }
  
  .stat-label {
    font-size: 0.75rem;
  }
  
  .booking-card {
    padding: 1rem;
  }
  
  .booking-card-title {
    font-size: 0.9375rem;
  }
  
  .qr-modal-content {
    width: 95%;
    max-width: 380px;
  }
  
  .qr-modal-body {
    padding: 1.5rem;
  }
  
  .qr-code-img {
    width: 220px;
  }
  
  .contact-value {
    font-size: 1.125rem;
  }
  
  .contact-icon-wrapper {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
  }
  
  .qr-float-btn {
    width: 55px;
    height: 55px;
    bottom: 20px;
    right: 20px;
    font-size: 1.3rem;
  }
}

@media (max-width: 374.98px) {
  .stats-grid {
    gap: 0.625rem;
  }
  
  .stat-module {
    padding: 0.75rem;
  }
  
  .stat-icon {
    width: 36px;
    height: 36px;
    font-size: 1rem;
  }
  
  .stat-count {
    font-size: 1.25rem;
  }
  
  .stat-label {
    font-size: 0.6875rem;
  }
  
  .booking-card {
    padding: 0.875rem;
  }
  
  .qr-modal-body {
    padding: 1.25rem;
  }
  
  .qr-code-img {
    width: 200px;
  }
  
  .contact-value {
    font-size: 1rem;
  }
}

footer {
  display: none !important;
}
</style>
<script>
$(document).ready(function() {
  console.log('Document ready - initializing booking page');
  
  // Mobile search functionality
  function initializeMobileSearch() {
    console.log('Initializing mobile search');
    
    // Initial count for mobile
    var totalCards = $('.booking-card').length;
    $('#showingCount').text(totalCards);
    $('#totalCount').text(totalCards);
    
    $('#searchInput').off('keyup').on('keyup', function() {
      var searchTerm = $(this).val().toLowerCase().trim();
      var visibleCount = 0;
      
      console.log('Searching for:', searchTerm);
      
      if (searchTerm === '') {
        // Show all cards if search is empty
        $('.booking-card').show();
        visibleCount = totalCards;
      } else {
        // Search through booking cards
        $('.booking-card').each(function() {
          var cardText = $(this).text().toLowerCase();
          var refCode = $(this).find('.ref-badge-mobile').text().toLowerCase();
          var facilityName = $(this).find('.booking-card-title').text().toLowerCase();
          var category = $(this).find('.booking-card-category').text().toLowerCase();
          var status = $(this).find('.status-badge-mobile').text().toLowerCase();
          
          // Check if any relevant text matches the search term
          if (cardText.indexOf(searchTerm) > -1 || 
              refCode.indexOf(searchTerm) > -1 ||
              facilityName.indexOf(searchTerm) > -1 ||
              category.indexOf(searchTerm) > -1 ||
              status.indexOf(searchTerm) > -1) {
            $(this).show();
            visibleCount++;
          } else {
            $(this).hide();
          }
        });
      }
      
      console.log('Found ' + visibleCount + ' matching bookings');
      $('#showingCount').text(visibleCount);
      $('#totalCount').text(totalCards);
    });
  }

  // Desktop DataTable initialization
  function initializeDesktopTable() {
    console.log('Initializing desktop table');
    
    var table = $('#bookingTable').DataTable({
      dom: 'rt<"bottom"ip>',
      pageLength: 10,
      responsive: false,
      scrollX: false,
      autoWidth: false,
      language: {
        search: "",
        searchPlaceholder: "Search bookings...",
        lengthMenu: "Show _MENU_ bookings",
        info: "Showing _START_ to _END_ of _TOTAL_ bookings",
        infoEmpty: "No bookings found",
        infoFiltered: "(filtered from _MAX_ total bookings)",
        paginate: {
          first: "First",
          last: "Last",
          next: "Next",
          previous: "Previous"
        }
      },
      initComplete: function() {
        // Update counts after DataTable initialization
        var info = this.api().page.info();
        $('#showingCount').text(info.recordsDisplay);
        $('#totalCount').text(info.recordsTotal);
      }
    });

    // Search for desktop
    $('#searchInput').off('keyup').on('keyup', function() {
      table.search(this.value).draw();
      var info = table.page.info();
      $('#showingCount').text(info.recordsDisplay);
      $('#totalCount').text(info.recordsTotal);
    });
  }

  // Check screen size and initialize appropriate view
  function initializeView() {
    if ($(window).width() >= 992) {
      console.log('Desktop view detected');
      if ($.fn.DataTable.isDataTable('#bookingTable')) {
        $('#bookingTable').DataTable().destroy();
      }
      initializeDesktopTable();
    } else {
      console.log('Mobile view detected');
      if ($.fn.DataTable.isDataTable('#bookingTable')) {
        $('#bookingTable').DataTable().destroy();
      }
      initializeMobileSearch();
    }
  }

  // Initial initialization
  initializeView();

  // Reinitialize on window resize with debounce
  let resizeTimer;
  $(window).on('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      initializeView();
    }, 250);
  });

  // Initialize tooltips
  $('[data-bs-toggle="tooltip"]').tooltip({
    placement: 'top',
    trigger: 'hover'
  });

  // View booking details
  $(document).on('click', '.view_data', function() {
    uni_modal("Booking Details", "view_booking.php?id=" + $(this).data('id'), 'modal-lg');
  });

  // Show QR Code Modal
  $(document).on('click', '.qr-float-btn', function() {
    $('#qrCodeModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
  });

  // Close modal when clicking X
  $('.qr-modal-close').on('click', function() {
    $('#qrCodeModal').fadeOut(300);
    $('body').css('overflow', 'auto');
  });

  // Close modal when clicking outside
  $(window).on('click', function(event) {
    if (event.target.id === 'qrCodeModal') {
      $('#qrCodeModal').fadeOut(300);
      $('body').css('overflow', 'auto');
    }
  });

  // Close modal with Escape key
  $(document).on('keydown', function(event) {
    if (event.key === 'Escape') {
      $('#qrCodeModal').fadeOut(300);
      $('body').css('overflow', 'auto');
    }
  });
});
</script>