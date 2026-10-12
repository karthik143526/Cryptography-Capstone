<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/encryption.php';
require_once 'includes/audit.php';
requireLogin();

if (!isset($_GET['id'])) {
    die("File ID not provided.");
}

$file_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Check permission
$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if (!$file) {
    die("File not found.");
}

$has_access = false;
if ($file['user_id'] == $user_id || isAdmin()) {
    $has_access = true;
} else {
    // Check if shared
    $stmt = $pdo->prepare("SELECT * FROM file_shares WHERE file_id = ? AND shared_with = ? AND permission IN ('Download', 'Access')");
    $stmt->execute([$file_id, $user_id]);
    if ($stmt->fetch()) {
        $has_access = true;
    }
}

if (!$has_access) {
    logAction($pdo, $user_id, 'Unauthorized Access', 'Files', "Attempted to download file ID: $file_id");
    die("You do not have permission to download this file.");
}

// Proceed to download
if (file_exists($file['file_path'])) {
    $decrypted_data = decryptFileContent($file['file_path']);
    if ($decrypted_data === false) {
        die("Decryption failed.");
    }
    
    logAction($pdo, $user_id, 'Download File', 'Files', "Downloaded file: " . $file['file_name']);
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($decrypted_data));
    echo $decrypted_data;
    exit;
} else {
    die("File does not exist on server.");
}
?>
