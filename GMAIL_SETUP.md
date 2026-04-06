# 📧 Gmail SMTP Setup Guide

## Current Status
- ✅ **From Email**: `lily087334438@gmail.com`
- ⚠️ **SMTP**: Currently using file storage (localhost testing)

## 🚀 For Real Email Sending (Optional)

### Step 1: Enable 2-Factor Authentication
1. Go to [Google Account Settings](https://myaccount.google.com/)
2. Security → 2-Step Verification → Turn On

### Step 2: Generate App Password
1. Go to [App Passwords](https://myaccount.google.com/apppasswords)
2. Select "Mail" and "Other (custom name)"
3. Enter "AI Library" as the name
4. Copy the 16-character password

### Step 3: Update PHP Configuration
Edit `D:\xampp\php\php.ini`:

```ini
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = lily087334438@gmail.com
```

### Step 4: Install PHPMailer (Recommended)
```bash
cd D:\xampp\htdocs\ai_lib
composer require phpmailer/phpmailer
```

### Step 5: Update Email Function
Replace the `sendEmail()` function with PHPMailer:

```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lily087334438@gmail.com';
        $mail->Password = 'YOUR_APP_PASSWORD'; // Replace with app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('lily087334438@gmail.com', 'AI Library');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}
```

## 🧪 Current Testing Setup
For now, emails are saved to `logs/emails/` folder. This works perfectly for development!

**View emails:** http://localhost/ai_lib/view_emails.php

## ⚡ Quick Test
Run: `D:\xampp\php\php.exe test_email.php`

The email will be saved to the logs folder for you to view.