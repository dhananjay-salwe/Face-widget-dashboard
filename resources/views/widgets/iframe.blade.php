<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $widget->welcome_title ?? 'Face Widget' }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0; height: 100%;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: {{ $standalone ? '#f3f4f6' : 'transparent' }};
            display: flex; align-items: center; justify-content: center;
        }
        :root {
            --widget-theme:  {{ $widget->theme_color  ?? '#66b0ff' }};
            --widget-button: {{ $widget->button_color ?? ($widget->theme_color ?? '#66b0ff') }};
        }
        .scanner-card {
            width: 320px; max-width: 100%; background: #f9fafb;
            border-radius: 16px; box-shadow: 0 10px 25px rgba(15,23,42,.12);
            padding: 20px 16px 18px;
        }
        .widget-title  { font-size: 1rem;  font-weight: 600; color: #111827; margin: 0 0 4px; }
        .widget-message{ font-size: .85rem; color: #4b5563; margin: 0 0 12px; }
        .scanner-circle {
            position: relative; width: 260px; max-width: 100%; margin: 0 auto;
            padding-top: 100%; border-radius: 999px; overflow: hidden;
            background: #e5f0ff; border: 4px solid var(--widget-theme);
        }
        .scanner-video, .scanner-image {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;
        }
        .scanner-image, .captured-image { display: none; }
        .status-container { margin-top: 12px; }
        .status-text { font-size: .9rem; color: #4b5563; margin: 0; }
        .start-button {
            margin-top: 12px; display: inline-flex; align-items: center; justify-content: center;
            padding: 6px 14px; border-radius: 999px; border: none;
            background: var(--widget-button); color: #fff; font-size: .9rem; cursor: pointer;
        }
        .start-button:disabled { opacity: .6; cursor: default; }
    </style>
</head>
<body>
    <div class="scanner-card">
        <h2 class="widget-title">{{ $widget->welcome_title ?? 'Face Authentication' }}</h2>
        <p class="widget-message">{{ $widget->welcome_message ?? 'Click below to verify your identity' }}</p>
        <div class="scanner-circle" id="scannerCircle">
            <img id="captured-image" class="captured-image" alt="" />
            <canvas id="capture-canvas" style="display:none;"></canvas>
            <video class="scanner-video" id="scannerVideo" autoplay playsinline muted></video>
            <img class="scanner-image" id="scannerImage" alt="Scanner overlay" />
        </div>
        <div class="status-container">
            <p class="status-text" id="statusText">Initializing...</p>
        </div>
        <button class="start-button" id="startButton">
            {{ $widget->button_text ?? 'Start Scan' }}
        </button>
    </div>

    <script type="module">
        // In iframe context, no Shadow DOM is needed — elements exist directly in the page
        window.WidgetConfig = {
            widget_id: @json($widget->id),
            theme:     @json($widget->theme_color ?? '#66b0ff'),
            base_url:  @json($baseUrl),
        };
        // Expose a no-op shadow root shim so widget.js works unchanged
        window.FaceWidgetShadowRoot = document;

        import('{{ $baseUrl }}/js/widget.js');
    </script>
</body>
</html>