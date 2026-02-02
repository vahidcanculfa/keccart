<?php
require_once 'config/init.php';
include 'includes/header.php';
http_response_code(404);
?>

<main class="container">
    <div class="error-page">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title">Sayfa Bulunamadı</h1>
            <p class="error-message">Aradığınız sayfa maalesef bulunmamaktadır.</p>
            
            <div class="error-suggestions">
                <p class="suggestions-title">Şunları deneyebilirsiniz:</p>
                <ul class="suggestions-list">
                    <li><i class="fas fa-home"></i> <a href="<?php echo BASE_URL; ?>index.php">Anasayfaya dön</a></li>
                    <li><i class="fas fa-search"></i> <a href="<?php echo BASE_URL; ?>shop/search.php">Ürün ara</a></li>
                    <li><i class="fas fa-envelope"></i> <a href="<?php echo BASE_URL; ?>#contact">İletişime geç</a></li>
                    <li><i class="fas fa-heart"></i> <a href="<?php echo BASE_URL; ?>shop/wishlist.php">Favorilerime git</a></li>
                </ul>
            </div>

            <div class="error-actions">
                <a href="<?php echo BASE_URL; ?>index.php" class="btn-primary">Anasayfaya Dön</a>
                <a href="<?php echo BASE_URL; ?>shop/search.php" class="btn-outline">Alışveriş Yap</a>
            </div>

            <div class="error-illustration">
                <div style="font-size: 120px; margin-bottom: 20px;">😢</div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
