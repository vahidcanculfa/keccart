<?php
require_once '../config/init.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo '<main class="container" style="margin-top:40px; margin-bottom:80px;">';
    echo '<div class="auth-card"><h2 class="auth-title">Favorilerim</h2><p class="mt-20">Favorileri görmek için giriş yapın.</p><a class="btn-primary btn-block" href="' . BASE_URL . 'auth/login.php">Giriş Yap</a></div>';
    echo '</main>';
    include '../includes/footer.php';
    exit;
}

$uid = (int)$_SESSION['user_id'];
$stmt = $db->prepare("SELECT p.* FROM products p JOIN wishlist w ON w.product_id = p.id WHERE w.user_id = ? ORDER BY p.id DESC");
$stmt->execute([$uid]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container" style="margin-top:40px; margin-bottom:80px;">
    <h2 class="home-title">Favorilerim</h2>
    <?php if (count($items) === 0): ?>
        <div class="auth-card"><p class="mt-20">Henüz favori ürün yok.</p></div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach($items as $product): ?>
                <div class="product-card">
                    <a href="<?php echo BASE_URL; ?>shop/product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration:none;">
                        <div class="product-image" style="height: 220px; background: #f8f9fa;">
                            <img src="<?php echo BASE_URL; ?>uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" style="width:100%; height:100%; object-fit: contain; padding: 10px;" loading="lazy">
                        </div>
                        <div class="product-info" style="padding: 15px;">
                            <h4 style="color: #3c4043; font-size: 15px; margin-bottom: 10px; height: 40px; overflow: hidden;"><?php echo htmlspecialchars($product['title']); ?></h4>
                            <p style="color: #202124; font-weight: 700; font-size: 18px;">$<?php echo number_format($product['price'], 2); ?></p>
                        </div>
                    </a>
                    <div style="padding: 0 15px 15px; display:flex; gap:10px;">
                        <a href="<?php echo BASE_URL; ?>core/cart-helper.php?action=add&id=<?php echo $product['id']; ?>" class="add-to-cart" style="text-decoration: none;">Sepete Ekle</a>
                        <a href="<?php echo BASE_URL; ?>core/wishlist.php?action=remove&product_id=<?php echo $product['id']; ?>" class="btn-outline" style="padding:10px 12px;">Sil</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
