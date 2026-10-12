<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    require_once 'includes/audit.php';
    logAction($pdo, $_SESSION['user_id'], 'Delete User', 'Admin', "Deleted user ID: $id");
    header("Location: manage_users.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php" class="active">Users</a>
        <a href="manage_files.php">Files</a>
        <a href="manage_permissions.php">Permissions</a>
        <a href="audit_logs.php">Audit Logs</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h2>Manage Users</h2>
        <?php if(isset($_GET['msg'])): ?><div class="alert alert-success">Action completed.</div><?php endif; ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= htmlspecialchars($user['status']) ?></td>
                        <td>
                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                <a href="manage_users.php?delete=<?= $user['id'] ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Delete this user?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
