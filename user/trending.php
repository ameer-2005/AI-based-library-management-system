<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../auth/login.php"); exit(); }

include("../config/database.php");
include("../includes/layout.php");

// AI Logic: Most borrowed books in the last 30 days
$query = "
    SELECT books.id, books.title, books.author, COUNT(borrow_records.book_id) as borrow_count 
    FROM borrow_records 
    JOIN books ON borrow_records.book_id = books.id 
    WHERE borrow_records.issue_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY borrow_records.book_id 
    ORDER BY borrow_count DESC 
    LIMIT 10
";

$result = mysqli_query($conn, $query);
?>

<style>
/* TRENDING PAGE */

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
  color: #f59e0b;
  font-size: 2.2rem;
}

.text-muted {
  color: #7f8c8d !important;
  margin-bottom: 2rem;
  font-size: 15px;
}

/* TRENDING TABLE CARD */

.trending-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.trending-table {
  margin: 0;
}

.trending-table thead {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.trending-table th {
  color: white;
  font-weight: 700;
  padding: 16px;
  border: none;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  font-size: 12px;
}

.trending-table tbody tr {
  border-bottom: 1px solid #eee;
  transition: all 0.3s;
}

.trending-table tbody tr:hover {
  background: #fffbf0;
  box-shadow: inset 0 2px 8px rgba(245, 158, 11, 0.08);
}

.trending-table tbody td {
  padding: 16px;
  color: #2c3e50;
  font-weight: 500;
  vertical-align: middle;
}

.rank-number {
  font-size: 18px;
  font-weight: 700;
  color: #f59e0b;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
  border-radius: 8px;
}

.rank-number.top {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.book-name {
  color: #667eea;
  font-weight: 700;
}

.author-name {
  color: #7f8c8d;
  font-size: 13px;
}

.borrow-badge {
  display: inline-block;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.2) 100%);
  color: #d97706;
  padding: 6px 12px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 12px;
  text-align: center;
  min-width: 50px;
}

/* EMPTY STATE */

.empty-trending {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.empty-trending i {
  font-size: 64px;
  color: #bdc3c7;
  margin-bottom: 20px;
  display: block;
}

.empty-trending h3 {
  color: #2c3e50;
  font-weight: 700;
  margin-bottom: 10px;
}

.empty-trending p {
  color: #7f8c8d;
  margin: 0;
}

</style>

<h2><i class="bi bi-fire"></i> Trending Books</h2>
<p class="text-muted">Most borrowed books in the last 30 days</p>

<div class="trending-card">
    <div class="table-responsive">
        <table class="table trending-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Rank</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th style="width: 120px;">Popular</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                $has_data = false;
                while($row = mysqli_fetch_assoc($result)): 
                    $has_data = true;
                ?>
                <tr>
                    <td>
                        <div class="rank-number <?php echo ($rank <= 3) ? 'top' : ''; ?>">
                            <?php echo $rank++; ?>
                        </div>
                    </td>
                    <td><span class="book-name"><?php echo htmlspecialchars($row['title']); ?></span></td>
                    <td><span class="author-name"><?php echo htmlspecialchars($row['author']); ?></span></td>
                    <td><span class="borrow-badge"><i class="bi bi-fire"></i> <?php echo $row['borrow_count']; ?> borrowed</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if(!$has_data): ?>
    <div class="empty-trending">
        <i class="bi bi-bar-chart-line"></i>
        <h3>No Trending Books Yet</h3>
        <p>Not enough borrowing data to show trending books. Come back later!</p>
    </div>
    <?php endif; ?>
</div>

<?php include("../includes/layout_footer.php"); ?>