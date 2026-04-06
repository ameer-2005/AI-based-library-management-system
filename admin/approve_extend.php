<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}

include("../config/database.php");

 $id = $_GET['id'];

// Get Settings
 $set = mysqli_fetch_assoc(mysqli_query($conn, "SELECT extend_days FROM settings LIMIT 1"));
 $days = $set['extend_days'];

 $rec = mysqli_fetch_assoc(mysqli_query($conn, "SELECT due_date FROM borrow_records WHERE id='$id'"));
 $new_due = date("Y-m-d", strtotime($rec['due_date']." +$days days"));

mysqli_query($conn, "UPDATE borrow_records SET due_date='$new_due', extend_requested=0, extend_count=extend_count+1 WHERE id='$id'");

header("Location: borrow_manage.php");
?>