<?php
require_once('./config.php');
if ($_settings->userdata('id') > 0 && $_settings->userdata('login_type') == 2) {
    $qry = $conn->query("SELECT * FROM `client_list` WHERE id = '{$_settings->userdata('id')}'");
    if ($qry->num_rows > 0) {
        $res = $qry->fetch_array();
        foreach ($res as $k => $v) {
            if (!is_numeric($k)) {
                $$k = $v;
            }
        }
    } else {
        echo "<script>alert('You are not allowed to access this page. Unknown User ID.'); location.replace('./')</script>";
    }
} else {
    echo "<script>alert('You are not allowed to access this page.'); location.replace('./')</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account | <?= $_settings->info('name') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #93c5fd;
            --secondary: #6b7280;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --light: #f9fafb;
            --dark: #111827;
            --border-radius: 0.5rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;

        }

        body {
             font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #374151;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container-main {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            flex: 1;
            width: 100%;
        }

        .auth-container {
            width: 100%;
            max-width: 56rem;
            margin: 0 auto;
        }

        .auth-card {
            background: #ffffff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .auth-header {
            padding: 3rem 3rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid #f3f4f6;
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.75rem;
        }

        .auth-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        .auth-body {
            padding: 3rem;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-label {
            display: block;
            font-size: 1rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.75rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #374151;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: var(--border-radius);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
            height: auto;
            min-height: 3.2rem;
            color: #374151;
        }

        select.form-control:focus {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%232563eb' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        select.form-control option {
            padding: 0.5rem 1rem;
            font-size: 1rem;
            background-color: #ffffff;
            color: #374151;
        }

        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
        }

        .input-group .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
        }

        .input-group-append {
            display: flex;
            margin-left: -1px;
        }

        .input-group-text {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #6b7280;
            text-align: center;
            white-space: nowrap;
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            cursor: pointer;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            font-size: 1rem;
            line-height: 1.5;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            user-select: none;
            border: 1px solid transparent;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .btn-primary {
            color: #ffffff;
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-primary:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
        }

        .text-center {
            text-align: center;
        }

        .mt-3 {
            margin-top: 1.5rem;
        }

        .mt-4 {
            margin-top: 2rem;
        }

        .text-sm {
            font-size: 0.9375rem;
        }

        .text-muted {
            color: #6b7280;
        }

        .alert {
            position: relative;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -1rem;
            margin-left: -1rem;
        }

        .col-md-4,
        .col-md-6,
        .col-md-8 {
            padding-right: 1rem;
            padding-left: 1rem;
        }

        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col-md-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }

        hr {
            margin: 2rem 0;
            border: 0;
            border-top: 1px solid #e5e7eb;
        }

        footer {
            display: none !important;
        }

        /* Enhanced Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100000; /* Increased z-index to ensure it's on top */
            pointer-events: none; /* Allows clicks to pass through the container itself */
        }

        .toast {
            /* THIS IS THE CHANGE: Permanently hides the toast visually */
            display: none !important;

            /* The following styles define the appearance and animation,
               but will be overridden by display: none; */
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 16px 20px;
            margin-bottom: 10px;
            /* display: flex;  -- This is now overridden */
            align-items: center;
            min-width: 300px;
            max-width: 400px;
            border-left: 4px solid var(--success);
            transform: translateX(400px); /* Will not be seen */
            opacity: 0; /* Will not be seen */
            transition: all 0.3s ease; /* Will not apply */
            pointer-events: auto; /* Will not apply */
        }

        .toast.show {
            /* These styles are also effectively overridden by display: none !important; */
            transform: translateX(0);
            opacity: 1;
        }

        .toast.success {
            border-left-color: var(--success);
        }

        .toast.error {
            border-left-color: var(--danger);
        }

        .toast-icon {
            font-size: 18px;
            margin-right: 12px;
            color: var(--success);
        }

        .toast.error .toast-icon {
            color: var(--danger);
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 2px;
        }

        .toast-message {
            font-size: 14px;
            color: #6b7280;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            color: #9ca3af;
            cursor: pointer;
            margin-left: 12px;
            padding: 0;
        }

        .toast-close:hover {
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .auth-card {
                border: none;
                box-shadow: none;
            }

            .col-md-4,
            .col-md-6,
            .col-md-8 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .auth-header {
                padding: 2rem 1.5rem 1rem;
            }

            .auth-body {
                padding: 2rem 1.5rem;
            }

            .toast-container {
                right: 10px;
                left: 10px;
            }

            .toast {
                min-width: auto;
                max-width: none;
            }
        }
    </style>
</head>
<br>

