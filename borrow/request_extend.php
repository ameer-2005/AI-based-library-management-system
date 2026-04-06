<<?php
session_start();
include("../config/database.php");

// Validate and sanitize input
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']); // Convert to integer for safety
    
    // Use prepared statement to prevent SQL injection
    $sql = "UPDATE borrow_records SET extend_requested = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            mysqli_stmt_close($stmt);
            header("Location: ../user/my_books.php");
            exit();
        } else {
            mysqli_stmt_close($stmt);
            echo "Error updating extend request. Please try again.";
            exit();
        }
    } else {
        echo "Database error. Please try again.";
        exit();
    }
} else {
    echo "Invalid request. No record ID provided.";
    exit();
}
?>