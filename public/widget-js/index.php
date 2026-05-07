<?php

// Serve widget JS files with CORS headers.
// This is a physical PHP file — Apache finds it directly,
// no .htaccess rewriting needed. Works on any server.

$allowed = ['widget.js', 'camera.js', 'api.js', 'ui.js'];

// Get requested filename from URL
// Handles both /widget-js/widget.js and /widget-js/?file=widget.js
$uri  = $_SERVER['REQUEST_URI'] ?? '';
$file = basename(parse_url($uri, PHP_URL_PATH));

if (!$file || !in_array($file, $allowed, true)) {
    http_response_code(404);
    exit('Not found');
}

$path = dirname(__DIR__) . '/js/' . $file;

if (!file_exists($path)) {
    http_response_code(404);
    exit('File not found: ' . $file);
}

header('Content-Type: application/javascript; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Cache-Control: public, max-age=3600');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

readfile($path);