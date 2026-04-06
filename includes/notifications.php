<?php
// Notification System Functions
include("email_functions.php");

function sendEmailNotification($to, $subject, $message) {
    // Use the centralized sendEmail function
    return sendEmail($to, $subject, $message);
}

function createNotification($conn, $user_id, $type, $title, $message) {
    $sql = "INSERT INTO notifications (user_id, type, title, message, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $type, $title, $message);
    return mysqli_stmt_execute($stmt);
}

function getUserNotifications($conn, $user_id, $limit = 10) {
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $limit);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function markNotificationRead($conn, $notification_id, $user_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $notification_id, $user_id);
    return mysqli_stmt_execute($stmt);
}

// Cron job functions (to be called by a cron job or scheduled task)
function sendDueDateReminders($conn) {
    // Send reminders 3 days before due date
    $sql = "SELECT borrow_records.*, users.name, users.email, books.title
            FROM borrow_records
            JOIN users ON borrow_records.user_id = users.id
            JOIN books ON borrow_records.book_id = books.id
            WHERE borrow_records.status = 'issued'
            AND borrow_records.due_date = DATE_ADD(CURDATE(), INTERVAL 3 DAY)";

    $result = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_assoc($result)) {
        $subject = "Book Due Reminder - AI Library";
        $message = "
        <h2>Book Due Reminder</h2>
        <p>Dear {$row['name']},</p>
        <p>Your borrowed book <strong>'{$row['title']}'</strong> is due in 3 days.</p>
        <p>Due Date: {$row['due_date']}</p>
        <p>Please return the book on time to avoid fines.</p>
        <br>
        <p>Best regards,<br>AI Library Team</p>
        ";

        if(sendEmailNotification($row['email'], $subject, $message)) {
            createNotification($conn, $row['user_id'], 'reminder', 'Book Due Soon', "Your book '{$row['title']}' is due in 3 days.");
        }
    }
}

function sendOverdueNotifications($conn) {
    // Send overdue notifications
    $sql = "SELECT borrow_records.*, users.name, users.email, books.title
            FROM borrow_records
            JOIN users ON borrow_records.user_id = users.id
            JOIN books ON borrow_records.book_id = books.id
            WHERE borrow_records.status = 'issued'
            AND borrow_records.due_date < CURDATE()";

    $result = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_assoc($result)) {
        $subject = "Overdue Book Notice - AI Library";
        $message = "
        <h2>Overdue Book Notice</h2>
        <p>Dear {$row['name']},</p>
        <p>Your borrowed book <strong>'{$row['title']}'</strong> is overdue.</p>
        <p>Due Date: {$row['due_date']}</p>
        <p>Please return the book immediately to avoid additional fines.</p>
        <br>
        <p>Best regards,<br>AI Library Team</p>
        ";

        if(sendEmailNotification($row['email'], $subject, $message)) {
            createNotification($conn, $row['user_id'], 'overdue', 'Book Overdue', "Your book '{$row['title']}' is overdue. Please return it immediately.");
        }
    }
}

function sendNewBookNotifications($conn) {
    // Send notifications about new books (last 7 days)
    $sql = "SELECT * FROM books WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $new_books = mysqli_query($conn, $sql);

    if(mysqli_num_rows($new_books) > 0) {
        $users = mysqli_query($conn, "SELECT id, name, email FROM users WHERE role = 'user'");

        while($user = mysqli_fetch_assoc($users)) {
            $subject = "New Books Added - AI Library";
            $message = "
            <h2>New Books Available!</h2>
            <p>Dear {$user['name']},</p>
            <p>We have added some new books to our collection:</p>
            <ul>
            ";

            mysqli_data_seek($new_books, 0); // Reset pointer
            while($book = mysqli_fetch_assoc($new_books)) {
                $message .= "<li>{$book['title']} by {$book['author']}</li>";
            }

            $message .= "
            </ul>
            <p>Visit our library to borrow these books!</p>
            <br>
            <p>Best regards,<br>AI Library Team</p>
            ";

            if(sendEmailNotification($user['email'], $subject, $message)) {
                createNotification($conn, $user['id'], 'info', 'New Books Added', 'Check out the latest additions to our library collection!');
            }
        }
    }
}
?>