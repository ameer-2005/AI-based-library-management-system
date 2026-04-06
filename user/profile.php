<?php
session_start();
include("../config/database.php");
if(!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "info";

// Get borrowed books count
$borrowed_count = 0;
$borrow_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE user_id = ?");
if($borrow_stmt) {
    mysqli_stmt_bind_param($borrow_stmt, 'i', $user_id);
    mysqli_stmt_execute($borrow_stmt);
    $borrow_result = mysqli_stmt_get_result($borrow_stmt);
    $borrowed_data = mysqli_fetch_assoc($borrow_result);
    $borrowed_count = $borrowed_data['total'] ?? 0;
    mysqli_stmt_close($borrow_stmt);
}

if(isset($_POST['change_pass'])){
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if($new !== $confirm) {
        $msg = "New passwords do not match.";
        $msg_type = "danger";
    } else {
        // Verify Old Password
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id='$user_id'"));
        
        if(password_verify($old, $user['password'])){
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id='$user_id'");
            $msg = "Password updated successfully!";
            $msg_type = "success";
        } else {
            $msg = "Current password is incorrect.";
            $msg_type = "danger";
        }
    }
}

include("../includes/layout.php");
?>

<style>
/* PROFILE PAGE */

body {
  background: #f5f7fa;
}

.content-wrapper {
  background: #f5f7fa;
}

h2 {
  color: #2c3e50;
  font-weight: 700;
  letter-spacing: -0.5px;
  margin-bottom: 2rem;
  font-size: 2rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

h2 i {
  color: #667eea;
  font-size: 2.2rem;
}

/* PROFILE CARD */

.profile-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.3s;
}

.profile-card:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.profile-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 40px 20px;
  text-align: center;
  color: white;
}

.profile-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  margin: 0 auto 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.profile-name {
  font-size: 24px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.profile-role {
  font-size: 14px;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.profile-info {
  padding: 30px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
}

.info-item:last-child {
  border-bottom: none;
}

.info-label {
  color: #7f8c8d;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  color: #2c3e50;
  font-weight: 700;
  font-size: 15px;
}

/* PASSWORD CHANGE CARD */

.change-password-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin-top: 25px;
}

.card-header-custom {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px;
  font-size: 18px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}

.card-body-custom {
  padding: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  color: #2c3e50;
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-control {
  border: 2px solid #e0e6ed;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 14px;
  transition: all 0.3s;
}

.form-control:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-update {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-update:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

/* ALERT */

.alert-custom {
  border: none;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
  font-size: 14px;
}

.alert-success-custom {
  background: linear-gradient(135deg, rgba(86, 171, 47, 0.1) 0%, rgba(168, 224, 99, 0.1) 100%);
  color: #056C0F;
  border-left: 4px solid #56ab2f;
}

.alert-danger-custom {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
  color: #991b1b;
  border-left: 4px solid #ef4444;
}

.alert-info-custom {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  color: #1e40af;
  border-left: 4px solid #667eea;
}

</style>

<h2><i class="bi bi-person-circle"></i> My Profile</h2>

<div class="row">
    <div class="col-lg-5">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h3 class="profile-name"><?= htmlspecialchars($_SESSION['name']) ?></h3>
                <p class="profile-role"><?= ucfirst($_SESSION['role']) ?></p>
            </div>
            <div class="profile-info">
                <div class="info-item">
                    <span class="info-label">Account Status</span>
                    <span class="info-value" style="color: #56ab2f;"><i class="bi bi-check-circle-fill"></i> Active</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member Since</span>
                    <span class="info-value">Mar 2026</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Books Borrowed</span>
                    <span class="info-value"><?php echo $borrowed_count; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="change-password-card">
            <div class="card-header-custom">
                <i class="bi bi-shield-lock"></i> Change Password
            </div>
            <div class="card-body-custom">
                <?php if($msg): ?>
                    <div class="alert-custom alert-<?php echo str_replace('success', 'success-custom', str_replace('danger', 'danger-custom', str_replace('info', 'info-custom', $msg_type))); ?>">
                        <?php if($msg_type === 'success'): ?>
                            <i class="bi bi-check-circle-fill"></i>
                        <?php elseif($msg_type === 'danger'): ?>
                            <i class="bi bi-exclamation-circle-fill"></i>
                        <?php else: ?>
                            <i class="bi bi-info-circle-fill"></i>
                        <?php endif; ?>
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="old_password" class="form-control" required placeholder="Enter your current password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="Confirm new password">
                    </div>
                    <button type="submit" name="change_pass" class="btn-update">
                        <i class="bi bi-check-lg"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>