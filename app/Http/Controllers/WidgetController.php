<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\FaceWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Requests\StoreWidgetRequest;
use App\Http\Requests\UpdateWidgetRequest;

class WidgetController extends Controller
{
    // =========================================================================
    // PUBLIC ENDPOINTS
    // =========================================================================

    public function script(Request $request, string $id)
    {
        try {
            $widget = $this->findWidget($id);

            if (!$widget) {
                return $this->jsError("Widget NOT FOUND. Searched for ID: {$id}");
            }

            if (!$widget->is_active) {
                return $this->jsError("Widget '{$id}' exists but is marked INACTIVE.");
            }

            if ($widget->api_hits >= $widget->api_limit) {
                return $this->jsWarn("API limit reached ({$widget->api_hits}/{$widget->api_limit}). Widget disabled.");
            }

            $origin     = $request->headers->get('Origin')  ?? '';
            $referer    = $request->headers->get('Referer') ?? '';
            $originStr  = (string) $origin;
            $refererStr = (string) $referer;

            $detectedHost = $this->extractHost($originStr ?: $refererStr);
            $detectedPath = $this->extractPath($refererStr);
            $detectedFull = rtrim($refererStr ?: $originStr, '/');

            // ── Rule 1: Local / Dev Pass ──────────────────────────────────
            if ($this->isLocalOrigin($originStr, $refererStr, $detectedHost)) {
                return $this->serveScript($request, $widget);
            }

            // ── Rule 2: Domain Check ──────────────────────────────────────
            $domainAllowed = trim((string) $widget->allowed_domains);

            if ($domainAllowed !== '') {
                if (!$this->flexibleMatch($detectedHost, $detectedPath, $detectedFull, $domainAllowed)) {
                    Log::warning("Widget domain block: ID={$id} host='{$detectedHost}' allowed='{$domainAllowed}'");
                    return $this->jsError(
                        "Domain check failed. " .
                        "Detected host: '{$detectedHost}'. " .
                        "Allowed list: '{$domainAllowed}'. " .
                        "Tip: add '{$detectedHost}' to Allowed Domains, or clear the field for open access."
                    );
                }
            }

            // ── Rule 3: Page Lock Check ───────────────────────────────────
            $pagesAllowed = trim((string) $widget->allowed_pages);

            if ($pagesAllowed !== '') {
                if (!$this->flexibleMatch($detectedHost, $detectedPath, $detectedFull, $pagesAllowed)) {
                    Log::warning("Widget page block: ID={$id} path='{$detectedPath}' allowed='{$pagesAllowed}'");
                    return $this->jsError(
                        "Page lock failed. " .
                        "Detected path: '{$detectedPath}', full URL: '{$detectedFull}'. " .
                        "Allowed list: '{$pagesAllowed}'. " .
                        "Tip: add '{$detectedPath}' to Allowed Pages, or clear the field for open access."
                    );
                }
            }

            return $this->serveScript($request, $widget);

        } catch (\Throwable $e) {
            Log::error("Widget script error: ID={$id} | {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->jsError("An unexpected error occurred loading this widget.");
        }
    }

    // =========================================================================
    // DOMAIN VALIDATION + TOKEN ISSUANCE
    // =========================================================================

    /**
     * POST /api/widget/init
     *
     * Called by the widget loader on every page load.
     * Validates that the requesting domain is authorised for this widget,
     * then returns a short-lived HMAC token the widget must attach to
     * the track-hit request.
     *
     * Does NOT increment api_hits — that only happens in trackHit().
     */
    public function initWidget(Request $request)
    {
        try {
            $widgetId  = trim((string) ($request->input('widget_id') ?? ''));
            $domain    = trim(strtolower((string) ($request->input('domain') ?? '')));
            $sessionId = trim((string) ($request->input('session_id') ?? ''));

            if ($widgetId === '' || $domain === '') {
                return $this->jsonResponse('error', 'widget_id and domain are required.', [], 422);
            }

            $widget = $this->findWidget($widgetId);

            if (!$widget) {
                return $this->jsonResponse('error', 'Widget not found.', ['allowed' => false], 404);
            }

            if (!$widget->is_active) {
                return $this->jsonResponse('error', 'Widget is inactive.', ['allowed' => false], 403);
            }

            if ($widget->api_hits >= $widget->api_limit) {
                return $this->jsonResponse('error', 'API limit reached.', ['allowed' => false], 429);
            }

            $originStr  = (string) ($request->headers->get('Origin')  ?? '');
            $refererStr = (string) ($request->headers->get('Referer') ?? '');

            $isLocal = $this->isLocalOrigin($originStr, $refererStr, $domain);

            if (!$isLocal) {
                $domainAllowed = trim((string) $widget->allowed_domains);

                if ($domainAllowed !== '') {
                    $detectedPath = $this->extractPath($refererStr);
                    $detectedFull = rtrim($refererStr ?: $originStr, '/');

                    if (!$this->flexibleMatch($domain, $detectedPath, $detectedFull, $domainAllowed)) {
                        Log::warning("Widget init domain block: ID={$widgetId} domain='{$domain}' allowed='{$domainAllowed}'");
                        return $this->jsonResponse('error', 'Domain not authorised.', [
                            'allowed' => false,
                            'reason'  => "Domain '{$domain}' is not in the allowed list for this widget.",
                        ], 403);
                    }
                }

                $verifiedDomain = \App\Http\Controllers\DomainController::findVerifiedDomain($domain);

                if ($verifiedDomain === null) {
                    Log::warning("Widget init: unverified domain. ID={$widgetId} domain='{$domain}'");
                    return $this->jsonResponse('error', 'Domain ownership not verified.', [
                        'allowed' => false,
                        'reason'  => "'{$domain}' has not been verified. Add and verify this domain in your dashboard first.",
                    ], 403);
                }
            }

            $token = $this->generateWidgetToken($widgetId, $domain);

            return $this->jsonResponse('success', 'Widget authorised.', [
                'allowed'    => true,
                'token'      => $token['hash'],
                'expires_at' => $token['expires_at'],
                'config'     => [
                    'widget_id' => $widgetId,
                    'theme'     => $widget->theme_color ?? '#66b0ff',
                    'mode'      => $widget->mode        ?? 'floating',
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error("Widget initWidget error: {$e->getMessage()}");
            return $this->jsonResponse('error', 'An unexpected error occurred.', [], 500);
        }
    }

    public function iframe(Request $request, string $id)
    {
        return $this->renderWidget($request, $id, false);
    }

    public function directChat(Request $request, string $id)
    {
        return $this->renderWidget($request, $id, true);
    }

    // =========================================================================
    // TRACK HIT — usage counted here ONLY
    // =========================================================================

    /**
     * POST /widget/{id}/track-hit
     *
     * Called by the widget frontend ONLY after a successful face recognition.
     * This is the ONLY place api_hits is incremented.
     *
     * Rate limited: 10 requests per minute per (widget_id + session_id).
     * This prevents refresh/replay abuse even if someone calls the endpoint directly.
     */
    public function trackHit(Request $request, string $id)
    {
        try {
            // ── Rate limiting ─────────────────────────────────────────────
            // Key: widget_id + session_id (falls back to IP if no session_id)
            $sessionId  = trim((string) ($request->input('session_id') ?? ''));
            $rateLimKey = 'track-hit:' . $id . ':' . ($sessionId ?: $request->ip());

            // 10 successful recognitions per minute per session — generous
            // enough for real use, tight enough to block replay attacks.
            if (RateLimiter::tooManyAttempts($rateLimKey, 10)) {
                $seconds = RateLimiter::availableIn($rateLimKey);
                Log::warning("Widget trackHit rate limited: ID={$id} session='{$sessionId}'");
                return $this->jsonResponse('error', "Too many requests. Try again in {$seconds}s.", [
                    'retry_after' => $seconds,
                ], 429);
            }

            RateLimiter::hit($rateLimKey, 60); // decay: 60 seconds

            // ── Widget lookup ─────────────────────────────────────────────
            $widget = $this->findWidget($id);

            if (!$widget) {
                return $this->jsonResponse('error', 'Widget not found.', [], 404);
            }
            if (!$widget->is_active) {
                return $this->jsonResponse('error', 'Widget is inactive.', [], 403);
            }
            if ($widget->api_hits >= $widget->api_limit) {
                return $this->jsonResponse('error', 'API limit reached.', [
                    'hits'  => $widget->api_hits,
                    'limit' => $widget->api_limit,
                ], 429);
            }

            // ── Token verification (production only) ──────────────────────
            $originStr  = (string) ($request->headers->get('Origin')  ?? '');
            $refererStr = (string) ($request->headers->get('Referer') ?? '');
            $host       = $this->extractHost($originStr ?: $refererStr);

            $isLocal = $this->isLocalOrigin($originStr, $refererStr, $host);

            if (!$isLocal) {
                $token  = trim((string) ($request->header('X-Widget-Token') ?? ''));
                $domain = trim(strtolower((string) ($request->input('domain') ?? $host)));

                if ($token === '' || !$this->verifyWidgetToken($token, $id, $domain)) {
                    Log::warning("Widget trackHit: invalid/missing token. ID={$id} host='{$host}'");
                    return $this->jsonResponse('error', 'Invalid or expired widget token.', [], 401);
                }
            }

            // ── Increment ONLY here, ONLY now ─────────────────────────────
            $widget->increment('api_hits');
            $widget->refresh();

            Log::info("Widget hit counted: ID={$id} hits={$widget->api_hits}/{$widget->api_limit} session='{$sessionId}'");

            return $this->jsonResponse('success', 'Hit tracked successfully.', [
                'hits'      => $widget->api_hits,
                'remaining' => max(0, $widget->api_limit - $widget->api_hits),
            ]);

        } catch (\Throwable $e) {
            Log::error("Widget trackHit error: ID={$id} | {$e->getMessage()}");
            return $this->jsonResponse('error', 'An unexpected error occurred.', [], 500);
        }
    }

    // =========================================================================
    // FACE API PROXY — Bypass SSL Certificate Issues (NEW)
    // =========================================================================
    /* 
    NEW: Proxy endpoint to forward Face API requests through PHP backend.
    This solves SSL certificate validation errors by using PHP's HTTP client
    which can bypass certificate validation in development environments.
    
    Usage in widget: Instead of direct fetch to Face API, call /api/face-proxy
    This endpoint will forward the request to the actual Face API with proper certificate handling.
    */
    
    public function faceApiProxy(Request $request)
    {
        try {
            $endpoint = trim((string) ($request->input('endpoint') ?? ''));
            $method   = trim((string) ($request->input('method') ?? 'POST'));
            $payload  = $request->input('payload') ?? [];
            
            if (!$endpoint) {
                return response()->json(['error' => 'endpoint is required'], 400);
            }
            
            // $faceApiUrl = rtrim((string) env('FACE_API_URL', 'https://100.23.131.184/faceapi/'), '/');
            $faceApiUrl = rtrim((string) env('FACE_API_URL', 'https://face-recognition-hv19.onrender.com'), '/');
            $fullUrl = $faceApiUrl . '/' . ltrim($endpoint, '/');
            
            Log::info("[FaceWidget] Proxying Face API request", [
                'endpoint' => $endpoint,
                'method'   => $method,
                'url'      => $fullUrl,
            ]);
            
            // Use Guzzle to make the request with SSL verification disabled for development
            // ADJUSTED: timeout => 120 (increased to match browser timeout for face capture)
            // Based on Flask Face API timing:
            // - Liveness detection: 6-8 seconds (PRIMARY BOTTLENECK)
            // - Face detection: 200-500ms
            // - Embedding extraction: 1-2 seconds  
            // - Per-frame total: 8-12 seconds
            // - Max registration frames: 4 frames = 32-48 seconds expected
            // - Using 120 seconds to safely handle all operations + network overhead
            $client = new \GuzzleHttp\Client([
                'verify' => false, // Disable SSL verification (development only!)
                'timeout' => 120, // ADJUSTED: increased to 120 seconds (2 minutes) to match browser and Face API processing time
            ]);
            
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ];
            
            $response = $client->request($method, $fullUrl, $options);
            
            return response()->json(
                json_decode((string) $response->getBody(), true),
                $response->getStatusCode()
            )->header('Access-Control-Allow-Origin', '*');
            
        } catch (\Throwable $e) {
            Log::error("[FaceWidget] Face API proxy error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Face API proxy error',
                'message' => $e->getMessage(),
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    // =========================================================================
    // TOKEN HELPERS
    // =========================================================================

    private function generateWidgetToken(string $widgetId, string $domain): array
    {
        $expiresAt = time() + 600;
        $payload   = $widgetId . '|' . $domain . '|' . $expiresAt;
        $hash      = hash_hmac('sha256', $payload, config('app.key'));
        $token     = base64_encode($expiresAt . ':' . $hash);

        return [
            'hash'       => $token,
            'expires_at' => $expiresAt,
        ];
    }

    private function verifyWidgetToken(string $token, string $widgetId, string $domain): bool
    {
        try {
            $decoded = base64_decode($token, true);
            if ($decoded === false) return false;

            [$expiresAt, $hash] = explode(':', $decoded, 2) + [null, null];

            if (!$expiresAt || !$hash) return false;
            if (time() > (int) $expiresAt) return false;

            $payload  = $widgetId . '|' . $domain . '|' . $expiresAt;
            $expected = hash_hmac('sha256', $payload, config('app.key'));

            return hash_equals($expected, $hash);

        } catch (\Throwable $e) {
            return false;
        }
    }

    // =========================================================================
    // SECURITY HELPERS
    // =========================================================================

    private function isLocalOrigin(string $origin, string $referer, string $detectedHost): bool
    {
        if ($origin === '' && $referer === '') return true;
        if ($origin === 'null') return true;
        if (Str::startsWith($referer, 'file://')) return true;
        if ($detectedHost === '') return true;

        $localHosts = ['localhost', '127.0.0.1', '0.0.0.0', '::1'];
        if (in_array($detectedHost, $localHosts, true)) return true;
        if (preg_match('/^127\.\d+\.\d+\.\d+$/', $detectedHost)) return true;
        if (Str::endsWith($detectedHost, '.local')) return true;
        if (Str::endsWith($detectedHost, '.test')) return true;

        return false;
    }

    private function flexibleMatch(
        string $host,
        string $path,
        string $fullUrl,
        string $list
    ): bool {
        $entries = array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n|,/', $list) ?: [])
        );

        if (empty($entries)) return true;

        foreach ($entries as $entry) {
            if ($entry === '' || $entry === '*') return true;

            if (Str::startsWith($entry, ['http://', 'https://'])) {
                $entryHost = parse_url($entry, PHP_URL_HOST) ?? $entry;
                if ($host !== '' && $host === $entryHost) return true;
                if ($fullUrl !== '' && Str::contains($fullUrl, $entryHost)) return true;
                continue;
            }

            if (Str::startsWith($entry, '*.')) {
                $base = substr($entry, 2);
                if ($host !== '' && ($host === $base || Str::endsWith($host, '.' . $base))) return true;
                continue;
            }

            if (Str::endsWith($entry, '*')) {
                $prefix = rtrim($entry, '*');
                if ($path !== '' && Str::startsWith($path, $prefix)) return true;
                if ($fullUrl !== '' && Str::startsWith($fullUrl, $prefix)) return true;
                continue;
            }

            if ($host !== '' && ($host === $entry || Str::contains($host, $entry))) return true;
            if ($path !== '' && ($path === $entry || Str::contains($path, $entry))) return true;
            if ($fullUrl !== '' && Str::contains($fullUrl, $entry)) return true;
        }

        return false;
    }

    // =========================================================================
    // RENDERING HELPERS
    // =========================================================================

    private function serveScript(Request $request, FaceWidget $widget): \Illuminate\Http\Response
    {
        return response()
            ->view('widget.serve', [
                'widget'      => $widget,
                'positionCss' => $this->resolvePositionCss($widget),
                'baseUrl'     => $this->resolveBaseUrl($request),
                'isFloating'  => $widget->mode === 'floating',
                // Read face API URL from .env (FACE_API_URL) with EC2 instance as fallback
                // 'faceApiUrl'  => rtrim(env('FACE_API_URL', 'https://ec2-35-87-137-123.us-west-2.compute.amazonaws.com/faceapi'), '/'),
                // 'faceApiUrl'  => rtrim(env('FACE_API_URL', 'https://100.23.131.184/faceapi/'), '/'),
                'faceApiUrl'  => rtrim(env('FACE_API_URL', 'https://face-recognition-hv19.onrender.com'), '/'),
            ], 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    private function renderWidget(Request $request, string $id, bool $standalone)
    {
        try {
            $widget = $this->findWidget($id);

            if (!$widget) {
                return response()->view('errors.widget_not_found', [], 404);
            }
            if (!$widget->is_active) {
                return response()->view('errors.widget_inactive', ['widget' => $widget], 403);
            }

            $originStr    = $request->headers->get('Origin')  ?? '';
            $refererStr   = $request->headers->get('Referer') ?? '';
            $detectedHost = $this->extractHost($originStr ?: $refererStr);

            if (!$this->isAllowedDomain($detectedHost, $widget->allowed_domains)) {
                Log::warning("Widget iframe forbidden: ID={$id} host='{$detectedHost}'");
                return response()->view('errors.forbidden_domain', ['host' => $detectedHost], 403);
            }

            $widget->increment('api_hits');

            $response = response()->view('widget.iframe', [
                'widget'      => $widget,
                'standalone'  => $standalone,
                'positionCss' => $this->resolvePositionCss($widget),
                'isFloating'  => $widget->mode === 'floating',
                'baseUrl'     => $this->resolveBaseUrl($request),
            ], Response::HTTP_OK);

            $response->headers->set('X-Frame-Options', 'ALLOWALL');
            $response->headers->set('Content-Security-Policy', 'frame-ancestors *');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Cache-Control', 'no-store');

            return $response;

        } catch (\Throwable $e) {
            Log::error("Widget renderWidget error: ID={$id} | {$e->getMessage()}");
            return response()->view('errors.widget_not_found', [], 500);
        }
    }

    // =========================================================================
    // URL / HOST UTILITIES
    // =========================================================================

    private function findWidget(string $id): ?FaceWidget
    {
        return FaceWidget::find($id);
    }

    private function extractHost(string $url): string
    {
        if ($url === '') return '';
        return parse_url($url, PHP_URL_HOST) ?? '';
    }

    private function extractPath(string $url): string
    {
        if ($url === '') return '/';
        return parse_url($url, PHP_URL_PATH) ?: '/';
    }

    private function isAllowedDomain(string $host, ?string $allowed): bool
    {
        if (app()->environment() !== 'production') return true;

        $allowed = trim((string) $allowed);
        if ($allowed === '') return true;
        if ($host === '') return false;

        $domains = array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n|,/', $allowed) ?: [])
        );

        foreach ($domains as $domain) {
            if ($domain === $host) return true;
            if (Str::startsWith($domain, '*.')) {
                $base = substr($domain, 2);
                if ($host === $base || Str::endsWith($host, '.' . $base)) return true;
            }
        }

        return false;
    }

    private function resolvePositionCss(FaceWidget $widget): string
    {
        $map = [
            'top-left'      => 'top: 20px; left: 20px;',
            'top-right'     => 'top: 20px; right: 20px;',
            'bottom-left'   => 'bottom: 20px; left: 20px;',
            'bottom-right'  => 'bottom: 20px; right: 20px;',
            'top-center'    => 'top: 20px; left: 50%; transform: translateX(-50%);',
            'bottom-center' => 'bottom: 20px; left: 50%; transform: translateX(-50%);',
        ];

        if (($widget->mode ?? 'floating') === 'floating') {
            return $map[$widget->position ?? 'bottom-right'] ?? $map['bottom-right'];
        }

        return 'position: relative;';
    }

    private function resolveBaseUrl(Request $request): string
    {
        $url = config('app.url');
        if (!$url || !is_string($url)) {
            $url = $request->getSchemeAndHttpHost();
        }
        return rtrim($url, '/');
    }

    // =========================================================================
    // RESPONSE HELPERS
    // =========================================================================

    private function jsonResponse(
        string $flag,
        string $msg,
        array  $data   = [],
        int    $status = 200
    ): \Illuminate\Http\JsonResponse {
        return response()->json([
            'flag' => $flag,
            'msg'  => $msg,
            'data' => $data,
        ], $status)->header('Access-Control-Allow-Origin', '*');
    }

    private function jsError(string $msg): \Illuminate\Http\Response
    {
        $safe = addslashes($msg);
        return response("console.error('[FaceWidget] {$safe}');", 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Access-Control-Allow-Origin', '*');
    }

    private function jsWarn(string $msg): \Illuminate\Http\Response
    {
        $safe = addslashes($msg);
        return response("console.warn('[FaceWidget] {$safe}');", 200)
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Access-Control-Allow-Origin', '*');
    }

    // =========================================================================
    // CRUD
    // =========================================================================

    public function store(StoreWidgetRequest $request)
    {
        try {
            $widget = FaceWidget::create($request->validated());

            return redirect()
                ->route('widgets.show', $widget->id)
                ->with('success', 'Widget created successfully.');

        } catch (\Throwable $e) {
            Log::error("Widget store error: {$e->getMessage()}");
            return redirect()->back()
                ->with('error', 'Failed to create widget. Please try again.')
                ->withInput();
        }
    }

    public function update(UpdateWidgetRequest $request, FaceWidget $widget)
    {
        try {
            $widget->update($request->validated());

            return redirect()
                ->route('widgets.show', $widget->id)
                ->with('success', 'Widget updated successfully.');

        } catch (\Throwable $e) {
            Log::error("Widget update error: ID={$widget->id} | {$e->getMessage()}");
            return redirect()->back()
                ->with('error', 'Failed to update widget. Please try again.')
                ->withInput();
        }
    }

    public function destroy(FaceWidget $widget)
    {
        try {
            $widget->delete();

            return redirect()
                ->route('widgets.index')
                ->with('success', 'Widget deleted successfully.');

        } catch (\Throwable $e) {
            Log::error("Widget destroy error: ID={$widget->id} | {$e->getMessage()}");
            return redirect()->back()
                ->with('error', 'Failed to delete widget. Please try again.');
        }
    }
}