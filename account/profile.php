<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Profil güncellemeleri
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $address = $_POST['address'];

    $update = $db->prepare("UPDATE users SET full_name = ?, phone = ?, city = ?, address = ? WHERE id = ?");
    if ($update->execute([$full_name, $phone, $city, $address, $user_id])) {
        $_SESSION['user_name'] = $full_name; 
        $success_msg = "Profil bilgileriniz başarıyla güncellendi.";
    } else {
        $error_msg = "Güncelleme sırasında hata oluştu.";
    }
}

// Şifre değiştir
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password && strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$hashed, $user_id]);
            $success_msg = "Şifreniz başarıyla değiştirildi.";
            $active_tab = 'security';
        } else {
            $error_msg = "Parolalar eşleşmediği veya 6 karakterden kısa.";
        }
    } else {
        $error_msg = "Mevcut şifreniz hatalı.";
    }
}

$query = $db->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Siparişleri getir
$orders_query = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$orders_query->execute([$user_id]);
$orders = $orders_query->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container">
    <div class="profile-wrapper">
        <!-- HEADER -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h1 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <!-- SEKMELEr -->
        <div class="profile-tabs">
            <a href="<?php echo BASE_URL; ?>account/profile.php?tab=profile" class="tab-link <?php echo $active_tab == 'profile' ? 'active' : ''; ?>"><i class="fas fa-user"></i> Profil</a>
            <a href="<?php echo BASE_URL; ?>account/profile.php?tab=orders" class="tab-link <?php echo $active_tab == 'orders' ? 'active' : ''; ?>"><i class="fas fa-boxes"></i> Siparişlerim</a>
            <a href="<?php echo BASE_URL; ?>account/profile.php?tab=addresses" class="tab-link <?php echo $active_tab == 'addresses' ? 'active' : ''; ?>"><i class="fas fa-map-marker-alt"></i> Adreslerim</a>
            <a href="<?php echo BASE_URL; ?>account/profile.php?tab=security" class="tab-link <?php echo $active_tab == 'security' ? 'active' : ''; ?>"><i class="fas fa-lock"></i> Güvenlik</a>
        </div>

        <!-- TAB İÇERİKLERİ -->
        <div class="profile-content">
            <?php if($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div class="alert alert-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <!-- PROFİL TABI -->
            <?php if($active_tab == 'profile'): ?>
                <div class="tab-panel">
                    <h2 class="tab-title">Profil Bilgileri</h2>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label>Tam Adınız</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required class="form-input">
                        </div>

                        <div class="form-group">
                            <label>E-posta Adresi (Değiştirilemez)</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="form-input" style="background: var(--bg-light);cursor: not-allowed;">
                        </div>

                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="form-input">
                        </div>

                        <div class="form-group">
                            <label>Şehir</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" class="form-input">
                        </div>

                        <div class="form-group">
                            <label>Adres</label>
                            <textarea name="address" rows="4" class="form-textarea"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-primary btn-large">Değişiklikleri Kaydet</button>
                    </form>
                </div>

            <!-- SİPARİŞLER TABI -->
            <?php elseif($active_tab == 'orders'): ?>
                <div class="tab-panel">
                    <h2 class="tab-title">Siparişlerim</h2>
                    <?php if(count($orders) > 0): ?>
                        <div class="orders-list">
                            <?php foreach($orders as $order): 
                                $order_items_query = $db->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                                $order_items_query->execute([$order['id']]);
                                $item_count = $order_items_query->fetch(PDO::FETCH_ASSOC)['count'];
                            ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div>
                                            <h3 class="order-id">Sipariş #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                            <p class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></p>
                                        </div>
                                        <div class="order-status <?php echo 'status-' . $order['status']; ?>">
                                            <?php 
                                            $status_text = ['pending' => 'Beklemede', 'processing' => 'İşleniyor', 'completed' => 'Tamamlandı', 'cancelled' => 'İptal Edildi'];
                                            echo $status_text[$order['status']] ?? $order['status'];
                                            ?>
                                        </div>
                                    </div>
                                    <div class="order-details">
                                        <p><strong><?php echo $item_count; ?></strong> ürün • <strong>$<?php echo number_format($order['total_price'], 2); ?></strong></p>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>orders/order-detail.php?id=<?php echo $order['id']; ?>" class="order-detail-link">Detayları Görüntüle →</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">📦</div>
                            <p>Henüz siparişiniz yok.</p>
                            <a href="<?php echo BASE_URL; ?>index.php" class="btn-primary" style="display: inline-block; margin-top: 16px; padding: 10px 24px;">Alışveriş Yap</a>
                        </div>
                    <?php endif; ?>
                </div>

            <!-- ADRESLERİM TABI -->
            <?php elseif($active_tab == 'addresses'): ?>
                <div class="tab-panel">
                    <h2 class="tab-title">Adreslerim</h2>
                    <div class="address-list">
                        <div class="address-card">
                            <div class="address-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="address-content">
                                <h3><?php echo htmlspecialchars($user['city'] ?? 'Adres Belirtilmemiş'); ?></h3>
                                <p><?php echo htmlspecialchars($user['address'] ?? 'Adres eklemediğiniz'); ?></p>
                                <p class="address-phone"><?php echo htmlspecialchars($user['phone'] ?? 'Telefon eklemediğiniz'); ?></p>
                            </div>
                            <a href="<?php echo BASE_URL; ?>account/profile.php?tab=profile" class="edit-btn">Düzenle</a>
                        </div>
                    </div>
                </div>

            <!-- GÜVENLİK TABI -->
            <?php elseif($active_tab == 'security'): ?>
                <div class="tab-panel">
                    <h2 class="tab-title">Şifre Değiştir</h2>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label>Mevcut Şifre</label>
                            <input type="password" name="current_password" required class="form-input" placeholder="••••••••">
                        </div>

                        <div class="form-group">
                            <label>Yeni Şifre</label>
                            <input type="password" name="new_password" required class="form-input" placeholder="••••••••" minlength="6">
                            <small class="form-helper">En az 6 karakter</small>
                        </div>

                        <div class="form-group">
                            <label>Yeni Şifreyi Onayla</label>
                            <input type="password" name="confirm_password" required class="form-input" placeholder="••••••••" minlength="6">
                        </div>

                        <button type="submit" class="btn-primary btn-large">Şifreyi Değiştir</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>