<?php 
require_once('./config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'admin/includes/PHPMailer/src/PHPMailer.php';
require 'admin/includes/PHPMailer/src/SMTP.php';
require 'admin/includes/PHPMailer/src/Exception.php';

// Cleanup expired tokens
$cleanup_query = "DELETE FROM password_reset_tokens WHERE token_expiry < NOW()";
$conn->query($cleanup_query);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_reset'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = 'error';
    } else {
        // Check if email exists in client_list
        $stmt = $conn->prepare("SELECT id, firstname, lastname, email FROM client_list WHERE email = ? AND delete_flag = 0 LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate 6-digit reset token
            $reset_token = rand(100000, 999999);
            
            // Token expires in 1 hour
            $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Delete any existing tokens for this email
            $conn->query("DELETE FROM password_reset_tokens WHERE email = '$email'");

            // Insert new token
            $insert_stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, token_expiry) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $email, $reset_token, $token_expiry);
            
            if ($insert_stmt->execute()) {
                // Send reset code via email
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
                    $mail->addAddress($email, $user['firstname'] . ' ' . $user['lastname']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Code - ' . $_settings->info('name');
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #2563eb;'>Password Reset Request</h2>
                            <p>Hello <strong>{$user['firstname']} {$user['lastname']}</strong>,</p>
                            <p>We received a request to reset your password. Use the code below to reset it:</p>
                            <h1 style='color: #2563eb; background-color: #f0f9ff; padding: 15px; text-align: center; border-radius: 8px;'>$reset_token</h1>
                            <p>Enter this code on the password reset page to create a new password.</p>
                            <p style='color: #6b7280; font-size: 14px;'>This code will expire in 1 hour.</p>
                            <p style='color: #ef4444; font-size: 14px;'><strong>Security Notice:</strong> If you didn't request this password reset, please ignore this email or contact support if you're concerned about your account security.</p>
                        </div>
                    ";

                    $mail->send();
                    $message = "Password reset code sent! Please check your email.";
                    $message_type = 'success';
                    
                    // Redirect to reset password page
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'reset_password.php?email=" . urlencode($email) . "';
                        }, 2000);
                    </script>";
                } catch (Exception $e) {
                    // If email fails, delete the token
                    $conn->query("DELETE FROM password_reset_tokens WHERE email = '$email'");
                    $message = "Failed to send reset code. Error: {$mail->ErrorInfo}";
                    $message_type = 'error';
                }
            } else {
                $message = "An error occurred. Please try again.";
                $message_type = 'error';
            }
            $insert_stmt->close();
        } else {
            // For security, show same message even if email doesn't exist
            $message = "If this email exists in our system, a password reset code will be sent.";
            $message_type = 'info';
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
  <title>Forgot Password | <?= $_settings->info('name') ?></title>
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

    .forgot-icon {
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

    .alert-info {
      color: #004085;
      background-color: #d1ecf1;
      border-color: #bee5eb;
    }

    .info-box {
      background-color: #fef3c7;
      border: 1px solid #fcd34d;
      border-radius: var(--border-radius);
      padding: 1rem;
      margin-bottom: 1.5rem;
      font-size: 0.875rem;
      color: #92400e;
    }

    .info-box i {
      margin-right: 0.5rem;
      color: #f59e0b;
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

      .forgot-icon {
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
          <div class="forgot-icon">
            <i class="fas fa-lock"></i>
          </div>
          <h1 class="auth-title">Forgot Password?</h1>
          <p class="auth-subtitle">
            No worries! Enter your email address and we'll send you a code to reset your password.
          </p>
        </div>

        <div class="auth-body">
          <?php if($message): ?>
            <div class="alert alert-<?= $message_type == 'success' ? 'success' : ($message_type == 'info' ? 'info' : 'danger') ?>">
              <?= $message ?>
            </div>
          <?php endif; ?>

          <div class="info-box">
            <i class="fas fa-shield-alt"></i>
            For security, the reset code will expire in 1 hour.
          </div>

          <form id="forgot-frm" action="" method="post">
            <input type="hidden" name="request_reset" value="1">
            
            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="your.email@example.com" required autofocus>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i>
                Send Reset Code
              </button>
            </div>

            <div class="text-center mt-3">
              <p class="text-sm text-muted">Remember your password? <a href="login.php" class="text-link">Sign In</a></p>
              <p class="text-sm text-muted mt-2"><a href="<?= base_url ?>" class="text-link">← Return to homepage</a></p>
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