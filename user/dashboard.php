<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

include("../config/database.php");
include("../includes/layout.php");

$user_id = $_SESSION['user_id'];

// Count currently borrowed books
$my_borrows = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE user_id='$user_id' AND status='issued'"))['total'];

// Count overdue books
$overdue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE user_id='$user_id' AND status='late'"))['total'];

// Get reading stats
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE user_id='$user_id' AND status='returned'"));
$total_read = $stats['total'];
?>

<style>
/* USER DASHBOARD STYLES */

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

/* STAT CARDS */

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  display: flex;
  align-items: center;
  gap: 20px;
}

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.stat-icon {
  width: 70px;
  height: 70px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  flex-shrink: 0;
}

.stat-icon.blue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-icon.green {
  background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
  color: white;
}

.stat-icon.orange {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.stat-icon.red {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.stat-content h4 {
  color: #2c3e50;
  font-weight: 700;
  font-size: 32px;
  margin: 0;
  line-height: 1;
}

.stat-content p {
  color: #7f8c8d;
  font-weight: 500;
  font-size: 14px;
  margin: 8px 0 0 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* QUICK ACTIONS SECTION */

.quick-actions-section {
  margin-top: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 35px;
  color: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.quick-actions-section h3 {
  color: white;
  font-weight: 700;
  font-size: 1.5rem;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

.action-btn {
  background: rgba(255, 255, 255, 0.15);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 15px 20px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-align: center;
  cursor: pointer;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  color: white;
}

.action-btn i {
  font-size: 24px;
}

/* FEATURES GRID */

.features-section {
  margin-top: 40px;
}

.section-title {
  color: #2c3e50;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-title i {
  color: #667eea;
  font-size: 1.8rem;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.feature-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  text-decoration: none;
  color: #2c3e50;
  transition: all 0.3s;
  border-left: 4px solid #667eea;
}

.feature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  border-left-color: #764ba2;
  color: #667eea;
}

.feature-card i {
  font-size: 32px;
  color: #667eea;
  margin-bottom: 12px;
  transition: all 0.3s;
  display: block;
}

.feature-card:hover i {
  color: #764ba2;
  transform: scale(1.1);
}

.feature-card-title {
  font-weight: 700;
  font-size: 15px;
  letter-spacing: 0.3px;
}

.feature-card-desc {
  font-size: 12px;
  opacity: 0.7;
  margin: 8px 0 0 0;
}

</style>

<h2><i class="bi bi-house"></i> Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>

<!-- STATS ROW -->
<div class="row" style="margin-bottom: 30px;">

<div class="col-md-6 col-lg-3 mb-3">
  <div class="stat-card">
    <div class="stat-icon blue">
      <i class="bi bi-book-fill"></i>
    </div>
    <div class="stat-content">
      <h4><?php echo $my_borrows; ?></h4>
      <p>Currently Borrowed</p>
    </div>
  </div>
</div>

<div class="col-md-6 col-lg-3 mb-3">
  <div class="stat-card">
    <div class="stat-icon green">
      <i class="bi bi-bookmark-check-fill"></i>
    </div>
    <div class="stat-content">
      <h4><?php echo $total_read; ?></h4>
      <p>Books Read</p>
    </div>
  </div>
</div>

<div class="col-md-6 col-lg-3 mb-3">
  <div class="stat-card">
    <div class="stat-icon orange">
      <i class="bi bi-hourglass-split"></i>
    </div>
    <div class="stat-content">
      <h4><?php echo $overdue; ?></h4>
      <p>Overdue Books</p>
    </div>
  </div>
</div>

<div class="col-md-6 col-lg-3 mb-3">
  <div class="stat-card">
    <div class="stat-icon red">
      <i class="bi bi-star-fill"></i>
    </div>
    <div class="stat-content">
      <h4>★★★★★</h4>
      <p>Your Rating</p>
    </div>
  </div>
</div>

</div>

<!-- QUICK ACTIONS -->

<div class="quick-actions-section">
  <h3><i class="bi bi-lightning-charge-fill"></i> Quick Actions</h3>
  <div class="actions-grid">
    <a href="books.php" class="action-btn">
      <i class="bi bi-search"></i> Browse Books
    </a>
    <a href="my_books.php" class="action-btn">
      <i class="bi bi-book"></i> My Books
    </a>
    <a href="recommend.php" class="action-btn">
      <i class="bi bi-robot"></i> Get AI Suggestions
    </a>
    <a href="trending.php" class="action-btn">
      <i class="bi bi-fire"></i> Trending
    </a>
  </div>
</div>

<!-- FEATURES SECTION -->

<div class="features-section">
  <h3 class="section-title">
    <i class="bi bi-stars"></i> Features
  </h3>
  
  <div class="feature-grid">
    <a href="books.php" class="feature-card">
      <i class="bi bi-collection"></i>
      <span class="feature-card-title">Browse Library</span>
      <p class="feature-card-desc">Explore our entire book collection</p>
    </a>

    <a href="reservations.php" class="feature-card">
      <i class="bi bi-bookmark-heart-fill"></i>
      <span class="feature-card-title">Reservations</span>
      <p class="feature-card-desc">Reserve books in advance</p>
    </a>

    <a href="trending.php" class="feature-card">
      <i class="bi bi-fire"></i>
      <span class="feature-card-title">Trending Books</span>
      <p class="feature-card-desc">Discover popular titles</p>
    </a>

    <a href="reading_history.php" class="feature-card">
      <i class="bi bi-clock-history"></i>
      <span class="feature-card-title">Reading History</span>
      <p class="feature-card-desc">View your reading journey</p>
    </a>

    <a href="fines.php" class="feature-card">
      <i class="bi bi-receipt"></i>
      <span class="feature-card-title">My Fines</span>
      <p class="feature-card-desc">Check pending fines</p>
    </a>

    <a href="notifications.php" class="feature-card">
      <i class="bi bi-bell-fill"></i>
      <span class="feature-card-title">Notifications</span>
      <p class="feature-card-desc">Stay updated on your books</p>
    </a>
  </div>
</div>

<?php include("../includes/layout_footer.php"); ?>