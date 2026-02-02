<?php
function log_audit($db, $user_id, $action, $meta = []) {
    try {
        $stmt = $db->prepare("INSERT INTO audit_log (user_id, action, meta, ip) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, json_encode($meta, JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Exception $e) {
        // fail silently
    }
}
