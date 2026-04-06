<?php
session_start();
include("../config/database.php");
include("../includes/layout.php");

if(!isset($_GET['id'])){
    echo "Book not found.";
    exit();
}

$id = $_GET['id'];

$q = mysqli_query($conn,"SELECT * FROM ebooks WHERE id='$id'");
$book = mysqli_fetch_assoc($q);

if(!$book){
    echo "Book not found in database.";
    exit();
}
?>

<h2><?php echo htmlspecialchars($book['title']); ?></h2>

<div class="card shadow mt-4">
<div class="card-body">

<iframe 
src="../ebooks/<?php echo htmlspecialchars($book['file_path']); ?>" 
width="100%" 
height="700px" 
style="border:none;">
</iframe>

</div>
</div>

<?php include("../includes/layout_footer.php"); ?>