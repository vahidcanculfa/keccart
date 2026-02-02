<?php
function rate_limit($key, $limit = 10, $seconds = 60) {
    $dir = __DIR__ . '/../storage/ratelimit/';
    if (!file_exists($dir)) mkdir($dir, 0755, true);
    $file = $dir . md5($key) . '.json';
    $data = ['count' => 0, 'ts' => time()];
    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        $json = @json_decode($raw, true);
        if ($json) $data = $json;
    }
    $now = time();
    if ($now - $data['ts'] > $seconds) {
        $data = ['count' => 1, 'ts' => $now];
    } else {
        $data['count']++;
    }
    file_put_contents($file, json_encode($data));
    return $data['count'] <= $limit;
}
