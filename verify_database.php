<?php
/**
 * Database Setup & Verification Script
 * Run this to verify your database configuration
 * 
 * Usage: 
 * 1. Place this file in the project root
 * 2. Access via browser: http://localhost/crypto/verify_database.php
 * 3. Check all items are marked as "OK"
 * 4. If any items fail, follow the remediation steps
 */

// Suppress any output before starting
ob_start();

// Simple error handler for display
set_error_handler(function($severity, $message, $file, $line) {
    // Continue with normal error handling
    return false;
});

$results = [];
$critical_errors = [];

// Test 1: Check if PHP extensions are loaded
$results['PHP Extensions'] = [
    'PDO Extension' => extension_loaded('pdo') ? '✓ OK' : '✗ FAILED',
    'MySQL PDO Driver' => extension_loaded('pdo_mysql') ? '✓ OK' : '✗ FAILED',
    'MySQLi Extension' => extension_loaded('mysqli') ? '✓ OK' : '✗ FAILED',
];

// Test 2: Try to load configuration
try {
    require_once __DIR__ . '/includes/config.php';
    $results['Configuration'] = ['Config File Loaded' => '✓ OK'];
} catch (Exception $e) {
    $results['Configuration'] = ['Config File Loaded' => '✗ FAILED: ' . $e->getMessage()];
    $critical_errors[] = 'Configuration file not found or has syntax errors';
}

// Test 3: Attempt database connection
$pdo = null;
try {
    if (defined('DB_HOST')) {
        $dsn = "mysql:host=" . DB_HOST . ":" . DB_PORT . ";charset=" . DB_CHARSET;
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]
        );
        $results['MySQL Connection'] = ['Server Connection' => '✓ OK'];
    }
} catch (PDOException $e) {
    $results['MySQL Connection'] = ['Server Connection' => '✗ FAILED: ' . $e->getMessage()];
    $critical_errors[] = 'Cannot connect to MySQL server at ' . DB_HOST . ':' . DB_PORT;
}

// Test 4: Check if database exists
if ($pdo !== null) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ":" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        $results['Database'] = ['Database Exists' => '✓ OK'];
    } catch (PDOException $e) {
        $results['Database'] = ['Database Exists' => '✗ FAILED: Database "' . DB_NAME . '" not found'];
        $critical_errors[] = 'Database does not exist or cannot access it';
        $pdo = null;
    }
}

// Test 5: Check tables
if ($pdo !== null) {
    $tables = ['users', 'files', 'file_shares', 'permissions', 'audit_logs'];
    $table_results = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $table_results[$table] = '✓ OK';
            } else {
                $table_results[$table] = '✗ NOT FOUND';
                $critical_errors[] = "Table '$table' does not exist";
            }
        } catch (PDOException $e) {
            $table_results[$table] = '✗ ERROR: ' . $e->getMessage();
            $critical_errors[] = "Cannot check table '$table'";
        }
    }
    $results['Database Tables'] = $table_results;
}

// Test 6: Check admin user
if ($pdo !== null) {
    try {
        $stmt = $pdo->query("SELECT id, email, role FROM users WHERE role = 'Administrator' LIMIT 1");
        $admin = $stmt->fetch();
        
        if ($admin) {
            $results['Default Admin User'] = [
                'Admin Exists' => '✓ OK (' . htmlspecialchars($admin['email']) . ')',
                'User ID' => $admin['id']
            ];
        } else {
            $results['Default Admin User'] = ['Admin Exists' => '✗ NOT FOUND'];
            $critical_errors[] = 'No administrator user found in database';
        }
    } catch (PDOException $e) {
        $results['Default Admin User'] = ['Error' => $e->getMessage()];
    }
}

