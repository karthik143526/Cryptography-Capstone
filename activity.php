<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$activities = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="user_dashboard.php">Dashboard</a>
        <a href="upload.php">Upload File</a>
        <a href="my_files.php">My Files</a>
        <a href="shared_files.php">Shared With Me</a>
        <a href="activity.php" class="active">Activity</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h2>My Activity Log</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($activities)): ?>
                        <tr><td colspan="4">No activity yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($activities as $activity): ?>
                        <tr>
                            <td><?= $activity['created_at'] ?></td>
                            <td><?= htmlspecialchars($activity['action']) ?></td>
                            <td><?= htmlspecialchars($activity['module']) ?></td>
                            <td><?= htmlspecialchars($activity['details']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
