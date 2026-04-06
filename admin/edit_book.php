<?php
session_start();
include("../config/database.php");
include("../includes/layout.php");

 $id = $_GET['id'];
 $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id='$id'"));
 $cats = mysqli_query($conn, "SELECT * FROM categories");

if(isset($_POST['update'])){
    $title  = $_POST['title'];
    $author = $_POST['author'];
    $cat    = $_POST['category_id'];
    $qty    = $_POST['quantity'];

    mysqli_query($conn, "UPDATE books SET title='$title', author='$author', category_id='$cat', quantity='$qty' WHERE id='$id'");
    header("Location: manage_books.php");
}
?>

<h2>Edit Book</h2>
<form method="POST" class="card shadow p-4 mt-4">
    <div class="mb-3">
        <input type="text" name="title" value="<?php echo $data['title']; ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <input type="text" name="author" value="<?php echo $data['author']; ?>" class="form-control">
    </div>
    <div class="mb-3">
        <select name="category_id" class="form-control">
            <?php while($c = mysqli_fetch_assoc($cats)): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $data['category_id']) ? 'selected' : ''; ?>>
                    <?php echo $c['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <input type="number" name="quantity" value="<?php echo $data['quantity']; ?>" class="form-control">
    </div>
    <button name="update" class="btn btn-success">Update</button>
</form>

<?php include("../includes/layout_footer.php"); ?>