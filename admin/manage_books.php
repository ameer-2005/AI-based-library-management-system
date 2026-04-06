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

 $result = mysqli_query($conn, "
    SELECT books.*, categories.name as cat_name 
    FROM books 
    JOIN categories ON books.category_id = categories.id
    ORDER BY books.id DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Books</h2>
    <a href="add_book.php" class="btn btn-primary"><i class="bi bi-plus"></i> Add New Book</a>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['author']; ?></td>
                    <td><span class="badge bg-info"><?php echo $row['cat_name']; ?></span></td>
                    <td><?php echo $row['available']; ?></td>
                    <td>
                        <a href="edit_book.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_book.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>