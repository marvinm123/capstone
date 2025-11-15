<?php
// Initialize variables to avoid undefined variable warnings
$id = $firstname = $middlename = $lastname = $gender = $contact = $address = $email = $status = '';

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $client_id = (int)$_GET['id'];
    $qry = $conn->query("SELECT * FROM `client_list` WHERE id = '{$client_id}' ");
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            // Fix: Only call stripslashes() on string values, handle null safely
            $$k = $v !== null ? stripslashes($v) : '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($id) ? "Update" : "Create New" ?> Client</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --gray-bg: #f9fafb;
            --gray-border: #e2e8f0;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --shadow: 0 6px 30px rgba(0, 0, 0, 0.06);
            --radius: 0.75rem;
            --error: #dc2626;
            --success: #16a34a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-bg);
            color: var(--text-main);
            line-height: 1.6;
        }

        .card.card-outline {
            background-color: #fff;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-border);
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 0 0 1.5rem;
            border-bottom: 1px solid var(--gray-border);
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control, .custom-select {
            border-radius: var(--radius);
            border: 1px solid var(--gray-border);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            width: 100%;
        }

        .form-control:focus, .custom-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .form-control.error {
            border-color: var(--error);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        label.control-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
            color: var(--text-main);
        }

        .required::after {
            content: " *";
            color: var(--error);
        }

        .btn {
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: #fff;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
        }

        .btn-default {
            background: #fff;
            color: var(--text-main);
            border: 1px solid var(--gray-border);
        }

        .btn-default:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: stretch;
            width: 100%;
        }

        .input-group .form-control {
            flex: 1 1 auto;
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group-text {
            background: #fff;
            border: 1px solid var(--gray-border);
            border-left: none;
            padding: 0.75rem 1rem;
            border-top-right-radius: var(--radius);
            border-bottom-right-radius: var(--radius);
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.2s ease;
        }

        .input-group-text:hover {
            color: var(--primary);
        }

        .text-muted {
            font-size: 0.875rem;
            color: var(--text-muted) !important;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
        }

        .alert-danger {
            color: var(--error);
            background-color: #fef2f2;
            border-color: #fecaca;
        }

        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: var(--shadow);
        }

        .modal-header {
            border-bottom: 1px solid var(--gray-border);
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            color: var(--text-main);
        }

        .modal-body {
            padding: 1.5rem;
            color: var(--text-muted);
        }

        .modal-footer {
            border-top: 1px solid var(--gray-border);
            padding: 1.5rem;
        }

        .btn-secondary {
            background: var(--gray-border);
            color: var(--text-main);
        }

        .btn-danger {
            background: var(--error);
            color: #fff;
        }
/* Fix for select boxes (Gender & Status) */
.custom-select {
    width: 100%;
    min-width: 100%;
    height: 48px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    border-radius: var(--radius);
    border: 1px solid var(--gray-border);
    background-color: #fff;
    color: var(--text-main);
    line-height: normal;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1rem;
    cursor: pointer;
}

.custom-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

/* Make sure both columns have enough room */
.form-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.5rem;
}

.form-col {
    flex: 1 1 50%;
    padding: 0 0.5rem;
    min-width: 300px;
}

/* Ensure dropdown text is visible and not clipped */
select {
    overflow: visible !important;
    text-overflow: clip !important;
    white-space: nowrap !important;
}

