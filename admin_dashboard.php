<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin();

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM files");
$totalFiles = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM file_shares");
$totalShares = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(file_size) FROM files");
$totalStorage = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$todayActions = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT u.name, a.action, a.created_at FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 10");
$recentActivities = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="admin_dashboard.php" class="active">📊 Dashboard</a>
        <a href="manage_users.php">👥 Users</a>
        <a href="manage_files.php">📁 Files</a>
        <a href="manage_permissions.php">🔐 Permissions</a>
        <a href="audit_logs.php">📋 Audit Logs</a>
        <a href="reports.php">📈 Reports</a>
        <hr style="border: none; border-top: 1px solid var(--border-color); margin: 15px 0;">
        <a href="logout.php" style="color: var(--danger);">🚪 Logout</a>
    </div>
    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2>Admin Control Panel</h2>
                <p style="color: var(--text-light); margin-top: 5px;">Monitor and manage your secure file storage system</p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.9rem; color: var(--text-light);">Last updated</div>
                <div style="font-weight: 600; color: var(--text-dark);"><?= date('M d, Y H:i') ?></div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="card-grid">
            <div class="card stat-info">
                <h3>👥 Total Users</h3>
                <div class="value"><?= $totalUsers ?></div>
                <div class="label">Active accounts</div>
            </div>
            <div class="card stat-success">
                <h3>📁 Total Files</h3>
                <div class="value"><?= $totalFiles ?></div>
                <div class="label">Files in system</div>
            </div>
            <div class="card stat-warning">
                <h3>🤝 File Shares</h3>
                <div class="value"><?= $totalShares ?></div>
                <div class="label">Active shares</div>
            </div>
            <div class="card stat-primary">
                <h3>💾 Storage Used</h3>
                <div class="value"><?= number_format($totalStorage / 1024 / 1024 / 1024, 2) ?></div>
                <div class="label">GB total</div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card-grid" style="margin-top: 30px;">
            <div class="card" style="border-left: 4px solid var(--success);">
                <h3>📊 Today's Activity</h3>
                <div class="value" style="color: var(--success);"><?= $todayActions ?></div>
                <div class="label">Actions logged</div>
            </div>
            <div class="card" style="border-left: 4px solid var(--primary-color);">
                <h3>🔒 System Status</h3>
                <div class="value" style="color: var(--primary-color); font-size: 1.8rem;">✓</div>
                <div class="label">All systems operational</div>
            </div>
        </div>

        <!-- System Activity -->
        <div style="margin-top: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: var(--text-dark); font-size: 1.3rem; font-weight: 700;">🕐 System Activity</h3>
                <a href="audit_logs.php" style="color: var(--primary-color); text-decoration: underline;">View all logs →</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recentActivities)): ?>
                            <tr><td colspan="3" style="text-align: center; padding: 20px; color: var(--text-light);">No activity yet</td></tr>
                        <?php else: ?>
                            <?php foreach($recentActivities as $activity): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary-color), var(--info)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem;">
                                            <?= strtoupper(substr($activity['name'] ?? 'S', 0, 1)) ?>
                                        </div>
                                        <span style="font-weight: 500;"><?= htmlspecialchars($activity['name'] ?? 'System') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span style="background: #e3f2fd; color: #1976d2; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        <?= htmlspecialchars($activity['action']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($activity['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="card" style="border: 2px solid var(--success); cursor: pointer; transition: all 0.3s ease;">
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 10px;">👥</div>
                    <h4 style="margin-bottom: 5px;">Manage Users</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 15px;">Add, edit or remove users</p>
                    <a href="manage_users.php" class="btn btn-small btn-success" style="width: 100%;">Go to Users</a>
                </div>
            </div>
            <div class="card" style="border: 2px solid var(--info); cursor: pointer; transition: all 0.3s ease;">
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 10px;">📁</div>
                    <h4 style="margin-bottom: 5px;">Manage Files</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 15px;">View and manage all files</p>
                    <a href="manage_files.php" class="btn btn-small" style="width: 100%;">Go to Files</a>
                </div>
            </div>
            <div class="card" style="border: 2px solid var(--warning); cursor: pointer; transition: all 0.3s ease;">
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 10px;">📋</div>
                    <h4 style="margin-bottom: 5px;">Audit Logs</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 15px;">View all system activity logs</p>
                    <a href="audit_logs.php" class="btn btn-small btn-warning" style="width: 100%;">View Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
