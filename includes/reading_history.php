<?php
// Reading history tracking functions

function addReadingHistory($conn, $user_id, $book_id, $pages_read = 0) {
    $stmt = $conn->prepare("
        INSERT INTO reading_history (user_id, book_id, pages_read) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE pages_read = ?, read_date = NOW()
    ");
    
    $stmt->bind_param("iiii", $user_id, $book_id, $pages_read, $pages_read);
    return $stmt->execute();
}

function getReadingStatistics($conn, $user_id) {
    $result = $conn->query("
        SELECT 
            COUNT(*) as total_books,
            SUM(pages_read) as total_pages,
            MAX(read_date) as last_read,
            AVG(pages_read) as avg_pages
        FROM reading_history
        WHERE user_id = $user_id
    ");
    
    return mysqli_fetch_assoc($result);
}

function getTopBooksRead($conn, $limit = 5) {
    $result = $conn->query("
        SELECT b.id, b.title, b.author, COUNT(*) as read_count
        FROM reading_history rh
        JOIN books b ON rh.book_id = b.id
        GROUP BY b.id
        ORDER BY read_count DESC
        LIMIT $limit
    ");
    
    $books = [];
    while($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
    return $books;
}

function getUserReadingStreak($conn, $user_id) {
    $result = $conn->query("
        SELECT DATEDIFF(CURDATE(), MAX(read_date)) as days_since_read
        FROM reading_history
        WHERE user_id = $user_id
    ");
    
    $row = mysqli_fetch_assoc($result);
    return $row['days_since_read'];
}
?>
