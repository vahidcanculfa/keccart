<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$order_query = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_query->execute([$order_id, $user_id]);
$order = $order_query->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='container' style='padding:80px 20px; text-align:center;'>
            <div style='font-size:48px; margin-bottom:20px;'>🔍</div>
            <h3>Sipariş bulunamadı veya yetkisiz erişim.</h3>
            <a href='" . BASE_URL . "account/my-orders.php' style='color:#1a73e8; text-decoration:none; font-weight:bold;'>Siparişlerime Dön</a>
          </div>";
    include '../includes/footer.php';
    exit;
}

$items_query = $db->prepare("
    SELECT oi.*, p.title, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_query->execute([$order_id]);
$items = $items_query->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container" style="min-height: 60vh; margin-top: 40px; margin-bottom: 80px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="margin:0; color: #202124;">Sipariş Detayı <span style="color: #5f6368; font-weight: normal;">#<?php echo $order['id']; ?></span></h2>
        <a href="<?php echo BASE_URL; ?>account/my-orders.php" style="color: #1a73e8; text-decoration:none; font-weight: 500; display: flex; align-items: center; gap: 5px;">
            <span>&larr;</span> Siparişlerime Dön
        </a>
    </div>

    <div style="background: white; border-radius: 15px; padding: 35px; box-shadow: var(--shadow-sm); border: 1px solid #f1f3f4;">
        <div style="border-bottom: 2px solid #f8f9fa; padding-bottom: 25px; margin-bottom: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 30px;">
            <div>
                <p style="color: #70757a; font-size: 13px; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Sipariş Tarihi</p>
                <p style="font-weight: 600; color: #202124;"><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></p>
            </div>
            <div>
                <p style="color: #70757a; font-size: 13px; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Durum</p>
                <?php 
                    $status_color = ($order['status'] == 'completed') ? '#1e8e3e' : (($order['status'] == 'cancelled') ? '#d93025' : '#856404');
                ?>
                <p style="font-weight: 700; color: <?php echo $status_color; ?>;"><?php echo strtoupper($order['status']); ?></p>
            </div>
            <div>
                <p style="color: #70757a; font-size: 13px; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Ödeme Yöntemi</p>
                <p style="font-weight: 600; color: #202124;">Kapıda Ödeme / Kredi Kartı</p>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; color: #70757a; font-size: 14px; border-bottom: 1px solid #f1f3f4;">
                    <th style="padding: 15px 0; font-weight: 500;">Ürün</th>
                    <th style="font-weight: 500;">Birim Fiyat</th>
                    <th style="font-weight: 500;">Adet</th>
                    <th style="text-align: right; font-weight: 500;">Ara Toplam</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr style="border-bottom: 1px solid #f8f9fa;">
                    <td style="padding: 20px 0; display: flex; align-items: center;">
                        <img src="<?php echo BASE_URL; ?>uploads/<?php echo $item['image']; ?>" width="64" height="64" style="border-radius: 10px; margin-right: 20px; object-fit: cover; border: 1px solid #eee;">
                        <span style="font-weight: 600; color: #3c4043;"><?php echo htmlspecialchars($item['title']); ?></span>
                    </td>
                    <td style="color: #5f6368;">$<?php echo number_format($item['price'], 2); ?></td>
                    <td style="color: #5f6368;">x <?php echo $item['quantity']; ?></td>
                    <td style="text-align: right; font-weight: 700; color: #202124;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 40px; text-align: right; border-top: 2px solid #f8f9fa; padding-top: 25px;">
            <p style="font-size: 16px; color: #5f6368; margin: 0;">Toplam Tutar</p>
            <h2 style="color: #1a73e8; font-size: 36px; margin: 5px 0 0;">$<?php echo number_format($order['total_price'], 2); ?></h2>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>