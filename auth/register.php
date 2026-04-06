<?php
session_start();
include("../config/database.php");
include("../includes/email_functions.php");

if(isset($_POST['register'])){
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email exists
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    if($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, 's', $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($check_result) > 0){
            $error = "Email already exists!";
        } else {
            // Directly register user without OTP verification
            $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            if($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, 'sss', $name, $email, $password);
                if(mysqli_stmt_execute($insert_stmt)){
                    $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                } else {
                    $error = "Registration failed: " . mysqli_stmt_error($insert_stmt);
                }
                mysqli_stmt_close($insert_stmt);
            } else {
                $error = "Database error!";
            }
            
            /*
            // Commented out OTP verification logic
            // Generate 6-digit OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $otp_expires = time() + 600; // 10 minutes
            
            // Store in session
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_expires'] = $otp_expires;
            $_SESSION['reg_name'] = $name;
            $_SESSION['reg_email'] = $email;
            $_SESSION['reg_password'] = $password;
            
            // Send OTP email
            $email_sent = sendOTP($email, $otp);
            if($email_sent){
                $show_otp_form = true;
                $success = "OTP sent to your email. Please enter it below to complete registration.";
            } else {
                $error = "Failed to send OTP email. Please try again.";
            }
            */
        }
        mysqli_stmt_close($check_stmt);
    } else {
        $error = "Database error!";
    }
}

/*
if(isset($_POST['verify_otp'])){
    if(isset($_SESSION['otp']) && isset($_SESSION['otp_expires']) && time() < $_SESSION['otp_expires']){
        $entered_otp = $_POST['otp'];
        if($entered_otp == $_SESSION['otp']){
            // OTP correct, register user
            $name = $_SESSION['reg_name'];
            $email = $_SESSION['reg_email'];
            $password = $_SESSION['reg_password'];
            
            $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            if($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, 'sss', $name, $email, $password);
                if(mysqli_stmt_execute($insert_stmt)){
                    // Clear session
                    unset($_SESSION['otp'], $_SESSION['otp_expires'], $_SESSION['reg_name'], $_SESSION['reg_email'], $_SESSION['reg_password']);
                    $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                } else {
                    $error = "Registration failed: " . mysqli_stmt_error($insert_stmt);
                }
                mysqli_stmt_close($insert_stmt);
            } else {
                $error = "Database error!";
            }
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    } else {
        $error = "OTP expired. Please register again.";
    }
}
*/
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>AI Library - Register</h4>
        </div>
        <div class="card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
                <p class="mt-3 text-center">Have an account? <a href="login.php">Login</a></p>
            </form>

            <!-- Commented out OTP verification form -->
            <?php /*
            <?php if(isset($show_otp_form) && $show_otp_form): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Enter OTP sent to your email</label>
                        <input type="text" name="otp" class="form-control" required maxlength="6" pattern="[0-9]{6}" placeholder="123456">
                    </div>
                    <button type="submit" name="verify_otp" class="btn btn-success w-100">Verify OTP</button>
                    <p class="mt-3 text-center"><a href="register.php">Back to Registration</a></p>
                </form>
            <?php else: ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
                    <p class="mt-3 text-center">Have an account? <a href="login.php">Login</a></p>
                </form>
            <?php endif; ?>
            */ ?>
        </div>
    </div>
</div>

</body>
</html>