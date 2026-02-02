<?php
require_once '../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?3d=csrf_error');
    exit;
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
if ($description === '') {
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?3d=missing_desc');
    exit;
}

$upload_name = '';
if (isset($_FILES['model']) && $_FILES['model']['error'] === UPLOAD_ERR_OK) {
    $allowed_ext = ['stl', 'obj', 'zip'];
    $max_size = 5 * 1024 * 1024;
    $ext = strtolower(pathinfo($_FILES['model']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?3d=bad_file');
        exit;
    }
    if ($_FILES['model']['size'] > $max_size) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?3d=big_file');
        exit;
    }
    $target_dir = __DIR__ . '/../uploads/3d_requests/';
    if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);
    $upload_name = time() . '_' . uniqid() . '.' . $ext;
    $target_file = $target_dir . $upload_name;
    if (!move_uploaded_file($_FILES['model']['tmp_name'], $target_file)) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?3d=upload_error');
        exit;
    }
    @chmod($target_file, 0644);
}

$log_dir = __DIR__ . '/../logs/';
if (!file_exists($log_dir)) mkdir($log_dir, 0755, true);
$log_file = $log_dir . '3d_requests.log';
$entry = [
    'time' => date('c'),
    'name' => $name,
    'email' => $email,
    'description' => $description,
    'file' => $upload_name,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
];
file_put_contents($log_file, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

try {
    $db->exec("CREATE TABLE IF NOT EXISTS three_d_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255),
        description TEXT,
        file VARCHAR(255),
        status VARCHAR(50) DEFAULT 'new',
        ip VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $stmt = $db->prepare("INSERT INTO three_d_requests (name, email, description, file, ip) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $description, $upload_name, $_SERVER['REMOTE_ADDR'] ?? '']);
    log_audit($db, $_SESSION['user_id'] ?? null, '3d_request_created', ['name'=>$name, 'email'=>$email]);
} catch (Exception $e) {
}

header('Location: ' . $_SERVER['HTTP_REFERER'] . '?3d=success');
exit;