<?php
session_start();

// If already logged in, redirect to home
if(isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

include("../config/database.php");
include("../includes/email_functions.php");

$message = '';
$error = '';

/*
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'] ?? '';
    
    if(empty($email)){
        $error = "Please enter your email address";
    } else {
        // Check if email exists and is not verified
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? AND email_verified = 0");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            
            // Generate new verification token
            $token = bin2hex(random_bytes(32));
            $expires_at = date("Y-m-d H:i:s", strtotime("+24 hours"));
            
            // Update verification token
            $update_stmt = $conn->prepare("UPDATE users SET verification_token = ?, verification_expires = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $token, $expires_at, $user_id);
            
            if($update_stmt->execute()){
                // Send verification email
                if(sendEmailVerification($conn, $email, $token)){
                    $message = "Verification email sent! Please check your inbox.";
                } else {
                    $message = "Verification email generated. Please contact admin if email is not received.";
                }
            } else {
                $error = "Error generating verification link: " . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            // Check if email is already verified
            $verified_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND email_verified = 1");
            $verified_check->bind_param("s", $email);
            $verified_check->execute();
            $verified_result = $verified_check->get_result();
            
            if($verified_result->num_rows > 0){
                $message = "This email is already verified. You can login now.";
            } else {
                // Don't reveal if email exists for security
                $message = "If this email exists in our system and is not verified, you'll receive a verification link shortly.";
            }
            $verified_check->close();
        }
        $stmt->close();
    }
}
*/
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resend Verification - AI Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .container { max-width: 450px; }
        .card {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-radius: 10px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px 10px 0 0;
            padding: 30px 20px;
            text-align: center;
        }
        .btn-primary { background: #667eea; border: none; }
        .btn-primary:hover { background: #764ba2; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>🔄 Resend Verification Email</h4>
            <small>Enter your email to receive a new verification link</small>
        </div>
        <div class="card-body p-4">
            <?php if($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Verification Email</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>