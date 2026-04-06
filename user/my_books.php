<?php
session_start();
include("../config/database.php");
include("../includes/layout.php");

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "
    SELECT borrow_records.*, books.title 
    FROM borrow_records 
    JOIN books ON borrow_records.book_id = books.id 
    WHERE borrow_records.user_id = '$user_id'
    ORDER BY borrow_records.id DESC
");
?>

<style>
/* BORROWED BOOKS PAGE */

body {
  background: #f5f7fa;
}

.content-wrapper {
  background: #f5f7fa;
}

h2 {
  color: #2c3e50;
  font-weight: 700;
  letter-spacing: -0.5px;
  margin-bottom: 2rem;
  font-size: 2rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

h2 i {
  color: #667eea;
  font-size: 2.2rem;
}

/* TABLE CARD */

.table-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.table-responsive {
  overflow-x: auto;
}

.borrow-table {
  margin: 0;
}

.borrow-table thead {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.borrow-table th {
  color: white;
  font-weight: 700;
  padding: 16px;
  border: none;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  font-size: 12px;
}

.borrow-table tbody tr {
  border-bottom: 1px solid #eee;
  transition: all 0.3s;
}

.borrow-table tbody tr:hover {
  background: #f8fafc;
  box-shadow: inset 0 2px 8px rgba(102, 126, 234, 0.08);
}

.borrow-table tbody td {
  padding: 16px;
  color: #2c3e50;
  font-weight: 500;
}

.book-name {
  color: #667eea;
  font-weight: 700;
}

.date-cell {
  font-size: 13px;
  color: #7f8c8d;
}

/* STATUS BADGES */

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-issued {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
  color: #667eea;
}

.badge-late {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.2) 100%);
  color: #ef4444;
}

.badge-returned {
  background: linear-gradient(135deg, rgba(86, 171, 47, 0.2) 0%, rgba(168, 224, 99, 0.2) 100%);
  color: #56ab2f;
}

/* ACTION BUTTONS */

.action-btn {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 12px;
  text-decoration: none;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
  color: white;
}

.action-btn:disabled,
.pending-text {
  background: #bfdbfe;
  color: #1e40af;
  cursor: default;
  opacity: 0.7;
}

.pending-text:hover {
  transform: none;
  box-shadow: none;
}

/* EMPTY STATE */

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #7f8c8d;
}

.empty-state i {
  font-size: 64px;
  color: #bdc3c7;
  margin-bottom: 20px;
  display: block;
}

.empty-state h3 {
  color: #2c3e50;
  font-size: 18px;
  margin-bottom: 10px;
  font-weight: 700;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

</style>

<h2><i class="bi bi-book-half"></i> My Borrowed Books</h2>

<div class="table-card">
    <div class="table-responsive">
        <table class="table borrow-table">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $has_books = false;
                while($row = mysqli_fetch_assoc($result)): 
                    $has_books = true;
                ?>
                <tr>
                    <td><span class="book-name"><?php echo htmlspecialchars($row['title']); ?></span></td>
                    <td><span class="date-cell"><?php echo date('M d, Y', strtotime($row['issue_date'])); ?></span></td>
                    <td><span class="date-cell"><?php echo date('M d, Y', strtotime($row['due_date'])); ?></span></td>
                    <td>
                        <?php if($row['status'] == 'issued'): ?>
                            <span class="status-badge badge-issued">Issued</span>
                        <?php elseif($row['status'] == 'late'): ?>
                            <span class="status-badge badge-late">Late</span>
                        <?php else: ?>
                            <span class="status-badge badge-returned">Returned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['status'] == 'issued'): ?>
                            <?php if($row['extend_requested'] == 0): ?>
                                <a href="../borrow/request_extend.php?id=<?php echo $row['id']; ?>" class="action-btn">
                                    <i class="bi bi-hourglass-split"></i> Extend
                                </a>
                            <?php else: ?>
                                <span class="pending-text"><i class="bi bi-hourglass-bottom"></i> Pending</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #7f8c8d; font-size: 12px;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <?php if(!$has_books): ?>
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <h3>No Borrowed Books</h3>
        <p>You haven't borrowed any books yet. Browse our library to get started!</p>
    </div>
    <?php endif; ?>
</div>

<?php include("../includes/layout_footer.php"); ?>