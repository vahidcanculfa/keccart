<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_email = $db->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->execute([$email]);

    if ($check_email->rowCount() > 0) {
        $message = "Bu e-posta adresi zaten kayıtlı!";
    } else {
        $insert = $db->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        if ($insert->execute([$full_name, $email, $password])) {
            header("Location: " . BASE_URL . "auth/login.php?success=1");
            exit;
        } else {
            $message = "Bir şeyler ters gitti!";
        }
    }
}
?>

<main class="container">
    <div class="auth-card">
        <h2 class="auth-title">Hesap Oluştur</h2>
        
        <?php if($message): ?>
            <div class="form-alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo BASE_URL; ?>auth/register.php" method="POST">
            <div class="form-group">
                <label>Ad Soyad</label>
                <input type="text" name="full_name" placeholder="Örn: Ahmet Yılmaz" required>
            </div>
            
            <div class="form-group">
                <label>E-posta</label>
                <input type="email" name="email" placeholder="ornek@mail.com" required>
            </div>
            
            <div class="form-group">
                <label>Şifre</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-primary btn-block">Kayıt Ol</button>
        </form>
        
        <p class="mt-20">Zaten hesabın var mı? <a href="<?php echo BASE_URL; ?>auth/login.php" class="link-primary">Giriş yap</a></p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>