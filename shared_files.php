<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT fs.*, f.file_name, f.file_size, u.name as owner_name FROM file_shares fs JOIN files f ON fs.file_id = f.id JOIN users u ON f.user_id = u.id WHERE fs.shared_with = ? ORDER BY fs.shared_at DESC");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="user_dashboard.php">📊 Dashboard</a>
        <a href="upload.php">⬆️ Upload File</a>
        <a href="my_files.php">📁 My Files</a>
        <a href="shared_files.php" class="active">🤝 Shared With Me</a>
        <a href="activity.php">📋 Activity</a>
        <a href="profile.php">👤 Profile</a>
        <hr style="border: none; border-top: 1px solid var(--border-color); margin: 15px 0;">
        <a href="logout.php" style="color: var(--danger);">🚪 Logout</a>
    </div>
    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2>🤝 Shared With Me</h2>
                <p style="color: var(--text-light); margin-top: 5px;"><?= count($files) ?> file<?= count($files) !== 1 ? 's' : '' ?> shared with you</p>
            </div>
        </div>
        
        <?php if(empty($files)): ?>
            <div style="background: var(--white); padding: 60px 40px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color);">
                <div style="font-size: 4rem; margin-bottom: 20px;">🤝</div>
                <h3 style="color: var(--text-dark); margin-bottom: 10px;">No files shared with you yet</h3>
                <p style="color: var(--text-light); margin-bottom: 25px;">When others share files with you, they'll appear here</p>
                <a href="my_files.php" class="btn btn-success" style="padding: 14px 32px; font-size: 1.05rem;">📁 Go to My Files</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Owner</th>
                            <th>Size</th>
                            <th>Permission</th>
                            <th>Shared Date</th>
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
                                        <span class="meta">Shared by <?= htmlspecialchars($file['owner_name']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600;"><?= htmlspecialchars($file['owner_name']) ?></span>
                            </td>
                            <td><?= number_format($file['file_size'] / 1024 / 1024, 2) ?> MB</td>
                            <td>
                                <?php if($file['permission'] === 'Download' || $file['permission'] === 'Access'): ?>
                                    <span style="background: #dcfce7; color: #166534; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        ✓ <?= htmlspecialchars($file['permission']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="background: #fef3c7; color: #b45309; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        👁️ View Only
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($file['shared_at'])) ?></td>
                            <td>
                                <?php if($file['permission'] === 'Download' || $file['permission'] === 'Access'): ?>
                                    <a href="download.php?id=<?= $file['file_id'] ?>" class="btn btn-small" style="background: var(--primary-color);">📥 Download</a>
                                <?php else: ?>
                                    <span style="color: var(--text-light); font-size: 0.9rem;">View only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sharing Statistics -->
            <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="card stat-success">
                    <h3>📊 Total Shared</h3>
                    <div class="value"><?= count($files) ?></div>
                </div>
                <div class="card stat-info">
                    <h3>💾 Total Size</h3>
                    <div class="value"><?= number_format(array_sum(array_column($files, 'file_size')) / 1024 / 1024, 1) ?></div>
                    <div class="label">MB</div>
                </div>
                <div class="card stat-warning">
                    <h3>🤝 Accessible</h3>
                    <div class="value"><?= count(array_filter($files, fn($f) => $f['permission'] !== 'View')) ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
