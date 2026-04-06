<?php
session_start();
include("../config/database.php");

if(!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$book_id = intval($_GET['book_id'] ?? 0);

if($book_id <= 0) {
    die("Invalid book ID.");
}

// Check if already borrowed and not returned
$check_stmt = mysqli_prepare($conn, "SELECT id FROM borrow_records WHERE user_id = ? AND book_id = ? AND status = 'issued'");
if(!$check_stmt) {
    die("Database error. Please try again.");
}
mysqli_stmt_bind_param($check_stmt, 'ii', $user_id, $book_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);
if(mysqli_num_rows($check_result) > 0){
    mysqli_stmt_close($check_stmt);
    die("You have already borrowed this book.");
}
mysqli_stmt_close($check_stmt);

// Get Settings
$settings_result = mysqli_query($conn, "SELECT borrow_days FROM settings LIMIT 1");
if(!$settings_result) {
    die("Database error. Please try again.");
}
$settings = mysqli_fetch_assoc($settings_result);
$days = $settings['borrow_days'] ?? 14;

$issue_date = date("Y-m-d");
$due_date = date("Y-m-d", strtotime("+$days days"));

// Insert Record
$insert_stmt = mysqli_prepare($conn, "INSERT INTO borrow_records (user_id, book_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'issued')");
if(!$insert_stmt) {
    die("Database error. Please try again.");
}
mysqli_stmt_bind_param($insert_stmt, 'iiss', $user_id, $book_id, $issue_date, $due_date);
if(!mysqli_stmt_execute($insert_stmt)) {
    mysqli_stmt_close($insert_stmt);
    die("Failed to create borrow record.");
}
mysqli_stmt_close($insert_stmt);

// Decrease Available
$update_stmt = mysqli_prepare($conn, "UPDATE books SET available = available - 1 WHERE id = ?");
if($update_stmt) {
    mysqli_stmt_bind_param($update_stmt, 'i', $book_id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
}

header("Location: ../user/my_books.php");
?>