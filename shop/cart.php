<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_price = 0;

// İlişkili ürünleri getir
$related_query = $db->prepare("SELECT id, title, price, image FROM products ORDER BY RAND() LIMIT 6");
$related_query->execute();
$related_products = $related_query->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container">
    <div class="cart-page-header">
        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Alışveriş Sepeti</h1>
        <p class="cart-count">Toplam <strong><?php echo count($cart); ?></strong> ürün</p>
    </div>
    
    <?php if(count($cart) > 0): ?>
        <div class="cart-layout">
            <!-- SEPET TABLOSU -->
            <div class="cart-items-section">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Fiyat</th>
                            <th>Miktar</th>
                            <th>Toplam</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total_price += $subtotal;
                        ?>
                        <tr class="cart-row">
                            <td class="cart-product">
                                <img src="<?php echo BASE_URL; ?>uploads/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <a href="<?php echo BASE_URL; ?>shop/product-detail.php?id=<?php echo $item['id']; ?>" class="product-link">Detayları Görüntüle</a>
                                </div>
                            </td>
                            <td class="cart-price">$<?php echo number_format($item['price'], 2); ?></td>
                            <td class="cart-quantity">
                                <div class="quantity-control">
                                    <button class="qty-btn" onclick="updateQty(<?php echo $item['id']; ?>, -1)">−</button>
                                    <input type="number" value="<?php echo $item['quantity']; ?>" class="qty-input" readonly>
                                    <button class="qty-btn" onclick="updateQty(<?php echo $item['id']; ?>, 1)">+</button>
                                </div>
                            </td>
                            <td class="cart-subtotal">$<?php echo number_format($subtotal, 2); ?></td>
                            <td class="cart-action">
                                <a href="<?php echo BASE_URL; ?>core/cart-helper.php?action=remove&id=<?php echo $item['id']; ?>" class="remove-btn" title="Sil">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- SEPET ÖZETİ -->
            <aside class="cart-summary">
                <div class="summary-card">
                    <h2 class="summary-title">Sipariş Özeti</h2>
                    
                    <div class="summary-breakdown">
                        <div class="summary-row">
                            <span>Ara Toplam</span>
                            <span>$<?php echo number_format($total_price, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Kargo</span>
                            <span class="shipping-info">
                                <?php 
                                if($total_price >= 100) {
                                    echo '<span style="color: #1e8e3e; font-weight: 600;">ÜCRETSİZ</span>';
                                } else {
                                    echo '$10.00';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="summary-row">
                            <span>KDV (%18)</span>
                            <span>$<?php echo number_format($total_price * 0.18, 2); ?></span>
                        </div>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row total-row">
                        <span>TOPLAM</span>
                        <span class="total-price">
                            $<?php 
                            $shipping = $total_price >= 100 ? 0 : 10;
                            $tax = $total_price * 0.18;
                            $final = $total_price + $shipping + $tax;
                            echo number_format($final, 2); 
                            ?>
                        </span>
                    </div>

                    <a href="<?php echo BASE_URL; ?>shop/checkout.php" class="btn-primary btn-checkout">Siparişi Tamamla</a>
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn-continue-shopping">Alışveriş Devam Et</a>
                </div>

                <!-- AVANTAJLAR -->
                <div class="cart-benefits">
                    <div class="benefit">
                        <i class="fas fa-truck benefit-emoji"></i>
                        <span class="benefit-text">100 TL+ ücretsiz kargo</span>
                    </div>
                    <div class="benefit">
                        <i class="fas fa-undo benefit-emoji"></i>
                        <span class="benefit-text">30 gün iade hakkı</span>
                    </div>
                    <div class="benefit">
                        <i class="fas fa-lock benefit-emoji"></i>
                        <span class="benefit-text">Güvenli ödeme</span>
                    </div>
                </div>
            </aside>
        </div>

        <!-- ÖNERİLEN ÜRÜNLER -->
        <?php if(count($related_products) > 0): ?>
        <section class="recommended-products">
            <h2 class="section-title"><i class="fas fa-lightbulb"></i> Sizin İçin Önerilenler</h2>
            <div class="products-carousel">
                <?php foreach($related_products as $rel_prod): 
                    // Sepette var mı kontrol et
                    $in_cart = false;
                    foreach($cart as $c) {
                        if($c['id'] == $rel_prod['id']) {
                            $in_cart = true;
                            break;
                        }
                    }
                    if($in_cart) continue; // Sepetteki ürünü gösterme
                ?>
                    <div class="product-card">
                        <div class="card-image">
                            <img src="<?php echo BASE_URL; ?>uploads/<?php echo $rel_prod['image']; ?>" alt="<?php echo htmlspecialchars($rel_prod['title']); ?>">
                            <a href="<?php echo BASE_URL; ?>shop/product-detail.php?id=<?php echo $rel_prod['id']; ?>" class="overlay-link">Detayları Görüntüle</a>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($rel_prod['title']); ?></h3>
                            <div class="card-price">$<?php echo number_format($rel_prod['price'], 2); ?></div>
                            <a href="<?php echo BASE_URL; ?>core/cart-helper.php?action=add&id=<?php echo $rel_prod['id']; ?>" class="btn-primary" style="width: 100%; padding: 8px; font-size: 12px;">Sepete Ekle</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty-cart">
            <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
            <h2>Sepetiniz Boş</h2>
            <p>Henüz hiç ürün eklemediniz.</p>
            <p class="empty-cart-sub">Hemen alışverişe başlayın!</p>
            <a href="<?php echo BASE_URL; ?>index.php" class="btn-primary" style="display: inline-block; padding: 12px 30px; margin-top: 20px;">Alışveriş Yap</a>
        </div>
    <?php endif; ?>
</main>

<script>
function updateQty(id, change) {
    // Miktar güncelleme JavaScript işlemi (backend entegrasyonu gerekebilir)
    alert('Miktar güncelleme özelliği yakında eklenecek!');
}
</script>

<?php include '../includes/footer.php'; ?>