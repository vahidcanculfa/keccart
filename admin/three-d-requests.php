<?php
require_once '../config/init.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}
include '../includes/header.php';

$entries = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="3d_requests.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','time','name','email','description','file','status','ip']);
    $stmt = $db->query("SELECT * FROM three_d_requests ORDER BY created_at DESC");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$row['id'],$row['created_at'],$row['name'],$row['email'],$row['description'],$row['file'],$row['status'],$row['ip']]);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $upd = $db->prepare("UPDATE three_d_requests SET status = ? WHERE id = ?");
    $upd->execute([$status, $id]);
    log_audit($db, $_SESSION['user_id'] ?? null, '3d_request_status_changed', ['id'=>$id,'status'=>$status]);
    header('Location: ' . BASE_URL . 'admin/three-d-requests.php');
    exit;
}

try {
    $query = "SELECT * FROM three_d_requests";
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $s = $_GET['status'];
        $stmt = $db->prepare("SELECT * FROM three_d_requests WHERE status = ? ORDER BY created_at DESC");
        $stmt->execute([$s]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $db->query("SELECT * FROM three_d_requests ORDER BY created_at DESC");
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $log_file = __DIR__ . '/../logs/3d_requests.log';
    if (file_exists($log_file)) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines);
        foreach ($lines as $line) {
            $arr = json_decode($line, true);
            if ($arr) $entries[] = $arr;
        }
    }
}
?>
<main class="admin-container" style="margin-top:30px;">
    <div class="admin-card">
        <div class="admin-header" style="margin-bottom:14px;">
            <h2 class="admin-title" style="font-size:18px;">3D Talepleri</h2>
            <a class="link-primary" href="<?php echo BASE_URL; ?>admin/index.php">Panele Dön</a>
        </div>
        <div class="admin-header" style="margin-bottom:12px;">
            <form method="GET" style="display:flex; gap:8px;">
                <select name="status" class="admin-select">
                    <option value="">All statuses</option>
                    <option value="new">New</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                </select>
                <button type="submit" class="btn-primary" style="padding:8px 12px;">Filter</button>
            </form>
            <form method="POST" action="<?php echo BASE_URL; ?>admin/three-d-requests.php" style="margin-left:auto;">
                <input type="hidden" name="export_csv" value="1">
                <button type="submit" class="btn-primary" style="padding:8px 12px;">Export CSV</button>
            </form>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Time</th>
                    <th>Bilgi</th>
                    <th>Dosya</th>
                    <th>Status</th>
                    <th style="text-align:right;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($entries as $i => $e): ?>
                    <tr>
                        <td><?php echo isset($e['id']) ? $e['id'] : ($i+1); ?></td>
                        <td><?php echo isset($e['created_at']) ? $e['created_at'] : ($e['time'] ?? ''); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($e['name'] ?? ''); ?></strong><br>
                            <small class="link-muted"><?php echo htmlspecialchars($e['email'] ?? ''); ?></small>
                            <p style="margin-top:8px; white-space:pre-wrap; color: var(--text-main);"><?php echo htmlspecialchars($e['description'] ?? ''); ?></p>
                        </td>
                        <td>
                            <?php if(!empty($e['file'])): ?>
                                <a class="link-primary" href="<?php echo BASE_URL; ?>uploads/3d_requests/<?php echo htmlspecialchars($e['file']); ?>" target="_blank"><?php echo htmlspecialchars($e['file']); ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display:flex; gap:6px; align-items:center;">
                                <input type="hidden" name="id" value="<?php echo $e['id'] ?? ''; ?>">
                                <select name="status" class="admin-select">
                                    <option value="new" <?php echo (isset($e['status']) && $e['status']==='new')? 'selected':''; ?>>New</option>
                                    <option value="processing" <?php echo (isset($e['status']) && $e['status']==='processing')? 'selected':''; ?>>Processing</option>
                                    <option value="completed" <?php echo (isset($e['status']) && $e['status']==='completed')? 'selected':''; ?>>Completed</option>
                                </select>
                                <button type="submit" class="btn-primary" style="padding:6px 8px;">Save</button>
                            </form>
                        </td>
                        <td style="text-align:right;">
                            <?php if(!empty($e['file'])): ?>
                                <?php $ext = strtolower(pathinfo($e['file'], PATHINFO_EXTENSION)); ?>
                                <?php if(in_array($ext, ['stl','obj','glb','gltf'])): ?>
                                    <button class="btn-outline preview-btn" data-file="<?php echo BASE_URL; ?>uploads/3d_requests/<?php echo rawurlencode($e['file']); ?>">Önizle</button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="viewerModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:4000; align-items:center; justify-content:center;">
    <div style="width:90%; max-width:1000px; background:white; border-radius:10px; padding:12px; position:relative;">
        <button id="closeViewer" style="position:absolute; right:12px; top:12px; background:none; border:none; font-size:18px; cursor:pointer;">✖</button>
        <div id="viewerContainer" style="width:100%; height:600px;"></div>
    </div>
