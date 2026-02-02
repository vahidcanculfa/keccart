<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $product_id = (int)$_GET['id'];

    $query = $db->prepare("SELECT * FROM products WHERE id = ?");
    $query->execute([$product_id]);
    $product = $query->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $cart_item = [
            'id' => $product['id'],
            'title' => $product['title'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => 1
        ];

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$product_id] = $cart_item;
        }
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $product_id = (int)$_GET['id'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: ../cart.php");
    exit;
}
?>