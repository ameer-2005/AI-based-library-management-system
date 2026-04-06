# 📧 Email Configuration Guide

Your AI Library system uses email for:
- User registration verification
- Password reset links
- Due date reminders
- Notifications

## Current Status
- ✅ Email functions work correctly
- ⚠️ Emails are NOT being sent (SMTP not configured)

## Quick Fix: Configure SMTP in XAMPP

### Option 1: Use MailHog (Recommended for Local Development)

MailHog is a local email testing tool that catches all emails without needing real SMTP.

**Steps:**
1. Download MailHog from: https://github.com/mailhog/MailHog/releases
2. Extract and run `MailHog.exe`
3. Access the UI at: http://localhost:1025
4. Edit `php.ini` in `D:\xampp\php\`:

```ini
[mail function]
SMTP = localhost
smtp_port = 1025
sendmail_from = noreply@ai-library.local
```

5. Restart Apache in XAMPP
6. All emails will now be captured and viewable at http://localhost:1025

### Option 2: Use Gmail SMTP (Production)

For production, use PHPMailer with Gmail:

1. Install PHPMailer:
```bash
composer require phpmailer/phpmailer
```

2. Update `includes/email_functions.php`:

```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com';
        $mail->Password = 'your-app-password'; // Use App Password, not your Gmail password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('your-email@gmail.com', 'AI Library');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email send failed: {$mail->ErrorInfo}");
        return false;
    }
}
```

### Option 3: Configure XAMPP's Built-in Mail Server

Edit `D:\xampp\php\php.ini`:

```ini
[mail function]
SMTP = smtp.your-server.com
smtp_port = 587
sendmail_from = noreply@ai-library.local
```

## Testing Emails Locally

Without SMTP configured, emails won't be sent, but:
- ✅ Registration still works (just can't verify email)
- ✅ Password reset functions still work
- ✅ All error handling is in place

### Bypass Email for Testing

To skip email verification during testing, edit `auth/login.php`:

```php
// Comment out this check temporarily for testing
// if($user['email_verified'] == 0){
//     $error = "Please verify your email address...";
```

## Current Implementation

The system now has robust error handling:
- Emails fail gracefully without crashing
- Users get clear messages about email status
- System logs all email errors
- Registration completes even if email fails to send

## Next Steps

1. **Choose your email method** (MailHog, Gmail, or custom SMTP)
2. **Update php.ini** with SMTP settings
3. **Restart Apache** for changes to take effect
4. **Test** by registering a new account
5. **Check email** in your mail service

## Troubleshooting

**Problem:** Still getting mail() error
- **Solution:** Make sure php.ini is saved and Apache is restarted

**Problem:** Emails sent but not received
- **Solution:** Check spam folder or email logs in `php_errors.log`

**Problem:** Want to disable email verification for development
- **Solution:** Edit the SQL directly: `UPDATE users SET email_verified = 1`

---

**For Production Use:** Always use a proper email service like SendGrid, Mailgun, or AWS SES with authentication and rate limiting.
