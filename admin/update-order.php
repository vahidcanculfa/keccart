<?php
require_once '../config/init.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Geçersiz istek (CSRF doğrulaması başarısız).');
    }

    $order_id = (int)$_POST['order_id'];
    $new_status = trim($_POST['status']);

    $query = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $query->execute([$new_status, $order_id]);

    header("Location: " . BASE_URL . "admin/orders.php?updated=1");
    exit;
} 