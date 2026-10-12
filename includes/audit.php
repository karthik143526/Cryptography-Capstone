<?php
function logAction($pdo, $userId, $action, $module, $details = '') {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, module, details) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $module, $details]);
}
?>
