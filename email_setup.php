<?php
// Email Setup Page
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $app_password = trim($_POST['app_password'] ?? '');

    if (empty($app_password)) {
        $error = "Please enter your Gmail App Password";
    } elseif (strlen($app_password) !== 16 || !ctype_alnum($app_password)) {
        $error = "Gmail App Password should be 16 characters (letters and numbers only)";
    } else {
        // Update config file
        $config_content = "<?php
// Gmail SMTP Configuration

return [
    'gmail' => [
        'username' => 'lily087334438@gmail.com',
        'app_password' => '" . $app_password . "',
        'from_name' => 'AI Library'
    ]
];
?>";

        if (file_put_contents('../config/email_config.php', $config_content)) {
            $message = "✅ Gmail App Password saved successfully! You can now send real emails.";
        } else {
            $error = "❌ Failed to save configuration. Check file permissions.";
        }
    }
}

// Check current config
$config = include '../config/email_config.php';
$has_password = !empty($config['gmail']['app_password']) && $config['gmail']['app_password'] !== 'YOUR_GMAIL_APP_PASSWORD';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Setup - AI Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 600px; margin-top: 50px; }
        .card { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border-radius: 10px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px 10px 0 0; text-align: center; }
        .btn-primary { background: #667eea; border: none; }
        .btn-primary:hover { background: #764ba2; }
        .alert { border-radius: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>📧 Gmail Email Setup</h4>
            <small>Configure Gmail SMTP for real email delivery</small>
        </div>
        <div class="card-body p-4">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="alert alert-info">
                <strong>📋 Setup Instructions:</strong>
                <ol class="mb-0 mt-2">
                    <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
                    <li>Sign in with <strong>lily087334438@gmail.com</strong></li>
                    <li>Generate an App Password for "AI Library"</li>
                    <li>Enter the 16-character password below</li>
                </ol>
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Gmail App Password</label>
                    <input type="password" name="app_password" class="form-control"
                           placeholder="Enter 16-character app password"
                           value="<?php echo $has_password ? '********' . substr($config['gmail']['app_password'], -4) : ''; ?>"
                           required>
                    <div class="form-text">
                        This is NOT your Gmail password. It's a special app password from Google.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <?php echo $has_password ? 'Update Password' : 'Save Password'; ?>
                </button>
            </form>

            <hr>

            <div class="text-center">
                <h6>Test Email System</h6>
                <a href="test_email.php" class="btn btn-outline-primary btn-sm">Test Email</a>
                <a href="view_emails.php" class="btn btn-outline-secondary btn-sm">View Emails</a>
            </div>

            <?php if($has_password): ?>
            <div class="alert alert-success mt-3">
                <strong>✅ Status:</strong> Gmail SMTP is configured and ready to send real emails!
            </div>
            <?php else: ?>
            <div class="alert alert-warning mt-3">
                <strong>⚠️ Status:</strong> Currently saving emails to files for testing. Configure Gmail above for real delivery.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
