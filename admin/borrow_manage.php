<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}

include("../config/database.php");
include("../includes/layout.php");

 $result = mysqli_query($conn, "
    SELECT borrow_records.*, users.name as user_name, books.title 
    FROM borrow_records 
    JOIN users ON borrow_records.user_id = users.id 
    JOIN books ON borrow_records.book_id = books.id
    ORDER BY borrow_records.id DESC
");
?>

<h2>Borrow Management</h2>
<div class="card shadow mt-4">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>User</th>
                    <th>Book</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Extension</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['user_name']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['issue_date']; ?></td>
                    <td><?php echo $row['due_date']; ?></td>
                    <td>
                        <?php if($row['status'] == 'issued'): ?>
                            <span class="badge bg-primary">Issued</span>
                        <?php elseif($row['status'] == 'late'): ?>
                            <span class="badge bg-danger">Late</span>
                        <?php else: ?>
                            <span class="badge bg-success">Returned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['extend_requested'] == 1): ?>
                            <span class="badge bg-warning text-dark">Requested</span>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['status'] == 'issued'): ?>
                            <a href="return_book.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Mark Returned</a>
                            
                            <?php if($row['extend_requested'] == 1): ?>
                                <a href="approve_extend.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Approve Extend</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted small">Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>