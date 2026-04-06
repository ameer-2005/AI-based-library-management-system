<?php

include("../config/database.php");
session_start();

// Validate authentication
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);

if($id <= 0) {
    die("Invalid record ID.");
}

$today = date("Y-m-d");

// Get borrow record
$select_stmt = mysqli_prepare($conn, "SELECT * FROM borrow_records WHERE id = ?");
if(!$select_stmt) {
    die("Database error. Please try again.");
}
mysqli_stmt_bind_param($select_stmt, 'i', $id);
mysqli_stmt_execute($select_stmt);
$result = mysqli_stmt_get_result($select_stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($select_stmt);

if(!$data) {
    die("Borrow record not found.");
}

// Verify the record belongs to current user
if($data['user_id'] != $_SESSION['user_id']) {
    die("Unauthorized access.");
}

$status = "returned";
if($today > $data['due_date']){
    $status = "late";
}

// Update borrow record
$update_stmt = mysqli_prepare($conn, "UPDATE borrow_records SET return_date = ?, status = ? WHERE id = ?");
if(!$update_stmt) {
    die("Database error. Please try again.");
}
mysqli_stmt_bind_param($update_stmt, 'ssi', $today, $status, $id);
if(!mysqli_stmt_execute($update_stmt)) {
    mysqli_stmt_close($update_stmt);
    die("Failed to update record.");
}
mysqli_stmt_close($update_stmt);

// Update book availability
$book_id = $data['book_id'];
$book_update = mysqli_prepare($conn, "UPDATE books SET available = available + 1 WHERE id = ?");
if($book_update) {
    mysqli_stmt_bind_param($book_update, 'i', $book_id);
    mysqli_stmt_execute($book_update);
    mysqli_stmt_close($book_update);
}

header("Location: ../user/my_books.php");
exit();

?>