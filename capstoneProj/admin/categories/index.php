<?php if($_settings->chk_flashdata('success')): ?>
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>", 'success');
</script>
<?php endif; ?>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

<!-- Styles -->
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

    .card.card-outline.card-primary.rounded-0.shadow {
        border-radius: var(--radius);
        background-color: #ffffff;
        box-shadow: var(--shadow);
        padding: 1.25rem;
        border: none;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
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
        transition: all 0.3s ease;
    }

    .btn-flat.btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }

    .btn-flat.btn-default {
        background-color: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--gray-border);
        padding: 0.55rem 1.1rem;
        border-radius: var(--radius);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-flat.btn-default:hover {
        background-color: var(--gray-bg);
        border-color: var(--text-muted);
    }

    /* Desktop Table Styles */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        border-collapse: separate !important;
        border-spacing: 0 0.5rem !important;
        width: 100%;
        margin-bottom: 0;
    }

    .table thead th {
        background-color: #eef2ff;
        color: var(--text-main);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 12px 16px;
        border: none;
        white-space: nowrap;
    }

    .table tbody tr {
        background-color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        border-radius: var(--radius);
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .table tbody td {
        padding: 14px 18px !important;
        vertical-align: middle;
        border: none !important;
        color: var(--text-muted);
    }

    .table tbody td:first-child {
        border-radius: var(--radius) 0 0 var(--radius);
    }

    .table tbody td:last-child {
        border-radius: 0 var(--radius) var(--radius) 0;
    }

    .badge {
        display: inline-block;
        padding: 0.4em 0.85em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .badge-success {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .truncate-1 {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 300px;
    }

    /* Mobile Search Bar */
    .mobile-search {
        display: none;
        margin-bottom: 1rem;
    }

    .mobile-search input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-border);
        border-radius: var(--radius);
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .mobile-search input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* Mobile Card View */
    .mobile-card-view {
        display: none;
    }

    .category-card {
        background: white;
        border-radius: var(--radius);
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .category-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .category-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-border);
    }

    .category-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.25rem;
    }

    .category-card-date {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .category-card-body {
        margin-bottom: 1rem;
    }

    .category-card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
    }

    .category-card-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .category-card-value {
        font-size: 0.9rem;
        color: var(--text-main);
    }

    .category-card-description {
        background: var(--gray-bg);
        padding: 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.5;
        margin-top: 0.5rem;
    }

    .category-card-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .category-card-actions .btn {
        flex: 1;
        min-width: 100px;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-view {
        background: #f1f5f9;
        color: var(--text-main);
        border: 1px solid var(--gray-border);
    }

    .btn-edit {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    /* Responsive Breakpoints */
    @media (max-width: 991px) {
        .table-responsive {
            display: none;
        }

        .mobile-card-view,
        .mobile-search {
            display: block;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-header h3.card-title {
            font-size: 1.3rem;
        }

        .btn-flat.btn-primary {
            width: 100%;
            justify-content: center;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Hide DataTables controls on mobile */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            display: none !important;
        }
    }

    @media (max-width: 768px) {
        .card.card-outline.card-primary.rounded-0.shadow {
            padding: 1rem;
        }

        .card-header h3.card-title {
            font-size: 1.2rem;
        }

        .category-card {
            padding: 1rem;
        }

        .category-card-title {
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .card.card-outline.card-primary.rounded-0.shadow {
            padding: 0.75rem;
            border-radius: 0.5rem;
        }

        .card-header {
            gap: 0.75rem;
        }

        .card-header h3.card-title {
            font-size: 1.1rem;
        }

        .category-card {
            padding: 0.875rem;
        }

        .category-card-actions .btn {
            min-width: 80px;
            font-size: 0.8rem;
            padding: 0.4rem 0.75rem;
        }
    }

    /* DataTables Responsive Overrides */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }
</style>

<div class="card card-outline card-primary rounded-0 shadow">
    <div class="card-header">
        <h3 class="card-title">List of Categories</h3>
        <div class="card-tools">
            <button type="button" id="create_new" class="btn btn-flat btn-primary btn-sm">
                <span class="fas fa-plus"></span> Create New
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <!-- Mobile Search Bar -->
            <div class="mobile-search">
                <input type="text" id="mobileSearch" placeholder="🔍 Search categories...">
            </div>

            <!-- Desktop Table View -->
            <div class="table-responsive">
                <table class="table table-bordered table-stripped" id="categoryTable">
                    <colgroup>
                        <col width="5%">
                        <col width="20%">
                        <col width="20%">
                        <col width="30%">
                        <col width="15%">
                        <col width="10%">
                    </colgroup>
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Date Created</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        $qry = $conn->query("SELECT * from `category_list` where delete_flag = 0 order by `name` asc ");
                        while($row = $qry->fetch_assoc()):
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo date("M j, Y", strtotime($row['date_created'])) ?></td>
                                <td><strong><?php echo $row['name'] ?></strong></td>
                                <td><p class="m-0 truncate-1"><?php echo $row['description'] ?></p></td>
                                <td class="text-center">
                                    <?php if($row['status'] == 1): ?>
                                        <span class="badge badge-success px-3 rounded-pill">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger px-3 rounded-pill">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td align="center">
                                    <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                        Action
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item view_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                            <span class="fa fa-eye text-dark"></span> View
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item edit_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                            <span class="fa fa-edit text-primary"></span> Edit
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                            <span class="fa fa-trash text-danger"></span> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-card-view">
                <?php 
                $i = 1;
                $qry = $conn->query("SELECT * from `category_list` where delete_flag = 0 order by `name` asc ");
                while($row = $qry->fetch_assoc()):
                ?>
                    <div class="category-card" data-search="<?= strtolower($row['name'] . ' ' . $row['description']) ?>">
                        <div class="category-card-header">
                            <div>
                                <div class="category-card-title"><?php echo $row['name'] ?></div>
                                <div class="category-card-date">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date("M j, Y", strtotime($row['date_created'])) ?>
                                </div>
                            </div>
                            <div>
                                <?php if($row['status'] == 1): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="category-card-body">
                            <div class="category-card-row">
                                <span class="category-card-label">Description</span>
                            </div>
                            <div class="category-card-description">
                                <?php echo $row['description'] ?>
                            </div>
                        </div>

                        <div class="category-card-actions">
                            <button class="btn btn-view view_data" data-id="<?php echo $row['id'] ?>">
                                <i class="fa fa-eye"></i> View
                            </button>
                            <button class="btn btn-edit edit_data" data-id="<?php echo $row['id'] ?>">
                                <i class="fa fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-delete delete_data" data-id="<?php echo $row['id'] ?>">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        // Initialize DataTable only for desktop
        if ($(window).width() >= 992) {
            $('#categoryTable').dataTable();
        }

        // Mobile search functionality
        $('#mobileSearch').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.category-card').each(function() {
                const cardData = $(this).data('search');
                if (cardData.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('#create_new').click(function(){
            uni_modal("Add New Category","categories/manage_category.php");
        })
        
        // Handle clicks for both desktop and mobile
        $(document).on('click', '.edit_data', function(){
            uni_modal("Edit Category","categories/manage_category.php?id="+$(this).attr('data-id'));
        });
        
        $(document).on('click', '.view_data', function(){
            uni_modal("View Category","categories/view_category.php?id="+$(this).attr('data-id'));
        });
        
        $(document).on('click', '.delete_data', function(){
            _conf("Are you sure to delete this category permanently?","delete_category",[$(this).attr('data-id')])
        });

        // Handle window resize
        $(window).on('resize', function() {
            if ($(window).width() >= 992 && !$.fn.DataTable.isDataTable('#categoryTable')) {
                $('#categoryTable').dataTable();
            }
        });
    })
    
    function delete_category($id){
        start_loader();
        $.ajax({
            url:_base_url_+"classes/Master.php?f=delete_category",
            method:"POST",
            data:{id: $id},
            dataType:"json",
            error:err=>{
                console.log(err)
                alert_toast("An error occured.",'error');
                end_loader();
            },
            success:function(resp){
                if(typeof resp== 'object' && resp.status == 'success'){
                    location.reload();
                }else{
                    alert_toast("An error occured.",'error');
                    end_loader();
                }
            }
        })
    }
</script>