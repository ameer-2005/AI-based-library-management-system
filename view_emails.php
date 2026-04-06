<?php
// Simple email viewer for local testing
$email_dir = "logs/emails";

if (!is_dir($email_dir)) {
    mkdir($email_dir, 0777, true);
}

$current_email = null;
if (isset($_GET['email'])) {
    $filename = basename($_GET['email']);
    $filepath = $email_dir . "/" . $filename;
    if (file_exists($filepath)) {
        $current_email = file_get_contents($filepath);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Viewer - AI Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .email-item { border-left: 4px solid #007bff; padding: 10px; margin-bottom: 10px; background: white; cursor: pointer; }
        .email-item:hover { background: #f0f0f0; }
        .email-detail { border: 1px solid #ddd; background: white; padding: 20px; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; }
        .badge-otp { background: #17a2b8; }
        .badge-reset { background: #dc3545; }
        .badge-verify { background: #28a745; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>📧 Captured Emails</h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php
                    $emails = array_reverse(glob($email_dir . "/*.txt"));
                    if (empty($emails)) {
                        echo '<p class="text-muted">No emails captured yet</p>';
                    } else {
                        foreach ($emails as $file) {
                            $content = file_get_contents($file);
                            $parts = explode("\n", $content);
                            $to = $parts[0] ?? '';
                            $subject = $parts[1] ?? '';
                            $filename = basename($file);
                            
                            // Extract email type
                            $type = 'email';
                            $badge = 'secondary';
                            if (strpos($subject, 'OTP') !== false) {
                                $type = 'OTP';
                                $badge = 'info';
                            } elseif (strpos($subject, 'Password') !== false) {
                                $type = 'Reset';
                                $badge = 'danger';
                            } elseif (strpos($subject, 'Verification') !== false) {
                                $type = 'Verify';
                                $badge = 'success';
                            }
                            
                            $active = ($current_email == $content) ? 'active' : '';
                            echo '<a href="?email=' . $filename . '" class="email-item ' . $active . '" style="text-decoration: none; color: black;">';
                            echo '<div><span class="badge badge-' . $badge . '">' . $type . '</span></div>';
                            echo '<small class="text-muted d-block text-truncate">' . $to . '</small>';
                            echo '<small class="text-muted d-block text-truncate">' . substr($subject, 0, 40) . '</small>';
                            echo '</a>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>📄 Email Details</h5>
                </div>
                <div class="card-body">
                    <?php
                    if ($current_email) {
                        echo '<pre>' . htmlspecialchars($current_email) . '</pre>';
                    } else {
                        echo '<p class="text-muted text-center mt-5">Select an email from the list to view details</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <strong>ℹ️ How to Use:</strong>
                <ul class="mb-0">
                    <li>Try registering a new account - the OTP email will appear here</li>
                    <li>Try "Forgot Password" - the reset email will appear here</li>
                    <li>Click any email to view full details including OTP code or reset link</li>
                    <li>All captured emails are stored in: <code>logs/emails/</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>
