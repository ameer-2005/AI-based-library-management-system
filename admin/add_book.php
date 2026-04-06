<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/layout.php");

 $categories = mysqli_query($conn, "SELECT * FROM categories");

if(isset($_POST['add_book'])){
    $title    = mysqli_real_escape_string($conn, $_POST['title']);
    $author   = mysqli_real_escape_string($conn, $_POST['author']);
    $cat_id   = $_POST['category_id'];
    $desc     = mysqli_real_escape_string($conn, $_POST['description']);
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO books (title, author, category_id, description, quantity, available) 
            VALUES ('$title', '$author', '$cat_id', '$desc', '$quantity', '$quantity')";
    
    if(mysqli_query($conn, $sql)){
        echo "<script>alert('Book Added Successfully'); window.location='manage_books.php';</script>";
    } else {
        $error = "Failed to add book";
    }
}
?>

<h2>Add New Book</h2>
<div class="card shadow mt-4">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label>Book Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Author</label>
                <input type="text" name="author" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Category</label>
                <select name="category_id" class="form-control" required>
                    <?php while($row = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" required min="1">
            </div>
            <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
            <a href="manage_books.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>