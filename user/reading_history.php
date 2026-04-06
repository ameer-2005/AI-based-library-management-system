<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user'){
    header("Location: ../auth/login.php"); 
    exit();
}

include("../config/database.php");
include("../includes/layout.php");

$user_id = $_SESSION['user_id'];


/* LAST 3 BOOKS READ */
$recent_books = mysqli_query($conn,"
SELECT b.title,b.author,bc.cover_path
FROM borrow_records br
JOIN books b ON br.book_id=b.id
LEFT JOIN book_covers bc ON bc.book_id=b.id
WHERE br.user_id='$user_id'
AND br.status='returned'
ORDER BY br.return_date DESC
LIMIT 3
");


/* READING HISTORY TABLE */
$history_result = mysqli_query($conn,"
SELECT br.*, b.title, b.author
FROM borrow_records br
JOIN books b ON br.book_id = b.id
WHERE br.user_id='$user_id'
AND br.status='returned'
ORDER BY br.id DESC
");


/* STATS */
$stats_result = mysqli_query($conn,"
SELECT 
COUNT(*) as total_books
FROM borrow_records
WHERE user_id='$user_id'
AND status='returned'
");
?>

<h2>📖 Reading History</h2>

<style>
/* READING HISTORY PAGE */

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

/* STATS CARDS */

.history-stat {
  background: white;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
}

.history-stat:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.stat-number {
  font-size: 36px;
  font-weight: 700;
  color: #667eea;
  margin-bottom: 8px;
}

.stat-label {
  color: #7f8c8d;
  font-weight: 600;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* HISTORY TABLE */

.history-table-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin-top: 30px;
}

.history-table {
  margin: 0;
}

.history-table thead {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.history-table th {
  color: white;
  font-weight: 700;
  padding: 16px;
  border: none;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  font-size: 12px;
}

.history-table tbody tr {
  border-bottom: 1px solid #eee;
  transition: all 0.3s;
}

.history-table tbody tr:hover {
  background: #f8fafc;
  box-shadow: inset 0 2px 8px rgba(102, 126, 234, 0.08);
}

.history-table tbody td {
  padding: 16px;
  color: #2c3e50;
  font-weight: 500;
}

.book-title-hist {
  color: #667eea;
  font-weight: 700;
}

.date-hist {
  color: #7f8c8d;
  font-size: 13px;
}

.duration {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  color: #667eea;
  padding: 6px 10px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 12px;
  display: inline-block;
}

/* EMPTY STATE */

.empty-history {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  margin-top: 30px;
}

.empty-history i {
  font-size: 64px;
  color: #bdc3c7;
  margin-bottom: 20px;
  display: block;
}

.empty-history h3 {
  color: #2c3e50;
  font-weight: 700;
  margin-bottom: 10px;
}

.empty-history p {
  color: #7f8c8d;
  margin: 0;
}

</style>

<div class="row mb-4" style="margin-bottom: 30px;">
  <div class="col-md-6 col-lg-3">
    <div class="history-stat">
      <div class="stat-number">
        <?php echo $stats['total_books'] ?? 0; ?>
      </div>
      <div class="stat-label">Books Read</div>
    </div>
  </div>
  
  <div class="col-md-6 col-lg-3">
    <div class="history-stat">
      <div class="stat-number" style="color: #56ab2f;">
        7.5
      </div>
      <div class="stat-label">Avg Rating</div>
    </div>
  </div>
  
  <div class="col-md-6 col-lg-3">
    <div class="history-stat">
      <div class="stat-number" style="color: #f59e0b;">
        24h
      </div>
      <div class="stat-label">Avg Duration</div>
    </div>
  </div>
  
  <div class="col-md-6 col-lg-3">
    <div class="history-stat">
      <div class="stat-number" style="color: #667eea;">
        Mar 5
      </div>
      <div class="stat-label">Last Return</div>
    </div>
  </div>
</div>

<div class="history-table-card">
    <div class="table-responsive">
        <table class="table history-table">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $has_history = false;
                while($row = mysqli_fetch_assoc($history_result)): 
                    $has_history = true;
                    $issued = new DateTime($row['issue_date']);
                    $returned = new DateTime($row['return_date'] ?? date('Y-m-d'));
                    $interval = $issued->diff($returned);
                    $days = $interval->days;
                ?>
                <tr>
                    <td><span class="book-title-hist"><?php echo htmlspecialchars($row['title']); ?></span></td>
                    <td><span class="date-hist"><?php echo htmlspecialchars($row['author']); ?></span></td>
                    <td><span class="date-hist"><?php echo date('M d, Y', strtotime($row['issue_date'])); ?></span></td>
                    <td><span class="date-hist"><?php echo date('M d, Y', strtotime($row['return_date'] ?? date('Y-m-d'))); ?></span></td>
                    <td><span class="duration"><?php echo $days; ?> days</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if(!$has_history): ?>
    <div class="empty-history">
        <i class="bi bi-book"></i>
        <h3>No Reading History Yet</h3>
        <p>Start borrowing books to build your reading history!</p>
    </div>
    <?php endif; ?>
</div>

<?php include("../includes/layout_footer.php"); ?>

</p>

</div>
</div>
</div>

</div>


<!-- RECENT BOOKS -->
<div class="card shadow mt-4">

<div class="card-header bg-dark text-white">
<h5 class="mb-0">📚 Recently Read Books</h5>
</div>

<div class="card-body">

<div class="row">

<?php if(mysqli_num_rows($recent_books)>0){ ?>

<?php while($book=mysqli_fetch_assoc($recent_books)){ ?>

<div class="col-md-4">

<div class="card shadow-sm">

<?php if(!empty($book['cover_path'])){ ?>
<img src="../<?php echo $book['cover_path']; ?>" 
class="card-img-top" style="height:220px;object-fit:cover;">
<?php } ?>

<div class="card-body text-center">

<h6><?php echo htmlspecialchars($book['title']); ?></h6>
<small class="text-muted">
<?php echo htmlspecialchars($book['author']); ?>
</small>

</div>

</div>

</div>

<?php } ?>

<?php } else { ?>

<div class="alert alert-info">
No recently read books yet.
</div>

<?php } ?>

</div>

</div>

</div>



<!-- FULL HISTORY TABLE -->
<div class="card shadow mt-4">

<div class="card-header bg-dark text-white">
<h5 class="mb-0">📚 Your Reading History</h5>
</div>

<div class="card-body">

<?php if(mysqli_num_rows($history_result)>0){ ?>

<div class="table-responsive">

<table class="table table-hover">

<thead class="table-light">
<tr>
<th>Book</th>
<th>Author</th>
<th>Borrowed</th>
<th>Returned</th>
</tr>
</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($history_result)){ ?>

<tr>

<td><strong><?php echo $row['title']; ?></strong></td>

<td><?php echo $row['author']; ?></td>

<td><?php echo date("M d, Y",strtotime($row['issue_date'])); ?></td>

<td><?php echo date("M d, Y",strtotime($row['return_date'])); ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php } else { ?>

<div class="alert alert-info">
📖 No reading history yet. Borrow and return books to see them here.
</div>

<?php } ?>

</div>

</div>

<?php include("../includes/layout_footer.php"); ?>