<?php 
$user = $conn->query("SELECT * FROM users where id ='".$_settings->userdata('id')."'");
foreach($user->fetch_array() as $k =>$v){
    $meta[$k] = $v;
}
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

    .card.card-outline.card-primary.rounded-0.shadow {
        border-radius: var(--radius);
        background-color: #ffffff;
        box-shadow: var(--shadow);
        padding: 1.25rem;
        border: none;
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

    .table {
        border-collapse: separate !important;
        border-spacing: 0 0.75rem !important;
        width: 100%;
    }

    .table thead th {
        background-color: #eef2ff;
        color: var(--text-main);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 12px 16px;
        border: none;
    }

    .table tbody tr {
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border-radius: var(--radius);
    }

    .table tbody td {
        padding: 14px 18px !important;
        vertical-align: middle;
        border: none !important;
        color: var(--text-muted);
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

    .badge-success {
        background-color: #22c55e;
        color: #fff;
    }

    .badge-danger {
        background-color: #ef4444;
        color: #fff;
    }

    .badge-primary {
        background-color: #2563eb;
        color: #fff;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: #fff;
    }

    .badge-warning {
        background-color: #facc15;
        color: #1e293b;
    }

    .truncate-1 {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ml-2 {
        margin-left: 0.5rem;
    }

    /* Additional styles for the form */
    .card-outline {
        border-radius: var(--radius);
        background-color: #ffffff;
        box-shadow: var(--shadow);
        border: none;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-group label {
        font-weight: 500;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .form-control {
        border: 1px solid var(--gray-border);
        border-radius: var(--radius);
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }
    
    .custom-select {
        border: 1px solid var(--gray-border);
        border-radius: var(--radius);
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        height: auto;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
        appearance: none;
    }
    
    .card-footer {
        background-color: #f8fafc;
        border-top: 1px solid var(--gray-border);
        padding: 1.25rem 1.5rem;
        border-bottom-left-radius: var(--radius);
        border-bottom-right-radius: var(--radius);
    }
    
    .btn {
        padding: 0.6rem 1.2rem;
        border-radius: var(--radius);
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-primary {
        background: linear-gradient(90deg, var(--primary), var(--primary-hover));
        border: none;
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    
    .btn-secondary {
        background-color: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--gray-border);
    }
    
    /* Remove hover effect for secondary button */
    .btn-secondary:hover {
        background-color: #ffffff;
        color: var(--text-main);
        border: 1px solid var(--gray-border);
        transform: none;
        box-shadow: none;
    }
    
    .alert {
        border-radius: var(--radius);
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        border: none;
    }
    
    .alert-danger {
        background-color: #fee2e2;
        color: #b91c1c;
    }
    
    .alert-success {
        background-color: #dcfce7;
        color: #166534;
    }
</style>
<?php if($_settings->chk_flashdata('success')): ?>
    
<script>
    alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif;?>
<div class="card card-outline ">
    <div class="card-header">
        <h3 class="card-title">Update Profile</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div id="msg"></div>
            <form action="" id="manage-user">    
                <input type="hidden" name="id" value="<?php echo $_settings->userdata('id') ?>">
                <div class="form-group">
                    <label for="name">First Name</label>
                    <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo isset($meta['firstname']) ? $meta['firstname']: '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="name">Last Name</label>
                    <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo isset($meta['lastname']) ? $meta['lastname']: '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($meta['username']) ? $meta['username']: '' ?>" required  autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" value="" autocomplete="off">
                    <small><i>Leave this blank if you dont want to change the password.</i></small>
                </div>
            </form>
        </div>
    </div>
    <div class="card-footer">
        <div class="col-md-12">
            <div class="row">
                <button class="btn btn-primary mr-2" form="manage-user">Update</button>
                <a class="btn btn-secondary" href="<?php echo base_url ?>admin/">Cancel</a>
            </div>
        </div>
    </div>
</div>

<script>
$('#manage-user').submit(function(e){
    e.preventDefault();
    var _this = $(this)
    start_loader()
    $.ajax({
        url:_base_url_+'classes/Users.php?f=save',
        data: new FormData($(this)[0]),
        cache: false,
        contentType: false,
        processData: false,
        method: 'POST',
        type: 'POST',
        success:function(resp){
            if(resp == 1){
                // Show success message
                $('#msg').html('<div class="alert alert-success">Profile successfully updated! Redirecting to dashboard...</div>');
                
                // Redirect to dashboard after 2 seconds
                setTimeout(function(){
                    window.location.href = '<?php echo base_url ?>admin/';
                }, 2000);
            } else {
                $('#msg').html('<div class="alert alert-danger">Username already exist</div>')
            }
            end_loader()
        },
        error: function(xhr, status, error) {
            $('#msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>')
            end_loader()
        }
    })
})
</script>