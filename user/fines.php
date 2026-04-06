<?php
session_start();
include("../config/database.php");
if(!isset($_SESSION['user_id'])) header("Location: ../auth/login.php");

$user_id = $_SESSION['user_id'];

include("../includes/layout.php");
?>

<style>
/* FINES PAGE */

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
  margin-bottom: 0.5rem;
  font-size: 2rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

h2 i {
  color: #ef4444;
  font-size: 2.2rem;
}

.text-muted {
  color: #7f8c8d !important;
  margin-bottom: 2rem;
  font-size: 15px;
}

/* FINES SUMMARY CARD */

.fines-summary {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  border-radius: 16px;
  padding: 40px;
  color: white;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
  margin-bottom: 30px;
  text-align: center;
}

.fines-summary h6 {
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 0.9;
  margin-bottom: 15px;
  font-weight: 700;
}

.fines-amount {
  font-size: 48px;
  font-weight: 700;
  margin: 0 0 15px 0;
}

.fines-message {
  font-size: 14px;
  opacity: 0.95;
}

/* FINES TABLE CARD */

.fines-table-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.fines-table {
  margin: 0;
}

.fines-table thead {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.fines-table th {
  color: white;
  font-weight: 700;
  padding: 16px;
  border: none;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  font-size: 12px;
}

.fines-table tbody tr {
  border-bottom: 1px solid #eee;
  transition: all 0.3s;
}

.fines-table tbody tr:hover {
  background: #fff5f5;
  box-shadow: inset 0 2px 8px rgba(239, 68, 68, 0.08);
}

.fines-table tbody td {
  padding: 16px;
  color: #2c3e50;
  font-weight: 500;
}

.book-title-fine {
  color: #667eea;
  font-weight: 700;
}

.date-fine {
  color: #7f8c8d;
  font-size: 13px;
}

.fine-amount {
  color: #ef4444;
  font-weight: 700;
  font-size: 14px;
}

/* EMPTY STATE */

.empty-fines {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.empty-fines i {
  font-size: 64px;
  color: #56ab2f;
  margin-bottom: 20px;
  display: block;
}

.empty-fines h3 {
  color: #2c3e50;
  font-weight: 700;
  margin-bottom: 10px;
}

.empty-fines p {
  color: #7f8c8d;
  margin: 0;
}

</style>

<h2><i class="bi bi-receipt"></i> My Fines</h2>
<p class="text-muted">Late return penalties and outstanding dues</p>

<?php
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(fine_amount) as total FROM borrow_records WHERE user_id='$user_id' AND fine_amount > 0"))['total'];
?>

<div class="fines-summary" style="<?php echo ($total && $total > 0) ? 'opacity: 1;' : 'opacity: 0.9; background: linear-gradient(135deg, #56ab2f 0%, #2d7a1f 100%);'; ?>">
    <h6 style="<?php echo ($total && $total > 0) ? '' : 'color: rgba(255,255,255,0.9);'; ?>">
        <?php echo ($total && $total > 0) ? 'TOTAL OUTSTANDING FINES' : 'NO OUTSTANDING FINES'; ?>
    </h6>
    <div class="fines-amount">
        ₹<?php echo ($total ? number_format($total, 2) : "0.00"); ?>
    </div>
    <div class="fines-message">
        <?php echo ($total && $total > 0) ? 'Please pay at the library counter to clear your dues.' : 'Great! You have no pending fines.'; ?>
    </div>
</div>

<div class="fines-table-card">
    <div class="table-responsive">
        <table class="table fines-table">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Days Late</th>
                    <th>Fine Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT borrow_records.*, books.title 
                                             FROM borrow_records 
                                             JOIN books ON borrow_records.book_id = books.id 
                                             WHERE borrow_records.user_id='$user_id' AND borrow_records.fine_amount > 0
                                             ORDER BY borrow_records.return_date DESC");
                
                $has_fines = false;
                while($row = mysqli_fetch_assoc($res)){
                    $has_fines = true;
                    $days_late = round($row['fine_amount'] / 1);
                    echo "
                    <tr>
                        <td><span class='book-title-fine'>" . htmlspecialchars($row['title']) . "</span></td>
                        <td><span class='date-fine'>" . date('M d, Y', strtotime($row['due_date'])) . "</span></td>
                        <td><span class='date-fine'>" . date('M d, Y', strtotime($row['return_date'])) . "</span></td>
                        <td><span style='color: #ef4444; font-weight: 700;'>$days_late days</span></td>
                        <td><span class='fine-amount'><i class='bi bi-exclamation-circle-fill'></i> ₹" . number_format($row['fine_amount'], 2) . "</span></td>
                    </tr>";
                }
                
                if(!$has_fines) {
                    echo "
                    <tr>
                        <td colspan='5'>
                            <div class='empty-fines' style='margin: 0; background: none; box-shadow: none;'>
                                <i class='bi bi-check-circle-fill'></i>
                                <h3>No Fines</h3>
                                <p>Keep up the good work! No outstanding fines.</p>
                            </div>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>