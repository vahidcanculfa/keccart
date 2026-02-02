<?php
require_once '../config/init.php';
require_once 'audit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$method = isset($_POST['method']) ? $_POST['method'] : 'simulated';

// Basic simulation: create payment record and mark order as paid
try {
    $stmt = $db->prepare("INSERT INTO payments (order_id, provider, provider_id, amount, currency, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$order_id, $method, 'SIM-' . time(), 0, 'USD', 'completed']);
    $db->prepare("UPDATE orders SET status = 'paid' WHERE id = ?")->execute([$order_id]);
    log_audit($db, $_SESSION['user_id'] ?? null, 'payment_simulated', ['order_id' => $order_id, 'method' => $method]);
} catch (Exception $e) {
}
header('Location: ' . BASE_URL . 'orders/order-detail.php?id=' . $order_id . '&pay=ok');
exit;