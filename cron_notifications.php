<?php
// Cron job script for sending notifications
// This should be run periodically (e.g., daily) via cron job or scheduled task

include("config/database.php");
include("includes/notifications.php");

echo "Starting notification cron job...\n";
echo date('Y-m-d H:i:s') . "\n\n";

// Send due date reminders (3 days before due)
echo "Sending due date reminders...\n";
sendDueDateReminders($conn);
echo "Due date reminders sent.\n\n";

// Send overdue notifications
echo "Sending overdue notifications...\n";
sendOverdueNotifications($conn);
echo "Overdue notifications sent.\n\n";

// Send new book notifications (weekly)
echo "Sending new book notifications...\n";
sendNewBookNotifications($conn);
echo "New book notifications sent.\n\n";

echo "Notification cron job completed!\n";
?>