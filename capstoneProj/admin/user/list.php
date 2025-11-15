<?php if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>", 'success')
</script>
<?php endif; ?>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

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

        .btn-flat.btn-primary {
            background: linear-gradient(90deg, var(--primary), var(--primary-hover));
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border-radius: var(--radius);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
            cursor: pointer;
        }

        .btn-flat.btn-default {
            background-color: #ffffff;
            color: var(--text-main);
            border: 1px solid var(--gray-border);
            padding: 0.55rem 1.1rem;
            border-radius: var(--radius);
            font-weight: 500;
            cursor: pointer;
        }

        .card.card-outline.card-primary.rounded-0.shadow {
            border-radius: var(--radius) !important;
            background-color: #ffffff;
            box-shadow: var(--shadow);
            padding: 1.25rem;
            border: none;
        }

        .table {
            border-collapse: separate !important;
            border-spacing: 0 1rem !important;
            width: 100%;
            /* Remove Bootstrap border-collapse override */
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
            vertical-align: middle;
        }

        .table tbody tr {
            background-color: #ffffff;
            /* Remove box-shadow on tr - breaks layout */
            /* Instead, add shadow on each td */
            border-radius: var(--radius);
        }

        .table tbody tr td {
            padding: 14px 18px !important;
            vertical-align: middle !important;
            border: none !important;
            color: var(--text-muted);
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border-radius: var(--radius);
            /* For consistent rounding inside table */
        }

        /* Remove default table-striped background */
        .table-striped tbody tr:nth-of-type(odd) td {
            background-color: #fff !important;
        }

        /* Truncate facility name */
        .truncate-1 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0; /* reset margin */
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
            color: var(--text-main);
            border-radius: 0.5rem;
            text-decoration: none;
            display: block;
        }

        .card-tools button {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            border: 1px solid var(--gray-border);
            background-color: #f9fafb;
            color: var(--text-main);
            cursor: pointer;
        }

        /* New badge spacing */
        .ml-2 {
            margin-left: 0.5rem;
        }
    </style>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card card-outline card-primary shadow rounded-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">List of System Users</h3>
        <a href="?page=user/manage_user" class="btn btn-sm btn-primary shadow">
            <i class="fas fa-plus mr-1"></i> Create New
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <colgroup>
                    <col width="5%">
                    <col width="35%">
                    <col width="30%">
                    <col width="20%">
                    <col width="10%">
                </colgroup>
                <thead class="thead-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Type</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    $qry = $conn->query("SELECT *, concat(firstname, ' ', lastname) as name FROM `users` 
                        WHERE id != '1' AND id != '{$_settings->userdata('id')}' AND `type` != 3 
                        ORDER BY concat(firstname, ' ', lastname) ASC");
                    while($row = $qry->fetch_assoc()):
                    ?>
                        <tr>
                            <td class="text-center"><?php echo $i++; ?></td>
                            <td><?php echo ucwords($row['name']) ?></td>
                            <td><?php echo $row['username'] ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['type'] == 1 ? 'primary' : 'secondary' ?>">
                                    <?php echo $row['type'] == 1 ? 'Administrator' : 'Staff' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-dark dropdown-toggle" data-toggle="dropdown">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="?page=user/manage_user&id=<?php echo $row['id'] ?>">
                                            <i class="fa fa-edit text-primary"></i> Edit
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                            <i class="fa fa-trash text-danger"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($qry->num_rows == 0): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.table').dataTable();

        $('.delete_data').click(function(){
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This user will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    delete_user(id);
                }
            });
        });
    });

    function delete_user(id){
    // start_loader();  <-- Removed
    $.ajax({
        url: _base_url_ + "classes/Users.php?f=delete",
        method: "POST",
        data: {id: id},
        dataType: "json",
        error: err => {
            console.log(err);
            alert_toast("An error occurred.", 'error');
            // end_loader();  <-- Removed
        },
        success: function(resp){
            if (resp.status == 'success') {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'User has been deleted.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                alert_toast("An error occurred.", 'error');
                // end_loader();  <-- Removed
            }
        }
    });
}

</script>
