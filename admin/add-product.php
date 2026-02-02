<?php
require_once '../config/init.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Geçersiz istek (CSRF doğrulaması başarısız).');
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    
    $image_name = "default.jpg";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            die('Dosya çok büyük. Maksimum 2MB.');
        }

        $extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowed_ext)) {
            die('Geçersiz dosya türü.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed_mime)) {
            die('Dosya görüntü değil veya desteklenmeyen MIME türü.');
        }

        if (!@getimagesize($_FILES['image']['tmp_name'])) {
            die('Geçersiz görüntü dosyası.');
        }

        $image_name = time() . "_" . uniqid() . "." . $extension;
        $target_file = $target_dir . $image_name;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            die('Dosya yüklenirken hata oluştu.');
        }
        @chmod($target_file, 0644);
    }
    
    $query = $db->prepare("INSERT INTO products (title, description, price, stock, image, category_id) VALUES (?, ?, ?, ?, ?, ?)");
    $query->execute([$title, $description, $price, $stock, $image_name, $category_id]);
    
    header("Location: index.php?success=1");
    exit;
}

$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Ekle | Keccart Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-container" style="max-width:600px;">
        <div class="admin-card">
            <div class="admin-header" style="margin-bottom:18px;">
                <h2 class="admin-title" style="font-size:18px;">Yeni Ürün Ekle</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div>
                    <label>Ürün Görseli</label>
                    <div class="admin-upload">
                        <input type="file" name="image" accept="image/*" class="admin-input">
                        <div class="form-hint">PNG, JPG veya WEBP (Max. 2MB)</div>
                    </div>
                </div>
                
                <div>
                    <label>Kategori</label>
                    <select name="category_id" required class="admin-select">
                        <option value="">Kategori Seçin</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Ürün Başlığı</label>
                    <input type="text" name="title" required placeholder="Örn: Kablosuz Kulaklık" class="admin-input">
                </div>

                <div>
                    <label>Açıklama</label>
                    <textarea name="description" placeholder="Ürün özelliklerini detaylandırın..." class="admin-textarea"></textarea>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
                    <div>
                        <label>Fiyat ($)</label>
                        <input type="number" step="0.01" name="price" required placeholder="0.00" class="admin-input">
                    </div>
                    <div>
                        <label>Stok Adedi</label>
                        <input type="number" name="stock" required placeholder="99" class="admin-input">
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;">Ürünü Yayınla</button>
                
                <a href="<?php echo BASE_URL; ?>admin/index.php" class="admin-back">Vazgeç ve Panele Dön</a>
            </form>
        </div>
    </div>
</body>
</html>