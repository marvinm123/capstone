<?php
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT f.*, c.name as category FROM `facility_list` f INNER JOIN category_list c ON f.category_id = c.id WHERE f.id = '{$_GET['id']}' ");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = stripslashes($v);
        }
    }
}
?>

<style>
    :root {
        --primary: #4361ee;
        --primary-light: #eef2ff;
        --success: #10b981;
        --danger: #ef4444;
        --text-primary: #111827;
        --text-secondary: #6b7280;
        --border: #e5e7eb;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.12);
        --radius-sm: 0.375rem;
        --radius-md: 0.75rem;
        --radius-lg: 1rem;
    }

    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background: #f9fafb;
        color: var(--text-primary);
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }

    .facility-container {
        padding: 2rem 1rem;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        box-sizing: border-box;
    }

    .facility-card {
        width: 100%;
        max-width: 1200px;
        background: #ffffff;
        border-radius: var(--radius-lg);
        padding: 2.5rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .facility-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-4px);
    }

    .facility-img-container {
        position: relative;
        border-radius: var(--radius-md);
        overflow: hidden;
        margin-bottom: 2.5rem;
        box-shadow: var(--shadow-sm);
    }

    .facility-img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        display: block;
    }

    .img-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        padding: 2rem 1.5rem 1rem;
        color: white;
    }

    .img-overlay h2 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
    }

    .img-overlay .category {
        display: inline-block;
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-sm);
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-card {
        background: var(--primary-light);
        padding: 1.25rem;
        border-radius: var(--radius-md);
        border-left: 4px solid var(--primary);
    }

    .info-label {
        font-weight: 500;
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-value {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .status-badge {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 999px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-badge.bg-success {
        background-color: var(--success);
        color: white;
    }

    .status-badge.bg-danger {
        background-color: var(--danger);
        color: white;
    }

    .btn-group-custom {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn {
        border-radius: var(--radius-sm);
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: var(--shadow-sm);
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-light {
        background: white;
        border: 1px solid var(--border);
        color: var(--text-primary);
    }

    .btn-light:hover {
        background: #f3f4f6;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-danger {
        background: var(--danger);
        color: white;
        border: none;
    }

    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-outline-light {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-primary);
    }

    .btn-outline-light:hover {
        background: #f3f4f6;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .description-section {
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: var(--radius-md);
        margin-top: 2rem;
    }

    .description-section .info-label {
        margin-bottom: 1rem;
    }

    .price-tag {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
    }

    @media (max-width: 768px) {
        .facility-container {
            padding: 1rem;
        }

        .facility-card {
            padding: 1.5rem;
        }

        .facility-img {
            height: 300px;
        }

        .img-overlay h2 {
            font-size: 1.5rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="facility-container">
    <div class="facility-card">
        <div class="facility-img-container">
            <img src="<?= validate_image($image_path ?? '') ?>" alt="<?= $name ?? 'Facility Image' ?>" class="facility-img">
            <div class="img-overlay">
                <h2><?= $name ?? 'Facility' ?></h2>
                <span class="category"><?= $category ?? 'Category' ?></span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Status
                </div>
                <div class="info-value">
                    <?php if (isset($status)): ?>
                        <span class="status-badge <?= $status == 1 ? 'bg-success' : 'bg-danger' ?>">
                            <?= $status == 1 ? 'Active' : 'Inactive' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    Price
                </div>
                <div class="price-tag">₱<?= isset($price) ? number_format($price, 2) : '0.00' ?></div>
            </div>
        </div>

        <div class="description-section">
            <div class="info-label">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Description
            </div>
            <div class="info-value"><?= $description ?? 'No description available.' ?></div>
        </div>

        <div class="btn-group-custom" style="margin-top: 2.5rem;">
            <a href="./?page=facilities/manage_facility&id=<?= $id ?? '' ?>" class="btn btn-light">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit Facility
            </a>
            <button id="delete_data" class="btn btn-danger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                Delete Facility
            </button>
            <a href="./?page=facilities" class="btn btn-outline-light">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Facilities
            </a>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#delete_data').click(function () {
        _conf("Are you sure you want to delete this facility permanently?", "delete_facility", []);
    });
});

function delete_facility($id = '<?= $id ?? '' ?>') {
    start_loader();
    $.ajax({
        url: _base_url_ + "classes/Master.php?f=delete_facility",
        method: "POST",
        data: { id: $id },
        dataType: "json",
        error: err => {
            console.error(err);
            alert_toast("An error occurred.", 'error');
            end_loader();
        },
        success: function (resp) {
            if (typeof resp === 'object' && resp.status === 'success') {
                location.href = './?page=facilities';
            } else {
                alert_toast("An error occurred.", 'error');
                end_loader();
            }
        }
    });
}
</script>