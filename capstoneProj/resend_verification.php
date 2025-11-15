<?php 
require_once('./config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'admin/includes/PHPMailer/src/PHPMailer.php';
require 'admin/includes/PHPMailer/src/SMTP.php';
require 'admin/includes/PHPMailer/src/Exception.php';

$message = '';
$message_type = '';
$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resend'])) {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
        $message_type = 'error';
    } else {
        // Check if email exists in temp_registrations
        $stmt = $conn->prepare("SELECT * FROM temp_registrations WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $temp_user = $result->fetch_assoc();
            
            // Generate new 6-digit verification code
            $v_code = rand(100000, 999999);
            
            // Calculate new code expiry (24 hours from now)
            $code_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Update verification code and expiry
            $update_stmt = $conn->prepare("UPDATE temp_registrations SET verification_code = ?, code_expiry = ? WHERE email = ?");
            $update_stmt->bind_param("sss", $v_code, $code_expiry, $email);
            
            if ($update_stmt->execute()) {
                // Send new verification code
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'marvinbaylosis03@gmail.com'; // Replace with your email
                    $mail->Password = 'tetiodszkbcuhoqe'; // Replace with your app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('marvinbaylosis03@gmail.com', $_settings->info('name'));
                    $mail->addAddress($email, $temp_user['firstname'] . ' ' . $temp_user['lastname']);
                    $mail->isHTML(true);
                    $mail->Subject = 'New Verification Code - ' . $_settings->info('name');
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #2563eb;'>New Verification Code</h2>
                            <p>Hello <strong>{$temp_user['firstname']} {$temp_user['lastname']}</strong>,</p>
                            <p>You requested a new verification code. Here it is:</p>
                            <h1 style='color: #2563eb; background-color: #f0f9ff; padding: 15px; text-align: center; border-radius: 8px;'>$v_code</h1>
                            <p>Please enter this code on the verification page to complete your registration.</p>
                            <p style='color: #6b7280; font-size: 14px;'>This code will expire in 24 hours.</p>
                            <p style='color: #6b7280; font-size: 14px;'><strong>Note:</strong> Your account will not be created until you verify your email.</p>
                        </div>
                    ";

                    $mail->send();
                    $message = "New verification code sent! Please check your email.";
                    $message_type = 'success';
                    
                    // Redirect to verification page after 2 seconds
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'verify.php?email=" . urlencode($email) . "';
                        }, 2000);
                    </script>";
                } catch (Exception $e) {
                    $message = "Failed to send verification email. Error: {$mail->ErrorInfo}. Please try again.";
                    $message_type = 'error';
                }
            } else {
                $message = "Failed to generate new code. Please try again.";
                $message_type = 'error';
            }
            $update_stmt->close();
        } else {
            // Check if email exists in client_list (already verified)
            $check_client = $conn->query("SELECT email FROM client_list WHERE email = '$email'");
            if ($check_client->num_rows > 0) {
                $message = "This email is already registered and verified. Please <a href='login.php'>login here</a>.";
                $message_type = 'info';
            } else {
                $message = "No pending registration found for this email. Please <a href='register.php'>register here</a>.";
                $message_type = 'error';
            }
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
  <title>Resend Verification Code | <?= $_settings->info('name') ?></title>
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

    .resend-icon {
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

    .form-control::placeholder {
      color: #9ca3af;
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

      .resend-icon {
        font-size: 3rem;
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
          <div class="resend-icon">
            <i class="fas fa-paper-plane"></i>
          </div>
          <h1 class="auth-title">Resend Verification Code</h1>
          <p class="auth-subtitle">
            Enter your email address to receive a new verification code
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
            A new 6-digit code will be sent to your email. The previous code will be invalidated.
          </div>

          <form id="resend-frm" action="" method="post">
            <input type="hidden" name="resend" value="1">
            
            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="your.email@example.com" required value="<?= htmlspecialchars($email) ?>">
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                Send New Code
              </button>
            </div>

            <div class="text-center mt-3">
              <p class="text-sm text-muted">Remember your code? <a href="verify.php?email=<?= urlencode($email) ?>" class="text-link">Verify Now</a></p>
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
    });
  </script>
</body>

</html>