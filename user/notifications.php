<?php
session_start();
include("../config/database.php");
include("../includes/layout.php");
include("../includes/notifications.php");

$user_id = $_SESSION['user_id'];

// Mark notification as read
if(isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    markNotificationRead($conn, $_POST['notification_id'], $user_id);
    header("Location: notifications.php");
    exit();
}

// Get notifications
$notifications = getUserNotifications($conn, $user_id, 50);
$unread_count = 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bell"></i> My Notifications</h2>
    <div>
        <span class="badge bg-primary">
            <?php
            mysqli_data_seek($notifications, 0);
            while($row = mysqli_fetch_assoc($notifications)) {
                if(!$row['is_read']) $unread_count++;
            }
            mysqli_data_seek($notifications, 0);
            echo $unread_count;
            ?> unread
        </span>
    </div>
</div>

<style>
/* NOTIFICATIONS PAGE */

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

.badge {
  padding: 8px 16px !important;
  font-size: 12px !important;
  text-transform: uppercase !important;
  letter-spacing: 0.5px !important;
}

/* NOTIFICATIONS CARD */

.notif-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.notif-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.notif-item {
  padding: 16px 24px;
  border-bottom: 1px solid #eee;
  transition: all 0.3s;
  display: flex;
  gap: 16px;
}

.notif-item:last-child {
  border-bottom: none;
}

.notif-item:hover {
  background: #f8fafc;
  box-shadow: inset 0 2px 8px rgba(102, 126, 234, 0.05);
}

.notif-item.unread {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
}

.notif-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.notif-icon.reminder {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.2) 100%);
  color: #f59e0b;
}

.notif-icon.overdue {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.2) 100%);
  color: #ef4444;
}

.notif-icon.success {
  background: linear-gradient(135deg, rgba(86, 171, 47, 0.2) 0%, rgba(168, 224, 99, 0.2) 100%);
  color: #56ab2f;
}

.notif-icon.info {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
  color: #667eea;
}

.notif-content {
  flex: 1;
}

.notif-title {
  color: #2c3e50;
  font-weight: 700;
  font-size: 15px;
  margin: 0 0 4px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.notif-message {
  color: #7f8c8d;
  font-size: 13px;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.notif-time {
  color: #a0a8af;
  font-size: 12px;
  margin: 0;
}

.notif-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.notif-action {
  display: flex;
  gap: 8px;
}

.mark-read-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
}

.mark-read-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  color: white;
}

/* EMPTY STATE */

.empty-notif {
  text-align: center;
  padding: 60px 20px;
}

.empty-notif i {
  font-size: 64px;
  color: #bdc3c7;
  margin-bottom: 20px;
  display: block;
}

.empty-notif h5 {
  color: #2c3e50;
  font-weight: 700;
  margin-bottom: 8px;
}

.empty-notif p {
  color: #7f8c8d;
  margin: 0;
  font-size: 14px;
}

</style>

<div class="notif-card">
    <?php if(mysqli_num_rows($notifications) > 0): ?>
        <ul class="notif-list">
            <?php while($notification = mysqli_fetch_assoc($notifications)): ?>
                <li class="notif-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                    <div class="notif-icon <?php 
                        $type = strtolower($notification['type']);
                        echo in_array($type, ['reminder', 'overdue', 'success', 'info']) ? $type : 'info'; 
                    ?>">
                        <?php
                        switch(strtolower($notification['type'])) {
                            case 'reminder': echo '<i class="bi bi-clock-fill"></i>'; break;
                            case 'overdue': echo '<i class="bi bi-exclamation-triangle-fill"></i>'; break;
                            case 'success': echo '<i class="bi bi-check-circle-fill"></i>'; break;
                            default: echo '<i class="bi bi-info-circle-fill"></i>';
                        }
                        ?>
                    </div>
                    <div class="notif-content">
                        <h6 class="notif-title">
                            <?php echo htmlspecialchars($notification['title']); ?>
                            <?php if(!$notification['is_read']): ?>
                                <span class="notif-badge">New</span>
                            <?php endif; ?>
                        </h6>
                        <p class="notif-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <p class="notif-time">
                            <i class="bi bi-clock"></i> <?php echo date('M d, Y at H:i', strtotime($notification['created_at'])); ?>
                        </p>
                    </div>
                    <div class="notif-action">
                        <?php if(!$notification['is_read']): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                <button type="submit" name="mark_read" class="mark-read-btn">
                                    Mark Read
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="empty-notif">
            <i class="bi bi-bell-slash"></i>
            <h5>No notifications yet</h5>
            <p>You'll receive notifications about due dates, new books, and more.</p>
        </div>
    <?php endif; ?>
</div>

<?php include("../includes/layout_footer.php"); ?>