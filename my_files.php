<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle file deletion
if (isset($_GET['delete'])) {
    $file_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);
    $file = $stmt->fetch();
    if ($file) {
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']); // Delete physical file
        }
        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$file_id]);
        
        require_once 'includes/audit.php';
        logAction($pdo, $user_id, 'Delete File', 'Files', "Deleted file: " . $file['file_name']);
        
        header("Location: my_files.php?deleted=1");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="user_dashboard.php">📊 Dashboard</a>
        <a href="upload.php">⬆️ Upload File</a>
        <a href="my_files.php" class="active">📁 My Files</a>
        <a href="shared_files.php">🤝 Shared With Me</a>
        <a href="activity.php">📋 Activity</a>
        <a href="profile.php">👤 Profile</a>
        <hr style="border: none; border-top: 1px solid var(--border-color); margin: 15px 0;">
        <a href="logout.php" style="color: var(--danger);">🚪 Logout</a>
    </div>
    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2>📁 My Files</h2>
                <p style="color: var(--text-light); margin-top: 5px;"><?= count($files) ?> file<?= count($files) !== 1 ? 's' : '' ?> in your storage</p>
            </div>
            <a href="upload.php" class="btn btn-success" style="padding: 12px 30px;">⬆️ Upload New File</a>
        </div>

        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert alert-success" style="animation: slideInDown 0.5s ease-out;">✅ File deleted successfully</div>
        <?php endif; ?>
        
        <?php if(empty($files)): ?>
            <div style="background: var(--white); padding: 60px 40px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color);">
                <div style="font-size: 4rem; margin-bottom: 20px;">📁</div>
                <h3 style="color: var(--text-dark); margin-bottom: 10px;">No files uploaded yet</h3>
                <p style="color: var(--text-light); margin-bottom: 25px;">Start by uploading your first file to secure cloud storage</p>
                <a href="upload.php" class="btn btn-success" style="padding: 14px 32px; font-size: 1.05rem;">⬆️ Upload Your First File</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Size</th>
                            <th>Upload Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($files as $file): ?>
                        <tr>
                            <td>
                                <div class="file-name-cell">
                                    <div class="file-icon default">📄</div>
                                    <div class="file-info">
                                        <span class="name"><?= htmlspecialchars($file['file_name']) ?></span>
                                        <span class="meta">Encrypted with AES-256</span>
                                    </div>
                                </div>
                            </td>
                            <td><?= number_format($file['file_size'] / 1024 / 1024, 2) ?> MB</td>
                            <td><?= date('M d, Y H:i', strtotime($file['uploaded_at'])) ?></td>
                            <td>
                                <span style="background: #d1fae5; color: #065f46; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                    🔒 Secure
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="download.php?id=<?= $file['id'] ?>" class="btn btn-small" style="background: var(--primary-color); white-space: nowrap;">📥 Download</a>
                                    <a href="share.php?id=<?= $file['id'] ?>" class="btn btn-small btn-success" style="white-space: nowrap;">🤝 Share</a>
                                    <a href="my_files.php?delete=<?= $file['id'] ?>" class="btn btn-small btn-danger" style="white-space: nowrap;" onclick="return confirm('Are you sure? This cannot be undone.');">🗑️ Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- File Statistics -->
            <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="card stat-info">
                    <h3>📊 Total Files</h3>
                    <div class="value"><?= count($files) ?></div>
                </div>
                <div class="card stat-success">
                    <h3>💾 Total Size</h3>
                    <div class="value"><?= number_format(array_sum(array_column($files, 'file_size')) / 1024 / 1024, 1) ?></div>
                    <div class="label">MB</div>
                </div>
                <div class="card stat-warning">
                    <h3>🔐 All Encrypted</h3>
                    <div class="value">✓</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
<?php require_once 'includes/footer.php'; ?>
