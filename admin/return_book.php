<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}

include("../config/database.php");
include("../includes/layout.php");

$message = '';
$error = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0){
    $error = "Invalid borrow record ID";
} else {
    // Get Record Details using prepared statement
    $stmt = $conn->prepare("SELECT * FROM borrow_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 0){
        $error = "Borrow record not found";
    } else {
        $data = $result->fetch_assoc();
        
        if($data['status'] == 'returned'){
            $error = "This book has already been returned";
        } else {
            // Handle confirmation button
            if(isset($_POST['confirm_return'])){
                $today = date("Y-m-d");
                $status = "returned";
                $fine = 0;
                
                // Calculate Fine if Late
                if($today > $data['due_date']){
                    $status = "late";
                    
                    // Get Fine Settings
                    $fine_stmt = $conn->prepare("SELECT daily_fine FROM fines_config WHERE id = 1");
                    $fine_stmt->execute();
                    $fine_result = $fine_stmt->get_result();
                    
                    if($fine_result->num_rows > 0){
                        $config = $fine_result->fetch_assoc();
                        $daily_fine = $config['daily_fine'];
                        
                        // Calculate Days Late
                        $due_date = new DateTime($data['due_date']);
                        $return_date = new DateTime($today);
                        $interval = $due_date->diff($return_date);
                        $days_late = $interval->days;
                        
                        $fine = $days_late * $daily_fine;
                    }
                    $fine_stmt->close();
                }
                
                // Update Record using prepared statement
                $update_stmt = $conn->prepare("UPDATE borrow_records SET return_date = ?, status = ?, fine_amount = ? WHERE id = ?");
                $update_stmt->bind_param("ssdi", $today, $status, $fine, $id);
                
                if($update_stmt->execute()){
                    // Increase Book Availability
                    $book_stmt = $conn->prepare("UPDATE books SET available = available + 1 WHERE id = ?");
                    $book_stmt->bind_param("i", $data['book_id']);
                    
                    if($book_stmt->execute()){
                        $message = "Book returned successfully!";
                        if($fine > 0){
                            $message .= " Fine of ₹" . number_format($fine, 2) . " has been charged.";
                        }
                        // Redirect after 2 seconds
                        echo "<meta http-equiv='refresh' content='2;url=borrow_manage.php'>";
                    } else {
                        $error = "Error updating book availability: " . $book_stmt->error;
                    }
                    $book_stmt->close();
                } else {
                    $error = "Error updating borrow record: " . $update_stmt->error;
                }
                $update_stmt->close();
            }
        }
    }
    $stmt->close();
}
?>

<div class="container mt-4">
    <h2>Return Book</h2>
    
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
    
    <?php if(!$error && isset($data)): ?>
        <div class="card shadow">
            <div class="card-body">
                <h5>Confirm Book Return</h5>
                <hr>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>User:</strong></label>
                        <p class="text-muted">
                            <?php 
                            $user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                            $user_stmt->bind_param("i", $data['user_id']);
                            $user_stmt->execute();
                            $user_result = $user_stmt->get_result();
                            $user = $user_result->fetch_assoc();
                            echo htmlspecialchars($user['name']);
                            $user_stmt->close();
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Book:</strong></label>
                        <p class="text-muted">
                            <?php 
                            $book_stmt = $conn->prepare("SELECT title FROM books WHERE id = ?");
                            $book_stmt->bind_param("i", $data['book_id']);
                            $book_stmt->execute();
                            $book_result = $book_stmt->get_result();
                            $book = $book_result->fetch_assoc();
                            echo htmlspecialchars($book['title']);
                            $book_stmt->close();
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Issue Date:</strong></label>
                        <p class="text-muted"><?php echo $data['issue_date']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Due Date:</strong></label>
                        <p class="text-muted"><?php echo $data['due_date']; ?></p>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Return Date:</strong></label>
                        <p class="text-muted"><?php echo date("Y-m-d"); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>Status:</strong></label>
                        <p class="text-muted">
                            <?php 
                            $today = date("Y-m-d");
                            if($today > $data['due_date']){
                                // Get Fine Settings
                                $fine_stmt = $conn->prepare("SELECT daily_fine FROM fines_config WHERE id = 1");
                                $fine_stmt->execute();
                                $fine_result = $fine_stmt->get_result();
                                $config = $fine_result->fetch_assoc();
                                $daily_fine = $config['daily_fine'];
                                
                                // Calculate Days Late
                                $due_date = new DateTime($data['due_date']);
                                $return_date = new DateTime($today);
                                $interval = $due_date->diff($return_date);
                                $days_late = $interval->days;
                                
                                $fine = $days_late * $daily_fine;
                                
                                echo '<span class="badge bg-danger">Late</span>';
                                echo '<br><small class="text-danger mt-2 d-block">Fine: ₹' . number_format($fine, 2) . ' (' . $days_late . ' days × ₹' . $daily_fine . '/day)</small>';
                                
                                $fine_stmt->close();
                            } else {
                                echo '<span class="badge bg-info">On Time</span>';
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <hr>
                <form method="POST">
                    <div class="d-flex gap-2">
                        <button type="submit" name="confirm_return" class="btn btn-success">Confirm Return</button>
                        <a href="borrow_manage.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include("../includes/layout_footer.php"); ?>