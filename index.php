<?php 
require_once 'config/db.php'; 
include 'includes/header.php'; 

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$filter = isset($_GET['filter']) ? $_GET['filter'] : null;

$cat_query = $db->query("SELECT * FROM categories");
$categories = $cat_query->fetchAll(PDO::FETCH_ASSOC);

if ($filter === 'other') {
    $query = $db->prepare("SELECT p.* FROM products p JOIN categories c ON p.category_id = c.id WHERE LOWER(c.name) NOT LIKE ? AND LOWER(c.name) NOT LIKE ? ORDER BY p.id DESC");
    $query->execute(['%din%','%hobi%']);
} elseif (in_array($filter, ['aksesuar','figur','figür','hobi','din'])) {
    $search = "%" . $filter . "%";
    $query = $db->prepare("SELECT p.* FROM products p JOIN categories c ON p.category_id = c.id WHERE LOWER(c.name) LIKE ? ORDER BY p.id DESC");
    $query->execute([$search]);
} elseif ($category_id) {
    $query = $db->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY id DESC");
    $query->execute([$category_id]);
} else {
    $query = $db->query("SELECT * FROM products ORDER BY id DESC");
}
$products = $query->fetchAll(PDO::FETCH_ASSOC);

$best_products = array_slice($products, 0, 8);
$new_products = array_slice($products, 0, 8);
$sale_products = array_slice($products, 0, 8);

$slides = [
    [
        'image' => 'assets/img/banner1.jpg',
        'title' => 'Bugün Keccart\'ta neyi keşfetmek istersin?',
        'description' => 'Sana özel seçilmiş en yeni koleksiyonları incele.',
        'link' => 'index.php'
    ],
    [
        'image' => 'assets/img/banner2.jpg',
        'title' => 'Yeni Sezon Ürünleri Kapında',
        'description' => 'Trendleri yakala, tarzını Keccart ile yansıt.',
        'link' => 'index.php'
    ]
];
?>

