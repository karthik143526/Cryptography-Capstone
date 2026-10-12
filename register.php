<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: user_dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = "Email or username already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $username, $hashed_password])) {
                require_once 'includes/audit.php';
                logAction($pdo, $pdo->lastInsertId(), 'Registration', 'Auth', 'User registered');
                $success = "Registration successful! You can now <a href='login.php'>Login</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
require_once 'includes/header.php';
?>
<?php if($success): ?>
<div class="success-container">
    <div class="success-box">
        <div class="success-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="check-mark">✓ Registration Successful!</h2>
        <p>Your account has been created successfully. You can now login to your account and start using the secure file storage system.</p>
        <a href="login.php" class="btn btn-success">Login Now</a>
        <br><br>
        <small style="color: var(--text-light);">Or <a href="index.php">return to home</a></small>
    </div>
</div>
<?php else: ?>
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: calc(100vh - 64px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    <div class="form-container" style="animation: popIn 0.8s ease-out; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); max-width: 500px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">✍️</div>
            <h2 style="color: #667eea; animation: slideInDown 0.6s ease-out;">Create Account</h2>
            <p style="color: #666; font-size: 0.95rem;">Join our secure file storage system</p>
        </div>
        <?php if($error): ?><div class="alert alert-danger" style="animation: slideInDown 0.5s ease-out;"><?= $error ?></div><?php endif; ?>
        <form method="POST" id="registerForm">
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.1s both;">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Your full name" required>
            </div>
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.2s both;">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
            </div>
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.25s both;">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
            </div>
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.3s both;">
                <label>Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Create a strong password" required>
            </div>
            <div class="form-group" style="animation: slideInUp 0.6s ease-out 0.35s both;">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm your password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%; background: #667eea; animation: slideInUp 0.6s ease-out 0.4s both;">Create Account</button>
        </form>
        <p style="text-align: center; margin-top: 20px; color: var(--text-light); animation: slideInUp 0.6s ease-out 0.45s both;">Already have an account? <a href="login.php" style="color: #667eea; font-weight: 500; text-decoration: underline;">Login here</a></p>
        <hr style="margin: 15px 0; border: none; border-top: 1px solid #e0e0e0; animation: slideInUp 0.6s ease-out 0.5s both;">
        <p style="text-align: center; color: #666; font-size: 0.9rem;">
            <a href="index.php" style="color: #667eea; text-decoration: underline;">← Back to Home</a>
        </p>
    </div>
</div>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>
