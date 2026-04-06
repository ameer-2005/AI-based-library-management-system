<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
header("Location: ../auth/login.php");
exit();
}

include("../config/database.php");
include("../includes/layout.php");

/* Count Total Books Currently Borrowed (All Users) */
$result = mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM borrow_records 
WHERE status='issued'
");
$data = mysqli_fetch_assoc($result);
$total_borrowed = $data['total'];

/* Count Total Books in System */
$result = mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM books
");
$data = mysqli_fetch_assoc($result);
$total_books = $data['total'];

/* Count Total Available Books */
$result = mysqli_query($conn,"
SELECT SUM(available) as total 
FROM books
");
$data = mysqli_fetch_assoc($result);
$available_books = $data['total'] ?? 0;

/* Count Total Active Users */
$result = mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM users 
WHERE role='user'
");
$data = mysqli_fetch_assoc($result);
$total_users = $data['total'];

/* Count Overdue Books */
$result = mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM borrow_records 
WHERE status='late'
");
$data = mysqli_fetch_assoc($result);
$overdue_books = $data['total'];
?>

<style>

/* IMPROVED DASHBOARD STYLING */

body {
  background: #f5f7fa;
}

.content-wrapper {
  background: #f5f7fa;
}

/* DASHBOARD TITLE */

h2 {
  color: #2c3e50;
  font-weight: 700;
  letter-spacing: -0.5px;
  margin-bottom: 2rem;
  font-size: 2rem;
}

/* DASHBOARD CARDS */

.dashboard-card {
  border-radius: 16px;
  color: white;
  padding: 30px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  border: none;
  position: relative;
  overflow: hidden;
}

.dashboard-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.1);
  opacity: 0;
  transition: opacity 0.3s;
}

.dashboard-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.dashboard-card:hover::before {
  opacity: 1;
}

.dashboard-card h1 {
  font-size: 48px;
  font-weight: 700;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.dashboard-card p {
  font-size: 14px;
  margin: 12px 0 0 0;
  opacity: 0.95;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  font-weight: 600;
}

/* BLUE CARD */

.card-blue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* GREEN CARD */

.card-green {
  background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
}

/* STATS ROW */

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

/* QUICK ACTIONS SECTION */

.quick-actions-card {
  background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
}

.quick-actions-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.quick-actions-card h3 {
  color: white;
  margin-bottom: 25px;
  font-size: 1.3rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

/* TOGGLE BUTTONS */

.toggle-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 12px 20px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-align: center;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.toggle-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.2);
  transition: left 0.3s ease;
  z-index: -1;
}

.toggle-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.toggle-btn:hover::before {
  left: 0;
}

.toggle-btn i {
  font-size: 18px;
}

/* MANAGEMENT LINKS SECTION */

.management-section {
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

.management-links-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}

.link-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  text-decoration: none;
  color: #2c3e50;
  transition: all 0.3s;
  border-left: 4px solid #667eea;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.link-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  border-left-color: #764ba2;
  color: #667eea;
}

.link-card i {
  font-size: 28px;
  color: #667eea;
  transition: all 0.3s;
}

.link-card:hover i {
  color: #764ba2;
  transform: scale(1.1);
}

.link-card-title {
  font-weight: 700;
  font-size: 15px;
  letter-spacing: 0.3px;
}

.link-card-desc {
  font-size: 12px;
  opacity: 0.7;
  margin: 0;
}

</style>

<h2><i class="bi bi-speedometer2" style="margin-right: 10px; color: #667eea;"></i>Admin Dashboard</h2>

<!-- STATS CARDS ROW -->

<div class="stats-row">

<!-- TOTAL BOOKS CARD -->
<div class="dashboard-card card-blue text-center">
<h1><?php echo $total_books; ?></h1>
<p>Total Books</p>
</div>



<!-- BORROWED BOOKS CARD -->
<div class="dashboard-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="text-center">
<h1><?php echo $total_borrowed; ?></h1>
<p>Books Borrowed</p>
</div>

<!-- OVERDUE BOOKS CARD -->
<div class="dashboard-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);" class="text-center">
<h1><?php echo $overdue_books; ?></h1>
<p>Overdue Books</p>
</div>

</div>

<!-- SECOND ROW -->

<div class="row" style="margin-bottom: 30px;">

<!-- ACTIVE USERS CARD -->
<div class="col-lg-5">
<div class="dashboard-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);" class="text-center">
<h1><?php echo $total_users; ?></h1>
<p>Active Users</p>
</div>
</div>

<!-- QUICK ACTIONS CARD -->
<div class="col-lg-7">
<div class="quick-actions-card text-center">
<h3><i class="bi bi-lightning-charge-fill"></i> Quick Actions</h3>
<div class="quick-actions-grid">
<a href="../admin/manage_books.php" class="toggle-btn">
<i class="bi bi-journal-text"></i> Manage Books
</a>
<a href="../admin/borrow_manage.php" class="toggle-btn">
<i class="bi bi-arrow-left-right"></i> Borrowing
</a>
<a href="../admin/manage_users.php" class="toggle-btn">
<i class="bi bi-people"></i> Users
</a>
<a href="../admin/analytics.php" class="toggle-btn">
<i class="bi bi-graph-up"></i> Analytics
</a>
</div>
</div>
</div>

</div>

<!-- MANAGEMENT LINKS SECTION -->

<div class="management-section">

<h3 class="section-title">
<i class="bi bi-sliders2"></i> Management Tools
</h3>

<div class="management-links-grid">

<a href="../admin/manage_books.php" class="link-card">
<i class="bi bi-journal-text"></i>
<span class="link-card-title">Manage All Books</span>
<p class="link-card-desc">Add, edit, and manage books in your library</p>
</a>

<a href="../admin/borrow_manage.php" class="link-card">
<i class="bi bi-arrow-left-right"></i>
<span class="link-card-title">Borrow Control</span>
<p class="link-card-desc">Manage book borrowing and returns</p>
</a>

<a href="../admin/manage_users.php" class="link-card">
<i class="bi bi-people"></i>
<span class="link-card-title">Manage Users</span>
<p class="link-card-desc">View and manage all users</p>
</a>

<a href="../admin/manage_categories.php" class="link-card">
<i class="bi bi-folder"></i>
<span class="link-card-title">Manage Categories</span>
<p class="link-card-desc">Organize your book categories</p>
</a>

<a href="../admin/manage_covers.php" class="link-card">
<i class="bi bi-image"></i>
<span class="link-card-title">Book Covers</span>
<p class="link-card-desc">Manage book cover images</p>
</a>

<a href="../admin/analytics.php" class="link-card">
<i class="bi bi-graph-up-arrow"></i>
<span class="link-card-title">Analytics</span>
<p class="link-card-desc">View system statistics and reports</p>
</a>

<a href="../admin/bulk_import.php" class="link-card">
<i class="bi bi-upload"></i>
<span class="link-card-title">Bulk Import</span>
<p class="link-card-desc">Import multiple books at once</p>
</a>

<a href="../admin/audit_logs.php" class="link-card">
<i class="bi bi-shield-check"></i>
<span class="link-card-title">Audit Logs</span>
<p class="link-card-desc">View system activity and logs</p>
</a>

</div>

</div>

<?php include("../includes/layout_footer.php"); ?>