<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>

<main class="container" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 600px; width: 100%; background: white; padding: 50px; border-radius: 20px; text-align: center; box-shadow: var(--shadow-sm); border: 1px solid #f1f3f4;">
        <div style="width: 80px; height: 80px; background: #e6f4ea; color: #1e8e3e; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 40px; margin-bottom: 30px;">
            ✓
        </div>
        
        <h2 style="color: #202124; font-size: 32px; margin-bottom: 10px;">Siparişiniz Alındı!</h2>
        <p style="color: #5f6368; font-size: 18px; margin-bottom: 30px;">Harika bir seçim yaptınız. Siparişinizi hazırlamak için hemen çalışmaya başlıyoruz.</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 40px; border: 1px dashed #dadce0;">
            <p style="margin: 0; color: #70757a; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Sipariş Numaranız</p>
            <h3 style="margin: 5px 0 0; color: #1a73e8; font-size: 24px;">#<?php echo $order_id; ?></h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>account/order-detail.php?id=<?php echo $order_id; ?>" style="display: block; padding: 16px; background: #1a73e8; color: white; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px; transition: background 0.3s;">
                Sipariş Detaylarını Görüntüle
            </a>
            
            <a href="<?php echo BASE_URL; ?>index.php" style="display: block; padding: 16px; background: white; color: #1a73e8; text-decoration: none; border-radius: 30px; font-weight: 600; border: 1px solid #dadce0; transition: background 0.3s;">
                Alışverişe Devam Et
            </a>
        </div>

        <p style="margin-top: 40px; color: #9aa0a6; font-size: 13px;">
            Siparişinizle ilgili güncellemeleri kayıtlı e-posta adresinizden takip edebilirsiniz.
        </p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>