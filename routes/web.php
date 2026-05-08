<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceWidgetController;
use App\Http\Controllers\WidgetController;
use App\Http\Controllers\DomainController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
 
Route::get('/fwd', [FaceWidgetController::class, 'index'])->name('widgets.index');
Route::get('/fwd/widgets', [FaceWidgetController::class, 'index']);
Route::get('/fwd/widgets/create', [FaceWidgetController::class, 'create'])->name('widgets.create');
Route::post('/fwd/widgets', [FaceWidgetController::class, 'store'])->name('widgets.store');
Route::get('/fwd/widgets/{widget}', [FaceWidgetController::class, 'show'])->name('widgets.show');
Route::get('/fwd/widgets/{widget}/edit', [FaceWidgetController::class, 'edit'])->name('widgets.edit');
Route::put('/fwd/widgets/{widget}', [FaceWidgetController::class, 'update'])->name('widgets.update');
Route::delete('/fwd/widgets/{widget}', [FaceWidgetController::class, 'destroy'])->name('widgets.destroy');
 
// ── Domain verification (authenticated dashboard users) ───────────────────────
Route::group([], function () {
    Route::get('/fwd/domains',                             [DomainController::class, 'index'])      ->name('domains.index');
    Route::post('/fwd/domains',                            [DomainController::class, 'store'])      ->name('domains.store');
    Route::post('/fwd/domains/{domain}/verify/meta',       [DomainController::class, 'verifyMeta']) ->name('domains.verify.meta');
    Route::post('/fwd/domains/{domain}/verify/dns',        [DomainController::class, 'verifyDns'])  ->name('domains.verify.dns');
    Route::delete('/fwd/domains/{domain}',                 [DomainController::class, 'destroy'])    ->name('domains.destroy');
    Route::post('/fwd/domains/{domain}/force-verify',      [DomainController::class, 'forceVerify'])->name('domains.force.verify');
    Route::post('/fwd/domains/{domain}/revoke',            [DomainController::class, 'revoke'])     ->name('domains.revoke');
});
 
Route::withoutMiddleware([VerifyCsrfToken::class])->group(function () {
 
    Route::get('/fwd/widget/{id}/script', [WidgetController::class, 'script'])->name('widget.script');
    Route::get('/fwd/widget/{id}/iframe', [WidgetController::class, 'iframe'])->name('widget.iframe');
    Route::get('/fwd/chat/{id}', [WidgetController::class, 'directChat'])->name('widget.chat');
 
    Route::options('/fwd/widget/{id}/track-hit', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', '*');
    });
 
    Route::post('/fwd/widget/{id}/track-hit', [WidgetController::class, 'trackHit'])->name('widget.track');
 
    // -----------------------------------------------------------------------
    // Widget domain validation + token issuance
    // Called by the widget loader (serve.blade.php) on every page load.
    // No CSRF needed — cross-origin POST from external websites.
    // -----------------------------------------------------------------------
    Route::options('/fwd/api/widget/init', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', '*');
    });
 
    Route::post('/fwd/api/widget/init', [WidgetController::class, 'initWidget'])->name('widget.init');
 
    // -----------------------------------------------------------------------
    // Face API Proxy (to bypass SSL certificate issues)
    // Allows widget to call Face API through backend proxy with SSL verification disabled
    // -----------------------------------------------------------------------
    Route::options('/fwd/api/face-proxy', function () {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', '*');
    });
 
    Route::post('/fwd/api/face-proxy', [WidgetController::class, 'faceApiProxy'])->name('api.face-proxy');
 
    // -----------------------------------------------------------------------
    // Serve JS files through PHP — guarantees CORS headers on every server
    // -----------------------------------------------------------------------
    Route::get('/fwd/widget-js/{file}', function (string $file) {
        $allowed = ['widget.js', 'camera.js', 'api.js', 'ui.js'];
 
        if (!in_array($file, $allowed, true)) {
            abort(404);
        }
 
        $path = public_path('js/' . $file);
 
        if (!file_exists($path)) {
            abort(404);
        }
 
        return response(file_get_contents($path), 200, [
            'Content-Type'                => 'application/javascript; charset=UTF-8',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control'               => 'public, max-age=3600',
        ]);
    })->where('file', '[a-zA-Z0-9._-]+')->name('widget.js');
 
});
 
Route::get('/fwd/api/widget/{id}', [WidgetController::class, 'iframe'])->name('widget.serve');





// Keep-alive route for UptimeRobot
Route::get('/ping', function () {
    return response('Server is awake', 200)->header('Content-Type', 'text/plain');
});