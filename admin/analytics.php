<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){ header("Location: ../auth/login.php"); exit(); }

include("../config/database.php");
include("../includes/layout.php");

// Add Chart.js library
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<?php

// Advanced Analytics Data with prepared statements for security

// 1. Top Categories
$cat_data = mysqli_query($conn, "
    SELECT categories.name, COUNT(borrow_records.id) as total
    FROM borrow_records
    JOIN books ON borrow_records.book_id = books.id
    JOIN categories ON books.category_id = categories.id
    GROUP BY categories.id, categories.name
    ORDER BY total DESC LIMIT 10
");

$cat_labels = [];
$cat_values = [];
if($cat_data) {
    while($row = mysqli_fetch_assoc($cat_data)){
        $cat_labels[] = $row['name'];
        $cat_values[] = $row['total'];
    }
}

// 2. Monthly Trend (Last 12 months)
$month_data = mysqli_query($conn, "
    SELECT DATE_FORMAT(issue_date, '%Y-%m') as month, COUNT(*) as total
    FROM borrow_records
    WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(issue_date, '%Y-%m')
    ORDER BY month ASC
");

$m_labels = [];
$m_values = [];
if($month_data) {
    while($row = mysqli_fetch_assoc($month_data)){
        $m_labels[] = date('M Y', strtotime($row['month'] . '-01'));
        $m_values[] = $row['total'];
    }
}

// 3. Top Books
$book_data = mysqli_query($conn, "
    SELECT books.title, COUNT(borrow_records.id) as total
    FROM borrow_records
    JOIN books ON borrow_records.book_id = books.id
    GROUP BY books.id, books.title
    ORDER BY total DESC LIMIT 10
");

$book_labels = [];
$book_values = [];
if($book_data) {
    while($row = mysqli_fetch_assoc($book_data)){
        $book_labels[] = substr($row['title'], 0, 30) . (strlen($row['title']) > 30 ? '...' : '');
        $book_values[] = $row['total'];
    }
}

// 4. User Activity
$user_data = mysqli_query($conn, "
    SELECT users.name, COUNT(borrow_records.id) as total
    FROM borrow_records
    JOIN users ON borrow_records.user_id = users.id
    GROUP BY users.id, users.name
    ORDER BY total DESC LIMIT 10
");

$user_labels = [];
$user_values = [];
if($user_data) {
    while($row = mysqli_fetch_assoc($user_data)){
        $user_labels[] = $row['name'];
        $user_values[] = $row['total'];
    }
}

// 5. Status Distribution
$status_data = mysqli_query($conn, "
    SELECT status, COUNT(*) as total
    FROM borrow_records
    GROUP BY status
");

$status_labels = [];
$status_values = [];
$status_colors = [];
if($status_data) {
    while($row = mysqli_fetch_assoc($status_data)){
        $status_labels[] = ucfirst($row['status']);
        $status_values[] = $row['total'];
        switch($row['status']){
            case 'issued': $status_colors[] = '#007bff'; break;
            case 'returned': $status_colors[] = '#28a745'; break;
            case 'late': $status_colors[] = '#dc3545'; break;
            default: $status_colors[] = '#6c757d';
        }
    }
}

// 6. Revenue Analytics
$revenue_data = mysqli_query($conn, "
    SELECT DATE_FORMAT(return_date, '%Y-%m') as month, COALESCE(SUM(fine_amount), 0) as total
    FROM borrow_records
    WHERE return_date IS NOT NULL AND fine_amount > 0
    AND return_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(return_date, '%Y-%m')
    ORDER BY month ASC
");

$rev_labels = [];
$rev_values = [];
if($revenue_data) {
    while($row = mysqli_fetch_assoc($revenue_data)){
        $rev_labels[] = date('M Y', strtotime($row['month'] . '-01'));
        $rev_values[] = (float)$row['total'];
    }
}

// Key Metrics with error handling
$total_books_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM books");
$total_books = $total_books_result ? mysqli_fetch_assoc($total_books_result)['total'] : 0;

$total_users_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'");
$total_users = $total_users_result ? mysqli_fetch_assoc($total_users_result)['total'] : 0;

$active_borrows_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE status='issued'");
$active_borrows = $active_borrows_result ? mysqli_fetch_assoc($active_borrows_result)['total'] : 0;

$total_fines_result = mysqli_query($conn, "SELECT COALESCE(SUM(fine_amount), 0) as total FROM borrow_records WHERE fine_amount > 0");
$total_fines = $total_fines_result ? mysqli_fetch_assoc($total_fines_result)['total'] : 0;

$avg_rating_result = mysqli_query($conn, "SELECT AVG(rating) as avg FROM reviews WHERE rating > 0");
$avg_rating = $avg_rating_result ? (mysqli_fetch_assoc($avg_rating_result)['avg'] ?? 0) : 0;

// Additional metrics
$late_returns_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow_records WHERE status='late'");
$late_returns = $late_returns_result ? mysqli_fetch_assoc($late_returns_result)['total'] : 0;

$available_books_result = mysqli_query($conn, "SELECT SUM(available) as total FROM books");
$available_books = $available_books_result ? mysqli_fetch_assoc($available_books_result)['total'] : 0;

$new_users_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$new_users = $new_users_result ? mysqli_fetch_assoc($new_users_result)['total'] : 0;

// Additional Analytics
// 7. Borrow by Day of Week
$day_of_week_data = mysqli_query($conn, "
    SELECT DAYNAME(issue_date) as day, COUNT(*) as total
    FROM borrow_records
    WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
    GROUP BY DAYNAME(issue_date), DAYOFWEEK(issue_date)
    ORDER BY DAYOFWEEK(issue_date)
");

$dow_labels = [];
$dow_values = [];
if($day_of_week_data) {
    while($row = mysqli_fetch_assoc($day_of_week_data)){
        $dow_labels[] = $row['day'];
        $dow_values[] = $row['total'];
    }
}

// 8. Return Time Analysis (Average days to return)
$return_time_data = mysqli_query($conn, "
    SELECT 
        ROUND(AVG(DATEDIFF(return_date, issue_date)), 1) as avg_days,
        COUNT(*) as total_returned
    FROM borrow_records
    WHERE return_date IS NOT NULL
");

$return_time_info = $return_time_data ? mysqli_fetch_assoc($return_time_data) : ['avg_days' => 0, 'total_returned' => 0];

// 9. Author Popularity
$author_data = mysqli_query($conn, "
    SELECT books.author, COUNT(borrow_records.id) as total
    FROM borrow_records
    JOIN books ON borrow_records.book_id = books.id
    WHERE books.author IS NOT NULL
    GROUP BY books.author
    ORDER BY total DESC LIMIT 8
");

$author_labels = [];
$author_values = [];
if($author_data) {
    while($row = mysqli_fetch_assoc($author_data)){
        $author_labels[] = $row['author'];
        $author_values[] = $row['total'];
    }
}

// 10. Fine Distribution
$fine_data = mysqli_query($conn, "
    SELECT 
        CASE 
            WHEN fine_amount = 0 THEN 'No Fine'
            WHEN fine_amount BETWEEN 1 AND 50 THEN '₹1-50'
            WHEN fine_amount BETWEEN 51 AND 100 THEN '₹51-100'
            WHEN fine_amount BETWEEN 101 AND 500 THEN '₹101-500'
            ELSE '₹500+'
        END as fine_range,
        COUNT(*) as total
    FROM borrow_records
    WHERE fine_amount >= 0
    GROUP BY fine_range
    ORDER BY fine_amount
");

$fine_labels = [];
$fine_values = [];
if($fine_data) {
    while($row = mysqli_fetch_assoc($fine_data)){
        $fine_labels[] = $row['fine_range'];
        $fine_values[] = $row['total'];
    }
}

// 11. Borrow Requests & Extensions
$extend_requests = mysqli_query($conn, "
    SELECT COUNT(*) as total FROM borrow_records WHERE extend_requested = 1
");
$extend_count = $extend_requests ? mysqli_fetch_assoc($extend_requests)['total'] : 0;

// 12. User Engagement Metrics
$user_engagement = mysqli_query($conn, "
    SELECT 
        COUNT(DISTINCT users.id) as active_users,
        COUNT(DISTINCT borrow_records.id) as total_borrows,
        ROUND(COUNT(DISTINCT borrow_records.id) / COUNT(DISTINCT users.id), 2) as avg_borrows_per_user
    FROM users
    LEFT JOIN borrow_records ON users.id = borrow_records.user_id
    WHERE users.role = 'user'
");

$engagement_info = $user_engagement ? mysqli_fetch_assoc($user_engagement) : ['active_users' => 0, 'total_borrows' => 0, 'avg_borrows_per_user' => 0];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-graph-up"></i> Advanced Analytics Dashboard</h2>
    <div>
        <button class="btn btn-outline-primary btn-sm" onclick="refreshCharts()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="btn btn-outline-success btn-sm" onclick="exportData()">
            <i class="bi bi-download"></i> Export
        </button>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row mb-3">
    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-primary shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-0">Total Books</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_books); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-book text-primary" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-success shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-0">Active Users</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_users); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people text-success" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-info shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-0">Active Borrows</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($active_borrows); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-arrow-left-right text-info" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-warning shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-0">Total Revenue</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">₹<?php echo number_format($total_fines, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-rupee text-warning" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-danger shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-0">Late Returns</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($late_returns); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-secondary shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-0">Available Books</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($available_books); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-stack text-secondary" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-info shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-0">New Users (30d)</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($new_users); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-plus text-info" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-success shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-0">Avg Rating</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($avg_rating, 1); ?>⭐</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-star text-success" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Metrics Row -->
<div class="row mb-3">
    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-success shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-0">Avg Days to Return</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($return_time_info['avg_days'], 1); ?> days</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-hourglass text-success" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-primary shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-0">Extension Requests</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($extend_count); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-arrow-repeat text-primary" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-warning shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-0">Avg Borrows/User</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($engagement_info['avg_borrows_per_user'], 1); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up text-warning" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-info shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-0">Total Returned</div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo number_format($return_time_info['total_returned']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle text-info" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-2">
        <div class="card border-left-secondary shadow py-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-0">Engagement Rate</div>
                      <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo round(($active_borrows / $total_users) * 100, 1); ?>%</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-activity text-secondary" style="font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row mb-3">
    <div class="col-lg-6 mb-3">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-primary"><i class="bi bi-bar-chart"></i> Most Popular Categories</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="catChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-success"><i class="bi bi-graph-up"></i> Monthly Borrowing Trend</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="monthChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row mb-3">
    <div class="col-lg-6 mb-3">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-info"><i class="bi bi-book"></i> Top Borrowed Books</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="bookChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-warning"><i class="bi bi-people"></i> Most Active Users</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="userChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 3 - New Advanced Analytics -->
<div class="row mb-3">
    <div class="col-lg-6 mb-3">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-danger"><i class="bi bi-pie-chart"></i> Borrow Status Distribution</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="statusChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-secondary"><i class="bi bi-currency-rupee"></i> Monthly Revenue</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="revenueChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 4 - Additional Analysis -->
<div class="row mb-3">
    <div class="col-lg-6 mb-3">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-info"><i class="bi bi-calendar"></i> Borrows by Day of Week</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="dowChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-success"><i class="bi bi-person-badge"></i> Top Authors</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="authorChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 5 - Fine Analysis -->
<div class="row mb-3">
    <div class="col-lg-6 mb-3">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-warning"><i class="bi bi-exclamation-circle"></i> Fine Distribution</h6>
            </div>
            <div class="card-body p-2">
                <canvas id="fineChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-primary"><i class="bi bi-clock-history"></i> Recent Borrow Activities</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>User</th>
                                <th>Book</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Fine (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_activity = mysqli_query($conn, "
                                SELECT 
                                    users.name as user_name,
                                    books.title,
                                    borrow_records.issue_date,
                                    borrow_records.due_date,
                                    borrow_records.status,
                                    COALESCE(borrow_records.fine_amount, 0) as fine_amount
                                FROM borrow_records
                                JOIN users ON borrow_records.user_id = users.id
                                JOIN books ON borrow_records.book_id = books.id
                                ORDER BY borrow_records.issue_date DESC
                                LIMIT 20
                            ");
                            
                            if($recent_activity) {
                                while($row = mysqli_fetch_assoc($recent_activity)) {
                                    $status_badge = '';
                                    switch($row['status']) {
                                        case 'issued':
                                            $status_badge = '<span class="badge bg-primary">Issued</span>';
                                            break;
                                        case 'returned':
                                            $status_badge = '<span class="badge bg-success">Returned</span>';
                                            break;
                                        case 'late':
                                            $status_badge = '<span class="badge bg-danger">Late</span>';
                                            break;
                                        default:
                                            $status_badge = '<span class="badge bg-secondary">Pending</span>';
                                    }
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['user_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars(substr($row['title'], 0, 40)) . "</td>";
                                    echo "<td>" . date('d M Y', strtotime($row['issue_date'])) . "</td>";
                                    echo "<td>" . date('d M Y', strtotime($row['due_date'])) . "</td>";
                                    echo "<td>" . $status_badge . "</td>";
                                    echo "<td>₹" . number_format($row['fine_amount'], 2) . "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performing Users Table -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-success"><i class="bi bi-trophy"></i> Top Performing Users</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Books</th>
                                <th>Avg Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $top_users = mysqli_query($conn, "
                                SELECT 
                                    users.name,
                                    COUNT(DISTINCT borrow_records.id) as book_count,
                                    COALESCE(AVG(reviews.rating), 0) as avg_rating
                                FROM users
                                LEFT JOIN borrow_records ON users.id = borrow_records.user_id
                                LEFT JOIN reviews ON users.id = reviews.user_id
                                WHERE users.role = 'user'
                                GROUP BY users.id, users.name
                                ORDER BY book_count DESC, avg_rating DESC
                                LIMIT 10
                            ");
                            
                            if($top_users) {
                                $rank = 1;
                                while($row = mysqli_fetch_assoc($top_users)) {
                                    echo "<tr>";
                                    echo "<td><strong>" . $rank++ . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . $row['book_count'] . "</td>";
                                    echo "<td>" . number_format($row['avg_rating'], 1) . "⭐</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-sm text-warning"><i class="bi bi-exclamation-circle"></i> Books with Low Stock</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Book Title</th>
                                <th>Available</th>
                                <th>Total</th>
                                <th>% Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $low_stock = mysqli_query($conn, "
                                SELECT 
                                    title,
                                    available,
                                    available + (SELECT COUNT(*) FROM borrow_records WHERE book_id = books.id AND status IN ('issued', 'late')) as total_copies,
                                    ROUND((available / (available + (SELECT COUNT(*) FROM borrow_records WHERE book_id = books.id AND status IN ('issued', 'late')))) * 100, 1) as availability_percent
                                FROM books
                                WHERE available < 3
                                ORDER BY available ASC
                                LIMIT 10
                            ");
                            
                            if($low_stock) {
                                while($row = mysqli_fetch_assoc($low_stock)) {
                                    $percent = $row['availability_percent'] ?? 0;
                                    $color = $percent < 30 ? 'danger' : ($percent < 60 ? 'warning' : 'success');
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars(substr($row['title'], 0, 30)) . "</td>";
                                    echo "<td><strong>" . $row['available'] . "</strong></td>";
                                    echo "<td>" . $row['total_copies'] . "</td>";
                                    echo "<td><span class='badge bg-" . $color . "'>" . $percent . "%</span></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Category Bar Chart
new Chart(document.getElementById('catChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($cat_labels); ?>,
        datasets: [{
            label: 'Borrows',
            data: <?php echo json_encode($cat_values); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Monthly Line Chart
new Chart(document.getElementById('monthChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($m_labels); ?>,
        datasets: [{
            label: 'Borrows',
            data: <?php echo json_encode($m_values); ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Top Books Horizontal Bar Chart
new Chart(document.getElementById('bookChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($book_labels); ?>,
        datasets: [{
            label: 'Borrows',
            data: <?php echo json_encode($book_values); ?>,
            backgroundColor: 'rgba(23, 162, 184, 0.8)',
            borderColor: 'rgba(23, 162, 184, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true }
        }
    }
});

// User Activity Chart
new Chart(document.getElementById('userChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($user_labels); ?>,
        datasets: [{
            label: 'Borrows',
            data: <?php echo json_encode($user_values); ?>,
            backgroundColor: 'rgba(255, 193, 7, 0.8)',
            borderColor: 'rgba(255, 193, 7, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Status Distribution Pie Chart
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($status_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($status_values); ?>,
            backgroundColor: <?php echo json_encode($status_colors); ?>,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($rev_labels); ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?php echo json_encode($rev_values); ?>,
            borderColor: '#6c757d',
            backgroundColor: 'rgba(108, 117, 125, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value;
                    }
                }
            }
        }
    }
});

// Day of Week Analysis Chart
new Chart(document.getElementById('dowChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dow_labels); ?>,
        datasets: [{
            label: 'Borrows',
            data: <?php echo json_encode($dow_values); ?>,
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(199, 199, 199, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Top Authors Chart
new Chart(document.getElementById('authorChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($author_labels); ?>,
        datasets: [{
            label: 'Books Borrowed',
            data: <?php echo json_encode($author_values); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true }
        }
    }
});

// Fine Distribution Chart
new Chart(document.getElementById('fineChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($fine_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($fine_values); ?>,
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(108, 117, 125, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Refresh function
function refreshCharts() {
    location.reload();
}

// Export function (placeholder)
function exportData() {
    alert('Export functionality will be implemented soon!');
}
</script>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-secondary { border-left: 0.25rem solid #858796 !important; }
</style>

<?php include("../includes/layout_footer.php"); ?>