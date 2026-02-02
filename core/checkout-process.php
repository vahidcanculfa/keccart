<?php
$db->beginTransaction();

try {
    

    foreach ($_SESSION['cart'] as $product_id => $item) {
        $quantity = $item['quantity'];

        $updateStock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $updateStock->execute([$quantity, $product_id, $quantity]);

        if ($updateStock->rowCount() === 0) {
            throw new Exception("Yetersiz stok: Ürün ID " . $product_id);
        }

        
    }

    $db->commit();
    
    unset($_SESSION['cart']);
    header("Location: ../order-success.php");

} catch (Exception $e) {
    $db->rollBack();
    header("Location: ../cart.php?error=" . urlencode($e->getMessage()));
}