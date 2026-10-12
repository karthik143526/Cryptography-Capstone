<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin();

$stmt = $pdo->query("SELECT p.* FROM permissions p ORDER BY p.role, p.module");
$permissions = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Users</a>
        <a href="manage_files.php">Files</a>
        <a href="manage_permissions.php" class="active">Permissions</a>
        <a href="audit_logs.php">Audit Logs</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h2>Manage Permissions</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Module</th>
                        <th>Permission</th>
                        <th>Access Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($permissions)): ?>
                        <tr><td colspan="4">No permissions defined in DB yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($permissions as $perm): ?>
                        <tr>
                            <td><?= htmlspecialchars($perm['role']) ?></td>
                            <td><?= htmlspecialchars($perm['module']) ?></td>
                            <td><?= htmlspecialchars($perm['permission']) ?></td>
                            <td><?= htmlspecialchars($perm['access_level']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
