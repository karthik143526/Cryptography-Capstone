<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$name, $email, $user_id])) {
        $_SESSION['name'] = $name;
        $success = "Profile updated successfully.";
        require_once 'includes/audit.php';
        logAction($pdo, $user_id, 'Update Profile', 'Profile', 'User updated profile info');
        
        // Refresh data
        $user['name'] = $name;
        $user['email'] = $email;
    }
}

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="user_dashboard.php">Dashboard</a>
        <a href="upload.php">Upload File</a>
        <a href="my_files.php">My Files</a>
        <a href="shared_files.php">Shared With Me</a>
        <a href="activity.php">Activity</a>
        <a href="profile.php" class="active">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h2>My Profile</h2>
        <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        
        <div class="form-container" style="margin: 20px 0; max-width: 500px;">
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Username (Read Only)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                </div>
                <button type="submit" class="btn btn-success">Update Profile</button>
            </form>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
