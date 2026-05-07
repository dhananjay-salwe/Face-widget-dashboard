<?php
// CORS header must be first — before any logic that could crash
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$allowed = ['widget.js', 'camera.js', 'api.js', 'ui.js'];
$file    = isset($_GET['f']) ? basename($_GET['f']) : '';

if (!$file || !in_array($file, $allowed, true)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    exit('Not found: ' . htmlspecialchars($file));
}

// public/widget-js/load.php → go up one level to public/ → then into js/
$jsDir = dirname(__DIR__) . '/js/';
$path  = realpath($jsDir . $file);

// realpath() returns false if file doesn't exist
// Also verify it's actually inside the js directory (prevent path traversal)
if ($path === false || strpos($path, realpath($jsDir)) !== 0) {
    http_response_code(404);
    header('Content-Type: text/plain');
    exit('File not found. Looking in: ' . $jsDir . $file);
}

header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

readfile($path);