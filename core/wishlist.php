<?php
require_once '../config/init.php';
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}
$uid = (int)$_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$uid, $product_id]);
        log_audit($db, $uid, 'wishlist_add', ['product_id' => $product_id]);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } catch (Exception $e) { header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=1'); }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || (isset($_GET['action']) && $_GET['action']==='remove')) {
    $product_id = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
    $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$uid, $product_id]);
    log_audit($db, $uid, 'wishlist_remove', ['product_id' => $product_id]);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
// GET: list
$stmt = $db->prepare("SELECT p.* FROM products p JOIN wishlist w ON w.product_id = p.id WHERE w.user_id = ?");
$stmt->execute([$uid]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($items);
