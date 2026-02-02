<?php 
require_once __DIR__ . '/../config/init.php'; 

$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

$root = BASE_URL;
$page_title = isset($page_title) ? $page_title . " | TEMUNGUR" : "TEMUNGUR - Güvenli Alışveriş";
$skipFilters = ['aksesuar','figur','figür','hobi','din','other','size','size özel','sizeözel'];
$skipIds = [11];
$categories = [];
try {
    $cat_query = $db->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $cat_query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Temungur e-ticaret platformu. En uygun fiyatlı ürünler.">
    <title><?php echo $page_title; ?></title>
    
    <link rel="icon" type="image/png" href="<?php echo $root; ?>assets/img/logo.png">
    <link rel="stylesheet" href="<?php echo $root; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="main-header">
    <div class="container header-wrapper">
        <button class="menu-toggle" id="openMenu" aria-label="Menü">☰</button>
        
        <div class="logo-area">
            <a href="<?php echo $root; ?>index.php" class="brand-link">
                <img src="<?php echo $root; ?>assets/img/logo.png" alt="Temungur" class="main-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span class="logo-text" style="display:none;">TEMUNGUR</span>
            </a>
        </div>
        
        <div class="search-area">
            <form action="<?php echo $root; ?>shop/search.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Ürün veya marka ara..." required aria-label="Arama">
                <button type="submit" class="search-btn">Ara</button>
            </form>
        </div>

        <nav class="user-nav">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="dropdown" id="userDropdownContainer">
                    <button onclick="toggleUserMenu(event)" class="dropdown-trigger" id="userBtn">
                        <span class="user-greeting">Merhaba, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?></span>
                        <i class="arrow-icon" id="arrowSymbol">▼</i>
                    </button>
                    <div class="dropdown-menu" id="myDropdown">
                        <div class="menu-header">
                            <small>Hesap Yönetimi</small>
                            <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
                        </div>
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="<?php echo $root; ?>admin/index.php" class="menu-item admin-link">🛠 Admin Paneli</a>
                        <?php endif; ?>
                        <a href="<?php echo $root; ?>account/profile.php" class="menu-item">👤 Profilim</a>
                        <a href="<?php echo $root; ?>account/my-orders.php" class="menu-item">📦 Siparişlerim</a>
                        <a href="<?php echo $root; ?>auth/logout.php" class="menu-item logout-link">🚪 Çıkış Yap</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="<?php echo $root; ?>auth/login.php" class="btn-login">Giriş Yap</a>
                    <a href="<?php echo $root; ?>auth/register.php" class="btn-register">Kayıt Ol</a>
                </div>
            <?php endif; ?>
            
            <a href="<?php echo $root; ?>shop/cart.php" class="cart-anchor">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-text">Sepet</span>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            </a>
        </nav>

    </div>
</header>

<div class="side-overlay" id="sideOverlay"></div>
<aside class="side-menu" id="sideMenu">
    <div class="side-menu-header">
        <strong>Kategoriler</strong>
        <button class="side-close" id="closeMenu" aria-label="Kapat">✕</button>
    </div>
    <nav class="side-menu-links">
        <form class="side-search" action="<?php echo $root; ?>shop/search.php" method="GET">
            <input type="text" name="q" placeholder="Ürün ara..." required>
            <button type="submit">Ara</button>
        </form>
        <a href="<?php echo $root; ?>index.php"><i class="fas fa-home menu-icon"></i>Tüm Ürünler</a>
        <a href="<?php echo $root; ?>index.php?filter=aksesuar"><i class="fas fa-puzzle-piece menu-icon"></i>Aksesuar</a>
        <a href="<?php echo $root; ?>index.php?filter=figur"><i class="fas fa-cube menu-icon"></i>Figürler</a>
        <a href="<?php echo $root; ?>index.php?filter=hobi"><i class="fas fa-wrench menu-icon"></i>Hobi</a>
        <a href="<?php echo $root; ?>index.php?filter=din"><i class="fas fa-book menu-icon"></i>Din</a>
        <a href="<?php echo $root; ?>index.php?filter=other"><i class="fas fa-ellipsis-h menu-icon"></i>Diğer Ürünler</a>
        <?php foreach($categories as $cat): ?>
            <?php
                if (in_array((int)$cat['id'], $skipIds)) continue;
                $lname = mb_strtolower(trim($cat['name']), 'UTF-8');
                $skip = false;
                foreach($skipFilters as $sf) {
                    if (mb_stripos($lname, $sf, 0, 'UTF-8') !== false) { $skip = true; break; }
                }
                if ($skip) continue;
            ?>
            <a href="<?php echo $root; ?>index.php?category=<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
        <?php endforeach; ?>
        <a href="<?php echo $root; ?>shop/wishlist.php"><i class="fas fa-heart menu-icon"></i>Favorilerim</a>
        <a href="<?php echo $root; ?>custom-size.php"><i class="fas fa-star menu-icon"></i>Size Özel</a>
    </nav>
</aside>

<script>
function toggleUserMenu(event) {
    event.stopPropagation();
    const dropdown = document.getElementById("myDropdown");
    const arrow = document.getElementById("arrowSymbol");
    
    const isShown = dropdown.classList.toggle("show-menu");
    if(arrow) {
        arrow.style.transform = isShown ? "rotate(180deg)" : "rotate(0deg)";
    }
}

window.onclick = function(event) {
    if (!event.target.closest('.dropdown')) {
        const dropdowns = document.getElementsByClassName("dropdown-menu");
        const arrow = document.getElementById("arrowSymbol");
        for (let menu of dropdowns) {
            if (menu.classList.contains('show-menu')) {
                menu.classList.remove('show-menu');
                if(arrow) arrow.style.transform = "rotate(0deg)";
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    if (localStorage.getItem('keccart_theme') === '3d') {
        body.classList.add('theme-3d');
    }
    document.addEventListener('click', function(e){
        if(e.target && e.target.id === 'threeDClose') {
            body.classList.remove('theme-3d');
            localStorage.removeItem('keccart_theme');
        }
    });

    const openMenu = document.getElementById('openMenu');
    const closeMenu = document.getElementById('closeMenu');
    const sideMenu = document.getElementById('sideMenu');
    const sideOverlay = document.getElementById('sideOverlay');
    if (openMenu && closeMenu && sideMenu && sideOverlay) {
        const open = () => { sideMenu.classList.add('open'); sideOverlay.classList.add('show'); };
        const close = () => { sideMenu.classList.remove('open'); sideOverlay.classList.remove('show'); };
        openMenu.addEventListener('click', open);
        closeMenu.addEventListener('click', close);
        sideOverlay.addEventListener('click', close);
    }
});
</script>