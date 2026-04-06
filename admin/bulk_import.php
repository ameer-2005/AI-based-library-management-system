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

$message = '';
$error = '';
$import_count = 0;

// Handle CSV upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])){
    $file = $_FILES['csv_file'];
    $allowed = ['text/csv', 'application/vnd.ms-excel'];
    
    if(!in_array($file['type'], $allowed)){
        $error = "Please upload a valid CSV file";
    } elseif($file['size'] > 2 * 1024 * 1024){ // 2MB limit
        $error = "File size exceeds 2MB limit";
    } else {
        $csv_file = $file['tmp_name'];
        
        if(($handle = fopen($csv_file, 'r')) !== FALSE){
            // Skip header
            $header = fgetcsv($handle, 1000, ',');
            
            $imported = 0;
            $skipped = 0;
            
            while(($row = fgetcsv($handle, 1000, ',')) !== FALSE){
                // CSV columns: title, author, category_id, description, available
                
                if(count($row) < 2){
                    $skipped++;
                    continue;
                }
                
                $title = trim($row[0]);
                $author = trim($row[1]);
                $category_id = !empty($row[2]) ? intval($row[2]) : null;
                $description = !empty($row[3]) ? trim($row[3]) : '';
                $available = !empty($row[4]) ? intval($row[4]) : 1;
                
                if(empty($title) || empty($author)){
                    $skipped++;
                    continue;
                }
                
                // Check if book already exists (by title and author)
                $check_stmt = $conn->prepare("SELECT id FROM books WHERE title = ? AND author = ?");
                if($check_stmt === false){
                    $skipped++;
                    continue;
                }
                $check_stmt->bind_param("ss", $title, $author);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if($check_result && $check_result->num_rows == 0){
                    // Validate category exists if provided
                    if($category_id){
                        $cat_check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
                        $cat_check->bind_param("i", $category_id);
                        $cat_check->execute();
                        $cat_result = $cat_check->get_result();
                        $cat_check->close();
                        
                        if($cat_result->num_rows == 0){
                            // Category doesn't exist, skip or use null
                            $category_id = null;
                        }
                    }
                    
                    // Insert new book
                    if($category_id){
                        $insert_stmt = $conn->prepare("
                            INSERT INTO books (title, author, category_id, description, available) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $insert_stmt->bind_param("ssssi", $title, $author, $category_id, $description, $available);
                    } else {
                        $insert_stmt = $conn->prepare("
                            INSERT INTO books (title, author, description, available) 
                            VALUES (?, ?, ?, ?)
                        ");
                        $insert_stmt->bind_param("sssi", $title, $author, $description, $available);
                    }
                    
                    if($insert_stmt->execute()){
                        $imported++;
                    } else {
                        $skipped++;
                    }
                    $insert_stmt->close();
                } else {
                    $skipped++;
                }
                $check_stmt->close();
            }
            
            fclose($handle);
            $message = "Import completed! $imported books imported, $skipped skipped.";
            $import_count = $imported;
        } else {
            $error = "Error reading CSV file";
        }
    }
}

?>

<h2>📥 Bulk Book Import</h2>

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
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">📤 Upload CSV File</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Select CSV File *</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        <small class="text-muted d-block mt-2">Maximum file size: 2MB</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Import Books</button>
                </form>
                
                <hr>
                
                <h6>CSV Format Requirements:</h6>
                <p>Your CSV file should have the following columns:</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Column</th>
                                <th>Required</th>
                                <th>Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>title</strong></td>
                                <td>Yes</td>
                                <td>The Great Gatsby</td>
                            </tr>
                            <tr>
                                <td><strong>author</strong></td>
                                <td>Yes</td>
                                <td>F. Scott Fitzgerald</td>
                            </tr>
                            <tr>
                                <td><strong>category_id</strong></td>
                                <td>No</td>
                                <td>1</td>
                            </tr>
                            <tr>
                                <td><strong>description</strong></td>
                                <td>No</td>
                                <td>A classic novel of the Jazz Age</td>
                            </tr>
                            <tr>
                                <td><strong>available</strong></td>
                                <td>No</td>
                                <td>5</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📝 Instructions</h5>
            </div>
            <div class="card-body">
                <h6>Step 1: Prepare CSV</h6>
                <p>Create a CSV file with book details. Use commas to separate columns.</p>
                
                <h6>Step 2: Add Categories</h6>
                <p>Use category IDs from the list below. If not specified, book won't be in a category.</p>
                
                <h6>Step 3: Upload</h6>
                <p>Click "Import Books" to upload the file.</p>
                
                <hr>
                
                <h6>Available Categories:</h6>
                <div class="list-group list-group-sm">
                    <?php 
                    $categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name");
                    if(mysqli_num_rows($categories_result) > 0):
                        while($cat = mysqli_fetch_assoc($categories_result)): 
                    ?>
                        <div class="list-group-item">
                            <small><strong>ID: <?php echo $cat['id']; ?></strong> - <?php echo htmlspecialchars($cat['name']); ?></small>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div class="alert alert-warning mt-2">
                            <small>No categories yet. <a href="manage_categories.php">Create one first</a></small>
                        </div>
                    <?php 
                    endif; 
                    ?>
                </div>
                
                <hr>
                
                <h6>Download Template:</h6>
                <a href="sample_books.csv" class="btn btn-sm btn-outline-primary" download>Download CSV Template</a>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>
