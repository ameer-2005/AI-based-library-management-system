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
$token = $_GET['token'] ?? '';

// Verify token
$token_valid = false;
$user_email = '';

if(!empty($token)){
    $stmt = $conn->prepare("SELECT u.email, u.id FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ? AND pr.expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $token_valid = true;
        $row = $result->fetch_assoc();
        $user_email = $row['email'];
        $user_id = $row['id'];
    } else {
        $error = "Invalid or expired reset link. Please request a new one.";
    }
    $stmt->close();
}

// Handle password reset form
if($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid){
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if(empty($password) || empty($confirm_password)){
        $error = "Please fill in all fields";
    } elseif($password !== $confirm_password){
        $error = "Passwords do not match";
    } elseif(strlen($password) < 8){
        $error = "Password must be at least 8 characters long";
    } else {
        $strength = checkPasswordStrength($password);
        
        if($strength < 30){
            $error = "Password is too weak. Use a combination of uppercase, lowercase, numbers, and special characters.";
        } else {
            // Hash and update password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if($update_stmt->execute()){
                // Delete used token
                $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $delete_stmt->bind_param("s", $token);
                $delete_stmt->execute();
                $delete_stmt->close();
                
                $message = "Password has been reset successfully! Redirecting to login...";
                echo "<meta http-equiv='refresh' content='2;url=login.php'>";
            } else {
                $error = "Error updating password: " . $update_stmt->error;
            }
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - AI Library</title>
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
        .strength-bar {
            height: 5px;
            background: #ddd;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }
        .strength-weak { background: #dc3545; }
        .strength-fair { background: #ffc107; }
        .strength-good { background: #17a2b8; }
        .strength-strong { background: #28a745; }
        .strength-very-strong { background: #20c997; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>🔐 Reset Your Password</h4>
            <small>Create a strong new password</small>
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
            
            <?php if($token_valid): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required onChange="checkStrength()">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Strength: <span id="strengthLabel">Weak</span>
                        </small>
                        <small class="text-muted">
                            ✓ At least 8 characters<br>
                            ✓ Mix of uppercase, lowercase, numbers, and symbols
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
            <?php else: ?>
                <p class="text-center text-muted">Please use a valid reset link from your email.</p>
                <a href="forgot_password.php" class="btn btn-primary w-100">Request New Link</a>
            <?php endif; ?>
            
            <hr>
            <p class="text-center small text-muted mb-0">
                Remembered your password? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
</div>

<script>
function checkStrength() {
    const password = document.getElementById('password').value;
    let strength = 0;
    
    if(password.length >= 8) strength += 20;
    if(password.length >= 12) strength += 10;
    if(password.length >= 16) strength += 10;
    if(/[A-Z]/.test(password)) strength += 15;
    if(/[a-z]/.test(password)) strength += 15;
    if(/[0-9]/.test(password)) strength += 15;
    if(/[!@#$%^&*()_+\-=\[\]{};:'"",.<>?\/\\|`~]/.test(password)) strength += 15;
    
    const fill = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    
    fill.style.width = strength + '%';
    
    if(strength < 30) {
        fill.className = 'strength-fill strength-weak';
        label.textContent = 'Weak';
    } else if(strength < 50) {
        fill.className = 'strength-fill strength-fair';
        label.textContent = 'Fair';
    } else if(strength < 70) {
        fill.className = 'strength-fill strength-good';
        label.textContent = 'Good';
    } else if(strength < 85) {
        fill.className = 'strength-fill strength-strong';
        label.textContent = 'Strong';
    } else {
        fill.className = 'strength-fill strength-very-strong';
        label.textContent = 'Very Strong';
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
