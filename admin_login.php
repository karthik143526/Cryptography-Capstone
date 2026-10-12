<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isAdmin()) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'Administrator'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        
        require_once 'includes/audit.php';
        logAction($pdo, $user['id'], 'Admin Login', 'Auth', 'Admin logged in');
        
        header("Location: admin_dashboard.php");
        exit();
    } else {
        require_once 'includes/audit.php';
        logAction($pdo, null, 'Failed Admin Login', 'Auth', "Failed admin login attempt for: $email");
        $error = "Invalid admin credentials.";
    }
}
require_once 'includes/header.php';
?>
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: calc(100vh - 64px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    <div class="form-container" style="animation: popIn 0.8s ease-out; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">⚙️</div>
            <h2 style="color: #667eea; animation: slideInDown 0.6s ease-out;">Admin Dashboard</h2>
            <p style="color: #666; font-size: 0.95rem;">Secure administrator login</p>
        </div>
        <?php if($error): ?><div class="alert alert-danger" style="animation: slideInDown 0.5s ease-out;"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.1s both;">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@example.com" required>
            </div>
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.2s both;">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%; background: #667eea; animation: slideInUp 0.6s ease-out 0.3s both;">Login as Admin</button>
        </form>
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0;">
        <p style="text-align: center; color: #666; font-size: 0.9rem;">
            <a href="index.php" style="color: #667eea; text-decoration: underline;">← Back to Home</a>
        </p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
