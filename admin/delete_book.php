<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");

$id = intval($_GET['id']);

// Check if book exists
$check_stmt = $conn->prepare("SELECT title FROM books WHERE id = ?");
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if($check_result->num_rows == 0){
    $_SESSION['error'] = "Book not found.";
    header("Location: manage_books.php");
    exit();
}

$book = $check_result->fetch_assoc();
$book_title = $book['title'];
$check_stmt->close();

// Check if book is currently borrowed
$borrow_check = $conn->prepare("SELECT COUNT(*) as count FROM borrow_records WHERE book_id = ? AND status = 'issued'");
$borrow_check->bind_param("i", $id);
$borrow_check->execute();
$borrow_result = $borrow_check->get_result();
$borrow_data = $borrow_result->fetch_assoc();
$borrow_check->close();

if($borrow_data['count'] > 0){
    $_SESSION['error'] = "Cannot delete book '$book_title' - it is currently borrowed by {$borrow_data['count']} user(s). Please wait for it to be returned.";
    header("Location: manage_books.php");
    exit();
}

// Check if book has active reservations
$reservation_check = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE book_id = ? AND status = 'active'");
$reservation_check->bind_param("i", $id);
$reservation_check->execute();
$reservation_result = $reservation_check->get_result();
$reservation_data = $reservation_result->fetch_assoc();
$reservation_check->close();

if($reservation_data['count'] > 0){
    $_SESSION['error'] = "Cannot delete book '$book_title' - it has {$reservation_data['count']} active reservation(s). Please cancel reservations first.";
    header("Location: manage_books.php");
    exit();
}

// Delete book cover if exists
$cover_stmt = $conn->prepare("SELECT cover_path FROM book_covers WHERE book_id = ?");
$cover_stmt->bind_param("i", $id);
$cover_stmt->execute();
$cover_result = $cover_stmt->get_result();

if($cover_result->num_rows > 0){
    $cover_data = $cover_result->fetch_assoc();
    $cover_path = $cover_data['cover_path'];
    if(file_exists($cover_path)){
        unlink($cover_path);
    }
    // Delete cover record
    $delete_cover = $conn->prepare("DELETE FROM book_covers WHERE book_id = ?");
    $delete_cover->bind_param("i", $id);
    $delete_cover->execute();
    $delete_cover->close();
}
$cover_stmt->close();

// Delete related records first (cascade delete simulation)
$conn->query("DELETE FROM reviews WHERE book_id = $id");
$conn->query("DELETE FROM reading_history WHERE book_id = $id");
$conn->query("DELETE FROM reservations WHERE book_id = $id");
$conn->query("DELETE FROM borrow_records WHERE book_id = $id");

// Now delete the book
$delete_stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
$delete_stmt->bind_param("i", $id);

if($delete_stmt->execute()){
    $_SESSION['success'] = "Book '$book_title' has been successfully deleted.";
} else {
    $_SESSION['error'] = "Error deleting book: " . $delete_stmt->error;
}

$delete_stmt->close();
header("Location: manage_books.php");
exit();
?>