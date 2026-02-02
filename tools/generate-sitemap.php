<?php
require_once __DIR__ . '/../config/db.php';
$urls = [];
$urls[] = ['loc' => BASE_URL, 'priority' => '1.0'];
$stmt = $db->query("SELECT id, created_at FROM products");
while($p = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $urls[] = ['loc' => BASE_URL . 'product-detail.php?id=' . $p['id'], 'priority' => '0.8', 'lastmod' => date('c', strtotime($p['created_at']))];
}
$xml = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n';
foreach($urls as $u) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
    if (isset($u['lastmod'])) $xml .= "    <lastmod>" . $u['lastmod'] . "</lastmod>\n";
    $xml .= "    <priority>" . $u['priority'] . "</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= '</urlset>';
file_put_contents(__DIR__ . '/../sitemap.xml', $xml);
echo "sitemap.xml generated\n";