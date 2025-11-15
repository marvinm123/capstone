<?php 
require_once('./config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'admin/includes/PHPMailer/src/PHPMailer.php';
require 'admin/includes/PHPMailer/src/SMTP.php';
require 'admin/includes/PHPMailer/src/Exception.php';

// ============================================
// AUTOMATIC CLEANUP: Delete expired temporary registrations
// This runs every time someone visits the registration page
// ============================================
$cleanup_query = "DELETE FROM temp_registrations WHERE code_expiry < NOW()";
$conn->query($cleanup_query);

// Optional: Log cleanup for debugging (comment out in production)
// $cleaned = $conn->affected_rows;
// if ($cleaned > 0) {
//     error_log("Cleaned up $cleaned expired registration(s)");
// }

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname = trim($_POST['lastname']);
    $gender = trim($_POST['gender']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
        $message_type = 'error';
    } 
    // Validate password match
    else if ($password !== $cpassword) {
        $message = "Passwords do not match.";
        $message_type = 'error';
    }
    // Validate password length
    else if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = 'error';
    }
    else {
        // Check if email already exists in client_list (already verified users)
        $check_client = $conn->query("SELECT email FROM client_list WHERE email = '$email'");
        
        // Check if email already exists in temp_registrations (pending verification)
        $check_temp = $conn->query("SELECT email, code_expiry FROM temp_registrations WHERE email = '$email'");
        
        if ($check_client->num_rows > 0) {
            $message = "Email address is already registered and verified. Please <a href='login.php'>login here</a>.";
            $message_type = 'error';
        } else if ($check_temp->num_rows > 0) {
            // Email exists in temp - check if still valid
            $temp_data = $check_temp->fetch_assoc();
            $current_time = date('Y-m-d H:i:s');
            
            if ($current_time < $temp_data['code_expiry']) {
                // Code still valid - redirect to verify page
                $message = "You already have a pending registration. We're redirecting you to verify your email.";
                $message_type = 'info';
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'verify.php?email=" . urlencode($email) . "';
                    }, 2000);
                </script>";
            } else {
                // Code expired - delete old entry and allow re-registration
                $conn->query("DELETE FROM temp_registrations WHERE email = '$email'");
                $message = "Your previous verification code expired. Please complete registration again.";
                $message_type = 'warning';
            }
        }
        
        // Proceed with registration
        if ($message == '') {
            // Generate 6-digit verification code
            $v_code = rand(100000, 999999);
            // Use MD5 hashing
            $hashed_password = md5($password);
            
            // Calculate code expiry (24 hours from now)
            $code_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Insert into TEMPORARY table (NOT client_list yet)
            $stmt = $conn->prepare("INSERT INTO temp_registrations (firstname, middlename, lastname, gender, contact, address, email, password, verification_code, code_expiry, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssssssss", $firstname, $middlename, $lastname, $gender, $contact, $address, $email, $hashed_password, $v_code, $code_expiry);

            if ($stmt->execute()) {
                // Send verification code
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
                    $mail->addAddress($email, $firstname . ' ' . $lastname);
                    $mail->isHTML(true);
                    $mail->Subject = 'Email Verification Code - ' . $_settings->info('name');
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #2563eb;'>Email Verification</h2>
                            <p>Hello <strong>$firstname $lastname</strong>,</p>
                            <p>Thank you for registering with " . $_settings->info('name') . "!</p>
                            <p>Your verification code is:</p>
                            <h1 style='color: #2563eb; background-color: #f0f9ff; padding: 15px; text-align: center; border-radius: 8px;'>$v_code</h1>
                            <p>Please enter this code on the verification page to complete your registration.</p>
                            <p style='color: #6b7280; font-size: 14px;'>This code will expire in 24 hours.</p>
                            <p style='color: #6b7280; font-size: 14px;'><strong>Note:</strong> Your account will not be created until you verify your email.</p>
                        </div>
                    ";

                    $mail->send();
                    $message = "Verification code sent! Please check your email and enter the code to complete registration.";
                    $message_type = 'success';
                    
                    // Redirect to verification page after 2 seconds
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'verify.php?email=" . urlencode($email) . "';
                        }, 2000);
                    </script>";
                } catch (Exception $e) {
                    // If email fails, delete the temp registration
                    $conn->query("DELETE FROM temp_registrations WHERE email = '$email'");
                    $message = "Failed to send verification email. Error: {$mail->ErrorInfo}. Please try again.";
                    $message_type = 'error';
                }
            } else {
                $message = "An error occurred during registration. Please try again.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php') ?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | <?= $_settings->info('name') ?></title>
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

    .company-logo {
      height: 7rem;
      width: auto;
      margin-bottom: 2rem;
      max-width: 100%;
      object-fit: contain;
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
      font-size: 1rem;
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

    .alert-warning {
      color: #856404;
      background-color: #fff3cd;
      border-color: #ffeaa7;
    }

    .row {
      display: flex;
      flex-wrap: wrap;
      margin-right: -1rem;
      margin-left: -1rem;
    }

    .col-md-4 {
      flex: 0 0 33.333333%;
      max-width: 33.333333%;
      padding-right: 1rem;
      padding-left: 1rem;
    }

    .col-md-6 {
      flex: 0 0 50%;
      max-width: 50%;
      padding-right: 1rem;
      padding-left: 1rem;
    }

    .col-md-8 {
      flex: 0 0 66.666667%;
      max-width: 66.666667%;
      padding-right: 1rem;
      padding-left: 1rem;
    }

    hr {
      margin: 2rem 0;
      border: 0;
      border-top: 1px solid #e5e7eb;
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

      .company-logo {
        height: 5rem;
        margin-bottom: 1.5rem;
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
          <h1 class="auth-title">Create Your Account</h1>
          <p class="auth-subtitle">Join our platform to access exclusive features and services</p>
        </div>

        <div class="auth-body">
          <?php if($message): ?>
            <div class="alert alert-<?= $message_type == 'success' ? 'success' : ($message_type == 'warning' ? 'warning' : 'danger') ?>">
              <?= $message ?>
            </div>
          <?php endif; ?>

          <form id="register-frm" action="" method="post">
            <input type="hidden" name="register" value="1">
            
            <div class="row">
              <div class="form-group col-md-4">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First Name" required value="<?= isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : '' ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="middlename" class="form-label">Middle Name (Optional)</label>
                <input type="text" id="middlename" name="middlename" class="form-control" placeholder="Middle Name" value="<?= isset($_POST['middlename']) ? htmlspecialchars($_POST['middlename']) : '' ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Last Name" required value="<?= isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '' ?>">
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-8">
                <label for="gender" class="form-label">Gender</label>
                <select id="gender" name="gender" class="form-control" required>
                  <option value="" disabled <?= !isset($_POST['gender']) ? 'selected' : '' ?>>Select your gender</option>
                  <option value="Male" <?= isset($_POST['gender']) && $_POST['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                  <option value="Female" <?= isset($_POST['gender']) && $_POST['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                  <option value="Other" <?= isset($_POST['gender']) && $_POST['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label for="contact" class="form-label">Contact Number</label>
                <input type="tel" id="contact" name="contact" class="form-control" placeholder="Contact Number" required pattern="\d{11}" value="<?= isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : '' ?>">
                <small class="text-muted">mobile number</small>
              </div>
            </div>

            <div class="form-group">
              <label for="address" class="form-label">Complete Address</label>
              <textarea id="address" name="address" rows="3" class="form-control" placeholder="Your Current Address" required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
            </div>

            <hr class="divider">

            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="your.name@email.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
              <small class="text-muted">We'll send a verification code to this email</small>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" id="password" name="password" class="form-control" placeholder="Create a strong password" required>
                  <div class="input-group-append">
                    <span class="input-group-text pass_type" data-type="password">
                      <i class="fas fa-eye-slash"></i>
                    </span>
                  </div>
                </div>
                <small class="text-muted">Minimum 8 characters with numbers and symbols</small>
              </div>
              <div class="form-group col-md-6">
                <label for="cpassword" class="form-label">Confirm Password</label>
                <div class="input-group">
                  <input type="password" id="cpassword" name="cpassword" class="form-control" placeholder="Re-enter your password" required>
                  <div class="input-group-append">
                    <span class="input-group-text pass_type" data-type="password">
                      <i class="fas fa-eye-slash"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group mt-4">
              <button type="submit" class="btn btn-primary btn-block">Send Verification Code</button>
            </div>

            <div class="text-center mt-3">
              <p class="text-sm text-muted">Already registered? <a href="<?= base_url . 'login.php' ?>" class="text-link">Sign in to your account</a></p>
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
      
      // Toggle password visibility
      $('.pass_type').on('click', function() {
        const input = $(this).closest('.input-group').find('input');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
      });
    });
  </script>
</body>

</html>