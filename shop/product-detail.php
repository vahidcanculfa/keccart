<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = $db->prepare("SELECT * FROM products WHERE id = ?");
$query->execute([$id]);
$product = $query->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// İlişkili ürünleri getir (aynı kategoride)
$related_query = $db->prepare("SELECT id, title, price, image FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$related_query->execute([$product['category_id'], $id]);
$related_products = $related_query->fetchAll(PDO::FETCH_ASSOC);

// Ortalama rating hesapla
$rating_query = $db->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = ?");
$rating_query->execute([$id]);
$rating_data = $rating_query->fetch(PDO::FETCH_ASSOC);
$avg_rating = round($rating_data['avg_rating'] ?? 0);

$review_query = $db->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$review_query->execute([$id]);
$reviews = $review_query->fetchAll(PDO::FETCH_ASSOC);

// Favorilerde var mı kontrol et
$is_wishlisted = false;
if(isset($_SESSION['user_id'])) {
    $wish_check = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wish_check->execute([$_SESSION['user_id'], $id]);
    $is_wishlisted = $wish_check->fetch(PDO::FETCH_ASSOC) !== false;
}
?>

<main class="container">
    <div class="product-detail-grid">
        <div class="product-images">
            <div class="main-image">
                <img id="mainImage" src="<?php echo BASE_URL; ?>uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
            </div>
        </div>

        <div class="product-details">
            <div class="breadcrumb-nav">
                <a href="<?php echo BASE_URL; ?>index.php">Anasayfa</a> / 
                <a href="<?php echo BASE_URL; ?>shop/search.php">Ürünler</a> / 
                <span><?php echo htmlspecialchars($product['title']); ?></span>
            </div>

            <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>

            <div class="rating-section">
                <div class="stars-display">
                    <span class="star-rating">
                        <?php 
                        for($i = 0; $i < 5; $i++) {
                            echo ($i < $avg_rating) ? '★' : '☆';
                        }
                        ?>
                    </span>
                    <span class="rating-text"><?php echo $avg_rating; ?>/5 (<?php echo count($reviews); ?> yorum)</span>
                </div>
            </div>

            <div class="price-section">
                <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                <div class="stock-badge <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                    <?php echo $product['stock'] > 0 ? 'Stokta var (' . $product['stock'] . ')' : 'Stok tükendi'; ?>
                </div>
            </div>

            <p class="product-desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <div class="product-cta">
                <div class="action-buttons">
                    <?php if($product['stock'] > 0): ?>
                        <a href="<?php echo BASE_URL; ?>core/cart-helper.php?action=add&id=<?php echo $product['id']; ?>" class="btn-primary btn-large">🛒 Sepete Ekle</a>
                    <?php else: ?>
                        <button disabled class="btn-primary btn-large" style="opacity: 0.5;">Stok Tükendi</button>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>core/cart-helper.php?action=<?php echo $is_wishlisted ? 'remove-wish' : 'add-wish'; ?>&id=<?php echo $product['id']; ?>" class="btn-outline btn-icon" title="Favorilere ekle">
                            <?php echo $is_wishlisted ? '❤️' : '🤍'; ?> Favorilerim
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn-outline btn-icon">❤️ Favorilere Ekle</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-benefits">
                <div class="benefit-item">
                    <i class="fas fa-truck benefit-icon"></i>
                    <span>100 TL üzeri ücretsiz kargo</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-undo benefit-icon"></i>
                    <span>30 gün geri ödeme garantisi</span>
                </div>
                <div class="benefit-item">
                    <i class="fas fa-headset benefit-icon"></i>
                    <span>24 saat canlı destek</span>
                </div>
            </div>

            <script type="application/ld+json">
<?php echo json_encode([
    "@context" => "https://schema.org",
    "@type" => "Product",
    "name" => $product['title'],
    "image" => [BASE_URL . 'uploads/' . $product['image']],
    "description" => substr($product['description'],0,200),
    "sku" => $product['id'],
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => $avg_rating,
        "reviewCount" => count($reviews)
    ],
    "offers" => ["@type" => "Offer", "priceCurrency" => "USD", "price" => $product['price'], "availability" => $product['stock'] > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock"]
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT); ?>
            </script>
        </div>
    </div>

    <!-- YORUMLAr SEKSİYONU -->
    <section class="reviews-section">
        <h2 class="section-title"><i class="fas fa-comments"></i> Müşteri Yorumları</h2>
        <div class="reviews-grid">
            <aside class="review-form-card">
                <h3>Sizin Görüşünüz?</h3>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <form action="<?php echo BASE_URL; ?>core/add-review.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <label>Puanı</label>
                        <div class="star-selector">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>" class="star-label">★</label>
                            <?php endfor; ?>
                        </div>

                        <label>Yorumunuz</label>
                        <textarea name="comment" required placeholder="Ne hoşunuza gitti ya da gitmedini söyleyin..." maxlength="500" rows="5"></textarea>
                        <small class="muted">500 karaktere kadar</small>

                        <button type="submit" class="btn-primary" style="width: 100%; margin-top: 12px;">Yorumu Gönder</button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Yorum yapmak için <a href="<?php echo BASE_URL; ?>auth/login.php">giriş yapın</a></p>
                    </div>
                <?php endif; ?>
            </aside>

            <div class="reviews-list">
                <?php if(count($reviews) > 0): ?>
                    <?php foreach($reviews as $rev): ?>
                        <article class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <strong><?php echo htmlspecialchars($rev['full_name']); ?></strong>
                                    <div class="review-rating">
                                        <?php for($i = 0; $i < 5; $i++) {
                                            echo ($i < $rev['rating']) ? '★' : '☆';
                                        } ?>
                                    </div>
                                </div>
                                <time class="review-date"><?php echo date('d.m.Y', strtotime($rev['created_at'])); ?></time>
                            </div>
                            <p class="review-text"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews-msg">Henüz yorum yok. İlk yorumu siz yapın!</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- İLİŞKİLİ ÜRÜNLER -->
    <?php if(count($related_products) > 0): ?>
    <section class="related-products">
        <h2 class="section-title"><i class="fas fa-link"></i> İlişkili Ürünler</h2>
        <div class="products-carousel">
            <?php foreach($related_products as $rel_prod): ?>
                <div class="product-card">
                    <div class="card-image">
                        <img src="<?php echo BASE_URL; ?>uploads/<?php echo $rel_prod['image']; ?>" alt="<?php echo htmlspecialchars($rel_prod['title']); ?>">
                        <a href="<?php echo BASE_URL; ?>shop/product-detail.php?id=<?php echo $rel_prod['id']; ?>" class="view-btn">Görüntüle</a>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($rel_prod['title']); ?></h3>
                        <div class="card-price">$<?php echo number_format($rel_prod['price'], 2); ?></div>
                        <a href="<?php echo BASE_URL; ?>core/cart-helper.php?action=add&id=<?php echo $rel_prod['id']; ?>" class="btn-primary" style="width: 100%;">Sepete Ekle</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>