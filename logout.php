<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    require_once 'includes/audit.php';
    logAction($pdo, $_SESSION['user_id'], 'Logout', 'Auth', 'User logged out');
}

session_destroy();
header("Location: login.php");
exit();
?>
