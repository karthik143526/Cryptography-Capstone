<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin();

$stmt = $pdo->query("SELECT a.*, u.name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC");
$logs = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Users</a>
        <a href="manage_files.php">Files</a>
        <a href="manage_permissions.php">Permissions</a>
        <a href="audit_logs.php" class="active">Audit Logs</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h2>Audit Logs</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td><?= $log['created_at'] ?></td>
                        <td><?= htmlspecialchars($log['name'] ?? 'System') ?></td>
                        <td><?= htmlspecialchars($log['module']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
