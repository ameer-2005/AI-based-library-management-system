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

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'] ?? '';
    
    if(empty($email)){
        $error = "Please enter your email address";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            
            // Generate unique token
            $token = bin2hex(random_bytes(50));
            $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // Store reset token
            $insert_stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iss", $user_id, $token, $expires_at);
            
            if($insert_stmt->execute()){
                // Send email
                if(sendPasswordResetEmail($conn, $email, $token)){
                    $message = "Password reset link has been sent to your email! Check your inbox.";
                } else {
                    $message = "Reset link generated. Please contact admin if email is not received.";
                }
            } else {
                $error = "Error generating reset link: " . $insert_stmt->error;
            }
            $insert_stmt->close();
        } else {
            // Don't reveal if email exists for security
            $message = "If this email exists in our system, you'll receive a reset link shortly.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - AI Library</title>
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
            <h4>🔐 Forgot Your Password?</h4>
            <small>Enter your email to receive reset instructions</small>
        </div>
        <div class="card-body p-4">
            <?php if($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-2">Send Reset Link</button>
                <a href="login.php" class="btn btn-outline-secondary w-100">Back to Login</a>
            </form>
            
            <hr>
            <p class="text-center small text-muted mb-0">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
