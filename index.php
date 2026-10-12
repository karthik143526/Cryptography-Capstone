<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: calc(100vh - 64px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    <div style="text-align: center; color: white; max-width: 600px; animation: slideInUp 0.8s ease-out;">
        <h1 style="font-size: 3.5rem; margin-bottom: 20px; animation: slideInDown 0.8s ease-out;">🔐 Secure File Storage</h1>
        <p style="font-size: 1.3rem; margin-bottom: 30px; opacity: 0.95; animation: slideInUp 0.8s ease-out 0.2s both;">A highly secure file storage system with military-grade encryption and robust access control.</p>
        
        <div style="margin-top: 40px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="login.php" class="btn" style="background: white; color: #667eea; font-weight: bold; animation: slideInLeft 0.8s ease-out 0.4s both;">👤 User Login</a>
            <a href="register.php" class="btn btn-success" style="animation: slideInUp 0.8s ease-out 0.4s both;">✍️ Register Now</a>
            <a href="admin_login.php" class="btn" style="background: #ff6b6b; animation: slideInRight 0.8s ease-out 0.4s both;">⚙️ Admin Login</a>
        </div>

        <div style="margin-top: 60px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px); animation: popIn 0.6s ease-out 0.5s both;">
                <div style="font-size: 2rem; margin-bottom: 10px;">🔒</div>
                <p style="font-weight: bold;">End-to-End Encryption</p>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px); animation: popIn 0.6s ease-out 0.6s both;">
                <div style="font-size: 2rem; margin-bottom: 10px;">👥</div>
                <p style="font-weight: bold;">Access Control</p>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px); animation: popIn 0.6s ease-out 0.7s both;">
                <div style="font-size: 2rem; margin-bottom: 10px;">📊</div>
                <p style="font-weight: bold;">Audit Logs</p>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