<style>
    .category-nav-wrapper {
        background: #fff;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 999; 
        box-shadow: 0 6px 18px rgba(16,24,40,0.04);
    }
    .pill-container {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        padding: 5px 0;
    }
    .pill-container::-webkit-scrollbar { display: none; }
    
    .pill-item {
        padding: 8px 20px;
        border-radius: 999px;
        background: #ffffff;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--border-color);
        transition: 0.2s all ease;
        box-shadow: 0 1px 2px rgba(16,24,40,0.04);
    }
    .pill-item:hover { background: #f8f9fa; border-color: #cfd8e3; color: var(--text-main); }
    .pill-item.active { background: #e8f0fe; color: var(--primary); border-color: #b9d1ff; }
    .pill-button { margin-left: auto; flex-shrink: 0; background: var(--primary); color: #fff; border-color: var(--primary); padding: 8px 18px; border-radius: 999px; font-weight: 700; }
    .pill-button:hover { background: var(--primary-hover); color: #fff; }

    .hero-slider {
        margin: 20px auto;
        max-width: 1200px;
        border-radius: 22px;
        overflow: hidden;
        min-height: 240px;
        position: relative;
        z-index: 1; 
        box-shadow: var(--shadow);
    }
    .slider-track {
        display: flex;
        height: 100%;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .slider-item {
        min-width: 100%;
        min-height: 350px;
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
    }
    .slider-content {
        background: rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(10px);
        padding: 28px 32px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.28);
    }
    .slider-content h2 { font-size: 30px; margin-bottom: 10px; }
    .slider-content .btn {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 25px;
        background: #fff;
        color: #1a73e8;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
    }

    .slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 20px;
        z-index: 10;
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .slider-arrow:hover { background: rgba(255, 255, 255, 0.4); }
    .prev-arrow { left: 20px; }
    .next-arrow { right: 20px; }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 22px;
        margin-top: 20px;
    }
    .product-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        transition: 0.3s;
        box-shadow: 0 6px 18px rgba(16,24,40,0.04);
    }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.06); }
    .add-to-cart {
        background: #1a73e8;
        color: white;
        text-align: center;
        display: block;
        padding: 12px;
        border-radius: 10px;
        font-weight: 500;
        transition: 0.3s;
    }
    .add-to-cart:hover { background: #1557b0; }
    .home-section { padding: 24px 0; }
    .home-title { font-size: 22px; font-weight: 700; color: var(--text-main); margin-bottom: 18px; }
    .trust-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
    .trust-card { background: #fff; border:1px solid var(--border-color); border-radius: 14px; padding: 16px; box-shadow: 0 6px 18px rgba(16,24,40,0.04); }
    .trust-title { font-weight: 700; margin-bottom: 6px; }
    .trust-text { color: var(--text-muted); font-size: 13px; }
    .trust-strip { background:#fff; border:1px solid var(--border-color); border-radius: 999px; padding: 10px 16px; display:flex; gap:18px; align-items:center; justify-content:center; box-shadow: 0 6px 18px rgba(16,24,40,0.04); flex-wrap: wrap; }
    .trust-strip-item { color: var(--text-muted); font-size: 13px; display:flex; align-items:center; gap:8px; }
    .trust-dot { width: 6px; height: 6px; border-radius: 999px; background: var(--primary); display:inline-block; }
    .testimonials { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
    .testimonial-card { background: #fff; border:1px solid var(--border-color); border-radius: 14px; padding: 16px; box-shadow: 0 6px 18px rgba(16,24,40,0.04); }
    .testimonial-name { font-weight: 700; margin-top: 10px; }
    .brands { display:flex; flex-wrap: wrap; gap: 10px; }
    .brand-chip { padding: 8px 12px; border-radius: 999px; background: #fff; border:1px solid var(--border-color); color: var(--text-muted); font-size: 12px; }
    .promo-bar { background: linear-gradient(90deg, #e8f0fe, #f6f8fb); border: 1px solid var(--border-color); border-radius: 999px; padding: 10px 16px; display:flex; align-items:center; justify-content:center; gap:16px; color: var(--text-main); font-size: 13px; }
    .promo-cta { color: var(--primary); font-weight: 700; text-decoration: none; }
    .collection-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
    .collection-card { background: #fff; border:1px solid var(--border-color); border-radius: 16px; overflow:hidden; box-shadow: 0 6px 18px rgba(16,24,40,0.04); }
    .collection-card .image { height: 140px; background: linear-gradient(135deg, #eef3ff, #f8fafc); display:flex; align-items:center; justify-content:center; color: var(--primary); font-weight: 800; }
    .collection-card .content { padding: 14px; }
    .collection-card .title { font-weight: 700; margin-bottom: 6px; }
    .collection-card .text { color: var(--text-muted); font-size: 13px; }
    .tabs { display:flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
    .tab-btn { padding: 8px 14px; border-radius: 999px; border:1px solid var(--border-color); background: #fff; color: var(--text-muted); font-weight: 600; cursor: pointer; }
    .tab-btn.active { background: #e8f0fe; color: var(--primary); border-color: #b9d1ff; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .stats-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
    .stat-card { background: #fff; border:1px solid var(--border-color); border-radius: 14px; padding: 16px; box-shadow: 0 6px 18px rgba(16,24,40,0.04); }
    .stat-value { font-size: 24px; font-weight: 800; color: var(--text-main); }
    .stat-label { color: var(--text-muted); font-size: 12px; }
    .newsletter { background: #fff; border:1px solid var(--border-color); border-radius: 16px; padding: 18px; display:flex; gap: 12px; align-items:center; justify-content:space-between; flex-wrap: wrap; box-shadow: 0 6px 18px rgba(16,24,40,0.04); }
    .newsletter input { padding: 10px 12px; border-radius: 8px; border:1px solid var(--border-color); min-width: 220px; }
    .quick-links { display:flex; flex-wrap: wrap; gap: 10px; }
    .quick-links a { padding: 8px 12px; border-radius: 999px; background: #fff; border:1px solid var(--border-color); text-decoration: none; color: var(--text-muted); font-size: 12px; }
    @media (max-width: 576px) {
        .home-section { padding: 18px 0; }
    }
</style>

<main style="background: linear-gradient(180deg, #f7f9fc, #fafafa);">
    <section class="container home-section">
        <div class="promo-bar">
            <span>Ücretsiz kargo: ₺750+ siparişlerde</span>
            <span>•</span>
            <span>24–48 saatte kargoda</span>
            <a class="promo-cta" href="index.php">Kampanyalara Bak</a>
        </div>
    </section>

    <section class="hero-slider">
        <button class="slider-arrow prev-arrow" onclick="moveSlide(-1)">&#10094;</button>
        <div class="slider-track">
            <?php foreach($slides as $slide): ?>
                <div class="slider-item" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?php echo $slide['image']; ?>');">
                    <div class="slider-content">
                        <h2><?php echo htmlspecialchars($slide['title']); ?></h2>
                        <p><?php echo htmlspecialchars($slide['description']); ?></p>
                        <a href="<?php echo $slide['link']; ?>" class="btn">Keşfetmeye Başla</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="slider-arrow next-arrow" onclick="moveSlide(1)">&#10095;</button>
    </section>


    <section class="container home-section">
        <div class="home-title">Koleksiyonlar</div>
        <div class="collection-grid">
            <div class="collection-card">
                <div class="image">3D</div>
                <div class="content">
                    <div class="title">Minimal Tasarımlar</div>
                    <div class="text">Modern ve sade parçalar</div>
                </div>
            </div>
            <div class="collection-card">
                <div class="image">LAB</div>
                <div class="content">
                    <div class="title">Maker Koleksiyonu</div>
                    <div class="text">Hobi ve deney setleri</div>
                </div>
            </div>
            <div class="collection-card">
                <div class="image">PRO</div>
                <div class="content">
                    <div class="title">Profesyonel Setler</div>
                    <div class="text">Üretim odaklı seçimler</div>
                </div>
            </div>
        </div>
    </section>

    <section class="container" style="padding: 24px 20px 40px;">
        <h3 style="color: #202124; font-size: 24px; margin-bottom: 14px; font-weight: 600;">
            <?php 
                if($category_id) {
                    $current_cat_name = 'Products';
                    foreach($categories as $c) {
                        if($c['id'] == $category_id) {
                            $current_cat_name = $c['name'];
                            break;
                        }
                    }
                    echo htmlspecialchars($current_cat_name);
                } else {
                    echo "Öne Çıkan Ürünler";
                }
            ?>
        </h3>
        <div class="tabs">
            <button class="tab-btn active" data-tab="tab-best">En Çok Satanlar</button>
            <button class="tab-btn" data-tab="tab-new">Yeni Gelenler</button>
            <button class="tab-btn" data-tab="tab-sale">İndirimde</button>
        </div>

        <div id="tab-best" class="tab-panel active">
            <div class="product-grid">
                <?php if(count($best_products) > 0): ?>
                    <?php foreach($best_products as $product): ?>
                        <div class="product-card">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none;">
                                <div class="product-image" style="height: 220px; background: #f8f9fa;">
                                    <img src="assets/img/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" style="width:100%; height:100%; object-fit: contain; padding: 10px;" loading="lazy">
                                </div>
                                <div class="product-info" style="padding: 15px;">
                                    <h4 style="color: #3c4043; font-size: 15px; margin-bottom: 10px; height: 40px; overflow: hidden;"><?php echo htmlspecialchars($product['title']); ?></h4>
                                    <p style="color: #202124; font-weight: 700; font-size: 18px;">$<?php echo number_format($product['price'], 2); ?></p>
                                </div>
                            </a>
                            <div style="padding: 0 15px 15px;">
                                <?php if($product['stock'] > 0): ?>
                                    <a href="core/cart-helper.php?action=add&id=<?php echo $product['id']; ?>" class="add-to-cart" style="text-decoration: none;">Sepete Ekle</a>
                                <?php else: ?>
                                    <button disabled style="width:100%; background:#f1f3f4; color:#9aa0a6; border:none; padding:12px; border-radius:10px;">Stokta Yok</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="tab-new" class="tab-panel">
            <div class="product-grid">
                <?php if(count($new_products) > 0): ?>
                    <?php foreach($new_products as $product): ?>
                        <div class="product-card">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none;">
                                <div class="product-image" style="height: 220px; background: #f8f9fa;">
                                    <img src="assets/img/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" style="width:100%; height:100%; object-fit: contain; padding: 10px;" loading="lazy">
                                </div>
                                <div class="product-info" style="padding: 15px;">
                                    <h4 style="color: #3c4043; font-size: 15px; margin-bottom: 10px; height: 40px; overflow: hidden;"><?php echo htmlspecialchars($product['title']); ?></h4>
                                    <p style="color: #202124; font-weight: 700; font-size: 18px;">$<?php echo number_format($product['price'], 2); ?></p>
                                </div>
                            </a>
                            <div style="padding: 0 15px 15px;">
                                <?php if($product['stock'] > 0): ?>
                                    <a href="core/cart-helper.php?action=add&id=<?php echo $product['id']; ?>" class="add-to-cart" style="text-decoration: none;">Sepete Ekle</a>
                                <?php else: ?>
                                    <button disabled style="width:100%; background:#f1f3f4; color:#9aa0a6; border:none; padding:12px; border-radius:10px;">Stokta Yok</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="tab-sale" class="tab-panel">
            <div class="product-grid">
                <?php if(count($sale_products) > 0): ?>
                    <?php foreach($sale_products as $product): ?>
                        <div class="product-card">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none;">
                                <div class="product-image" style="height: 220px; background: #f8f9fa;">
                                    <img src="assets/img/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" style="width:100%; height:100%; object-fit: contain; padding: 10px;" loading="lazy">
                                </div>
                                <div class="product-info" style="padding: 15px;">
                                    <h4 style="color: #3c4043; font-size: 15px; margin-bottom: 10px; height: 40px; overflow: hidden;"><?php echo htmlspecialchars($product['title']); ?></h4>
                                    <p style="color: #202124; font-weight: 700; font-size: 18px;">$<?php echo number_format($product['price'], 2); ?></p>
                                </div>
                            </a>
                            <div style="padding: 0 15px 15px;">
                                <?php if($product['stock'] > 0): ?>
                                    <a href="core/cart-helper.php?action=add&id=<?php echo $product['id']; ?>" class="add-to-cart" style="text-decoration: none;">Sepete Ekle</a>
                                <?php else: ?>
                                    <button disabled style="width:100%; background:#f1f3f4; color:#9aa0a6; border:none; padding:12px; border-radius:10px;">Stokta Yok</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="container home-section">
        <div class="home-title">Neden Keccart?</div>
        <div class="trust-strip">
            <div class="trust-strip-item"><span class="trust-dot"></span>Hızlı Teslimat • 24–48 saat</div>
            <div class="trust-strip-item"><span class="trust-dot"></span>Güvenli Ödeme</div>
            <div class="trust-strip-item"><span class="trust-dot"></span>Kolay İade • 14 gün</div>
        </div>
    </section>

    <section class="container home-section">
        <div class="home-title">Müşteri Yorumları</div>
        <div class="testimonials">
            <div class="testimonial-card">
                <div>Ürün kalitesi beklediğimden iyi çıktı, paketleme özenli.</div>
                <div class="testimonial-name">Buse K.</div>
            </div>
            <div class="testimonial-card">
                <div>Hızlı kargo ve sorunsuz teslimat. Tekrar alışveriş yaparım.</div>
                <div class="testimonial-name">Mert A.</div>
            </div>
            <div class="testimonial-card">
                <div>Size Özel talebimde hızlı dönüş aldım, çok memnun kaldım.</div>
                <div class="testimonial-name">Selin D.</div>
            </div>
        </div>
    </section>

    <section class="container home-section">
        <div class="home-title">Rakamlarla Keccart</div>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-value">10K+</div><div class="stat-label">Mutlu Müşteri</div></div>
            <div class="stat-card"><div class="stat-value">4.8/5</div><div class="stat-label">Memnuniyet</div></div>
            <div class="stat-card"><div class="stat-value">24 Saat</div><div class="stat-label">Ortalama Dönüş</div></div>
            <div class="stat-card"><div class="stat-value">48 Saat</div><div class="stat-label">Kargoda</div></div>
        </div>
    </section>

    <section class="container home-section">
        <div class="newsletter">
            <div>
                <div class="home-title" style="margin-bottom:6px;">Bülten</div>
                <div class="trust-text">Yeni ürünler ve kampanyaları kaçırma.</div>
            </div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap: wrap;">
                <input type="email" placeholder="E-posta adresin">
                <a class="btn-primary" href="index.php" style="padding:10px 16px;">Abone Ol</a>
            </div>
        </div>
    </section>

    <section class="container home-section" style="padding-bottom: 80px;">
        <div class="home-title">Markalar</div>
        <div class="brands">
            <span class="brand-chip">Keccart Studio</span>
            <span class="brand-chip">PrintLab</span>
            <span class="brand-chip">Nova3D</span>
            <span class="brand-chip">CoreParts</span>
            <span class="brand-chip">UrbanCraft</span>
        </div>
    </section>

    <section class="container home-section" style="padding-bottom: 80px;">
        <div class="home-title">Hızlı Kategoriler</div>
        <div class="quick-links">
            <a href="index.php?filter=aksesuar">Aksesuar</a>
            <a href="index.php?filter=figur">Figür</a>
            <a href="index.php?filter=hobi">Hobi</a>
            <a href="index.php?filter=din">Din</a>
            <a href="size-ozel.php">Size Özel</a>
        </div>
    </section>
</main>

<script>
    let idx = 0;
    const track = document.querySelector('.slider-track');
    const items = document.querySelectorAll('.slider-item');

    function updateSlider() {
        if(!track) return;
        track.style.transform = `translateX(-${idx * 100}%)`;
    }

    function moveSlide(direction) {
        idx += direction;
        if (idx >= items.length) idx = 0;
        if (idx < 0) idx = items.length - 1;
        updateSlider();
    }

    let autoSlide = setInterval(() => moveSlide(1), 5000);

    document.querySelectorAll('.slider-arrow').forEach(arrow => {
        arrow.addEventListener('click', () => {
            clearInterval(autoSlide);
            autoSlide = setInterval(() => moveSlide(1), 5000);
        });
    });

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            const id = btn.getAttribute('data-tab');
            const panel = document.getElementById(id);
            if(panel) panel.classList.add('active');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>