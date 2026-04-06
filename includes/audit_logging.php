<?php
// Audit logging functions

function logAuditAction($conn, $admin_id, $action, $details = '', $ip_address = '') {
    if(empty($ip_address)){
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    $stmt = $conn->prepare("
        INSERT INTO audit_logs (admin_id, action, details, ip_address) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("isss", $admin_id, $action, $details, $ip_address);
    return $stmt->execute();
}

function getAuditLogs($conn, $limit = 100, $offset = 0) {
    $result = $conn->query("
        SELECT al.*, u.name as admin_name
        FROM audit_logs al
        LEFT JOIN users u ON al.admin_id = u.id
        ORDER BY al.action_date DESC
        LIMIT $limit OFFSET $offset
    ");
    
    return $result;
}

function getActionStats($conn) {
    $result = $conn->query("
        SELECT action, COUNT(*) as count
        FROM audit_logs
        GROUP BY action
        ORDER BY count DESC
    ");
    
    $stats = [];
    while($row = mysqli_fetch_assoc($result)){
        $stats[] = $row;
    }
    return $stats;
}

function getAdminActivityLog($conn, $admin_id, $limit = 50) {
    $result = $conn->query("
        SELECT * FROM audit_logs
        WHERE admin_id = $admin_id
        ORDER BY action_date DESC
        LIMIT $limit
    ");
    
    return $result;
}
?>
