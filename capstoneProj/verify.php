<?php 
require_once('./config.php');

// ============================================
// AUTOMATIC CLEANUP: Delete expired temporary registrations
// This runs every time someone visits the verification page
// ============================================
$cleanup_query = "DELETE FROM temp_registrations WHERE code_expiry < NOW()";
$conn->query($cleanup_query);

$message = '';
$message_type = '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);

    if (empty($email) || empty($code)) {
        $message = "Please enter the verification code.";
        $message_type = 'error';
    } else {
        // Check verification code in temp_registrations table
        $stmt = $conn->prepare("SELECT * FROM temp_registrations WHERE email = ? AND verification_code = ? LIMIT 1");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $temp_user = $result->fetch_assoc();
            
            // Check if code has expired
            $current_time = date('Y-m-d H:i:s');
            if ($current_time > $temp_user['code_expiry']) {
                $message = "Verification code has expired. Please register again.";
                $message_type = 'error';
                // Delete expired temp registration
                $conn->query("DELETE FROM temp_registrations WHERE id = " . $temp_user['id']);
            } else {
                // Code is valid - Move data to client_list
                $insert_stmt = $conn->prepare("INSERT INTO client_list (firstname, middlename, lastname, gender, contact, address, email, password, status, delete_flag, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 0, NOW())");
                $insert_stmt->bind_param("ssssssss", 
                    $temp_user['firstname'],
                    $temp_user['middlename'],
                    $temp_user['lastname'],
                    $temp_user['gender'],
                    $temp_user['contact'],
                    $temp_user['address'],
                    $temp_user['email'],
                    $temp_user['password']
                );
                
                if ($insert_stmt->execute()) {
                    // Successfully created account - Delete temp registration
                    $conn->query("DELETE FROM temp_registrations WHERE id = " . $temp_user['id']);
                    
                    $message = "Email verified successfully! Your account has been created. Redirecting to login...";
                    $message_type = 'success';
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 2000);
                    </script>";
                } else {
                    $message = "Verification failed. Please try again or contact support.";
                    $message_type = 'error';
                }
                $insert_stmt->close();
            }
        } else {
            $message = "Invalid verification code. Please check and try again.";
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
  <title>Email Verification | <?= $_settings->info('name') ?></title>
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

    .verification-icon {
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
      font-size: 1.125rem;
      line-height: 1.5;
      color: #374151;
      background-color: #ffffff;
      border: 2px solid #d1d5db;
      border-radius: var(--border-radius);
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
      text-align: center;
      letter-spacing: 0.5rem;
      font-weight: 600;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .form-control::placeholder {
      color: #9ca3af;
      letter-spacing: normal;
      font-weight: normal;
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

    .btn-primary:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
    }

    .btn-secondary {
      color: #374151;
      background-color: #f3f4f6;
      border-color: #e5e7eb;
    }

    .btn-secondary:hover {
      background-color: #e5e7eb;
      border-color: #d1d5db;
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

    .alert-info {
      color: #004085;
      background-color: #d1ecf1;
      border-color: #bee5eb;
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

      .verification-icon {
        font-size: 3rem;
      }

      .form-control {
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
          <div class="verification-icon">
            <i class="fas fa-envelope-circle-check"></i>
          </div>
          <h1 class="auth-title">Verify Your Email</h1>
          <p class="auth-subtitle">
            We've sent a 6-digit verification code to<br>
            <span class="email-display"><?= htmlspecialchars($email) ?></span>
          </p>
        </div>

        <div class="auth-body">
          <?php if($message): ?>
            <div class="alert alert-<?= $message_type == 'success' ? 'success' : ($message_type == 'info' ? 'info' : 'danger') ?>">
              <?= $message ?>
            </div>
          <?php endif; ?>

          <div class="info-box">
            <i class="fas fa-info-circle"></i>
            Check your inbox and spam folder. Your account will be created once you verify.
          </div>

          <form id="verify-frm" action="" method="post">
            <input type="hidden" name="verify" value="1">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            
            <div class="form-group">
              <label for="code" class="form-label">Enter Verification Code</label>
              <input type="text" id="code" name="code" class="form-control" placeholder="000000" required maxlength="6" pattern="\d{6}" autocomplete="off">
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                Verify & Create Account
              </button>
            </div>

            <div class="text-center mt-3">
              <p class="text-sm text-muted">Didn't receive the code? <a href="resend_verification.php?email=<?= urlencode($email) ?>" class="text-link">Resend Code</a></p>
              <p class="text-sm text-muted mt-2"><a href="<?= base_url . 'register.php' ?>" class="text-link">← Back to Registration</a></p>
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
      
      // Auto-format verification code input (only allow numbers)
      $('#code').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
      });

      // Allow form submission with Enter key
      $('#code').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
          e.preventDefault();
          $(this).closest('form').submit();
        }
      });

      // Optional: Auto-submit when 6 digits are entered (but don't prevent manual submit)
      let autoSubmitTimeout;
      $('#code').on('input', function() {
        clearTimeout(autoSubmitTimeout);
        if (this.value.length === 6) {
          autoSubmitTimeout = setTimeout(function() {
            $('#verify-frm').submit();
          }, 500); // Wait 0.5 seconds before auto-submitting
        }
      });
    });
  </script>
</body>

</html>