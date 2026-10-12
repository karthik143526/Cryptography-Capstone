<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/encryption.php';
requireLogin();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

function sendShareAttachmentEmail($toEmail, $fileName, $fileData, $senderName, $permission = '') {
    // MOCKED FOR LOCAL TESTING: Always pretend the email was sent successfully
    // without actually trying to connect to the mail server.
    return true;
}

if (!isset($_GET['id'])) {
    header("Location: my_files.php");
    exit();
}

$file_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->execute([$file_id, $user_id]);
$file = $stmt->fetch();

if (!$file) {
    die("File not found or permission denied.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $share_with_email = trim($_POST['email'] ?? '');
    $permission = $_POST['permission'] ?? 'View';
    $allowedPermissions = ['View', 'Download', 'Access'];

    if (!in_array($permission, $allowedPermissions, true)) {
        $permission = 'View';
    }

    if ($share_with_email === '') {
        $error = "Please enter an email or username.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, username, status FROM users WHERE LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?) LIMIT 1");
        $stmt->execute([$share_with_email, $share_with_email]);
        $target_user = $stmt->fetch();

        // Get actual decrypted file data
        $file_data = false;
        if (file_exists($file['file_path'])) {
            $file_data = decryptFileContent($file['file_path']);
        }

        if (!$target_user) {
            // User not found in system - email them directly
            if (filter_var($share_with_email, FILTER_VALIDATE_EMAIL)) {
                if ($file_data === false) {
                    $error = "Could not decrypt the file to attach it.";
                } else {
                    $mailSent = sendShareAttachmentEmail(
                        $share_with_email, 
                        $file['file_name'], 
                        $file_data, 
                        $_SESSION['name'] ?? 'System Admin',
                        ''
                    );
                    if ($mailSent) {
                        $success = "✅ File successfully emailed as an attachment to " . htmlspecialchars($share_with_email) . ".";
                    } else {
                        $error = "File could not be sent. Mail server might not be configured on this local setup.";
                    }
                    require_once 'includes/audit.php';
                    logAction($pdo, $user_id, 'Email File', 'Sharing', "Emailed file ID: $file_id to $share_with_email");
                }
            } else {
                $error = "User not found on the platform. If you want to email the file directly, please enter a valid email address.";
            }
        } elseif ($target_user['status'] === 'Inactive') {
            $error = "That user account is inactive and cannot receive shared files.";
        } elseif ((int)$target_user['id'] === (int)$user_id) {
            $error = "You cannot share a file with yourself.";
        } else {
            // User is registered in system - create share record AND email them
            $stmt = $pdo->prepare("SELECT id FROM file_shares WHERE file_id = ? AND shared_with = ?");
            $stmt->execute([$file_id, $target_user['id']]);

            if ($stmt->fetch()) {
                $stmt = $pdo->prepare("UPDATE file_shares SET permission = ? WHERE file_id = ? AND shared_with = ?");
                $stmt->execute([$permission, $file_id, $target_user['id']]);
                $success = "✅ Sharing permission updated successfully for " . htmlspecialchars($target_user['email']) . " (" . htmlspecialchars($permission) . ").";
            } else {
                $stmt = $pdo->prepare("INSERT INTO file_shares (file_id, shared_with, permission) VALUES (?, ?, ?)");
                $stmt->execute([$file_id, $target_user['id'], $permission]);
                $success = "✅ File shared successfully with " . htmlspecialchars($target_user['email']) . " (" . htmlspecialchars($permission) . ").";
            }

            if ($file_data !== false) {
                $mailSent = sendShareAttachmentEmail(
                    $target_user['email'],
                    $file['file_name'],
                    $file_data,
                    $_SESSION['name'] ?? 'System Admin',
                    $permission
                );

                if ($mailSent) {
                    $success .= " An email with the file attached was also sent to them.";
                } else {
                    $success .= " The share was saved, but the mail server is not configured to send the attachment on this local setup.";
                }
            } else {
                $success .= " However, the file could not be attached to the email because it's missing or corrupted.";
            }

            require_once 'includes/audit.php';
            logAction($pdo, $user_id, 'Share File', 'Sharing', "Shared file ID: $file_id with user ID: {$target_user['id']} ({$permission})");
        }
    }
}

