<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$myFilesCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM file_shares WHERE shared_with = ?");
$stmt->execute([$user_id]);
$sharedFilesCount = $stmt->fetchColumn();

// Get total storage
$stmt = $pdo->prepare("SELECT SUM(file_size) FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalStorage = $stmt->fetchColumn() ?? 0;

// Get recent files
$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recentFiles = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT action, created_at FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 8");
$stmt->execute([$user_id]);
$recentActivities = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="user_dashboard.php" class="active">📊 Dashboard</a>
        <a href="upload.php">⬆️ Upload File</a>
        <a href="my_files.php">📁 My Files</a>
        <a href="shared_files.php">🤝 Shared With Me</a>
        <a href="activity.php">📋 Activity</a>
        <a href="profile.php">👤 Profile</a>
        <hr style="border: none; border-top: 1px solid var(--border-color); margin: 15px 0;">
        <a href="logout.php" style="color: var(--danger);">🚪 Logout</a>
    </div>
    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2>Welcome back, 👋 <?= htmlspecialchars($_SESSION['name']) ?></h2>
                <p style="color: var(--text-light); margin-top: 5px;">Manage and secure your files with encrypted storage</p>
            </div>
            <a href="upload.php" class="btn btn-success" style="padding: 12px 30px; font-size: 1rem;">⬆️ Upload File</a>
        </div>
        
        <!-- Stats Cards -->
        <div class="card-grid">
            <div class="card stat-info">
                <h3>📁 Total Files</h3>
                <div class="value"><?= $myFilesCount ?></div>
                <div class="label">Files uploaded by you</div>
            </div>
            <div class="card stat-success">
                <h3>🤝 Shared Files</h3>
                <div class="value"><?= $sharedFilesCount ?></div>
                <div class="label">Files shared with you</div>
            </div>
            <div class="card stat-primary">
                <h3>💾 Storage Used</h3>
                <div class="value"><?= number_format($totalStorage / 1024 / 1024, 1) ?></div>
                <div class="label">MB of 50 GB</div>
            </div>
            <div class="card stat-warning">
                <h3>📊 Account Status</h3>
                <div class="value" style="color: var(--success); font-size: 1.5rem;">✓</div>
                <div class="label">Active & Secure</div>
            </div>
        </div>

        <!-- Recent Files Section -->
        <div style="margin-top: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: var(--text-dark); font-size: 1.3rem; font-weight: 700;">📋 Recent Files</h3>
                <a href="my_files.php" style="color: var(--primary-color); text-decoration: underline;">View all →</a>
            </div>
            
            <?php if(empty($recentFiles)): ?>
                <div style="background: var(--white); padding: 40px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color);">
                    <div style="font-size: 3rem; margin-bottom: 15px;">📁</div>
                    <p style="color: var(--text-light); margin-bottom: 15px;">No files uploaded yet</p>
                    <a href="upload.php" class="btn btn-success">Upload Your First File</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentFiles as $file): ?>
                            <tr>
                                <td>
                                    <div class="file-name-cell">
                                        <div class="file-icon default">📄</div>
                                        <div class="file-info">
                                            <span class="name"><?= htmlspecialchars($file['file_name']) ?></span>
                                            <span class="meta">Encrypted</span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= number_format($file['file_size'] / 1024, 1) ?> KB</td>
                                <td><?= date('M d, Y', strtotime($file['uploaded_at'])) ?></td>
                                <td>
                                    <a href="download.php?id=<?= $file['id'] ?>" class="btn btn-small">Download</a>
                                    <a href="share.php?id=<?= $file['id'] ?>" class="btn btn-small btn-success">Share</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity Section -->
        <div style="margin-top: 40px;">
            <h3 style="color: var(--text-dark); font-size: 1.3rem; font-weight: 700; margin-bottom: 20px;">🕐 Recent Activity</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recentActivities)): ?>
                            <tr><td colspan="2" style="text-align: center; padding: 20px; color: var(--text-light);">No activity yet</td></tr>
                        <?php else: ?>
                            <?php foreach($recentActivities as $activity): ?>
                            <tr>
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
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
