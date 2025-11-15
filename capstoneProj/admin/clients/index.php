<?php 
// Check for success flash data safely
if(isset($_settings) && method_exists($_settings, 'chk_flashdata') && $_settings->chk_flashdata('success')): 
?>
<script>
    alert_toast("<?php echo addslashes($_settings->flashdata('success')) ?>",'success')
</script>
<?php endif; ?>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
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

    .btn-flat.btn-primary, .btn.btn-primary {
        background: linear-gradient(90deg, var(--primary), var(--primary-hover));
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        border-radius: var(--radius);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        cursor: pointer;
    }

    .btn.btn-outline-secondary {
        border: 1px solid var(--gray-border);
        background: #fff;
        color: var(--text-main);
    }

    .card.card-outline {
        border-radius: var(--radius);
        background-color: #fff;
        box-shadow: var(--shadow);
        border: none;
        padding: 1.2rem;
    }

    .table {
        border-collapse: separate !important;
        border-spacing: 0 0.5rem !important;
        width: 100%;
    }

    .table thead tr th {
        background-color: #eef2ff;
        color: var(--text-main);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 12px 16px;
        border: none !important;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }

    .table tbody tr td {
        background-color: #fff;
        padding: 16px !important;
        border: none !important;
        color: var(--text-muted);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border-radius: var(--radius);
        vertical-align: top; /* Changed from middle to top for better text wrapping */
        word-wrap: break-word;
        word-break: break-word;
        line-height: 1.5;
    }

    /* Specific column adjustments for better text wrapping */
    .table tbody tr td:nth-child(3), /* Name column */
    .table tbody tr td:nth-child(4), /* Contact column */
    .table tbody tr td:nth-child(5), /* Email column */
    .table tbody tr td:nth-child(7) { /* Address column */
        min-width: 150px;
        max-width: 300px;
    }

    .table tbody tr td:nth-child(7) { /* Address column - give more space */
        min-width: 200px;
        max-width: 400px;
    }

    .badge {
        display: inline-block;
        padding: 0.35em 0.75em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .badge-success { background-color: #22c55e; color: #fff; }
    .badge-danger { background-color: #ef4444; color: #fff; }
    .badge-new { 
        background-color: #ffc107;
        color: #212529;
        margin-left: 0.5rem;
        font-weight: 600;
        font-size: 0.7rem;
        vertical-align: middle;
    }

    .dropdown-menu a.dropdown-item {
        font-size: 0.9rem;
        padding: 0.5rem 1.2rem;
        color: var(--text-main);
        border-radius: 0.5rem;
        text-decoration: none;
        display: block;
    }

    /* Remove truncation and allow natural text wrapping */
    .table tbody tr td p {
        margin: 0;
        line-height: 1.5;
        word-wrap: break-word;
        white-space: normal;
    }

    /* Ensure table is responsive */
    .table-responsive {
        overflow-x: auto;
    }

    /* Better row height for wrapped content */
    .table tbody tr {
        height: auto;
        min-height: 80px;
    }

    /* Action column stays compact */
    .table tbody tr td:last-child {
        vertical-align: middle;
        min-width: 120px;
        white-space: nowrap;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table tbody tr td {
            padding: 12px 10px !important;
            font-size: 0.9rem;
        }
        
        .table thead tr th {
            padding: 10px 12px;
            font-size: 0.75rem;
        }
        
        .card.card-outline {
            padding: 1rem;
        }
        
        .btn-group .dropdown-menu {
            position: absolute;
            right: 0;
            left: auto;
        }
    }

    #dynamicLegend .custom-legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .custom-legend-color {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 0.6rem;
    }
</style>

<div class="card card-outline">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">List of Clients</h3>
        <button id="showSummaryChartBtn" class="btn btn-sm btn-primary">
            <span class="fas fa-chart-pie mr-1"></span>Summary Chart
        </button>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped mb-0" id="clientsTable">
                <colgroup>
                    <col width="5%">
                    <col width="15%">
                    <col width="20%">
                    <col width="15%">
                    <col width="15%">
                    <col width="10%">
                    <col width="20%">
                </colgroup>
                <thead class="thead-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>Date Created</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th class="text-center">Status</th>
                        <th>Address</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $i = 1;
                $addresses = [];
                
                // Use prepared statement for security
                try {
                    $stmt = $conn->prepare("SELECT id, date_created, lastname, firstname, middlename, contact, email, status, address FROM client_list WHERE delete_flag=0 ORDER BY lastname, firstname, middlename ASC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while($row = $result->fetch_assoc()):
                        // Sanitize data safely
                        foreach($row as $k=>$v) {
                            $row[$k] = $v !== null ? trim(stripslashes($v)) : '';
                        }
                        $addresses[] = strtolower($row['address']);

                        $is_new = (strtotime($row['date_created']) >= strtotime('-1 day'));
                ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td><?php echo date("F j, Y, g:i A", strtotime($row['date_created'])) ?></td>
                        <td>
                            <div class="name-container">
                                <?php echo htmlspecialchars(ucwords($row['lastname'] . ', ' . $row['firstname'] . ' ' . $row['middlename']), ENT_QUOTES, 'UTF-8') ?>
                                <?php if($is_new): ?>
                                    <span class="badge badge-new" title="New client registered within 24 hours">New</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['contact'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-center">
                            <?php if($row['status'] == 1): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="address-content">
                                <?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                    Actions
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="?page=clients/manage_client&id=<?php echo (int)$row['id'] ?>">
                                        <span class="fa fa-edit text-primary"></span> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo (int)$row['id'] ?>">
                                        <span class="fa fa-trash text-danger"></span> Delete
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                    $stmt->close();
                } catch (Exception $e) {
                    echo '<tr><td colspan="8" class="text-center text-danger">Error loading client data</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pie Chart Modal -->
<div class="modal fade" id="summaryChartModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:900px;">
        <div class="modal-content rounded shadow" style="height: 600px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Client Distribution by Address</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4" style="height: calc(100% - 56px);">
                <div class="row h-100">
                    <div class="col-md-8 d-flex align-items-center justify-content-center">
                        <canvas id="summaryPieChart" style="width: 100%; height: 100%;"></canvas>
                    </div>
                    <div class="col-md-4 d-flex flex-column justify-content-center" id="dynamicLegend">
                        <!-- Legend generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('.delete_data').click(function(){
        const clientId = $(this).data('id');
        _conf("Are you sure you want to delete this client permanently?","delete_client",[clientId]);
    });

    // Initialize DataTable
    if ($.fn.DataTable) {
        $('#clientsTable').DataTable({
            "pageLength": 25,
            "order": [[1, 'desc']],
            "language": {
                "emptyTable": "No clients found",
                "info": "Showing _START_ to _END_ of _TOTAL_ clients",
                "infoEmpty": "Showing 0 to 0 of 0 clients",
                "infoFiltered": "(filtered from _MAX_ total clients)",
                "search": "Search:",
                "zeroRecords": "No matching clients found"
            }
        });
    }

    let addresses = <?php echo json_encode($addresses, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    $('#showSummaryChartBtn').click(function(){
        const total = addresses.length;
        
        if (total === 0) {
            alert_toast("No client data available for chart", 'warning');
            return;
        }

        let containsCount = addresses.filter(addr => addr.includes('grande')).length;
        let notContainsCount = total - containsCount;

        let percentContains = total ? (containsCount / total) * 100 : 0;
        let percentNotContains = total ? (notContainsCount / total) * 100 : 0;

        $('#summaryChartModal').modal('show');

        // Destroy existing chart if it exists
        if(window.summaryChart) {
            window.summaryChart.destroy();
        }

        const ctx = document.getElementById('summaryPieChart').getContext('2d');
        const data = {
            labels: ['Residents', 'Non-Residents'],
            datasets: [{
                data: [percentContains.toFixed(2), percentNotContains.toFixed(2)],
                backgroundColor: ['#36A2EB', '#FF6384'],
                borderColor: ['#fff', '#fff'],
                borderWidth: 2,
                hoverOffset: 15
            }]
        };

        window.summaryChart = new Chart(ctx, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: false 
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round(value);
                                return `${label}: ${percentage}% (${Math.round((value / 100) * total)} clients)`;
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: `Client Distribution - Total: ${total} clients`,
                        font: { 
                            size: 16,
                            family: "'Plus Jakarta Sans', sans-serif"
                        }
                    }
                }
            }
        });

        // Generate custom legend
        let legendHTML = `
            <div class="mb-3">
                <h6 class="font-weight-bold">Client Distribution</h6>
                <p class="text-muted small">Total: ${total} clients</p>
            </div>
        `;
        
        data.labels.forEach((label, i) => {
            const count = Math.round((data.datasets[0].data[i] / 100) * total);
            legendHTML += `
                <div class="custom-legend-item">
                    <span class="custom-legend-color" style="background-color: ${data.datasets[0].backgroundColor[i]}"></span>
                    <div>
                        <strong>${label}</strong><br>
                        <small class="text-muted">${data.datasets[0].data[i]}% (${count} clients)</small>
                    </div>
                </div>
            `;
        });
        
        $('#dynamicLegend').html(legendHTML);
    });
});

function delete_client(id){
    if (!confirm('Are you sure you want to delete this client?')) {
        return;
    }
    
    start_loader();
    $.ajax({
        url: _base_url_ + "classes/Users.php?f=delete_client",
        method: "POST",
        data: { 
            id: parseInt(id),
            csrf_token: '<?php echo isset($_SESSION["csrf_token"]) ? $_SESSION["csrf_token"] : ""; ?>'
        },
        dataType: "json",
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert_toast("An error occurred while deleting the client.", 'error');
            end_loader();
        },
        success: function(resp) {
            if (typeof resp === 'object' && resp.status === 'success') {
                alert_toast("Client deleted successfully", 'success');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                const errorMsg = resp && resp.message ? resp.message : "An error occurred.";
                alert_toast(errorMsg, 'error');
                end_loader();
            }
        }
    });
}
</script>