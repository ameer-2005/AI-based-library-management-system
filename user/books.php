<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: ../auth/login.php"); exit(); }

include("../config/database.php");
include("../includes/layout.php");
include("../includes/reservations.php");

$user_id = $_SESSION['user_id'];

// Handle reservation
if(isset($_POST['reserve_book'])) {
    $result = createReservation($conn, $user_id, $_POST['book_id']);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';
}

$result = mysqli_query($conn, "
    SELECT books.*, categories.name as cat_name
    FROM books
    JOIN categories ON books.category_id = categories.id
");
?>

<style>
/* BOOK CATALOG STYLES */

body {
  background: #f5f7fa;
}

.content-wrapper {
  background: #f5f7fa;
}

.catalog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 15px;
}

.catalog-header h2 {
  color: #2c3e50;
  font-weight: 700;
  letter-spacing: -0.5px;
  font-size: 2rem;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.catalog-header h2 i {
  color: #667eea;
  font-size: 2.2rem;
}

.catalog-header .btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  padding: 12px 24px;
  font-weight: 600;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.catalog-header .btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.catalog-header .btn i {
  margin-right: 8px;
}

/* BOOK CARDS */

.book-card {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.book-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.book-card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 48px;
  position: relative;
  overflow: hidden;
}

.book-card-header i {
  opacity: 0.8;
}

.book-card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.book-title {
  font-weight: 700;
  color: #2c3e50;
  font-size: 15px;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.book-author {
  color: #7f8c8d;
  font-size: 13px;
  margin: 0 0 12px 0;
}

.book-category {
  display: inline-block;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.book-status {
  flex-grow: 1;
  display: flex;
  align-items: center;
  margin: 0 0 12px 0;
}

.status-available {
  color: #56ab2f;
  font-size: 13px;
  font-weight: 600;
}

.status-unavailable {
  color: #ef4444;
  font-size: 13px;
  font-weight: 600;
}

.status-available i,
.status-unavailable i {
  margin-right: 6px;
}

.book-actions {
  display: flex;
  gap: 10px;
}

.book-btn {
  flex: 1;
  padding: 10px 12px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 12px;
  transition: all 0.3s;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  text-align: center;
}

.book-btn-borrow {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.book-btn-borrow:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.book-btn-reserve {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
}

.book-btn-reserve:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
}

/* ALERT STYLES */

.alert {
  border: none;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.alert-success {
  background: linear-gradient(135deg, rgba(86, 171, 47, 0.1) 0%, rgba(168, 224, 99, 0.1) 100%);
  border-left: 4px solid #56ab2f;
  color: #056C0F;
}

.alert-danger {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
  border-left: 4px solid #ef4444;
  color: #991b1b;
}

</style>

<div class="catalog-header">
    <h2><i class="bi bi-book"></i> Library Catalog</h2>
    <a href="reservations.php" class="btn btn-primary">
        <i class="bi bi-bookmark-plus"></i> My Reservations
    </a>
</div>

<?php if(isset($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <?php while($row = mysqli_fetch_assoc($result)): ?>
    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="book-card">
            <div class="book-card-header">
                <i class="bi bi-book-half"></i>
            </div>
            <div class="book-card-body">
                <h5 class="book-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                <p class="book-author"><?php echo htmlspecialchars($row['author']); ?></p>
                <span class="book-category"><?php echo htmlspecialchars($row['cat_name']); ?></span>
                <div class="book-status">
                    <?php if($row['available'] > 0): ?>
                        <span class="status-available"><i class="bi bi-check-circle-fill"></i> Available: <?php echo $row['available']; ?></span>
                    <?php else: ?>
                        <span class="status-unavailable"><i class="bi bi-x-circle-fill"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>
                <div class="book-actions">
                    <?php if($row['available'] > 0): ?>
                        <a href="../borrow/issue.php?book_id=<?php echo $row['id']; ?>" class="book-btn book-btn-borrow">
                            <i class="bi bi-book"></i> Borrow
                        </a>
                    <?php else: ?>
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="reserve_book" class="book-btn book-btn-reserve" style="width: 100%;">
                                <i class="bi bi-bookmark-plus"></i> Reserve
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include("../includes/layout_footer.php"); ?>