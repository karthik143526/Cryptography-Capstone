<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    if ($file) {
        if (file_exists($file['file_path'])) unlink($file['file_path']);
        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$id]);
        require_once 'includes/audit.php';
        logAction($pdo, $_SESSION['user_id'], 'Delete File', 'Admin', "Deleted file: " . $file['file_name']);
    }
    header("Location: manage_files.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT f.*, u.name as owner FROM files f JOIN users u ON f.user_id = u.id ORDER BY f.uploaded_at DESC");
$files = $stmt->fetchAll();

$totalSize = array_sum(array_column($files, 'file_size'));

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="admin_dashboard.php">📊 Dashboard</a>
        <a href="manage_users.php">👥 Users</a>
        <a href="manage_files.php" class="active">📁 Files</a>
        <a href="manage_permissions.php">🔐 Permissions</a>
        <a href="audit_logs.php">📋 Audit Logs</a>
        <a href="reports.php">📈 Reports</a>
        <hr style="border: none; border-top: 1px solid var(--border-color); margin: 15px 0;">
        <a href="logout.php" style="color: var(--danger);">🚪 Logout</a>
    </div>
    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2>📁 Manage Files</h2>
                <p style="color: var(--text-light); margin-top: 5px;">Monitor and manage all files in the system</p>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success" style="animation: slideInDown 0.5s ease-out;">✅ File deleted successfully</div>
        <?php endif; ?>

        <!-- File Statistics -->
        <div class="card-grid">
            <div class="card stat-info">
                <h3>📁 Total Files</h3>
                <div class="value"><?= count($files) ?></div>
            </div>
            <div class="card stat-success">
                <h3>💾 Total Size</h3>
                <div class="value"><?= number_format($totalSize / 1024 / 1024 / 1024, 2) ?></div>
                <div class="label">GB</div>
            </div>
            <div class="card stat-warning">
                <h3>📊 Avg File Size</h3>
                <div class="value"><?= count($files) > 0 ? number_format($totalSize / count($files) / 1024 / 1024, 2) : 0 ?></div>
                <div class="label">MB</div>
            </div>
        </div>

        <div class="table-responsive" style="margin-top: 30px;">
            <table>
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Owner</th>
                        <th>File Size</th>
                        <th>Uploaded</th>
                        <th>Encryption</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($files)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 30px; color: var(--text-light);">No files in system yet</td></tr>
                    <?php else: ?>
                        <?php foreach($files as $file): ?>
                        <tr>
                            <td>
                                <div class="file-name-cell">
                                    <div class="file-icon default">📄</div>
                                    <div class="file-info">
                                        <span class="name"><?= htmlspecialchars($file['file_name']) ?></span>
                                        <span class="meta">ID: <?= $file['id'] ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600;"><?= htmlspecialchars($file['owner']) ?></span>
                            </td>
                            <td><?= number_format($file['file_size'] / 1024 / 1024, 2) ?> MB</td>
                            <td><?= date('M d, Y H:i', strtotime($file['uploaded_at'])) ?></td>
                            <td>
                                <span style="background: #d1fae5; color: #065f46; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                    🔒 AES-256
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="download.php?id=<?= $file['id'] ?>" class="btn btn-small" style="background: var(--primary-color);">📥 Download</a>
                                    <a href="manage_files.php?delete=<?= $file['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Delete this file permanently?');">🗑️ Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
