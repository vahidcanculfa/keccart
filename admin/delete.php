<?php
require_once '../config/init.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Geçersiz istek (CSRF doğrulaması başarısız).');
    }

    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        $imgQuery = $db->prepare("SELECT image FROM products WHERE id = ?");
        $imgQuery->execute([$id]);
        $product = $imgQuery->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['image'] !== 'default.jpg') {
            $file_path = "../uploads/" . $product['image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $query = $db->prepare("DELETE FROM products WHERE id = ?");
        $query->execute([$id]);
    }
}

header("Location: " . BASE_URL . "admin/index.php?deleted=1");
exit;