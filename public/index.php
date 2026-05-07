<?php


// Emergency CORS bypass for widget JS files
// Remove this once proper routing is confirmed working
if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/widget-js/')) {
    $file = basename($_SERVER['REQUEST_URI']);
    $allowed = ['widget.js', 'camera.js', 'api.js', 'ui.js'];
    
    if (in_array($file, $allowed)) {
        $path = __DIR__ . '/js/' . $file;
        if (file_exists($path)) {
            header('Access-Control-Allow-Origin: *');
            header('Content-Type: application/javascript; charset=UTF-8');
            header('Cache-Control: public, max-age=3600');
            readfile($path);
            exit;
        }
    }
}



use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);