<body>
    <div class="toast-container" id="toast-container"></div>

    <div class="container-main">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1 class="auth-title">Manage Account Details</h1>
                    <p class="auth-subtitle">Update your personal information and credentials</p>
                </div>

                <div class="auth-body">
                    <form id="register-frm" method="POST">
                        <input type="hidden" name="id" value="<?= $id ?? '' ?>">

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" id="firstname" name="firstname" class="form-control" value="<?= $firstname ?? '' ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="middlename" class="form-label">Middle Name</label>
                                <input type="text" id="middlename" name="middlename" class="form-control" value="<?= $middlename ?? '' ?>">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" id="lastname" name="lastname" class="form-control" value="<?= $lastname ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-8">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male" <?= isset($gender) && $gender == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= isset($gender) && $gender == 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= isset($gender) && $gender == 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="contact" class="form-label">Contact Number</label>
                                <input type="tel" id="contact" name="contact" class="form-control" value="<?= $contact ?? '' ?>" pattern="\d{11}" required>
                                <small class="text-muted">11-digit mobile number</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" rows="3" class="form-control"><?= $address ?? '' ?></textarea>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= $email ?? '' ?>" required>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Leave blank to keep current">
                                    <div class="input-group-append">
                                        <span class="input-group-text pass_type" data-type="password">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="cpassword" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" id="cpassword" class="form-control">
                                    <div class="input-group-append">
                                        <span class="input-group-text pass_type" data-type="password">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="oldpassword" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" name="oldpassword" id="oldpassword" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text pass_type" data-type="password">
                                        <i class="fa fa-eye-slash"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button class="btn btn-primary btn-block" type="submit">Update Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url ?>plugins/jquery/jquery.min.js"></script>
    <script>
        // Enhanced Toast Notification Function
        function showToast(message, type = 'success', title = null) {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const toastTitle = title || (type === 'success' ? 'Success!' : 'Error!');

            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${toastTitle}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="removeToast(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;

            toastContainer.appendChild(toast);

            // Trigger animation (these lines will have no visual effect due to display: none;)
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);

            // Auto remove after 5 seconds (still useful to clear elements from DOM)
            setTimeout(() => {
                removeToast(toast.querySelector('.toast-close'));
            }, 5000);
        }

        function removeToast(closeButton) {
            const toast = closeButton.closest('.toast');
            toast.classList.remove('show');
            // Element is already hidden by CSS, but we still remove it from DOM
            setTimeout(() => {
                toast.remove();
            }, 300);
        }

        // Alternative alert_toast function for compatibility
        function alert_toast(message, type = 'success') {
            showToast(message, type);
        }

        // Placeholder loader functions (implement based on your existing system)
        function start_loader() {
            const btn = document.querySelector('#register-frm button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        }

        function end_loader() {
            const btn = document.querySelector('#register-frm button[type="submit"]');
            btn.disabled = false;
            btn.innerHTML = 'Update Account';
        }

        $(function() {
            $('.pass_type').click(function() {
                const input = $(this).closest('.input-group').find('input');
                const type = input.attr('type') === 'password' ? 'text' : 'password';
                input.attr('type', type);
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });

            $('#register-frm').submit(function(e) {
                e.preventDefault();
                const form = $(this);
                $('.err-msg').remove();

                if ($('#password').val() !== $('#cpassword').val()) {
                    form.prepend('<div class="alert alert-danger err-msg">Passwords do not match.</div>');
                    // showToast("Passwords do not match.", 'error', 'Validation Error'); // This will not show
                    return false;
                }

                start_loader();
                $.ajax({
                    url: _base_url_ + "classes/Users.php?f=save_client",
                    method: 'POST',
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    error: err => {
                        console.log(err);
                        // showToast("An error occurred while updating your account", 'error'); // This will not show
                        end_loader();
                    },
                    success: function(resp) {
                        if (resp.status === 'success') {
                            // showToast("Account updated successfully!", 'success'); // This will not show

                            form.prepend('<div class="alert alert-success err-msg"><i class="fas fa-check-circle"></i> Account updated successfully!</div>');

                            $('#password, #cpassword').val('');

                            setTimeout(() => {
                                $('.alert-success').fadeOut();
                            }, 3000);

                        } else if (resp.status === 'failed' && resp.msg) {
                            form.prepend('<div class="alert alert-danger err-msg">' + resp.msg + '</div>');
                            // showToast(resp.msg, 'error'); // This will not show
                        } else {
                            // showToast("An error occurred while updating your account", 'error'); // This will not show
                        }
                        end_loader();
                    }
                });
            });
        });
    </script>
</body>
</html>