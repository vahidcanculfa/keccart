<?php
require_once '../config/init.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = $db->prepare("SELECT * FROM products WHERE id = ?");
$query->execute([$id]);
$product = $query->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Ürün bulunamadı!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $update = $db->prepare("UPDATE products SET title = ?, description = ?, price = ?, stock = ? WHERE id = ?");
    if ($update->execute([$title, $description, $price, $stock, $id])) {
        header("Location: " . BASE_URL . "admin/index.php?updated=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürünü Düzenle | Keccart Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body style="background:#f4f7f6; padding:50px; font-family: 'Roboto', sans-serif;">
    <div class="container" style="max-width:650px; background:white; padding:40px; border-radius:15px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); margin: 0 auto;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px;">
            <h2 style="margin:0; color:#202124; font-size: 24px;">Ürünü Düzenle</h2>
            <img src="<?php echo BASE_URL; ?>uploads/<?php echo $product['image']; ?>" width="60" height="60" style="object-fit: cover; border-radius: 10px; border: 1px solid #eee;">
        </div>

        <form method="POST">
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color: #3c4043; font-size: 14px;">Ürün Başlığı</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required style="width:100%; padding:12px; border:1px solid #dadce0; border-radius:8px; box-sizing: border-box; font-size: 15px; outline-color: #1a73e8;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color: #3c4043; font-size: 14px;">Açıklama</label>
                <text area name="description" style="width:100%; padding:12px; border:1px solid #dadce0; border-radius:8px; height:120px; box-sizing: border-box; font-size: 15px; font-family: inherit; outline-color: #1a73e8;"><?php echo htmlspecialchars($product['description']); ?></text area>
            </div>

            <div style="display:flex; gap:20px; margin-bottom:35px;">
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color: #3c4043; font-size: 14px;">Fiyat ($)</label>
                    <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required style="width:100%; padding:12px; border:1px solid #dadce0; border-radius:8px; box-sizing: border-box; font-size: 15px; outline-color: #1a73e8;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color: #3c4043; font-size: 14px;">Stok Adedi</label>
                    <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required style="width:100%; padding:12px; border:1px solid #dadce0; border-radius:8px; box-sizing: border-box; font-size: 15px; outline-color: #1a73e8;">
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" style="background:#1a73e8; color:white; border:none; padding:16px; border-radius:30px; cursor:pointer; width:100%; font-size:16px; font-weight:bold; box-shadow: 0 4px 12px rgba(26,115,232,0.3);">Değişiklikleri Kaydet</button>
                <a href="<?php echo BASE_URL; ?>admin/index.php" style="display:block; text-align:center; padding: 12px; text-decoration:none; color:#5f6368; font-size: 14px; font-weight: 500;">İptal Et</a>
            </div>
        </form>
    </div>
</body>
</html>