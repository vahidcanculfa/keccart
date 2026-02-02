<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

$keyword = isset($_GET['q']) ? substr(trim($_GET['q']), 0, 150) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$category_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// Sorgu oluştur
$base_query = "SELECT * FROM products WHERE (title LIKE ? OR description LIKE ?)";
$params = ["%$keyword%", "%$keyword%"];

if($category_id > 0) {
    $base_query .= " AND category_id = ?";
    $params[] = $category_id;
}

// Sıralama
switch($sort) {
    case 'price-low': $base_query .= " ORDER BY price ASC"; break;
    case 'price-high': $base_query .= " ORDER BY price DESC"; break;
    case 'popular': $base_query .= " ORDER BY id DESC LIMIT 50"; break; // En çok satanları simüle et
    default: $base_query .= " ORDER BY id DESC";
}

$query = $db->prepare($base_query);
$query->execute($params);
$results = $query->fetchAll(PDO::FETCH_ASSOC);

// Kategorileri getir
$cat_query = $db->prepare("SELECT DISTINCT category_id FROM products");
$cat_query->execute();
$categories = $cat_query->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container">
    <div class="search-header">
        <h1 class="page-title"><i class="fas fa-search"></i> Arama Sonuçları</h1>
        <p class="result-count">"<?php echo htmlspecialchars($keyword); ?>" için <strong><?php echo count($results); ?></strong> ürün bulundu</p>
    </div>

    <div class="search-layout">
        <!-- FİLTRELER -->
        <aside class="search-filters">
            <div class="filter-section">
                <h3 class="filter-title">Filtrele</h3>
                
                <form method="GET" id="filterForm">
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($keyword); ?>">
                    
                    <!-- Kategori Filtresi -->
                    <div class="filter-group">
                        <label class="filter-label">Kategori</label>
                        <select name="cat" onchange="document.getElementById('filterForm').submit();" class="filter-select">
                            <option value="0">Tüm Kategoriler</option>
                            <option value="1" <?php echo $category_id == 1 ? 'selected' : ''; ?>>Aksesuar</option>
                            <option value="2" <?php echo $category_id == 2 ? 'selected' : ''; ?>>Figürler</option>
                            <option value="3" <?php echo $category_id == 3 ? 'selected' : ''; ?>>Hobi</option>
                        </select>
                    </div>

                    <!-- Fiyat Filtresi -->
                    <div class="filter-group">
                        <label class="filter-label">Sıralama</label>
                        <select name="sort" onchange="document.getElementById('filterForm').submit();" class="filter-select">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>En Yeni</option>
                            <option value="price-low" <?php echo $sort == 'price-low' ? 'selected' : ''; ?>>Fiyat: Düşük → Yüksek</option>
                            <option value="price-high" <?php echo $sort == 'price-high' ? 'selected' : ''; ?>>Fiyat: Yüksek → Düşük</option>
                            <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>En Popüler</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="filter-section">
                <a href="<?php echo BASE_URL; ?>shop/search.php?q=<?php echo urlencode($keyword); ?>" class="clear-filters">Filtreleri Temizle</a>
            </div>
        </aside>

        <!-- ÜRÜNLER -->
        <section class="search-results">
            <?php if(count($results) > 0): ?>
                <div class="products-grid">
                    <?php foreach($results as $product): ?>
                        <div class="product-card">
                            <div class="card-image">
                                <img src="<?php echo BASE_URL; ?>uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <a href="<?php echo BASE_URL; ?>shop/product-detail.php?id=<?php echo $product['id']; ?>" class="overlay-link">
                                    Detayları Görüntüle
                                </a>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars(substr($product['title'], 0, 40)); ?></h3>
                                <p class="card-desc"><?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></p>
                                <div class="card-footer">
                                    <span class="card-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <a href="<?php echo BASE_URL; ?>core/cart-helper.php?action=add&id=<?php echo $product['id']; ?>" class="card-btn">Ekle</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">😢</div>
                    <h2>Sonuç bulunamadı</h2>
                    <p>"<?php echo htmlspecialchars($keyword); ?>" ile ilgili ürün bulunamadı.</p>
                    <p>Lütfen farklı bir arama terimi deneyin.</p>
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn-primary" style="display: inline-block; margin-top: 16px; padding: 12px 24px;">Anasayfaya Dön</a>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include '../includes/footer.php'; ?>