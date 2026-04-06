<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}

include("../config/database.php");
include("../includes/layout.php");

$message = '';
$error = '';

// Add category
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])){
    $name = trim($_POST['name']);
    
    if(empty($name)){
        $error = "Category name is required";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        
        if($stmt->execute()){
            $message = "Category added successfully!";
        } else {
            if(strpos($stmt->error, 'Duplicate') !== false){
                $error = "Category with this name already exists";
            } else {
                $error = "Error adding category: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Delete category
if(isset($_GET['delete'])){
    $cat_id = intval($_GET['delete']);
    
    // Check if category is in use
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM books WHERE category_id = ?");
    $check_stmt->bind_param("i", $cat_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    
    if($check_row['count'] > 0){
        $error = "Cannot delete category that has books. Remove books first.";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $delete_stmt->bind_param("i", $cat_id);
        
        if($delete_stmt->execute()){
            $message = "Category deleted successfully!";
        } else {
            $error = "Error deleting category";
        }
        $delete_stmt->close();
    }
    $check_stmt->close();
}

// Get all categories
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<h2>📂 Manage Categories</h2>

<?php if($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">➕ Add New Category</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" class="form-control" name="name" required maxlength="100">
                    </div>
                    
                    <button type="submit" name="add_category" class="btn btn-primary w-100">Add Category</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">📋 All Categories</h5>
            </div>
            <div class="card-body">
                <?php if(mysqli_num_rows($categories_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Category Name</th>
                                    <th>Books</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                                    <?php
                                    // Count books in this category
                                    $book_count_result = $conn->query("SELECT COUNT(*) as count FROM books WHERE category_id = {$cat['id']}");
                                    $book_count = mysqli_fetch_assoc($book_count_result)['count'];
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                        <td><span class="badge bg-info"><?php echo $book_count; ?></span></td>
                                        <td>
                                            <?php if($book_count == 0): ?>
                                                <a href="?delete=<?php echo $cat['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Delete this category?');">Delete</a>
                                            <?php else: ?>
                                                <span class="text-muted small">In use</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-0">No categories yet. Create one to organize your books!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>
