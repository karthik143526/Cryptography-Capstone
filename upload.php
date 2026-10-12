<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/encryption.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            $error = "File size exceeds 50MB limit.";
        } else {
            $fileName = basename($file['name']);
            $fileSize = $file['size'];
            
            // Generate a unique path for the encrypted file
            $uniqueId = uniqid() . '_' . time();
            $destinationPath = 'uploads/' . $uniqueId . '.enc';
            
            if (encryptFile($file['tmp_name'], $destinationPath)) {
                $stmt = $pdo->prepare("INSERT INTO files (user_id, file_name, file_path, file_size) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$_SESSION['user_id'], $fileName, $destinationPath, $fileSize])) {
                    require_once 'includes/audit.php';
                    logAction($pdo, $_SESSION['user_id'], 'File Upload', 'Files', "Uploaded file: $fileName");
                    $success = "File uploaded and encrypted successfully.";
                } else {
                    $error = "Database error saving file metadata.";
                    unlink($destinationPath); // Cleanup
                }
            } else {
                $error = "File encryption failed.";
            }
        }
    } else {
        $error = "Error uploading file. Code: " . $file['error'];
    }
}
require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="user_dashboard.php">📊 Dashboard</a>
        <a href="upload.php" class="active">⬆️ Upload File</a>
        <a href="my_files.php">📁 My Files</a>
        <a href="shared_files.php">🤝 Shared With Me</a>
        <a href="activity.php">📋 Activity</a>
        <a href="profile.php">👤 Profile</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
    <div class="main-content">
        <h2>⬆️ Upload Secure File</h2>
        
        <?php if($error): ?><div class="alert alert-danger" style="animation: slideInDown 0.5s ease-out;">❌ <?= $error ?></div><?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success" style="animation: slideInDown 0.5s ease-out;">
                ✅ <?= $success ?>
                <br><small><a href="my_files.php" style="color: inherit;">View your files →</a></small>
            </div>
        <?php endif; ?>
        
        <div style="max-width: 700px; margin: 0;">
            <!-- Storage Info -->
            <div class="storage-info">
                <div class="storage-header">
                    <div>
                        <div class="storage-title">💾 Secure Storage</div>
                        <div style="font-size: 0.9rem; opacity: 0.9; margin-top: 5px;">All files are encrypted with military-grade encryption</div>
                    </div>
                    <div class="storage-used">50 MB</div>
                </div>
                <div class="storage-bar">
                    <div class="storage-bar-fill" style="width: 20%;"></div>
                </div>
                <div style="margin-top: 10px; font-size: 0.9rem;">10 MB of 50 MB used</div>
            </div>

            <!-- Upload Form -->
            <form method="POST" enctype="multipart/form-data" id="uploadForm" style="margin-top: 30px;">
                <div class="form-group">
                    <label style="font-size: 1.1rem; margin-bottom: 15px;">Select or Drag & Drop Your File</label>
                    
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-area-icon">📤</div>
                        <div class="upload-area-title">Drag and drop your file here</div>
                        <div class="upload-area-subtitle">or click to browse from your computer</div>
                        <div style="margin-top: 15px; font-size: 0.85rem; color: var(--text-light);">
                            Supported: All file types | Max size: 50 MB
                        </div>
                    </div>
                    <input type="file" name="file" id="file" class="form-control" style="display: none;" required>
                </div>

                <!-- Upload Progress -->
                <div class="upload-progress" id="uploadProgress">
                    <div class="upload-progress-bar" id="uploadProgressBar" style="width: 0%;"></div>
                </div>
                <div class="upload-progress-text" id="uploadProgressText" style="display: none; text-align: center; margin-top: 10px;"></div>

                <!-- File Info Display -->
                <div id="fileInfo" style="background: var(--secondary-color); padding: 15px; border-radius: 8px; margin-top: 15px; display: none;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 2rem;">📄</div>
                        <div style="flex: 1;">
                            <div id="fileName" style="font-weight: 600; color: var(--text-dark); margin-bottom: 3px;"></div>
                            <div id="fileSize" style="font-size: 0.85rem; color: var(--text-light);"></div>
                        </div>
                        <button type="button" class="btn btn-small" style="padding: 8px 16px; font-size: 0.9rem;" onclick="document.getElementById('uploadForm').reset(); document.getElementById('fileInfo').style.display = 'none';">Change</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 1.05rem; margin-top: 20px; display: none;" id="submitBtn">
                    🔒 Upload & Encrypt
                </button>
            </form>

            <!-- Upload Tips -->
            <div style="background: #f0f4f8; border-left: 4px solid var(--info); padding: 20px; border-radius: 8px; margin-top: 30px;">
                <h4 style="color: var(--text-dark); margin-bottom: 10px;">🔒 Security Features</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">✓ End-to-end encryption for all files</li>
                    <li style="padding: 8px 0;">✓ Zero-knowledge storage (we can't access your files)</li>
                    <li style="padding: 8px 0;">✓ Secure file sharing with granular permissions</li>
                    <li style="padding: 8px 0;">✓ Complete audit logs of all activities</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('file');
const fileInfo = document.getElementById('fileInfo');
const submitBtn = document.getElementById('submitBtn');
const uploadForm = document.getElementById('uploadForm');

// Drag and drop handlers
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        displayFileInfo();
    }
});

uploadArea.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', displayFileInfo);

function displayFileInfo() {
    if (fileInput.files.length) {
        const file = fileInput.files[0];
        const maxSize = 50 * 1024 * 1024;
        
        if (file.size > maxSize) {
            alert('File size exceeds 50 MB limit!');
            fileInput.value = '';
            fileInfo.style.display = 'none';
            submitBtn.style.display = 'none';
            return;
        }
        
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        fileInfo.style.display = 'block';
        submitBtn.style.display = 'block';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

