<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: ../index.php?review=csrf_error");
        exit;
    }

    $product_id = (int)$_POST['product_id'];
    $user_id = (int)$_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $rating = ($rating >= 1 && $rating <= 5) ? $rating : 5;
    $comment = trim($_POST['comment']);
    if (strlen($comment) > 1000) $comment = substr($comment, 0, 1000);

    $check = $db->prepare("SELECT id FROM products WHERE id = ?");
    $check->execute([$product_id]);
    if (!$check->fetch()) {
        header("Location: ../index.php?review=invalid_product");
        exit;
    } 

    $query = $db->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $query->execute([$product_id, $user_id, $rating, $comment]);

    header("Location: ../product-detail.php?id=" . $product_id . "&review=success");
} else {
    header("Location: ../index.php");
}
exit; 