/* On smaller screens, stack vertically */
@media (max-width: 768px) {
    .form-col {
        flex: 1 1 100%;
    }
}


        
    </style>

    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title"><?= isset($id) ? "Update" : "Create New" ?> Client Details</h3>
        </div>
        <div class="card-body">
            <form action="" id="client-form">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="firstname" class="control-label required">First Name</label>
                            <input name="firstname" id="firstname" type="text" class="form-control" 
                                   value="<?= htmlspecialchars($firstname) ?>" 
                                   placeholder="Enter first name" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="lastname" class="control-label required">Last Name</label>
                            <input name="lastname" id="lastname" type="text" class="form-control" 
                                   value="<?= htmlspecialchars($lastname) ?>" 
                                   placeholder="Enter last name" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="middlename" class="control-label">Middle Name</label>
                    <input name="middlename" id="middlename" type="text" class="form-control" 
                           value="<?= htmlspecialchars($middlename) ?>" 
                           placeholder="Enter middle name (optional)">
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="gender" class="control-label">Gender</label>
                            <select name="gender" id="gender" class="custom-select">
                                <option value="">Select Gender</option>
                                <option value="Male" <?= $gender == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $gender == 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= $gender == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="status" class="control-label">Status</label>
                            <select name="status" id="status" class="custom-select">
                                <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $status == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="contact" class="control-label required">Contact Number</label>
                            <input name="contact" id="contact" type="text" class="form-control" 
                                   value="<?= htmlspecialchars($contact) ?>" 
                                   placeholder="Enter contact number" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="email" class="control-label required">Email Address</label>
                            <input name="email" id="email" type="email" class="form-control" 
                                   value="<?= htmlspecialchars($email) ?>" 
                                   placeholder="Enter email address" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address" class="control-label required">Address</label>
                    <input name="address" id="address" type="text" class="form-control" 
                           value="<?= htmlspecialchars($address) ?>" 
                           placeholder="Enter complete address" required>
                </div>

                <div class="form-group">
                    <label for="password" class="control-label">New Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="<?= isset($id) ? 'Leave blank to keep current password' : 'Enter password' ?>"
                               <?= !isset($id) ? 'required' : '' ?>>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fa fa-eye-slash text-muted pass_type" data-type="password"></i>
                            </span>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <?= isset($id) ? 'Leave blank if not changing password' : 'Password is required for new clients' ?>
                    </small>
                </div>
            </form>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <button class="btn btn-default" type="button" id="cancel-btn">Cancel</button>
            <button class="btn btn-primary" form="client-form" id="save-btn">
                <i class="fas fa-save mr-1"></i>
                <?= isset($id) ? 'Update' : 'Create' ?> Client
            </button>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Changes?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel? All unsaved changes will be lost.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Stay on Page</button>
                    <button type="button" class="btn btn-danger" id="confirmCancel">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Password visibility toggle
        $('.pass_type').click(function() {
            const $icon = $(this);
            const $input = $icon.closest('.input-group').find('input');
            const type = $input.attr('type') === 'password' ? 'text' : 'password';
            
            $input.attr('type', type);
            $icon.toggleClass('fa-eye fa-eye-slash');
        });

        // Form validation and submission
        $('#client-form').submit(function(e) {
            e.preventDefault();
            const $form = $(this);
            const $saveBtn = $('#save-btn');
            const originalText = $saveBtn.html();
            
            // Clear previous errors
            $('.error').removeClass('error');
            $('.alert').remove();
            
            // Basic validation
            let isValid = true;
            const requiredFields = ['firstname', 'lastname', 'contact', 'email', 'address'];
            
            requiredFields.forEach(field => {
                const $field = $('#' + field);
                if (!$field.val().trim()) {
                    $field.addClass('error');
                    isValid = false;
                }
            });

            // Email validation
            const email = $('#email').val().trim();
            if (email && !isValidEmail(email)) {
                $('#email').addClass('error');
                isValid = false;
            }

            if (!isValid) {
                showAlert('Please fill in all required fields correctly.', 'danger');
                return;
            }

            // Disable save button and show loading state
            $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
            start_loader();

            $.ajax({
                url: _base_url_ + "classes/Users.php?f=save_client",
                data: new FormData(this),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                dataType: 'json',
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showAlert('An error occurred while saving. Please try again.', 'danger');
                    end_loader();
                    $saveBtn.prop('disabled', false).html(originalText);
                },
                success: function(resp) {
                    if (resp.status === 'success') {
                        showAlert(resp.msg || 'Client saved successfully!', 'success');
                        setTimeout(() => {
                            location.href = "./?page=clients";
                        }, 1500);
                    } else {
                        showAlert(resp.msg || 'An error occurred while saving.', 'danger');
                        $saveBtn.prop('disabled', false).html(originalText);
                    }
                    end_loader();
                }
            });
        });

        // Cancel button handlers
        $('#cancel-btn').click(function() {
            if (isFormDirty()) {
                $('#cancelConfirmModal').modal('show');
            } else {
                window.location.href = "./?page=clients";
            }
        });

        $('#confirmCancel').click(function() {
            window.location.href = "./?page=clients";
        });

        // Remove error class when user starts typing
        $('input, select').on('input change', function() {
            $(this).removeClass('error');
        });

        // Helper functions
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showAlert(message, type) {
            const alertClass = type === 'danger' ? 'alert-danger' : 'alert-success';
            const $alert = $('<div>').addClass(`alert ${alertClass}`).text(message);
            $alert.hide().prependTo('#client-form').show('slow');
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                $alert.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        }

        function isFormDirty() {
            const originalData = {
                firstname: '<?= htmlspecialchars($firstname) ?>',
                middlename: '<?= htmlspecialchars($middlename) ?>',
                lastname: '<?= htmlspecialchars($lastname) ?>',
                gender: '<?= htmlspecialchars($gender) ?>',
                contact: '<?= htmlspecialchars($contact) ?>',
                address: '<?= htmlspecialchars($address) ?>',
                email: '<?= htmlspecialchars($email) ?>',
                status: '<?= htmlspecialchars($status) ?>'
            };

            const currentData = {
                firstname: $('#firstname').val(),
                middlename: $('#middlename').val(),
                lastname: $('#lastname').val(),
                gender: $('#gender').val(),
                contact: $('#contact').val(),
                address: $('#address').val(),
                email: $('#email').val(),
                status: $('#status').val()
            };

            return JSON.stringify(originalData) !== JSON.stringify(currentData);
        }
    });
    </script>
</body>
</html>