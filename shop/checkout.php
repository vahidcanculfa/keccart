<?php
require_once '../config/init.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    header("Location: " . BASE_URL . "shop/cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$total_price = 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;

// Sepet toplam hesapla
foreach ($cart as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Sipariş oluştur
if ($step == 3 && $payment_method) {
    try {
        $db->beginTransaction();

        $stmt = $db->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$user_id, $total_price]);
        $order_id = $db->lastInsertId();

        $stmtItem = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($cart as $item) {
            $stmtItem->execute([
                $order_id, 
                $item['id'], 
                $item['quantity'], 
                $item['price']
            ]);

            $updateStock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStock->execute([$item['quantity'], $item['id']]);
        }

        $db->commit();
        
        unset($_SESSION['cart']);
        
        header("Location: " . BASE_URL . "shop/checkout-success.php?id=" . $order_id);
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Sipariş oluşturma hatası: " . $e->getMessage();
    }
}

// Kullanıcı bilgisi
$user_query = $db->prepare("SELECT * FROM users WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch(PDO::FETCH_ASSOC);
?>

<main class="container">
    <div class="checkout-wrapper">
        <!-- İlerleme Çubuğu -->
        <div class="progress-bar">
            <div class="progress-step <?php echo $step >= 1 ? 'active' : ''; ?>">
                <div class="step-number">1</div>
                <div class="step-label">Özet</div>
            </div>
            <div class="progress-line <?php echo $step >= 2 ? 'active' : ''; ?>"></div>
            <div class="progress-step <?php echo $step >= 2 ? 'active' : ''; ?>">
                <div class="step-number">2</div>
                <div class="step-label">Adres</div>
            </div>
            <div class="progress-line <?php echo $step >= 3 ? 'active' : ''; ?>"></div>
            <div class="progress-step <?php echo $step >= 3 ? 'active' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-label">Ödeme</div>
            </div>
        </div>

        <div class="checkout-content">
            <!-- SOL TARAF: FORM -->
            <div class="checkout-form-section">
                <?php if($step == 1): ?>
                    <!-- ADIM 1: SEPET ÖZETİ -->
                    <div class="checkout-step">
                        <h2 class="step-title">Sepet Özeti</h2>
                        <div class="order-items">
                            <?php foreach($cart as $item): ?>
                                <div class="order-item">
                                    <img src="<?php echo BASE_URL; ?>uploads/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div class="item-details">
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="item-qty">Miktar: <strong><?php echo $item['quantity']; ?></strong></p>
                                    </div>
                                    <div class="item-price">
                                        <span class="unit-price">$<?php echo number_format($item['price'], 2); ?></span>
                                        <span class="total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form method="POST" action="" class="checkout-form">
                            <input type="hidden" name="step" value="2">
                            <button type="submit" class="btn-primary btn-large">Devam Et →</button>
                        </form>
                    </div>

                <?php elseif($step == 2): ?>
                    <!-- ADIM 2: ADRES BİLGİLERİ -->
                    <div class="checkout-step">
                        <h2 class="step-title">Teslimat Adresi</h2>
                        <form method="POST" action="" class="checkout-form">
                            <input type="hidden" name="step" value="3">

                            <div class="form-group">
                                <label>Adı Soyadı</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly class="form-input">
                            </div>

                            <div class="form-group">
                                <label>Telefon</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label>Şehir</label>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label>Adres</label>
                                <textarea name="address" required class="form-textarea"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="button" onclick="history.back();" class="btn-outline">← Geri</button>
                                <button type="submit" class="btn-primary">Devam Et →</button>
                            </div>
                        </form>
                    </div>

                <?php elseif($step == 3): ?>
                    <!-- ADIM 3: ÖDEME -->
                    <div class="checkout-step">
                        <h2 class="step-title">Ödeme Yöntemi</h2>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-error"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" class="checkout-form">
                            <input type="hidden" name="step" value="3">
                            
                            <div class="payment-methods">
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="card" required>
                                    <span class="method-label"><i class="fas fa-credit-card"></i> Kredi Kartı</span>
                                </label>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="transfer" required>
                                    <span class="method-label"><i class="fas fa-university"></i> Banka Transferi</span>
                                </label>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="wallet" required>
                                    <span class="method-label"><i class="fas fa-wallet"></i> Mobil Cüzdan</span>
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="button" onclick="history.back();" class="btn-outline">← Geri</button>
                                <button type="submit" class="btn-primary">Siparişi Tamamla</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SAĞ TARAF: ÖZETİ -->
            <aside class="checkout-summary">
                <div class="summary-card">
                    <h3 class="summary-title">Sipariş Özeti</h3>
                    
                    <div class="summary-items">
                        <?php foreach($cart as $item): ?>
                            <div class="summary-item">
                                <span><?php echo htmlspecialchars(substr($item['name'], 0, 25)); ?> × <?php echo $item['quantity']; ?></span>
                                <span class="amount">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-costs">
                        <div class="cost-row">
                            <span>Ara Toplam</span>
                            <span>$<?php echo number_format($total_price, 2); ?></span>
                        </div>
                        <div class="cost-row">
                            <span>Kargo</span>
                            <span class="shipping-free"><?php echo $total_price >= 100 ? 'ÜCRETSİZ' : '+$10'; ?></span>
                        </div>
                        <div class="cost-row">
                            <span>KDV (%18)</span>
                            <span>$<?php echo number_format($total_price * 0.18, 2); ?></span>
                        </div>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total">
                        <span>Toplam Tutar</span>
                        <span class="total-amount">
                            $<?php 
                            $shipping = $total_price >= 100 ? 0 : 10;
                            $tax = $total_price * 0.18;
                            $final = $total_price + $shipping + $tax;
                            echo number_format($final, 2); 
                            ?>
                        </span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>