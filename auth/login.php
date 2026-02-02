<?php 
require_once '../config/init.php'; 
include '../includes/header.php'; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $query = $db->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_login'] = time();
        
        header("Location: " . BASE_URL . "index.php");
        exit;
    } else {
        $error = "Geçersiz e-posta veya şifre!";
    }
}
?>

<main class="container">
    <div class="auth-card">
        <h2 class="auth-title">Giriş Yap</h2>
        
        <?php if($error): ?>
            <div class="form-alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['success'])): ?>
            <div class="form-alert" style="background:#e6f4ea; color:#1e8e3e;">Kayıt başarılı! Şimdi giriş yapabilirsiniz.</div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>auth/login.php" method="POST">
            <div class="form-group">
                <label>E-posta</label>
                <input type="email" name="email" placeholder="ornek@mail.com" required>
            </div>
            
            <div class="form-group">
                <label>Şifre</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%;">Giriş Yap</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; display:flex; justify-content:center; gap:12px;">
            <span style="color: var(--text-muted); align-self:center;">Hesabın yok mu?</span>
            <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn-register">Kayıt Ol</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>