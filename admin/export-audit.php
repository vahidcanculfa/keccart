<?php
require_once '../config/init.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php"); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="audit_log.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','time','user_id','action','meta','ip']);
    $stmt = $db->query("SELECT * FROM audit_log ORDER BY created_at DESC");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) fputcsv($out, [$row['id'],$row['created_at'],$row['user_id'],$row['action'],$row['meta'],$row['ip']]);
    fclose($out); exit;
}
?>
<main class="container" style="margin-top:40px;">
    <h2>Audit Log</h2>
    <form method="GET"><button name="export" value="1" style="padding:8px;background:#1a73e8;color:#fff;border:none;border-radius:8px;">Export CSV</button></form>
</main>