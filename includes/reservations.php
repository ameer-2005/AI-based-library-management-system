<?php
// Reservation System Functions

function createReservation($conn, $user_id, $book_id) {
    // Check if book is available
    $book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT available FROM books WHERE id = $book_id"));
    if($book['available'] > 0) {
        return ['success' => false, 'message' => 'Book is currently available. You can borrow it directly.'];
    }

    // Check if user already has an active reservation for this book
    $existing = mysqli_query($conn, "SELECT id FROM reservations WHERE user_id = $user_id AND book_id = $book_id AND status = 'active'");
    if(mysqli_num_rows($existing) > 0) {
        return ['success' => false, 'message' => 'You already have an active reservation for this book.'];
    }

    // Check user's active reservations limit (max 3)
    $active_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reservations WHERE user_id = $user_id AND status = 'active'"))['count'];
    if($active_count >= 3) {
        return ['success' => false, 'message' => 'You can have maximum 3 active reservations.'];
    }

    // Create reservation (expires in 7 days)
    $expiry_date = date('Y-m-d', strtotime('+7 days'));
    $sql = "INSERT INTO reservations (user_id, book_id, expiry_date) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $user_id, $book_id, $expiry_date);

    if(mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'Book reserved successfully! You will be notified when it becomes available.'];
    } else {
        return ['success' => false, 'message' => 'Failed to create reservation.'];
    }
}

function cancelReservation($conn, $reservation_id, $user_id) {
    $sql = "UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $reservation_id, $user_id);
    return mysqli_stmt_execute($stmt);
}

function getUserReservations($conn, $user_id) {
    $sql = "SELECT reservations.*, books.title, books.author
            FROM reservations
            JOIN books ON reservations.book_id = books.id
            WHERE reservations.user_id = ?
            ORDER BY reservations.reservation_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function checkAndFulfillReservations($conn) {
    // Find books that became available and fulfill oldest reservations
    $available_books = mysqli_query($conn, "
        SELECT books.id, books.title
        FROM books
        WHERE books.available > 0
        AND books.id IN (SELECT book_id FROM reservations WHERE status = 'active')
    ");

    while($book = mysqli_fetch_assoc($available_books)) {
        // Get oldest active reservation for this book
        $reservation = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT reservations.*, users.name, users.email
            FROM reservations
            JOIN users ON reservations.user_id = users.id
            WHERE reservations.book_id = {$book['id']}
            AND reservations.status = 'active'
            AND reservations.expiry_date >= CURDATE()
            ORDER BY reservations.reservation_date ASC
            LIMIT 1
        "));

        if($reservation) {
            // Fulfill the reservation
            mysqli_query($conn, "UPDATE reservations SET status = 'fulfilled' WHERE id = {$reservation['id']}");

            // Create notification
            include_once("includes/notifications.php");
            createNotification($conn, $reservation['user_id'], 'success',
                'Reservation Fulfilled!',
                "Great news! Your reserved book '{$book['title']}' is now available for pickup.");

            // Send email notification
            $subject = "Book Reservation Fulfilled - AI Library";
            $message = "
            <h2>Great News!</h2>
            <p>Dear {$reservation['name']},</p>
            <p>Your reserved book <strong>'{$book['title']}'</strong> is now available for borrowing.</p>
            <p>Please visit the library to pick up your book within 3 days.</p>
            <br>
            <p>Best regards,<br>AI Library Team</p>
            ";
            sendEmailNotification($reservation['email'], $subject, $message);
        }
    }
}

function cleanupExpiredReservations($conn) {
    // Cancel expired reservations
    mysqli_query($conn, "UPDATE reservations SET status = 'cancelled' WHERE status = 'active' AND expiry_date < CURDATE()");
}
?>