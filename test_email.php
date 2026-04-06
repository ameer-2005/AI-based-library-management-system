<?php
// Test email script
// ensure timezone is set before any date calls
if(!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Kolkata');
}
require_once 'includes/email_functions.php';
require_once 'config/email_config.php';

$config = include 'config/email_config.php';
$has_gmail_config = !empty($config['gmail']['app_password']) &&
                   $config['gmail']['app_password'] !== 'YOUR_GMAIL_APP_PASSWORD';

// Test sending an email
$test_email = 'lily087334438@gmail.com';
$result = sendEmail($test_email, 'Test Email - AI Library', '
<h1>🧪 Email Test Successful!</h1>
<p>This is a test email from your AI Library system.</p>
<p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
<p><strong>Status:</strong> ' . ($has_gmail_config ? 'Real Gmail SMTP' : 'File Storage (Testing)') . '</p>
<p>If you receive this email, your email system is working perfectly! 🎉</p>
');

$message = '';
$alert_class = '';

if ($result) {
    $message = "✅ Email sent successfully!";
    $alert_class = "alert-success";

    if ($has_gmail_config) {
        $message .= " Check your Gmail inbox for the test email.";
    } else {
        $message .= " Email saved to logs folder. <a href='view_emails.php' target='_blank'>View it here</a>.";
    }
} else {
    $message = "❌ Email failed to send. Check the error logs.";
    $alert_class = "alert-danger";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Test - AI Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .container { max-width: 500px; }
        .card { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border-radius: 10px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px 10px 0 0; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>📧 Email Test Results</h4>
        </div>
        <div class="card-body p-4">
            <div class="alert <?php echo $alert_class; ?> text-center">
                <h5><?php echo $message; ?></h5>
            </div>

            <div class="text-center mt-4">
                <div class="row">
                    <div class="col-6">
                        <a href="email_setup.php" class="btn btn-primary w-100">Email Setup</a>
                    </div>
                    <div class="col-6">
                        <a href="view_emails.php" class="btn btn-secondary w-100">View Emails</a>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="auth/register.php" class="btn btn-success">Test Registration</a>
                    <a href="auth/forgot_password.php" class="btn btn-warning">Test Password Reset</a>
                </div>
            </div>

            <hr>

            <div class="small text-muted">
                <strong>Current Configuration:</strong><br>
                From: lily087334438@gmail.com<br>
                Method: <?php echo $has_gmail_config ? 'Gmail SMTP (Real emails)' : 'File Storage (Testing)'; ?><br>
                Test Email: <?php echo $test_email; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
?>