</div>

<script type="module">
import * as THREE from 'https://unpkg.com/three@0.160.0/build/three.module.js';
import { STLLoader } from 'https://unpkg.com/three@0.160.0/examples/jsm/loaders/STLLoader.js';
import { OBJLoader } from 'https://unpkg.com/three@0.160.0/examples/jsm/loaders/OBJLoader.js';
import { GLTFLoader } from 'https://unpkg.com/three@0.160.0/examples/jsm/loaders/GLTFLoader.js';

let scene, camera, renderer;
let currentMesh;
function initViewer(container) {
    renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    container.innerHTML = '';
    container.appendChild(renderer.domElement);
    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(0, 0, 100);
    const light = new THREE.DirectionalLight(0xffffff, 1);
    light.position.set(0, 0, 1).normalize();
    scene.add(light);
    const ambient = new THREE.AmbientLight(0xcccccc, 0.4);
    scene.add(ambient);
}
function animate() { if(!renderer) return; requestAnimationFrame(animate); renderer.render(scene, camera); }

function clearScene() { if (currentMesh) { scene.remove(currentMesh); currentMesh = null; } }

document.addEventListener('click', async function(e){
    if (e.target && e.target.classList.contains('preview-btn')) {
        const url = e.target.getAttribute('data-file');
        const container = document.getElementById('viewerContainer');
        initViewer(container);
        clearScene();
        const ext = url.split('.').pop().toLowerCase();
        if (ext === 'stl') {
            const loader = new STLLoader();
            loader.load(url, function(geometry){
                const material = new THREE.MeshPhongMaterial({ color: 0xdddddd });
                const mesh = new THREE.Mesh(geometry, material);
                geometry.computeBoundingBox();
                const bb = geometry.boundingBox;
                const size = bb.getSize(new THREE.Vector3()).length();
                const center = bb.getCenter(new THREE.Vector3());
                mesh.position.x = -center.x; mesh.position.y = -center.y; mesh.position.z = -center.z;
                mesh.scale.multiplyScalar(50 / size);
                scene.add(mesh); currentMesh = mesh; animate();
            });
        } else if (ext === 'obj') {
            const loader = new OBJLoader();
            loader.load(url, function(object){
                object.traverse(function(child){ if(child.isMesh) child.material = new THREE.MeshStandardMaterial({color:0xcccccc}); });
                const box = new THREE.Box3().setFromObject(object);
                const size = box.getSize(new THREE.Vector3()).length();
                const center = box.getCenter(new THREE.Vector3());
                object.position.x = -center.x; object.position.y = -center.y; object.position.z = -center.z;
                object.scale.multiplyScalar(50/size);
                scene.add(object); currentMesh = object; animate();
            });
        } else if (ext === 'glb' || ext === 'gltf') {
            const loader = new GLTFLoader();
            loader.load(url, function(gltf){
                const obj = gltf.scene;
                const box = new THREE.Box3().setFromObject(obj);
                const size = box.getSize(new THREE.Vector3()).length();
                const center = box.getCenter(new THREE.Vector3());
                obj.position.x = -center.x; obj.position.y = -center.y; obj.position.z = -center.z;
                obj.scale.multiplyScalar(50/size);
                scene.add(obj); currentMesh = obj; animate();
            });
        } else {
            alert('Önizleme desteklenmiyor. Dosyayı indirip kontrol edebilirsiniz.');
            return;
        }
        document.getElementById('viewerModal').style.display = 'flex';
    }
    if (e.target && e.target.id === 'closeViewer') {
        document.getElementById('viewerModal').style.display = 'none';
        if (renderer) { renderer.dispose(); renderer = null; }
    }
});
</script>
<?php include '../includes/footer.php'; ?>