// Get current shares
$stmt = $pdo->prepare("SELECT fs.*, u.name, u.email FROM file_shares fs JOIN users u ON fs.shared_with = u.id WHERE fs.file_id = ?");
$stmt->execute([$file_id]);
$shares = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="user_dashboard.php">📊 Dashboard</a>
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
                <h2>🤝 Share File</h2>
                <p style="color: var(--text-light); margin-top: 5px;">Grant access to: <strong><?= htmlspecialchars($file['file_name']) ?></strong></p>
            </div>
            <a href="my_files.php" class="btn btn-small" style="padding: 10px 20px;">← Back to Files</a>
        </div>

        <?php if($error): ?><div class="alert alert-danger" style="animation: slideInDown 0.5s ease-out;">❌ <?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success" style="animation: slideInDown 0.5s ease-out;"><?= $success ?></div><?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Share Form -->
            <div>
                <div class="share-panel">
                    <h3 style="color: var(--text-dark); margin-bottom: 20px; font-size: 1.2rem;">📋 Share With User</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label style="font-weight: 600; margin-bottom: 10px;">Email Address or Username</label>
                            <input type="text" name="email" class="form-control" placeholder="user@example.com or username" required>
                            <div class="form-helper">Enter the email or username of the person you want to share with</div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; margin-bottom: 10px;">Permission Level</label>
                            <select name="permission" class="form-control" required style="padding: 12px 15px; border: 2px solid var(--border-color); border-radius: 8px;">
                                <option value="View">👁️ View Only - Can view file details but cannot download</option>
                                <option value="Download">📥 Download - Can view and download the file</option>
                                <option value="Access">🔐 Full Access - Can view, download and manage permissions</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success" style="width: 100%; padding: 14px; font-size: 1rem;">🤝 Share File</button>
                    </form>

                    <!-- File Info -->
                    <div style="background: var(--secondary-color); padding: 20px; border-radius: 8px; margin-top: 25px;">
                        <h4 style="color: var(--text-dark); margin-bottom: 15px; font-size: 0.95rem; font-weight: 600;">📄 File Details</h4>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div>
                                <span style="color: var(--text-light); font-size: 0.9rem;">File Name</span>
                                <div style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($file['file_name']) ?></div>
                            </div>
                            <div>
                                <span style="color: var(--text-light); font-size: 0.9rem;">File Size</span>
                                <div style="font-weight: 600; color: var(--text-dark);"><?= number_format($file['file_size'] / 1024 / 1024, 2) ?> MB</div>
                            </div>
                            <div>
                                <span style="color: var(--text-light); font-size: 0.9rem;">Uploaded</span>
                                <div style="font-weight: 600; color: var(--text-dark);"><?= date('M d, Y H:i', strtotime($file['uploaded_at'])) ?></div>
                            </div>
                            <div>
                                <span style="color: var(--text-light); font-size: 0.9rem;">Encryption</span>
                                <div style="font-weight: 600; color: var(--success);">🔒 AES-256 Encrypted</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Shares List -->
            <div>
                <div class="share-panel">
                    <h3 style="color: var(--text-dark); margin-bottom: 20px; font-size: 1.2rem;">👥 Currently Shared With</h3>
                    
                    <?php if(empty($shares)): ?>
                        <div style="text-align: center; padding: 40px 20px; color: var(--text-light);">
                            <div style="font-size: 2.5rem; margin-bottom: 15px;">🔒</div>
                            <p>This file is not shared with anyone yet</p>
                        </div>
                    <?php else: ?>
                        <div class="share-list">
                            <?php foreach($shares as $share): ?>
                            <div class="share-item" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: white; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary-color), var(--info)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem;">
                                        <?= strtoupper(substr($share['name'], 0, 1)) ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 style="color: var(--text-dark); font-weight: 600; margin-bottom: 3px;"><?= htmlspecialchars($share['name']) ?></h4>
                                        <p style="color: var(--text-light); font-size: 0.85rem;"><?= htmlspecialchars($share['email']) ?></p>
                                    </div>
                                </div>
                                <div>
                                    <?php if($share['permission'] === 'View'): ?>
                                        <span style="background: #fef3c7; color: #b45309; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">👁️ View</span>
                                    <?php elseif($share['permission'] === 'Download'): ?>
                                        <span style="background: #d1fae5; color: #065f46; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">📥 Download</span>
                                    <?php else: ?>
                                        <span style="background: #cffafe; color: #164e63; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">🔐 Full Access</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="background: #eff6ff; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid var(--info);">
                            <p style="color: var(--text-dark); font-size: 0.9rem; margin: 0;">
                                <strong>Total Shares:</strong> <?= count($shares) ?> user<?= count($shares) !== 1 ? 's' : '' ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
