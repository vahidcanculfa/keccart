<?php
require_once '../config/init.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$query = $db->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC");
$orders = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Yönetimi | Keccart Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <div class="admin-card">
            <div class="admin-header" style="margin-bottom:18px;">
                <h2 class="admin-title" style="font-size:18px;">Sipariş Yönetimi</h2>
                <a href="<?php echo BASE_URL; ?>admin/index.php" class="link-primary">&larr; Panele Dön</a>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sipariş ID</th>
                        <th>Müşteri</th>
                        <th>Toplam Tutar</th>
                        <th>Tarih</th>
                        <th>Mevcut Durum</th>
                        <th style="text-align:right;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                    <tr>
                        <td>#<?php echo $o['id']; ?></td>
                        <td><?php echo htmlspecialchars($o['full_name']); ?></td>
                        <td>$<?php echo number_format($o['total_price'], 2); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($o['created_at'])); ?></td>
                        <td>
                            <?php
                                $status_class = 'status-pending';
                                if($o['status'] == 'completed') $status_class = 'status-completed';
                                if($o['status'] == 'cancelled') $status_class = 'status-cancelled';
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo strtoupper($o['status']); ?></span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <form action="<?php echo BASE_URL; ?>admin/update-order.php" method="POST" style="display:inline-flex; gap:8px; align-items: center;">
                                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                    <select name="status" class="admin-select">
                                        <option value="pending" <?php if($o['status']=='pending') echo 'selected'; ?>>Beklemede</option>
                                        <option value="completed" <?php if($o['status']=='completed') echo 'selected'; ?>>Tamamlandı</option>
                                        <option value="cancelled" <?php if($o['status']=='cancelled') echo 'selected'; ?>>İptal Et</option>
                                    </select>
                                    <button type="submit" class="btn-primary" style="padding:8px 12px;">Güncelle</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>