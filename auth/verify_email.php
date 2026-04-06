<?php
session_start();
include("../config/database.php");

$message = '';
$error = '';
$token = $_GET['token'] ?? '';

/*
if(!empty($token)){
    // Verify token
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE verification_token = ? AND verification_expires > NOW() AND email_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        // Update user as verified
        $update_stmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, verification_expires = NULL WHERE id = ?");
        $update_stmt->bind_param("i", $user_id);
        
        if($update_stmt->execute()){
            $message = "Email verified successfully! You can now login to your account.";
        } else {
            $error = "Error verifying email. Please try again.";
        }
        $update_stmt->close();
    } else {
        $error = "Invalid or expired verification link. Please request a new one.";
    }
    $stmt->close();
} else {
    $error = "No verification token provided.";
}
*/
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification - AI Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .container { max-width: 500px; }
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
            <h4><?php echo $error ? '❌ Verification Failed' : '✅ Email Verification'; ?></h4>
        </div>
        <div class="card-body p-4">
            <?php if($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
                <div class="text-center">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <div class="text-center">
                    <a href="register.php" class="btn btn-secondary">Register Again</a>
                    <a href="login.php" class="btn btn-primary ms-2">Go to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>