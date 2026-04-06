<?php
session_start();
include("../config/database.php");
include("../includes/layout.php");

$result = mysqli_query($conn,"SELECT * FROM ebooks");
?>

<h2>📚 E-Books</h2>

<div class="row mt-4">

<?php while($book=mysqli_fetch_assoc($result)){ ?>

<div class="col-md-4">

<div class="card shadow mb-4">
<div class="card-body">

<h5><?php echo $book['title']; ?></h5>
<p class="text-muted"><?php echo $book['author']; ?></p>

<a href="read_book.php?id=<?php echo $book['id']; ?>" 
class="btn btn-primary">
Read Book
</a>

</div>
</div>

</div>

<?php } ?>

</div>

<?php include("../includes/layout_footer.php"); ?>