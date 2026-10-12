<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin();

require_once 'includes/header.php';
?>
<div class="dashboard-layout">
    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Users</a>
        <a href="manage_files.php">Files</a>
        <a href="manage_permissions.php">Permissions</a>
        <a href="audit_logs.php">Audit Logs</a>
        <a href="reports.php" class="active">Reports</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h2>System Reports</h2>
        <p>This section is a placeholder for generating advanced analytical reports (e.g., PDF generation or chart integrations for file usage, system growth, and user engagement over time).</p>
        <div class="card" style="margin-top: 20px;">
            <h3>Generate Summary Report</h3>
            <button class="btn btn-success" onclick="alert('Report generation will be available in the next version.')">Download PDF Report</button>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
