<?php
session_start();
include("../config/database.php");

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if(!$stmt) {
        $error = "Database error. Please try again.";
    } else {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    
        if($user && password_verify($pass, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            if($user['role'] == 'admin'){
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Email or Password";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-header bg-dark text-white text-center">
            <h4>AI Library - Login</h4>
        </div>
        <div class="card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login" class="btn btn-dark w-100">Login</button>
                <p class="mt-3 text-center">
                    <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                </p>
                <p class="text-center">No account? <a href="register.php">Register</a></p>
            </form>
        </div>
    </div>
</div>

</body>
</html>