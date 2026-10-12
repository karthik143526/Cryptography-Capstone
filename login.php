<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: user_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'Inactive') {
            $error = "Your account has been deactivated.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            require_once 'includes/audit.php';
            logAction($pdo, $user['id'], 'Login', 'Auth', 'User logged in successfully');
            
            if ($user['role'] === 'Administrator') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        }
    } else {
        require_once 'includes/audit.php';
        logAction($pdo, null, 'Failed Login', 'Auth', "Failed login attempt for: $identifier");
        $error = "Invalid credentials.";
    }
}
require_once 'includes/header.php';
?>
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: calc(100vh - 64px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    <div class="form-container" style="animation: popIn 0.8s ease-out; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); max-width: 450px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">👤</div>
            <h2 style="color: #667eea; animation: slideInDown 0.6s ease-out;">User Login</h2>
            <p style="color: #666; font-size: 0.95rem;">Access your secure file storage</p>
        </div>
        <?php if($error): ?><div class="alert alert-danger" style="animation: slideInDown 0.5s ease-out;"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.1s both;">
                <label>Email or Username</label>
                <input type="text" name="identifier" class="form-control" placeholder="Enter your email or username" required>
            </div>
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.2s both;">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%; background: #667eea; animation: slideInUp 0.6s ease-out 0.3s both;">Login</button>
            <div style="margin-top: 20px; text-align: center;">
                <a href="register.php" style="color: #667eea; text-decoration: underline; font-weight: 500;">Don't have an account? Register</a>
            </div>
        </form>
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0;">
        <p style="text-align: center; color: #666; font-size: 0.9rem;">
            <a href="index.php" style="color: #667eea; text-decoration: underline;">← Back to Home</a>
        </p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
