<?php

session_start();
include("../config/database.php");

$result = mysqli_query($conn,"
SELECT borrow_records.*, users.name, books.title
FROM borrow_records
JOIN users ON borrow_records.user_id = users.id
JOIN books ON borrow_records.book_id = books.id
");

?>

<?php include("../includes/header.php"); ?>

<div class="container mt-5">

<h3>Borrow History</h3>

<table class="table table-bordered">

<tr>
<th>User</th>
<th>Book</th>
<th>Issue</th>
<th>Due</th>
<th>Return</th>
<th>Status</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['name']; ?></td>
<td><?php echo $row['title']; ?></td>
<td><?php echo $row['issue_date']; ?></td>
<td><?php echo $row['due_date']; ?></td>
<td><?php echo $row['return_date']; ?></td>
<td><?php echo $row['status']; ?></td>

</tr>

<?php } ?>

</table>

</div>

<?php include("../includes/footer.php"); ?>