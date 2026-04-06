<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}

include("../config/database.php");
include("../includes/layout.php");
include("../includes/audit_logging.php");

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get audit logs
$logs_result = getAuditLogs($conn, $limit, $offset);

// Get total count for pagination
$total_result = $conn->query("SELECT COUNT(*) as count FROM audit_logs");
$total_row = mysqli_fetch_assoc($total_result);
$total_logs = $total_row['count'];
$total_pages = ceil($total_logs / $limit);

// Get action statistics
$action_stats = getActionStats($conn);
?>

<h2>🔍 Audit Logs</h2>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">📋 Admin Actions Log</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($log = mysqli_fetch_assoc($logs_result)): ?>
                                <tr>
                                    <td>
                                        <small><?php echo date("M d, Y H:i:s", strtotime($log['action_date'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($log['admin_name'] ?? 'System'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($log['action']); ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($log['details'], 0, 50)); ?></small>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($log['ip_address']); ?></code>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1">First</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php 
                            for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++):
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>">Last</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📊 Action Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach(array_slice($action_stats, 0, 5) as $stat): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($stat['action']); ?></h6>
                                    <h3 class="text-primary"><?php echo $stat['count']; ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/layout_footer.php"); ?>
