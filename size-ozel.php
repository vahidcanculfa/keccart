<?php
require_once 'config/init.php';
include 'includes/header.php';

$success = isset($_GET['3d']) && $_GET['3d'] === 'success';
?>
<main class="container custom-3d-page">
    <div class="custom-3d-card">

        <div class="custom-3d-header">
            <div>
                <h2>Size Özel 3D Print Talebi</h2>
                <p>Boyut, malzeme ve detayları yazın. Model dosyası ekleyebilirsiniz.</p>
            </div>
            <div class="custom-3d-badges">
                <span class="badge">STL</span>
                <span class="badge">OBJ</span>
                <span class="badge">ZIP</span>
                <span class="badge">Max 5MB</span>
            </div>
        </div>

        <div class="custom-3d-hero">
            <div>
                <h3>Özel üretim, net süreç</h3>
                <p>Modelini yükle, detayları paylaş, hızlı teklif ve üretim planı ile dönüş al.</p>
            </div>
            <div class="hero-visual">3D</div>
        </div>

        <?php if($success): ?>
            <div class="alert-success">Talebiniz alındı. En kısa sürede iletişime geçeceğiz.</div>
        <?php endif; ?>

        <div class="custom-3d-steps">
            <div class="custom-3d-step">
                <h4>1) Talebi oluştur</h4>
                <p>Ölçü, malzeme ve notlarını ekle.</p>
            </div>
            <div class="custom-3d-step">
                <h4>2) Modeli yükle</h4>
                <p>STL, OBJ veya ZIP yükleyebilirsin.</p>
            </div>
            <div class="custom-3d-step">
                <h4>3) Hızlı geri dönüş</h4>
                <p>Kısa sürede fiyat ve teslim planı ile dönüş yaparız.</p>
            </div>
        </div>

        <div class="custom-3d-grid">
            <form class="custom-3d-form" id="custom3dForm" method="POST" action="<?php echo BASE_URL; ?>core/3d-request.php" enctype="multipart/form-data" data-success="<?php echo BASE_URL; ?>size-ozel.php?3d=success">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label>Ad Soyad</label>
                    <input type="text" name="name" placeholder="Adınız (isteğe bağlı)" value="<?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" placeholder="E-posta (isteğe bağlı)" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Talep Detayı</label>
                    <textarea name="description" placeholder="Ne istiyorsunuz? (boyut, malzeme, notlar)" required></textarea>
                </div>

                <div class="form-group">
                    <label>Model Dosyası</label>
                    <div class="drop-zone" id="dropZone">
                        <div class="drop-icon">⬆</div>
                        <div class="drop-title">Dosyayı sürükle bırak veya seç</div>
                        <div class="drop-sub" id="fileName">Henüz dosya seçilmedi</div>
                        <button type="button" class="btn-outline" id="pickFile">Dosya Seç</button>
                        <input type="file" id="modelFile" name="model" accept=".stl,.obj,.zip" class="file-input" hidden>
                    </div>
                    <div class="form-hint">Desteklenen dosyalar: .stl, .obj, .zip</div>
                    <div class="upload-error" id="uploadError"></div>
                    <div class="upload-progress" id="uploadProgress">
                        <div class="upload-progress-bar" id="uploadProgressBar"></div>
                    </div>
                    <div class="upload-progress-text" id="uploadProgressText"></div>
                    <div class="upload-summary" id="uploadSummary"></div>
                </div>

                <label class="theme-toggle">
                    <input type="checkbox" id="applyTheme" />
                    <span>Bu sayfada 3D tema uygula</span>
                </label>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Gönder</button>
                    <a href="index.php" class="link-muted">Geri Dön</a>
                </div>
            </form>

            <div class="custom-3d-preview">
                <div class="preview-title">Önizleme</div>
                <div id="previewArea" class="preview-box">
                    <div class="preview-empty">Dosya yükleyince önizleme burada görünecek.</div>
                </div>
                <div class="preview-note">Model yükledikten sonra otomatik önizleme oluşur.</div>
                <div class="mini-quote">
                    <div class="mini-quote-title">Tahmini Fiyat & Teslim</div>
                    <div class="mini-quote-row"><span>Fiyat Aralığı</span><strong>₺300–₺1.500</strong></div>
                    <div class="mini-quote-row"><span>Teslim Süresi</span><strong>3–7 gün</strong></div>
                    <div class="mini-quote-row"><span>Revizyon</span><strong>Ücretsiz 1 revizyon</strong></div>
                </div>
            </div>
        </div>

        <div class="custom-3d-faq">
            <h3>Sık Sorulanlar</h3>
            <details class="faq-item">
                <summary>Hangi dosya tiplerini destekliyorsunuz?</summary>
                <p>STL, OBJ ve ZIP desteklenir. ZIP içinde bu dosyalar yer alabilir.</p>
            </details>
            <details class="faq-item">
                <summary>Teklif ne kadar sürede gelir?</summary>
                <p>Genellikle 24 saat içinde dönüş sağlıyoruz.</p>
            </details>
            <details class="faq-item">
                <summary>Modelim hazır değilse ne yapmalıyım?</summary>
                <p>Detayları açıklaman yeterli. Gerekirse modelleme desteği sağlar, yönlendiririz.</p>
            </details>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const chk = document.getElementById('applyTheme');
    const form = document.getElementById('custom3dForm');
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('modelFile');
    const pickFile = document.getElementById('pickFile');
    const fileName = document.getElementById('fileName');
    const progressWrap = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const uploadError = document.getElementById('uploadError');
    const uploadSummary = document.getElementById('uploadSummary');

    const allowedExt = ['stl','obj','zip'];
    const maxSize = 5 * 1024 * 1024;

    if(localStorage.getItem('keccart_theme') === '3d') chk.checked = true;
    chk.addEventListener('change', function(){
        if(this.checked) { document.body.classList.add('theme-3d'); localStorage.setItem('keccart_theme','3d'); }
        else { document.body.classList.remove('theme-3d'); localStorage.removeItem('keccart_theme'); }
    });

    pickFile.addEventListener('click', function(){
        fileInput.click();
    });

    function showError(message){
        if(!message) { uploadError.style.display = 'none'; uploadError.textContent = ''; return; }
        uploadError.textContent = message;
        uploadError.style.display = 'block';
    }

    function updateFileName(file){
        if(!file) { fileName.textContent = 'Henüz dosya seçilmedi'; return; }
        fileName.textContent = file.name + ' • ' + Math.round(file.size/1024) + ' KB';
    }

    function updateSummary(file){
        if(!file) { uploadSummary.style.display = 'none'; uploadSummary.innerHTML = ''; return; }
        const ext = file.name.split('.').pop().toLowerCase();
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        uploadSummary.innerHTML = '<div class="upload-summary-title">Yükleme Özeti</div>' +
            '<div class="upload-summary-row"><span>Dosya</span><strong>' + file.name + '</strong></div>' +
            '<div class="upload-summary-row"><span>Boyut</span><strong>' + sizeMb + ' MB</strong></div>' +
            '<div class="upload-summary-row"><span>Tür</span><strong>' + ext.toUpperCase() + '</strong></div>';
        uploadSummary.style.display = 'block';
    }

    function validateFile(file){
        if(!file) { showError(''); return true; }
        const ext = file.name.split('.').pop().toLowerCase();
        if(!allowedExt.includes(ext)) {
            showError('Dosya türü desteklenmiyor. STL, OBJ veya ZIP yükleyin.');
            return false;
        }
        if(file.size > maxSize) {
            showError('Dosya çok büyük. Maksimum 5MB.');
            return false;
        }
        showError('');
        return true;
    }

    fileInput.addEventListener('change', function(){
        const file = this.files[0];
        updateFileName(file);
        if(!validateFile(file)) { return; }
        updateSummary(file);
    });

    ['dragenter','dragover'].forEach(evt => {
        dropZone.addEventListener(evt, function(e){
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
    });
    ['dragleave','drop'].forEach(evt => {
        dropZone.addEventListener(evt, function(e){
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
    });

    dropZone.addEventListener('drop', function(e){
        const file = e.dataTransfer.files[0];
        if(!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        updateFileName(file);
        if(!validateFile(file)) { return; }
        updateSummary(file);
        fileInput.dispatchEvent(new Event('change'));
    });

    const params = new URLSearchParams(window.location.search);
    if(params.get('3d') === 'success') {
        const last = localStorage.getItem('last3dUpload');
        if(last) {
            try {
                const data = JSON.parse(last);
                uploadSummary.innerHTML = '<div class="upload-summary-title">Son Yükleme</div>' +
                    '<div class="upload-summary-row"><span>Dosya</span><strong>' + data.name + '</strong></div>' +
                    '<div class="upload-summary-row"><span>Boyut</span><strong>' + data.size + ' MB</strong></div>' +
                    '<div class="upload-summary-row"><span>Tür</span><strong>' + data.ext + '</strong></div>';
                uploadSummary.style.display = 'block';
                localStorage.removeItem('last3dUpload');
            } catch (e) {}
        }
    }

    form.addEventListener('submit', function(e){
        const file = fileInput.files[0];
        if(!validateFile(file)) { e.preventDefault(); return; }
        if (file) {
            const ext = file.name.split('.').pop().toUpperCase();
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            localStorage.setItem('last3dUpload', JSON.stringify({ name: file.name, size: sizeMb, ext }));
        }
        if (!window.XMLHttpRequest || !window.FormData) return;
        e.preventDefault();
        progressWrap.style.display = 'block';
        progressBar.style.width = '0%';
        progressText.textContent = '0%';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.upload.onprogress = function(ev){
            if(!ev.lengthComputable) return;
            const p = Math.round((ev.loaded / ev.total) * 100);
            progressBar.style.width = p + '%';
            progressText.textContent = p + '%';
        };
        xhr.onload = function(){
            if (xhr.responseURL) { window.location = xhr.responseURL; return; }
            window.location = form.dataset.success || window.location.href;
        };
        xhr.onerror = function(){
            progressText.textContent = 'Yükleme başarısız';
        };
        xhr.send(new FormData(form));
    });
});
</script>

<script type="module">
import * as THREE from 'https://unpkg.com/three@0.160.0/build/three.module.js';
import { STLLoader } from 'https://unpkg.com/three@0.160.0/examples/jsm/loaders/STLLoader.js';
import { OBJLoader } from 'https://unpkg.com/three@0.160.0/examples/jsm/loaders/OBJLoader.js';
let renderer, scene, camera, current;
function init(container) {
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
function animate(){ if(!renderer) return; requestAnimationFrame(animate); renderer.render(scene, camera); }
function clear(){ if(current){ scene.remove(current); current = null; } }
document.getElementById('modelFile').addEventListener('change', function(e){
    const f = this.files[0];
    if(!f) return;
    const url = URL.createObjectURL(f);
    const container = document.getElementById('previewArea');
    init(container); clear();
    const ext = f.name.split('.').pop().toLowerCase();
    if(ext === 'stl'){
        const loader = new STLLoader();
        loader.load(url, function(geometry){
            const mat = new THREE.MeshPhongMaterial({ color: 0xdddddd });
            const mesh = new THREE.Mesh(geometry, mat);
            geometry.computeBoundingBox();
            const bb = geometry.boundingBox; const size = bb.getSize(new THREE.Vector3()).length(); const center = bb.getCenter(new THREE.Vector3());
            mesh.position.x = -center.x; mesh.position.y = -center.y; mesh.position.z = -center.z;
            mesh.scale.multiplyScalar(50/size);
            scene.add(mesh); current = mesh; animate();
        });
    } else if(ext === 'obj'){
        const loader = new OBJLoader();
        loader.load(url, function(obj){
            obj.traverse(function(c){ if(c.isMesh) c.material = new THREE.MeshStandardMaterial({color:0xcccccc}); });
            const box = new THREE.Box3().setFromObject(obj); const size = box.getSize(new THREE.Vector3()).length(); const center = box.getCenter(new THREE.Vector3());
            obj.position.x = -center.x; obj.position.y = -center.y; obj.position.z = -center.z; obj.scale.multiplyScalar(50/size);
            scene.add(obj); current = obj; animate();
        });
    } else {
        container.innerHTML = '<div class="preview-empty">Önizleme desteklenmiyor</div>';
    }
});
</script>

<?php include 'includes/footer.php'; ?>