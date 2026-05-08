(function () {
    'use strict';
    if (document.getElementById('face-widget-root')) return;

    <!-- var BASE_URL     = @json($baseUrl); -->
    <!-- This is the extra verification did by Narendra -->
    var BASE_URL     = new URL(document.currentScript.src).origin; // auto-detect base URL from script src

    var SCRIPT_ORIGIN = new URL(document.currentScript.src).origin;

    if (SCRIPT_ORIGIN !== BASE_URL) {
        console.warn('[FaceWidget] Script source mismatch');
    }
    var WIDGET_ID    = @json($widget->id);
    var IS_FLOATING  = @json($isFloating);
    var POSITION_CSS = @json($positionCss);
    var THEME        = @json($widget->theme_color  ?? '#66b0ff');
    var BTN_COLOR    = @json($widget->button_color ?? $widget->theme_color ?? '#66b0ff');
    var TITLE        = @json($widget->welcome_title   ?? 'Face Authentication');
    var MESSAGE      = @json($widget->welcome_message ?? 'Click below to verify your identity');
    var BTN_TEXT          = @json($widget->button_text     ?? 'Start Scan');
    // SHOW_START_BUTTON: true = manual button visible, false = auto-start camera on load
    var SHOW_START_BUTTON = @json($widget->show_start_button ?? true);
    // WIDGET_AUTH_TYPE: 'register' or 'login' — determines API endpoint
    var WIDGET_AUTH_TYPE  = @json($widget->widget_auth_type ?? 'register');

    // Grab currentScript immediately — it becomes null after any async call.
    var _currentScript = document.currentScript;

    // ====================================================================
    // SESSION ID — persisted in localStorage to detect refresh abuse
    // A new session_id is generated once per browser session.
    // Sent with every API request so the server can rate-limit per session.
    // ====================================================================
    var SESSION_ID = (function () {
        try {
            var key = 'fw_session_' + WIDGET_ID;
            var existing = localStorage.getItem(key);
            if (existing) return existing;
            var id = typeof crypto !== 'undefined' && crypto.randomUUID
                ? crypto.randomUUID()
                : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                    var r = Math.random() * 16 | 0;
                    return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                  });
            localStorage.setItem(key, id);
            return id;
        } catch (e) {
            // localStorage blocked (e.g. private browsing) — use in-memory id
            return 'mem_' + Math.random().toString(36).substr(2, 12);
        }
    })();

    // ====================================================================
    // STEP 1 — Domain Validation Gate
    // Calls /api/widget/init before building anything.
    // On failure, silently exits — no widget is rendered.
    // ====================================================================
    var _widgetToken = null;

    function initWidgetGate(callback) {
        var domain = window.location.hostname;

        fetch(BASE_URL + '/api/widget/init', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                <!-- 'X-Requested-With': 'XMLHttpRequest' -->
            },
            body: JSON.stringify({
                widget_id:  WIDGET_ID,
                domain:     domain,
                session_id: SESSION_ID
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || !data.data || data.data.allowed !== true) {
                console.warn('[FaceWidget] Domain not authorised:', domain);
                return;
            }
            _widgetToken = data.data.token || null;
            callback();
        })
        .catch(function (err) {
            console.error('[FaceWidget] Init error:', err);
            callback(); // fail-open on network error
        });
    }

    // ====================================================================
    // STEP 2 — Widget Bootstrap (runs only after gate passes)
    // ====================================================================
    function bootstrapWidget() {

        var host = document.createElement('div');
        host.id  = 'face-widget-root';
        host.style.cssText = IS_FLOATING
            ? 'position:fixed;z-index:2147483647;' + POSITION_CSS
            : 'display:inline-block;';

        var shadow = host.attachShadow({ mode: 'open' });
        window.FaceWidgetShadowRoot = shadow;
        window.WidgetConfig = { widget_id: WIDGET_ID, theme: THEME, base_url: BASE_URL };

        // ====================================================================
        // STYLES
        // ====================================================================
        var style = document.createElement('style');
        style.textContent = [
            ':host {',
            '  --widget-theme: ' + THEME + ';',
            '  --widget-button: ' + BTN_COLOR + ';',
            '  all: initial;',
            '  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;',
            '}',
            '* { box-sizing: border-box; margin: 0; padding: 0; }',

            '.scanner-card {',
            '  width: 280px;',
            '  background: #ffffff;',
            '  border-radius: 16px;',
            '  box-shadow: 0 8px 30px rgba(15,23,42,.13);',
            '  padding: 20px 16px 16px;',
            '  display: flex;',
            '  flex-direction: column;',
            '  align-items: center;',
            '  gap: 10px;',
            '}',

            '.widget-title {',
            '  font-size: .95rem; font-weight: 700; color: #111827;',
            '  align-self: flex-start;',
            '}',
            '.widget-message {',
            '  font-size: .78rem; color: #6b7280;',
            '  align-self: flex-start; line-height: 1.4;',
            '}',

            '.scanner-circle {',
            '  position: relative;',
            '  width: 160px; height: 160px;',
            '  border-radius: 50%;',
            '  overflow: hidden;',
            '  background: #e8f0fe;',
            '  border: 3px solid var(--widget-theme);',
            '  flex-shrink: 0;',
            '}',
            '.scanner-video {',
            '  position: absolute; top:0; left:0;',
            '  width:100%; height:100%; object-fit:cover;',
            '  /* Mirror the video preview for normal selfie UX */',
            '  transform: scaleX(-1);',
            '}',
            '.captured-image {',
            '  display:none; position:absolute; top:0; left:0;',
            '  width:100%; height:100%; object-fit:cover;',
            '}',
            '.face-silhouette {',
            '  position: absolute; top:0; left:0;',
            '  width:100%; height:100%;',
            '  display: flex; align-items: center; justify-content: center;',
            '}',
            '.scan-line {',
            '  position: absolute; left:0; right:0; height: 2px;',
            '  background: var(--widget-theme); opacity: 0;',
            '  top: 20%;',
            '}',
            '.status-text {',
            '  font-size: .8rem; color: #6b7280;',
            '  align-self: flex-start;',
            '}',
            '.start-button {',
            '  width: 100%;',
            '  padding: 9px 0;',
            '  border-radius: 999px;',
            '  border: none;',
            '  background: var(--widget-button);',
            '  color: #fff;',
            '  font-size: .88rem;',
            '  font-weight: 600;',
            '  cursor: pointer;',
            '  letter-spacing: .01em;',
            '  display: none;', /* JS will override to block if SHOW_START_BUTTON===true */
            '}',
            '.start-button:disabled { opacity: .6; cursor: default; }',

            '.scanner-circle.scanning { border-color: var(--widget-theme); animation: pulse 1.5s infinite; }',
            '.scanner-circle.recognized { border-color: #10b981; }',
            '.scanner-circle.error { border-color: #ef4444; }',

            '.scanner-circle.scanning .scan-line {',
            '  animation: scanline 2s ease-in-out infinite;',
            '}',

            '@keyframes pulse {',
            '  0%,100% { box-shadow: 0 0 0 0 rgba(102,176,255,.4); }',
            '  50%      { box-shadow: 0 0 0 8px rgba(102,176,255,0); }',
            '}',
            '@keyframes scanline {',
            '  0%   { top: 10%; opacity: 0; }',
            '  10%  { opacity: .8; }',
            '  90%  { opacity: .8; }',
            '  100% { top: 90%; opacity: 0; }',
            '}',
        ].join('\n');

        // ====================================================================
        // DOM — Card
        // ====================================================================
        var card = document.createElement('div');
        card.className = 'scanner-card';

        var titleEl = document.createElement('h2');
        titleEl.className = 'widget-title';
        titleEl.textContent = TITLE;

        var msgEl = document.createElement('p');
        msgEl.className = 'widget-message';
        msgEl.textContent = MESSAGE;

        var circle = document.createElement('div');
        circle.className = 'scanner-circle';
        circle.id = 'scannerCircle';

        var silhouette = document.createElement('div');
        silhouette.className = 'face-silhouette';
        silhouette.id = 'faceSilhouette';
        silhouette.innerHTML = '<svg width="90" height="90" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" opacity="0.35">'
            + '<ellipse cx="50" cy="38" rx="22" ry="26" fill="#94a3b8"/>'
            + '<ellipse cx="50" cy="85" rx="35" ry="25" fill="#94a3b8"/>'
            + '</svg>';

        var scanLine = document.createElement('div');
        scanLine.className = 'scan-line';
        scanLine.id = 'scanLine';

        var video = document.createElement('video');
        video.className = 'scanner-video';
        video.id = 'scannerVideo';
        video.autoplay = true;
        video.playsInline = true;
        video.muted = true;
        video.style.display = 'none';

        var capturedImg = document.createElement('img');
        capturedImg.className = 'captured-image';
        capturedImg.id = 'captured-image';
        capturedImg.alt = '';

        var canvas = document.createElement('canvas');
        canvas.id = 'capture-canvas';
        canvas.style.display = 'none';

        circle.appendChild(silhouette);
        circle.appendChild(scanLine);
        circle.appendChild(video);
        circle.appendChild(capturedImg);
        circle.appendChild(canvas);

        var statusP = document.createElement('p');
        statusP.className = 'status-text';
        statusP.id = 'statusText';
        statusP.textContent = 'Ready to scan';

        var btn = document.createElement('button');
        btn.className = 'start-button';
        btn.id = 'startButton';
        btn.textContent = BTN_TEXT;

        card.appendChild(titleEl);
        card.appendChild(msgEl);
        card.appendChild(circle);
        card.appendChild(statusP);
        card.appendChild(btn);
        shadow.appendChild(style);
        shadow.appendChild(card);

        // ====================================================================
        // INJECT — Floating vs Embedded
        // ====================================================================
        if (IS_FLOATING) {
            document.body.appendChild(host);
        } else {
            var container = document.getElementById('face-widget-container');
            if (container) {
                container.appendChild(host);
            } else if (_currentScript && _currentScript.parentNode) {
                _currentScript.parentNode.insertBefore(host, _currentScript.nextSibling);
            } else {
                document.body.appendChild(host);
            }
        }

        // ====================================================================
        // CAMERA
        // ====================================================================
        var activeStream = null;

        async function startCamera(videoElement) {
            try {
                var stream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
                });
                videoElement.srcObject = stream;
                activeStream = stream;
                await new Promise(function (resolve) {
                    videoElement.onloadedmetadata = function () { videoElement.play(); resolve(); };
                });
                var sil = shadow.getElementById('faceSilhouette');
                if (sil) sil.style.display = 'none';
                videoElement.style.display = 'block';
                return true;
            } catch (e) {
                console.error('[FaceWidget] Camera error:', e);
                return false;
            }
        }

        function stopCamera(videoElement) {
            if (activeStream) {
                activeStream.getTracks().forEach(function (t) { t.stop(); });
                activeStream = null;
            }
            if (videoElement) {
                videoElement.srcObject = null;
                videoElement.style.display = 'none';
            }
            var sil = shadow.getElementById('faceSilhouette');
            if (sil) sil.style.display = 'flex';
        }

        async function captureFrame(videoElement) {
            if (!videoElement.videoWidth || !videoElement.videoHeight) return null;
            var c = document.createElement('canvas');
            c.width  = videoElement.videoWidth;
            c.height = videoElement.videoHeight;
            var ctx = c.getContext('2d');
            
            // FIX: Mirror correction for face API
            // The video DISPLAY is mirrored (scaleX(-1) in CSS for normal UX)
            // But we need to send the non-mirrored frame to the face API
            // for correct facial landmark detection
            
            // Flip the canvas horizontally to correct the mirror effect
            // This ensures the face API receives the original (non-mirrored) frame
            ctx.translate(c.width, 0);   // Move origin to right side
            ctx.scale(-1, 1);             // Flip horizontally (remove mirror effect)
            ctx.drawImage(videoElement, 0, 0);
            
            return new Promise(function (resolve) {
                c.toBlob(function (blob) { resolve(blob || null); }, 'image/jpeg', 0.8);
            });
        }

        // ====================================================================
        // EXTERNAL FACE API
        // ====================================================================
        var API_BASE = @json($faceApiUrl); // injected from WidgetController via FACE_API_URL in .env
        
        var PROXY_URL = BASE_URL + '/api/face-proxy'; // Proxy endpoint avoids browser CORS preflight failures.
        
        /* OLD CODE: Using backend proxy to bypass SSL:
        // var PROXY_URL = BASE_URL + '/api/face-proxy'; // Proxy endpoint to bypass SSL issues
        // ISSUE: Proxy was adding too much latency, causing timeouts during liveness detection
        // SOLUTION: Use direct API calls instead (faster, fewer hops)
        */
        
        /* 
        IMPORTANT: SSL Certificate Issue - DIRECT CALLS
        
        Solution: Call Face API directly (self-signed cert is acceptable)
        The Flask API is running on same network with self-signed cert.
        Browser HTTPS validation is less strict when called directly.
        
        Flow: Widget → Face API Server (DIRECT)
        
        This avoids:
        - Backend proxy latency overhead
        - Request processing delays
        - Multiple hop network delay
        
        OLD CODE: Backend proxy (added 500-2000ms latency per request)
        NEW CODE: Direct API calls (immediate, single hop)
        */

        async function initRegister(clientId) {
            try {
                /* OLD CODE: Direct fetch to Face API (caused SSL error):
                // var r = await fetch(API_BASE + '/api/init_register', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ client_id: clientId })
                // });
                */
                
                // OLD CODE: Without timeout:
                // var r = await fetch(PROXY_URL, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         endpoint: '/api/init_register',
                //         method: 'POST',
                //         payload: { client_id: clientId }
                //     })
                // });
                
                // OLD CODE: Using backend proxy (added latency):
                // var abortController = new AbortController();
                // var timeoutId = setTimeout(function() { 
                //     abortController.abort(); 
                // }, 20000);
                // var r = await fetch(PROXY_URL, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         endpoint: '/api/init_register',
                //         method: 'POST',
                //         payload: { client_id: clientId }
                //     }),
                //     signal: abortController.signal
                // });
                
                // OLD CODE: Direct API call to Flask Face API (causes browser CORS preflight failure on the new Render API)
                // ADJUSTED: 20-second timeout for init_register (Flask API: ~100ms for session init)
                // UPDATED: Use WIDGET_AUTH_TYPE to determine endpoint (register or login)
                var abortController = new AbortController();
                var timeoutId = setTimeout(function() { 
                    abortController.abort(); 
                }, 20000); // 20 seconds timeout - init_register is fast
                
                // OLD CODE: Endpoint without Face API mount prefix returned 404 on the new Render API:
                // var endpoint = WIDGET_AUTH_TYPE === 'login' ? '/api/init_login' : '/api/init_register';

                // Determine endpoint based on widget auth type.
                // New Face API routes are mounted under /faceapi.
                var endpoint = WIDGET_AUTH_TYPE === 'login' ? '/faceapi/api/init_login' : '/faceapi/api/init_register';
                
                // var r = await fetch(API_BASE + endpoint, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ client_id: clientId }),
                //     signal: abortController.signal
                // });

                // OLD CODE: Route through Laravel proxy. This fixed CORS but added an extra server hop.
                // var r = await fetch(PROXY_URL, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         endpoint: endpoint,
                //         method: 'POST',
                //         payload: { client_id: clientId }
                //     }),
                //     signal: abortController.signal
                // });

                // NEW CODE: Direct call to the corrected /faceapi route.
                // Your Flask app has CORS enabled for these mounted endpoints, so this avoids proxy latency.
                var r = await fetch(API_BASE + endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ client_id: clientId }),
                    signal: abortController.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!r.ok) throw new Error('Init failed with status ' + r.status);
                return await r.json();
            } catch (e) {
                console.error('[FaceWidget] initRegister error:', e);
                console.error('[FaceWidget] API URL:', API_BASE);
                console.error('[FaceWidget] Error details:', {
                    message: e.message,
                    type: e.name,
                    stack: e.stack
                });
                return { success: false, error: e.message };
            }
        }

        async function processFrame(frameBlob, clientId) {
            try {
                var base64 = await new Promise(function (resolve, reject) {
                    var reader = new FileReader();
                    reader.onloadend = function () { resolve(reader.result.split(',')[1]); };
                    reader.onerror   = reject;
                    reader.readAsDataURL(frameBlob);
                });
                
                /* OLD CODE: Direct fetch to Face API (caused SSL error):
                // var r = await fetch(API_BASE + '/api/process_frame', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ frame: base64, client_id: clientId })
                // });
                */
                
                // OLD CODE: Without timeout:
                // var r = await fetch(PROXY_URL, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         endpoint: '/api/process_frame',
                //         method: 'POST',
                //         payload: { frame: base64, client_id: clientId }
                //     })
                // });
                
                // OLD CODE: Using backend proxy (added latency):
                // var abortController = new AbortController();
                // var timeoutId = setTimeout(function() { 
                //     abortController.abort(); 
                // }, 120000);
                // var r = await fetch(PROXY_URL, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         endpoint: '/api/process_frame',
                //         method: 'POST',
                //         payload: { frame: base64, client_id: clientId }
                //     }),
                //     signal: abortController.signal
                // });
                
                // OLD CODE: Direct API call to Flask Face API (causes browser CORS preflight failure on the new Render API)
                // ADJUSTED: AbortController with 120-second timeout for face capture and processing
                // Based on Flask Face API timing analysis:
                // - Liveness detection: 6-8 seconds (PRIMARY BOTTLENECK from check_liveness_production)
                // - Face detection: 200-500ms
                // - Embedding extraction: 1-2 seconds
                // - Duplicate checking: <500ms
                // - Per-frame total: 8-12 seconds
                // - Session needs MAX_REGISTRATION_FRAMES = 4 frames
                // - Total expected time: 32-48 seconds for registration
                // - Using 120 seconds (2 minutes) to safely handle all frame processing + network overhead
                var abortController = new AbortController();
                var timeoutId = setTimeout(function() { 
                    abortController.abort(); 
                }, 120000); // 120 seconds timeout - matches Face API liveness + processing time
                
                // var r = await fetch(API_BASE + '/api/process_frame', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ 
                //         frame: base64, 
                //         client_id: clientId
                //         // Device detection can be added if needed: device: navigator.userAgent.includes('Mobile') ? 'mobile' : 'desktop'
                //     }),
                //     signal: abortController.signal
                // });

                // OLD CODE: Use the Laravel proxy to avoid browser CORS checks against the Face API.
                // This doubles the upload path for every base64 camera frame and is too slow for liveness.
                // var r = await fetch(PROXY_URL, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         // OLD CODE: endpoint: '/api/process_frame',
                //         endpoint: '/faceapi/api/process_frame',
                //         method: 'POST',
                //         payload: { 
                //             frame: base64, 
                //             client_id: clientId
                //             // Device detection can be added if needed: device: navigator.userAgent.includes('Mobile') ? 'mobile' : 'desktop'
                //         }
                //     }),
                //     signal: abortController.signal
                // });

                // OLD CODE: endpoint without Face API mount prefix returned 404 on the new Render API.
                // var processEndpoint = '/api/process_frame';

                // NEW CODE: Direct call to the mounted Flask endpoint.
                var processEndpoint = '/faceapi/api/process_frame';
                var r = await fetch(API_BASE + processEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        frame: base64, 
                        client_id: clientId
                        // Device detection can be added if needed: device: navigator.userAgent.includes('Mobile') ? 'mobile' : 'desktop'
                    }),
                    signal: abortController.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return await r.json();
            } catch (e) {
                if (e.name === 'AbortError') {
                    console.error('[FaceWidget] processFrame TIMEOUT (120s exceeded) - Face API processing took too long');
                } else {
                    console.error('[FaceWidget] processFrame error:', e);
                    console.error('[FaceWidget] API URL:', API_BASE);
                    console.error('[FaceWidget] Error details:', {
                        message: e.message,
                        type: e.name
                    });
                }
                return { success: false };
            }
        }

        // ====================================================================
        // UI HELPERS
        // ====================================================================
        function getEl(id) { return shadow.getElementById(id); }

        function setIdleState() {
            var c = getEl('scannerCircle');
            if (c) c.className = 'scanner-circle';
            var s = getEl('statusText');
            if (s) s.textContent = 'Ready to scan';
            var sil = getEl('faceSilhouette');
            if (sil) sil.style.display = 'flex';
            var v = getEl('scannerVideo');
            if (v) v.style.display = 'none';
        }

        function setScanningState() {
            var c = getEl('scannerCircle');
            if (c) c.className = 'scanner-circle scanning';
        }

        function setRecognizedState() {
            var c = getEl('scannerCircle');
            if (c) c.className = 'scanner-circle recognized';
            var s = getEl('statusText');
            if (s) s.textContent = 'Recognized';
        }

        function setErrorState(msg) {
            var c = getEl('scannerCircle');
            if (c) c.className = 'scanner-circle error';
            var s = getEl('statusText');
            if (s) s.textContent = msg || 'Error';
        }

        function updateStatus(msg) {
            var s = getEl('statusText');
            if (s) s.textContent = msg;
        }

        function updateProgress(pct) {
            var s = getEl('statusText');
            if (s) s.dataset.progress = String(Math.max(0, Math.min(100, pct || 0)));
        }

        // ====================================================================
        // USAGE TRACKING
        // Called ONLY after a successful face recognition (result.final + face_id).
        // Never called on page load, refresh, or failed scans.
        // ====================================================================
        var usageRegistered = false;

        async function registerUsageHit() {
            if (usageRegistered) return; // only count once per widget session
            usageRegistered = true;
            try {
                var headers = {
                    'Content-Type':      'application/json',
                    <!-- 'X-Requested-With':  'XMLHttpRequest' -->
                };
                if (_widgetToken) {
                    headers['X-Widget-Token'] = _widgetToken;
                }
                var res = await fetch(BASE_URL + '/widget/' + WIDGET_ID + '/track-hit', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        client_id:  CLIENT_ID,
                        session_id: SESSION_ID,
                        domain:     window.location.hostname
                    })
                });
                if (!res.ok) {
                    // If server rejects (e.g. limit reached), reset flag so it
                    // doesn't silently swallow the error state.
                    usageRegistered = false;
                    console.warn('[FaceWidget] track-hit rejected:', res.status);
                }
            } catch (e) {
                usageRegistered = false; // allow retry on next recognition
                console.error('[FaceWidget] track-hit error:', e);
            }
        }

        // ====================================================================
        // CONTROLLER
        // ====================================================================
        var CLIENT_ID = 'client_' + (function () {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return window.crypto.randomUUID();
            }
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                var r = Math.random() * 16 | 0;
                return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
            });
        })();

        // OLD CODE: 800ms made liveness slow because the Flask API needs multiple recent frames.
        // var FRAME_INTERVAL = 800;
        var FRAME_INTERVAL = 350;
        var isScanning     = false;
        var scanInterval   = null;
        var isProcessing   = false;
        var lastStatus     = '';

        var videoEl  = shadow.getElementById('scannerVideo');
        var startBtn = shadow.getElementById('startButton');

        async function startCapture() {
            if (isScanning) return;
            isScanning = true;
            setScanningState();
            updateProgress(0);
            updateStatus('Starting camera...');

            var started = await startCamera(videoEl);
            if (!started) {
                setErrorState('Camera access denied');
                isScanning = false;
                return;
            }

            updateStatus('Initializing session...');
            var init = await initRegister(CLIENT_ID);
            if (!init || init.success === false) {
                setErrorState('Init failed');
                stopCamera(videoEl);
                isScanning = false;
                return;
            }

            // ── NO usage hit here — only counted on successful recognition ──
            updateStatus('Scanning...');
            scanInterval = setInterval(function () { scanFrame(); }, FRAME_INTERVAL);
            scanFrame();
        }

        async function scanFrame() {
            if (!isScanning || isProcessing) return;
            isProcessing = true;
            try {
                var blob = await captureFrame(videoEl);
                if (!blob) return;

                var result = await processFrame(blob, CLIENT_ID);
                if (!result) return;

                console.log('[FaceWidget] result:', result);
                applyState(result);

                if (result.final === true) {
                    stopCapture();
                    stopCamera(videoEl);
                }
                if (result.stop_sending) {
                    stopCapture();
                    if (!result.final) {
                        stopCamera(videoEl);
                        setErrorState(result.status || result.message || 'Scan stopped');
                    }
                }
                if (result.final === true && result.face_id) {
                    // ── SUCCESS PATH: recognition complete → count usage now ──
                    await registerUsageHit();
                    handleRecognition(result.face_id);
                } else if (result.final === true && !result.face_id) {
                    // ── FAILURE PATH: final but no face_id → do NOT count ──
                    handleFailed(result.status || result.message || 'Recognition failed');
                }
            } catch (e) {
                console.error('[FaceWidget] scanFrame error:', e);
                // Exception → do NOT count usage
            } finally {
                isProcessing = false;
            }
        }

        function applyState(result) {
            if (!result) return;
            // OLD CODE: The previous API returned `status`; the new Flask API returns `message`.
            // if (!result || !result.status) return;
            var stateMessage = result.status || result.message || '';
            if (!stateMessage) return;
            if (stateMessage !== lastStatus) {
                updateStatus(stateMessage);
                lastStatus = stateMessage;
            }
            var msg = stateMessage.toLowerCase();
            if (msg.includes('spoof') || msg.includes('failed') ||
                msg.includes('error') || msg.includes('locked')) {
                setErrorState(stateMessage);
                return;
            }
            if (msg.includes('capturing')) {
                setScanningState();
                var m = stateMessage.match(/(\d+)\/(\d+)/);
                if (m) updateProgress(Math.min((parseInt(m[1]) / parseInt(m[2])) * 100, 95));
                return;
            }
            if (msg.includes('recognized')) {
                updateProgress(100);
                setRecognizedState();
                return;
            }
            setScanningState();
        }

        function handleRecognition(faceId) {
            stopCapture();
            updateProgress(100);
            setRecognizedState();
            updateStatus('Recognized! ID: ' + (faceId || 'Unknown'));
            window.dispatchEvent(new CustomEvent('faceWidgetRecognized', { detail: { faceId: faceId } }));
            <!-- window.postMessage({ type: 'FACE_RECOGNITION_COMPLETE', faceId: faceId, timestamp: new Date().toISOString() }, '*'); -->

            <!-- This is also change by narendra from  -->
            window.postMessage({ type: 'FACE_RECOGNITION_COMPLETE', faceId: faceId, timestamp: new Date().toISOString() }, window.location.origin);

            <!-- To this line -->
            setTimeout(function () { setIdleState(); updateProgress(0); }, 5000);
        }

        function handleFailed(message) {
            stopCapture();
            stopCamera(videoEl);
            setErrorState(message);
            setTimeout(function () { setIdleState(); updateProgress(0); }, 3000);
        }

        function stopCapture() {
            isScanning = false;
            if (scanInterval) { clearInterval(scanInterval); scanInterval = null; }
        }

        setIdleState();

        // ====================================================================
        // START MODE — controlled by show_start_button setting on the widget
        // true  = show button, user clicks to begin
        // false = hide button, camera auto-starts after 1.5s
        // ====================================================================
        if (SHOW_START_BUTTON) {
            // ── BUTTON MODE: make button visible and wire up click ──
            if (startBtn) {
                startBtn.style.display = 'block';
                startBtn.addEventListener('click', startCapture);
            }
        } else {
            // ── AUTO-START MODE: camera begins automatically after 1.5s ──
            setTimeout(startCapture, 1500);
        }

        // ====================================================================
        // RESET / RELOAD CAMERA — call these from your host page if needed
        // ====================================================================

        // ── Method 1: postMessage reset (call from any page) ──────────────
        // Send this from your host page to reset the widget camera:
        //
        //   document.getElementById('face-widget-script').contentWindow
        //     ... OR simply:
        //   window.postMessage({ type: 'FACE_WIDGET_RESET' }, '*');
        //
        // window.addEventListener('message', function(e) {
        //     if (!e.data || e.data.type !== 'FACE_WIDGET_RESET') return;
        //     stopCapture();
        //     stopCamera(videoEl);
        //     setIdleState();
        //     usageRegistered = false;
        //     lastStatus = '';
        //     if (SHOW_START_BUTTON) {
        //         updateStatus('Ready to scan');
        //     } else {
        //         setTimeout(startCapture, 1000);
        //     }
        // });

        // ── window.FaceWidget.reset() — call from your page to reset the widget ──
        // Examples:
        //   <button onclick="window.FaceWidget.reset()">Scan Again</button>
        //   document.getElementById('myBtn').addEventListener('click', window.FaceWidget.reset);
        window.FaceWidget = window.FaceWidget || {};
        window.FaceWidget.reset = function() {
            stopCapture();
            stopCamera(videoEl);
            setIdleState();
            usageRegistered = false;
            lastStatus = '';
            if (SHOW_START_BUTTON && startBtn) {
                // Button mode: re-enable button, wait for user click
                startBtn.disabled = false;
                updateStatus('Ready to scan');
            } else {
                // Auto-start mode: restart camera automatically after 800ms
                setTimeout(startCapture, 800);
            }
        };

        // ── Method 3: reload the widget script entirely ───────────────────
        // Removes the widget from DOM and re-injects the script tag.
        // Use when you want a completely fresh start (new session_id etc).
        //
        // function reloadFaceWidget() {
        //     var root = document.getElementById('face-widget-root');
        //     if (root) root.remove();
        //     var oldScript = document.getElementById('face-widget-script');
        //     if (oldScript) {
        //         var newScript = document.createElement('script');
        //         newScript.id  = 'face-widget-script';
        //         newScript.src = oldScript.src + '?t=' + Date.now(); // bust cache
        //         document.body.appendChild(newScript);
        //     }
        // }
        // ====================================================================

        window.addEventListener('beforeunload', function () {
            stopCapture();
            stopCamera(videoEl);
        });

    } // end bootstrapWidget()

    // ====================================================================
    // ENTRY POINT — Gate first, then widget
    // ====================================================================
    initWidgetGate(bootstrapWidget);

})();
