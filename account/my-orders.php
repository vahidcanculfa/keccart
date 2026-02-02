<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$query->execute([$user_id]);
$orders = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container" style="min-height: 60vh; margin-top: 40px; margin-bottom: 80px;">
    <h2 class="section-title">Siparişlerim</h2>

    <?php if(count($orders) > 0): ?>
        <div style="overflow-x: auto; background: white; border-radius: 12px; box-shadow: var(--shadow-sm); padding: 25px; border: 1px solid #f1f3f4;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #f8f9fa; color: #5f6368; font-size: 14px;">
                        <th style="padding: 15px;">Sipariş No</th>
                        <th>Tarih</th>
                        <th>Toplam Tutar</th>
                        <th>Durum</th>
                        <th style="text-align: right;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): 
                        $status_bg = "#fff3cd"; $status_color = "#856404";
                        if($order['status'] == 'completed') { $status_bg = "#e6f4ea"; $status_color = "#1e8e3e"; }
                        elseif($order['status'] == 'cancelled') { $status_bg = "#fce8e6"; $status_color = "#d93025"; }
                    ?>
                        <tr style="border-bottom: 1px solid #f8f9fa;">
                            <td style="padding: 20px; font-weight: bold; color: #202124;">#<?php echo $order['id']; ?></td>
                            <td style="color: #5f6368;"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                            <td style="color: #1a73e8; font-weight: 700;">$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <span style="padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>;">
                                    <?php echo strtoupper($order['status']); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?php echo BASE_URL; ?>account/order-detail.php?id=<?php echo $order['id']; ?>" style="display: inline-block; padding: 8px 16px; background: #f1f3f4; color: #3c4043; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 500; transition: 0.2s;">Detayları Gör</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 80px 20px; background: white; border-radius: 12px; box-shadow: var(--shadow-sm);">
            <div style="font-size: 60px; margin-bottom: 20px;">📦</div>
            <h3 style="color: #202124;">Henüz bir siparişiniz yok.</h3>
            <p style="color: #5f6368; margin-bottom: 30px;">Alışverişe başlayarak ilk siparişinizi verebilirsiniz.</p>
            <a href="<?php echo BASE_URL; ?>index.php" style="display: inline-block; padding: 14px 30px; background: #1a73e8; color: white; text-decoration: none; border-radius: 25px; font-weight: bold;">Alışverişe Başla</a>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>