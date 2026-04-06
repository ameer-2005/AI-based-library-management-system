<?php
// Session and security management

// Session timeout configuration
define('SESSION_TIMEOUT', 30 * 60); // 30 minutes
define('REMEMBER_ME_DURATION', 30 * 24 * 60 * 60); // 30 days

function initializeSecureSession() {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => '',
        'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? true : false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Prevent session fixation
    if(!isset($_SESSION['initiated'])){
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
        $_SESSION['created'] = time();
        $_SESSION['last_activity'] = time();
    }
}

function checkSessionTimeout() {
    if(isset($_SESSION['last_activity'])){
        if((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT){
            // Session expired
            session_destroy();
            return false;
        }
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

function createSessionRecord($conn, $user_id) {
    include("config/database.php");
    
    $session_id = session_id();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $expires_at = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
    
    $stmt = $conn->prepare("
        INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("issss", $user_id, $session_id, $ip_address, $user_agent, $expires_at);
    return $stmt->execute();
}

function cleanupExpiredSessions($conn) {
    include("config/database.php");
    
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
    return $stmt->execute();
}

function logFailedLoginAttempt($conn, $email, $ip_address) {
    // Implement rate limiting/brute force protection
    $key = 'failed_login_' . md5($email . $ip_address);
    $attempts = $_SESSION[$key] ?? 0;
    $attempts++;
    
    $_SESSION[$key] = $attempts;
    
    // Lock account after 5 failed attempts
    if($attempts >= 5){
        $stmt = $conn->prepare("UPDATE users SET account_locked = TRUE WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
        
        return false;
    }
    
    return true;
}

function validatePasswordStrength($password) {
    $errors = [];
    
    if(strlen($password) < 8){
        $errors[] = "Password must be at least 8 characters";
    }
    
    if(!preg_match('/[A-Z]/', $password)){
        $errors[] = "Password must contain uppercase letters";
    }
    
    if(!preg_match('/[a-z]/', $password)){
        $errors[] = "Password must contain lowercase letters";
    }
    
    if(!preg_match('/[0-9]/', $password)){
        $errors[] = "Password must contain numbers";
    }
    
    if(!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $password)){
        $errors[] = "Password must contain special characters (!@#$%^&*)";
    }
    
    return $errors;
}

function checkPasswordHistory($conn, $user_id, $new_password) {
    // Check if password has been used before (optional)
    // This is a simplified version
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if($user && password_verify($new_password, $user['password'])){
        return false; // Same as previous password
    }
    
    $stmt->close();
    return true;
}

function generateSecureApiToken() {
    return bin2hex(random_bytes(32));
}
?>
