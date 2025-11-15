<?php if($_settings->chk_flashdata('success')): ?>
<script>
	alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
	
</script>
<?php endif;?>

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
        }

        .table-striped tbody tr:nth-of-type(odd) td {
            background-color: #fff !important;
        }

        .truncate-1 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
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

        .ml-2 {
            margin-left: 0.5rem;
        }

        /* Responsive wrapper for table */
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Mobile Styles - Tablets and below */
        @media (max-width: 991px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .card-header h3.card-title {
                font-size: 1.3rem;
            }

            .card-tools {
                width: 100%;
            }

            .card-tools .btn {
                width: 100%;
                justify-content: center;
            }

            .table-responsive-wrapper {
                margin: 0 -1.25rem;
                padding: 0 1.25rem;
            }

            .table {
                min-width: 900px;
            }

            .table thead tr th {
                font-size: 0.75rem;
                padding: 10px 12px;
                white-space: nowrap;
            }

            .table tbody tr td {
                padding: 12px 14px !important;
                font-size: 0.85rem;
            }

            .btn-flat.btn-default {
                padding: 0.45rem 0.9rem;
                font-size: 0.8rem;
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.3em 0.65em;
            }
        }

        /* Small Mobile */
        @media (max-width: 767px) {
            .card.card-outline {
                padding: 1rem !important;
            }

            .card-header h3.card-title {
                font-size: 1.2rem;
            }

            .table-responsive-wrapper {
                margin: 0 -1rem;
                padding: 0 1rem;
            }

            .table {
                min-width: 850px;
            }

            .table thead tr th {
                font-size: 0.7rem;
                padding: 8px 10px;
            }

            .table tbody tr td {
                padding: 10px 12px !important;
                font-size: 0.8rem;
            }

            .btn-flat.btn-primary {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }

            .dropdown-menu a.dropdown-item {
                font-size: 0.85rem;
                padding: 0.45rem 1rem;
            }
        }

        /* Extra Small Mobile */
        @media (max-width: 576px) {
            .card.card-outline {
                padding: 0.75rem !important;
            }

            .card-header h3.card-title {
                font-size: 1.1rem;
            }

            .btn-flat.btn-primary {
                padding: 0.45rem 0.85rem;
                font-size: 0.8rem;
            }

            .table-responsive-wrapper {
                margin: 0 -0.75rem;
                padding: 0 0.75rem;
            }

            .table {
                min-width: 800px;
                border-spacing: 0 0.75rem !important;
            }

            .table thead tr th {
                font-size: 0.65rem;
                padding: 7px 8px;
            }

            .table tbody tr td {
                padding: 8px 10px !important;
                font-size: 0.75rem;
            }

            .badge {
                font-size: 0.65rem;
                padding: 0.25em 0.55em;
            }

            .btn-flat.btn-default {
                padding: 0.4rem 0.7rem;
                font-size: 0.75rem;
            }

            .dropdown-menu a.dropdown-item {
                font-size: 0.8rem;
                padding: 0.4rem 0.9rem;
            }
        }

        /* Very Small Mobile */
        @media (max-width: 400px) {
            .card-header h3.card-title {
                font-size: 1rem;
            }

            .btn-flat.btn-primary {
                padding: 0.4rem 0.75rem;
                font-size: 0.75rem;
            }

            .table {
                min-width: 750px;
            }

            .table thead tr th {
                font-size: 0.6rem;
                padding: 6px 7px;
            }

            .table tbody tr td {
                padding: 7px 8px !important;
                font-size: 0.7rem;
            }
        }

        /* Ensure DataTables controls are responsive */
        @media (max-width: 767px) {
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                text-align: left !important;
                margin-bottom: 0.75rem;
            }

            .dataTables_wrapper .dataTables_length label,
            .dataTables_wrapper .dataTables_filter label {
                font-size: 0.85rem;
            }

            .dataTables_wrapper .dataTables_length select {
                padding: 0.25rem 0.5rem;
                font-size: 0.85rem;
            }

            .dataTables_wrapper .dataTables_filter input {
                padding: 0.25rem 0.5rem;
                font-size: 0.85rem;
                width: 100%;
                max-width: 200px;
            }

            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                text-align: center !important;
                font-size: 0.8rem;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }
</style>

<div class="card card-outline">
	<div class="card-header">
		<h3 class="card-title">List of Facilities</h3>
		<div class="card-tools">
			<a href="?page=facilities/manage_facility" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>  Create New</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
            <div class="table-responsive-wrapper">
                <table class="table table-bordered table-stripped">
                    <colgroup>
                        <col width="5%">
                        <col width="15%">
                        <col width="15%">
                        <col width="15%">
                        <col width="25%">
                        <col width="10%">
                        <col width="15%">
                    </colgroup>
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Date Created</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                            $qry = $conn->query("SELECT f.*,c.name as category from `facility_list` f inner join category_list c on f.category_id = c.id where f.delete_flag = 0 order by (f.`facility_code`) asc ");
                            while($row = $qry->fetch_assoc()):
                                foreach($row as $k=> $v){
                                    $row[$k] = trim(stripslashes($v));
                                }
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo date("F j, Y, g:i A", strtotime($row['date_created'])) ?></td>
                                <td><?php echo ucwords($row['facility_code']) ?></td>
                                <td><?php echo ucwords($row['category']) ?></td>
                                <td><?php echo ($row['name']) ?></td>
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
                                        <a class="dropdown-item" href="?page=facilities/view_facility&id=<?php echo $row['id'] ?>"><span class="fa fa-eye text-dark"></span> View</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="?page=facilities/manage_facility&id=<?php echo $row['id'] ?>"><span class="fa fa-edit text-primary"></span> Edit</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-trash text-danger"></span> Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$('.delete_data').click(function(){
			_conf("Are you sure to delete this Facility permanently?","delete_facility",[$(this).attr('data-id')])
		})
        $('.table th, .table td').addClass("align-middle px-2 py-1")
		$('.table').dataTable();
	})
	function delete_facility($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_facility",
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