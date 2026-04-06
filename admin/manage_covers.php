<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}

include("../config/database.php");
include("../includes/layout.php");

$message = '';
$error = '';

// Ensure uploads directory exists and is writable
$upload_dir = "../assests/book_covers/";
if(!is_dir($upload_dir)){
    if(!mkdir($upload_dir, 0777, true)){
        $error = "Failed to create upload directory. Check permissions.";
    }
} elseif(!is_writable($upload_dir)){
    $error = "Upload directory is not writable. Check permissions.";
}

// Handle cover upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['cover'])){
    $book_id = intval($_POST['book_id']);
    
    // Verify book exists
    $verify_stmt = $conn->prepare("SELECT id FROM books WHERE id = ?");
    $verify_stmt->bind_param("i", $book_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if($verify_result->num_rows == 0){
        $error = "Book not found";
    } else {
        $file = $_FILES['cover'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        // Check for upload errors
        if($file['error'] !== UPLOAD_ERR_OK){
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds maximum size allowed by server',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum size specified in form',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $error = $upload_errors[$file['error']] ?? 'Unknown upload error';
        } elseif(!in_array($file['type'], $allowed)){
            $error = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed. Uploaded type: " . $file['type'];
        } elseif($file['size'] > 5 * 1024 * 1024){ // 5MB limit
            $error = "File size exceeds 5MB limit. File size: " . round($file['size']/1024/1024, 2) . "MB";
        } else {
            // Generate unique filename
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = "book_" . $book_id . "_" . time() . "." . $ext;
            $filepath = $upload_dir . $filename;
            
            if(move_uploaded_file($file['tmp_name'], $filepath)){
                // Check if cover already exists and delete it
                $old_cover_stmt = $conn->prepare("SELECT cover_path FROM book_covers WHERE book_id = ?");
                $old_cover_stmt->bind_param("i", $book_id);
                $old_cover_stmt->execute();
                $old_result = $old_cover_stmt->get_result();
                
                if($old_result->num_rows > 0){
                    $old_cover = $old_result->fetch_assoc();
                    $old_file = $upload_dir . basename($old_cover['cover_path']);
                    if(file_exists($old_file)){
                        unlink($old_file);
                    }
                    
                    // Update existing record
                    $update_stmt = $conn->prepare("UPDATE book_covers SET cover_path = ?, uploaded_at = NOW() WHERE book_id = ?");
                    $update_stmt->bind_param("si", $filepath, $book_id);
                    if($update_stmt->execute()){
                        $message = "Book cover updated successfully!";
                    } else {
                        $error = "Database error: " . $update_stmt->error;
                    }
                    $update_stmt->close();
                } else {
                    // Insert new record
                    $insert_stmt = $conn->prepare("INSERT INTO book_covers (book_id, cover_path) VALUES (?, ?)");
                    $insert_stmt->bind_param("is", $book_id, $filepath);
                    if($insert_stmt->execute()){
                        $message = "Book cover uploaded successfully!";
                    } else {
                        $error = "Database error: " . $insert_stmt->error;
                    }
                    $insert_stmt->close();
                }
                
                $old_cover_stmt->close();
            } else {
                $error = "Error uploading file. Check file permissions and disk space. Upload path: " . $filepath;
            }
        }
    }
    $verify_stmt->close();
}

// Get all books
$books_result = $conn->query("
    SELECT b.*, bc.cover_path 
    FROM books b 
    LEFT JOIN book_covers bc ON b.id = bc.book_id 
    ORDER BY b.title ASC
");
?>

<h2>📚 Manage Book Covers</h2>

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

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mt-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Books</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php while($book = mysqli_fetch_assoc($books_result)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <?php if($book['cover_path']): ?>
                                    <img src="<?php echo htmlspecialchars($book['cover_path']); ?>" 
                                         class="card-img-top" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="height: 200px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                        <span class="text-muted">No Cover</span>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h6>
                                    <p class="card-text small text-muted"><?php echo htmlspecialchars($book['author']); ?></p>
                                    
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <input type="file" class="form-control" name="cover" accept="image/*" required>
                                            <button class="btn btn-primary" type="submit">Upload</button>
                                        </div>
                                        <small class="text-muted d-block mt-2">JPG, PNG, GIF, WebP • Max 5MB</small>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow mt-4 bg-light">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📋 Instructions</h5>
            </div>
            <div class="card-body">
                <h6>How to Upload Covers:</h6>
                <ol>
                    <li>Find the book you want to add a cover for</li>
                    <li>Click "Choose File" and select an image</li>
                    <li>Click "Upload" to save the cover</li>
                    <li>The cover will appear in previews and listings</li>
                </ol>
                
                <hr>
                
                <h6>Supported Formats:</h6>
                <ul class="list-unstyled text-sm">
                    <li>✓ JPEG (.jpg, .jpeg)</li>
                    <li>✓ PNG (.png)</li>
                    <li>✓ GIF (.gif)</li>
                    <li>✓ WebP (.webp)</li>
                </ul>
                
                <hr>
                
                <h6>Recommendations:</h6>
                <ul class="list-unstyled text-sm">
                    <li>✓ Recommended size: 300x400 pixels</li>
                    <li>✓ Maximum file size: 5MB</li>
                    <li>✓ Use high-quality images</li>
                    <li>✓ Aspect ratio: 3:4 (portrait)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>
