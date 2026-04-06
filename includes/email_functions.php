<?php
// set default timezone to Indian Standard Time for consistency
if(!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Kolkata');
}

// Include PHPMailer with absolute path reference
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

// Load email configuration using absolute path
$config = include __DIR__ . '/../config/email_config.php';

function sendEmail($to, $subject, $message) {
    global $config;

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0; // Set to 2 for debugging
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $config['gmail']['username'];
        $mail->Password = $config['gmail']['app_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($config['gmail']['username'], $config['gmail']['from_name']);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message); // Plain text version

        $result = $mail->send();

        // Log success
        $log_message = "[" . date('Y-m-d H:i:s') . "] Email SENT to: $to | Subject: $subject\n";
        error_log($log_message, 3, "D:\\xampp\\php\\logs\\email_success.log");

        return $result;

    } catch (Exception $e) {
        // Log failure and fallback to file storage
        $error_msg = "Email FAILED to: $to | Error: " . $mail->ErrorInfo;
        error_log("[" . date('Y-m-d H:i:s') . "] " . $error_msg . "\n", 3, "D:\\xampp\\php\\logs\\email_errors.log");

        // Fallback: Save to file for testing
        return saveEmailToFile($to, $subject, $message, $error_msg);
    }
}

function saveEmailToFile($to, $subject, $message, $error = "") {
    $email_log_dir = "D:\\xampp\\htdocs\\ai_lib\\logs\\emails";
    if (!is_dir($email_log_dir)) {
        @mkdir($email_log_dir, 0777, true);
    }

    $timestamp = date('Y-m-d_H-i-s');
    $email_file = $email_log_dir . "\\" . $timestamp . "_" . md5($to . time()) . ".txt";
    $email_content = "TO: $to\n";
    $email_content .= "SUBJECT: $subject\n";
    $email_content .= "TIME: " . date('Y-m-d H:i:s') . "\n";
    if ($error) {
        $email_content .= "ERROR: $error\n";
    }
    $email_content .= "---MESSAGE---\n";
    $email_content .= $message . "\n";

    if (@file_put_contents($email_file, $email_content)) {
        error_log("Email saved to file: $email_file\n", 3, "D:\\xampp\\php\\logs\\email_fallback.log");
        return true;
    }

    return false;
}

function sendDueDateNotification($conn, $user_email, $book_title, $due_date) {
    $subject = "Book Due Date Reminder - AI Library";
    $message = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; border-radius: 5px; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📚 Book Due Date Reminder</h2>
                </div>
                <div class='content'>
                    <p>Dear Reader,</p>
                    <p>This is a reminder that the following book is due soon:</p>
                    <p><strong>Book:</strong> " . htmlspecialchars($book_title) . "</p>
                    <p><strong>Due Date:</strong> " . htmlspecialchars($due_date) . "</p>
                    <p>Please return the book on or before the due date to avoid fine charges.</p>
                    <p>Click the link below to access your borrowing history:</p>
                    <p><a href='http://localhost/ai_lib/borrow/history.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Borrow History</a></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from AI Library System. Please do not reply.</p>
                </div>
            </div>
        </body>
    </html>";

    return sendEmail($user_email, $subject, $message);
}

function sendPasswordResetEmail($conn, $user_email, $reset_token) {
    $subject = "Password Reset Request - AI Library";
    $reset_link = "http://localhost/ai_lib/auth/reset_password.php?token=" . urlencode($reset_token);

    $message = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; border-radius: 5px; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔐 Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>Dear User,</p>
                    <p>We received a request to reset your password. Click the button below to proceed:</p>
                    <p><a href='" . htmlspecialchars($reset_link) . "' style='background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a></p>
                    <p><small>This link will expire in 1 hour.</small></p>
                    <p>If you didn't request this, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from AI Library System. Please do not reply.</p>
                </div>
            </div>
        </body>
    </html>";

    return sendEmail($user_email, $subject, $message);
}

/*
function sendEmailVerification($conn, $user_email, $verification_token) {
    $subject = "Email Verification - AI Library";
    $verification_link = "http://localhost/ai_lib/auth/verify_email.php?token=" . urlencode($verification_token);

    $message = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; border-radius: 5px; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✅ Verify Your Email - AI Library</h2>
                </div>
                <div class='content'>
                    <p>Dear User,</p>
                    <p>Welcome to AI Library! Please verify your email address to complete your registration.</p>
                    <p><a href='" . htmlspecialchars($verification_link) . "' style='background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify Email Address</a></p>
                    <p><small>This link will expire in 24 hours.</small></p>
                    <p>If the button doesn't work, copy and paste this link into your browser:</p>
                    <p><small>" . htmlspecialchars($verification_link) . "</small></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from AI Library System. Please do not reply.</p>
                </div>
            </div>
        </body>
    </html>";

    return sendEmail($user_email, $subject, $message);
}
*/

/*
function sendOTP($user_email, $otp_code) {
    $subject = "OTP Verification - AI Library";

    $message = "
    <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #17a2b8; color: white; padding: 20px; border-radius: 5px; }
                .content { padding: 20px; background: #f8f9fa; }
                .otp-code { font-size: 24px; font-weight: bold; color: #17a2b8; text-align: center; margin: 20px 0; }
                .footer { margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔢 OTP Verification</h2>
                </div>
                <div class='content'>
                    <p>Dear User,</p>
                    <p>Your One-Time Password (OTP) for email verification is:</p>
                    <div class='otp-code'>" . htmlspecialchars($otp_code) . "</div>
                    <p>This OTP will expire in 10 minutes.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from AI Library System. Please do not reply.</p>
                </div>
            </div>
        </body>
    </html>";

    return sendEmail($user_email, $subject, $message);
}
*/

function checkPasswordStrength($password) {
    $strength = 0;

    // Length check
    if(strlen($password) >= 8) $strength += 20;
    if(strlen($password) >= 12) $strength += 10;
    if(strlen($password) >= 16) $strength += 10;

    // Uppercase letters
    if(preg_match('/[A-Z]/', $password)) $strength += 15;

    // Lowercase letters
    if(preg_match('/[a-z]/', $password)) $strength += 15;

    // Numbers
    if(preg_match('/[0-9]/', $password)) $strength += 15;

    // Special characters
    if(preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $password)) $strength += 15;

    return min($strength, 100);
}

function getPasswordStrengthLabel($strength) {
    if($strength < 30) return "Weak";
    if($strength < 50) return "Fair";
    if($strength < 70) return "Good";
    if($strength < 85) return "Strong";
    return "Very Strong";
}
?>
