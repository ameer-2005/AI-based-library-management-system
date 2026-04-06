<?php

session_start();
include("../config/database.php");

$user = $_SESSION['user_id'];

$book = $_POST['book_id'];
$rating = $_POST['rating'];
$review = $_POST['review'];

$sql = "
INSERT INTO reviews(user_id,book_id,rating,review)
VALUES('$user','$book','$rating','$review')
";

mysqli_query($conn,$sql);

header("Location: book_details.php?id=".$book);

?>