<?php
require_once '../config/init.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$product_query = $db->query("SELECT * FROM products ORDER BY id DESC");
$products = $product_query->fetchAll(PDO::FETCH_ASSOC);

$order_count_query = $db->query("SELECT COUNT(id) as total FROM orders");
$order_count = $order_count_query->fetch(PDO::FETCH_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Keccart | Admin Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <header class="admin-card admin-header">
            <h1 class="admin-title">Admin Dashboard</h1>
            <nav class="admin-nav">
                <a class="active" href="<?php echo BASE_URL; ?>admin/index.php">Ürünler</a>
                <a href="<?php echo BASE_URL; ?>admin/three-d-requests.php">3D Talepler</a>
                <a href="<?php echo BASE_URL; ?>admin/export-audit.php">Audit Log</a>
                <a href="<?php echo BASE_URL; ?>admin/orders.php">Siparişler (<?php echo $order_count; ?>)</a>
                <a href="<?php echo BASE_URL; ?>index.php">Siteye Dön</a>
            </nav>
        </header>

        <div class="admin-stat-grid">
            <div class="admin-stat-card">
                <h3>Toplam Ürün</h3>
                <p><?php echo count($products); ?></p>
            </div>
            <div class="admin-stat-card">
                <h3>Toplam Sipariş</h3>
                <p><?php echo $order_count; ?></p>
            </div>
        </div>

        <section class="admin-card admin-section">
            <div class="admin-header" style="margin-bottom:18px;">
                <h3 class="admin-title" style="font-size:18px;">Ürün Yönetimi</h3>
                <a href="<?php echo BASE_URL; ?>admin/add-product.php" class="btn-primary">+ Yeni Ürün Ekle</a>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Görsel</th>
                        <th>Ürün Adı</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th style="text-align:right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td>
                            <img src="<?php echo BASE_URL; ?>uploads/<?php echo $p['image']; ?>" width="50" height="50" style="object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                        </td>
                        <td><?php echo htmlspecialchars($p['title']); ?></td>
                        <td>$<?php echo number_format($p['price'], 2); ?></td>
                        <td><span class="status-badge status-pending"><?php echo $p['stock']; ?></span></td>
                        <td>
                            <div class="admin-actions">
                                <a href="<?php echo BASE_URL; ?>admin/edit.php?id=<?php echo $p['id']; ?>" class="link-primary">Düzenle</a>
                                <form method="POST" action="<?php echo BASE_URL; ?>admin/delete.php" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?');">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" class="link-muted" style="background:none;border:none;cursor:pointer;">Sil</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>