// Test 7: Check file permissions
$upload_dir = __DIR__ . '/uploads/';
$results['File System'] = [
    'Uploads Directory' => (is_dir($upload_dir) ? '✓ OK' : '✗ NOT FOUND'),
    'Uploads Writable' => (is_writable($upload_dir) ? '✓ OK' : '✗ NOT WRITABLE'),
    'Includes Directory' => (is_dir(__DIR__ . '/includes/') ? '✓ OK' : '✗ NOT FOUND'),
];

// Clear output buffer
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Verification - Secure File Storage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .status-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .status-card {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .status-card.critical {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
        }
        
        .status-card.success {
            background: #dcfce7;
            border-left: 4px solid #16a34a;
        }
        
        .status-card h3 {
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .status-card p {
            font-size: 0.9rem;
            color: #666;
        }
        
        .test-section {
            margin-bottom: 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .test-section-header {
            background: #f9fafb;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #1f2937;
        }
        
        .test-section-content {
            padding: 20px;
        }
        
        .test-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .test-item:last-child {
            border-bottom: none;
        }
        
        .test-label {
            color: #4b5563;
            font-weight: 500;
        }
        
        .test-result {
            font-family: 'Monaco', 'Menlo', 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        .success {
            color: #16a34a;
        }
        
        .error {
            color: #dc2626;
        }
        
        .warning {
            color: #ea580c;
        }
        
        .info {
            color: #0369a1;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #7f1d1d;
            border-left: 4px solid #dc2626;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #15803d;
            border-left: 4px solid #16a34a;
        }
        
        .remediation {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .remediation h3 {
            color: #92400e;
            margin-bottom: 10px;
        }
        
        .remediation ol {
            margin-left: 20px;
            color: #78350f;
        }
        
        .remediation li {
            margin-bottom: 8px;
        }
        
        .remediation code {
            background: #fef3c7;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9rem;
        }
        
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Database Verification</h1>
            <p>Secure File Storage - Setup Checker</p>
        </div>
        
        <div class="content">
            <?php if (empty($critical_errors)): ?>
                <div class="alert alert-success">
                    ✅ All critical checks passed! Your database is properly configured.
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    ⚠️ Found <?= count($critical_errors) ?> critical issue<?= count($critical_errors) > 1 ? 's' : '' ?>. See details below.
                </div>
            <?php endif; ?>
            
            <div class="status-summary">
                <div class="status-card <?= empty($critical_errors) ? 'success' : 'critical' ?>">
                    <h3><?= empty($critical_errors) ? '✓' : '✗' ?></h3>
                    <p><?= empty($critical_errors) ? 'All Systems' : 'Issues Found' ?></p>
                </div>
            </div>
            
            <?php foreach ($results as $section => $items): ?>
            <div class="test-section">
                <div class="test-section-header"><?= htmlspecialchars($section) ?></div>
                <div class="test-section-content">
                    <?php foreach ($items as $label => $result): ?>
                    <div class="test-item">
                        <span class="test-label"><?= htmlspecialchars($label) ?></span>
                        <span class="test-result <?= (strpos($result, '✓') !== false) ? 'success' : ((strpos($result, '✗') !== false) ? 'error' : 'info') ?>">
                            <?= htmlspecialchars($result) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (!empty($critical_errors)): ?>
            <div class="remediation">
                <h3>⚙️ Remediation Steps</h3>
                <ol>
                    <?php foreach ($critical_errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ol>
                
                <h3 style="margin-top: 20px; margin-bottom: 10px;">Quick Fix:</h3>
                <ol>
                    <li>Open <strong>XAMPP Control Panel</strong></li>
                    <li>Click <strong>Start</strong> next to <strong>MySQL</strong></li>
                    <li>Open <strong>phpMyAdmin</strong></li>
                    <li>Create database: <code>crypto</code></li>
                    <li>Click <strong>Import</strong> and select <code>crypto.sql</code> from your project folder</li>
                    <li>Run this verification page again</li>
                </ol>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>Last checked: <?= date('Y-m-d H:i:s') ?></p>
            <p><small>Remove this file (verify_database.php) after verification in production</small></p>
        </div>
    </div>
</body>
</html>
