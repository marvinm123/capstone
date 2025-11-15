<?php 
require_once('./config.php');

// Cleanup expired tokens
$cleanup_query = "DELETE FROM password_reset_tokens WHERE token_expiry < NOW()";
$conn->query($cleanup_query);

$message = '';
$message_type = '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    $token = trim($_POST['token']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email) || empty($token)) {
        $message = "Please enter the reset code.";
        $message_type = 'error';
    } else if (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = 'error';
    } else if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = 'error';
    } else {
        // Verify token
        $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE email = ? AND token = ? LIMIT 1");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $token_data = $result->fetch_assoc();
            
            // Check if token has expired
            $current_time = date('Y-m-d H:i:s');
            if ($current_time > $token_data['token_expiry']) {
                $message = "Reset code has expired. Please request a new one.";
                $message_type = 'error';
                // Delete expired token
                $conn->query("DELETE FROM password_reset_tokens WHERE id = " . $token_data['id']);
            } else {
                // Token is valid - Update password
                $hashed_password = md5($new_password);
                
                $update_stmt = $conn->prepare("UPDATE client_list SET password = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $hashed_password, $email);
                
                if ($update_stmt->execute()) {
                    // Delete used token
                    $conn->query("DELETE FROM password_reset_tokens WHERE id = " . $token_data['id']);
                    
                    $message = "Password reset successful! Redirecting to login...";
                    $message_type = 'success';
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 2000);
                    </script>";
                } else {
                    $message = "Failed to reset password. Please try again.";
                    $message_type = 'error';
                }
                $update_stmt->close();
            }
        } else {
            $message = "Invalid reset code. Please check and try again.";
            $message_type = 'error';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php') ?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password | <?= $_settings->info('name') ?></title>
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
      --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
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
      max-width: 32rem;
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

    .company-logo {
      height: 6rem;
      width: auto;
      margin-bottom: 1.5rem;
      max-width: 100%;
      object-fit: contain;
    }

    .reset-icon {
      font-size: 4rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }

    .auth-title {
      font-size: 1.875rem;
      font-weight: 600;
      color: #111827;
      margin-bottom: 0.75rem;
    }

    .auth-subtitle {
      color: #6b7280;
      font-size: 0.9375rem;
      line-height: 1.6;
    }

    .email-display {
      color: var(--primary);
      font-weight: 600;
      word-break: break-all;
    }

    .auth-body {
      padding: 2.5rem 3rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
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
      border: 2px solid #d1d5db;
      border-radius: var(--border-radius);
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .form-control.token-input {
      text-align: center;
      letter-spacing: 0.5rem;
      font-size: 1.125rem;
      font-weight: 600;
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
      border: 2px solid #d1d5db;
      border-left: none;
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
      text-decoration: none;
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
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
    }

    .text-center {
      text-align: center;
    }

    .mt-3 {
      margin-top: 1.5rem;
    }

    .text-sm {
      font-size: 0.9375rem;
    }

    .text-muted {
      color: #6b7280;
    }

    .text-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .text-link:hover {
      text-decoration: underline;
      color: var(--primary-dark);
    }

    .alert {
      position: relative;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
      border: 1px solid transparent;
      border-radius: var(--border-radius);
      font-size: 0.9375rem;
      animation: slideDown 0.3s ease-in-out;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
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

    .info-box {
      background-color: #f0f9ff;
      border: 1px solid #bfdbfe;
      border-radius: var(--border-radius);
      padding: 1rem;
      margin-bottom: 1.5rem;
      font-size: 0.875rem;
      color: #1e40af;
    }

    .info-box i {
      margin-right: 0.5rem;
      color: var(--primary);
    }

    .password-strength {
      font-size: 0.875rem;
      margin-top: 0.5rem;
      padding: 0.5rem;
      border-radius: 0.25rem;
      display: none;
    }

    .password-strength.weak {
      background-color: #fee;
      color: #c33;
      display: block;
    }

    .password-strength.medium {
      background-color: #ffe;
      color: #c93;
      display: block;
    }

    .password-strength.strong {
      background-color: #efe;
      color: #3c3;
      display: block;
    }

    @media (max-width: 768px) {
      .auth-card {
        border: none;
        box-shadow: none;
      }

      .auth-header {
        padding: 2rem 1.5rem 1rem;
      }

      .auth-body {
        padding: 2rem 1.5rem;
      }

      .company-logo {
        height: 4.5rem;
      }

      .reset-icon {
        font-size: 3rem;
      }

      .form-control.token-input {
        letter-spacing: 0.3rem;
      }
    }
  </style>
</head>

<body>
  <script>
    start_loader()
  </script>

  <div class="container-main">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <img src="<?= validate_image($_settings->info('logo')) ?>" alt="<?= $_settings->info('name') ?>" class="company-logo">
          <div class="reset-icon">
            <i class="fas fa-key"></i>
          </div>
          <h1 class="auth-title">Reset Your Password</h1>
          <p class="auth-subtitle">
            Enter the code we sent to<br>
            <span class="email-display"><?= htmlspecialchars($email) ?></span>
          </p>
        </div>

        <div class="auth-body">
          <?php if($message): ?>
            <div class="alert alert-<?= $message_type == 'success' ? 'success' : 'danger' ?>">
              <?= $message ?>
            </div>
          <?php endif; ?>

          <div class="info-box">
            <i class="fas fa-info-circle"></i>
            Create a strong password with at least 8 characters.
          </div>

          <form id="reset-frm" action="" method="post">
            <input type="hidden" name="reset_password" value="1">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            
            <div class="form-group">
              <label for="token" class="form-label">Reset Code</label>
              <input type="text" id="token" name="token" class="form-control token-input" placeholder="000000" required maxlength="6" pattern="\d{6}" autocomplete="off">
            </div>

            <div class="form-group">
              <label for="new_password" class="form-label">New Password</label>
              <div class="input-group">
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password" required minlength="8">
                <div class="input-group-append">
                  <span class="input-group-text pass_type" data-target="new_password">
                    <i class="fas fa-eye-slash"></i>
                  </span>
                </div>
              </div>
              <div id="password-strength" class="password-strength"></div>
            </div>

            <div class="form-group">
              <label for="confirm_password" class="form-label">Confirm New Password</label>
              <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password" required minlength="8">
                <div class="input-group-append">
                  <span class="input-group-text pass_type" data-target="confirm_password">
                    <i class="fas fa-eye-slash"></i>
                  </span>
                </div>
              </div>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                Reset Password
              </button>
            </div>

            <div class="text-center mt-3">
              <p class="text-sm text-muted">Didn't receive the code? <a href="forgot_password.php" class="text-link">Request New Code</a></p>
              <p class="text-sm text-muted mt-2"><a href="login.php" class="text-link">← Back to Login</a></p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="<?= base_url ?>plugins/jquery/jquery.min.js"></script>
  <script src="<?= base_url ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      end_loader();
      
      // Auto-format token input (only allow numbers)
      $('#token').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
      });

      // Toggle password visibility
      $('.pass_type').on('click', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
      });

      // Password strength indicator
      $('#new_password').on('input', function() {
        const password = $(this).val();
        const strengthDiv = $('#password-strength');
        
        if (password.length === 0) {
          strengthDiv.hide();
          return;
        }
        
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        strengthDiv.removeClass('weak medium strong');
        
        if (strength <= 2) {
          strengthDiv.addClass('weak').text('⚠️ Weak password');
        } else if (strength <= 3) {
          strengthDiv.addClass('medium').text('⚡ Medium strength');
        } else {
          strengthDiv.addClass('strong').text('✓ Strong password');
        }
      });

      // Confirm password match validation
      $('#confirm_password').on('input', function() {
        const password = $('#new_password').val();
        const confirm = $(this).val();
        
        if (confirm && password !== confirm) {
          this.setCustomValidity('Passwords do not match');
        } else {
          this.setCustomValidity('');
        }
      });
    });
  </script>
</body>

</html>