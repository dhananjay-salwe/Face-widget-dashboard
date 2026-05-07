<style>
@keyframes previewScan {
    0%   { top: 15%; opacity: 0; }
    10%  { opacity: 0.7; }
    90%  { opacity: 0.7; }
    100% { top: 85%; opacity: 0; }
}

/* ── Form card ── */
.form-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: background 0.22s, border-color 0.22s;
}
.form-card-header {
    padding: 1.1rem 1.4rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 9px;
    transition: border-color 0.22s;
}
.form-card-header-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: var(--accent-subtle);
    border: 1px solid var(--accent-subtle-b);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; color: var(--accent);
    transition: background 0.22s;
}
.form-card-title {
    font-size: 0.9rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--text-primary);
}
.form-card-body { padding: 1.4rem; }

/* ── Field groups ── */
.field-group { margin-bottom: 1.1rem; }

.field-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 6px;
}
.field-label .req { color: var(--danger); margin-left: 2px; }

/* ── Inputs / selects / textareas ── */
.f-input, .f-select, .f-textarea {
    width: 100%;
    font-family: var(--font-sans);
    font-size: 0.855rem;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 8px 11px;
    color: var(--text-primary);
    transition: border-color 0.15s, box-shadow 0.15s, background 0.22s;
    -webkit-appearance: none;
}
.f-input::placeholder, .f-textarea::placeholder { color: var(--text-muted); }
.f-input:focus, .f-select:focus, .f-textarea:focus {
    outline: none;
    border-color: var(--border-focus);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.09);
}
[data-theme="dark"] .f-input:focus,
[data-theme="dark"] .f-select:focus,
[data-theme="dark"] .f-textarea:focus {
    box-shadow: 0 0 0 3px rgba(59,130,246,0.14);
}
.f-input.border-red-500, .f-select.border-red-500, .f-textarea.border-red-500 {
    border-color: var(--danger) !important;
    box-shadow: 0 0 0 3px rgba(220,38,38,0.07);
}
.f-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 2.2rem;
    cursor: pointer;
}
.f-select option { background: var(--bg-elevated); color: var(--text-primary); }

/* ── Color field ── */
.color-field-wrap {
    display: flex; align-items: center; gap: 10px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 6px 11px;
    transition: all 0.15s;
}
.color-field-wrap:focus-within {
    border-color: var(--border-focus);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.09);
}
.color-input {
    width: 32px; height: 32px;
    border: none; border-radius: 7px; padding: 0;
    background: none; cursor: pointer; flex-shrink: 0;
    -webkit-appearance: none;
}
.color-input::-webkit-color-swatch-wrapper { padding: 0; }
.color-input::-webkit-color-swatch { border: 1px solid var(--border); border-radius: 6px; }
.color-hex {
    font-family: var(--font-mono);
    font-size: 0.8rem;
    color: var(--text-secondary);
}

/* ── Toggle ── */
.toggle-field {
    display: flex; align-items: center; gap: 10px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}
.toggle-field:hover { background: var(--bg-hover); border-color: var(--border-strong); }
.toggle-input-hidden { display: none; }
.toggle-track {
    width: 38px; height: 22px;
    border-radius: 99px;
    background: var(--border-strong);
    border: 1px solid var(--border-strong);
    position: relative;
    flex-shrink: 0;
    transition: background 0.22s, border-color 0.22s;
}
.toggle-track.is-on { background: var(--accent); border-color: var(--accent); }
.toggle-thumb {
    position: absolute;
    top: 2px; left: 2px;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    transition: transform 0.22s cubic-bezier(0.34,1.4,0.64,1);
}
.toggle-track.is-on .toggle-thumb { transform: translateX(16px); }
.toggle-text {
    font-size: 0.855rem;
    font-weight: 500;
    color: var(--text-secondary);
}

/* ── Field errors ── */
.field-err {
    font-size: 0.775rem;
    color: var(--danger);
    margin-top: 5px;
    display: flex; align-items: center; gap: 4px;
}

/* ── Divider between field groups ── */
.field-divider {
    height: 1px;
    background: var(--border);
    margin: 1.4rem 0;
    transition: background 0.22s;
}

/* ── Submit button ── */
.submit-btn {
    width: 100%;
    font-family: var(--font-sans);
    font-size: 0.875rem;
    font-weight: 700;
    background: var(--accent);
    color: var(--accent-text);
    border: none;
    border-radius: var(--radius);
    padding: 11px 16px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 7px;
    transition: background 0.15s, transform 0.12s, box-shadow 0.15s;
    box-shadow: 0 1px 3px rgba(37,99,235,0.2);
}
.submit-btn:hover {
    background: var(--accent-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37,99,235,0.25);
}
.submit-btn:active { transform: translateY(0); }
.submit-btn:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

/* ── Preview card ── */
.preview-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    position: sticky;
    top: 76px;
    transition: background 0.22s, border-color 0.22s;
}
.preview-card-header {
    padding: 1.1rem 1.4rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 9px;
    transition: border-color 0.22s;
}
.preview-card-header-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: var(--bg-subtle);
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; color: var(--text-muted);
}
.preview-card-title {
    font-size: 0.9rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--text-primary);
}
.preview-card-body { padding: 1.2rem; }

/* ── Browser chrome ── */
.browser-chrome {
    background: var(--bg-subtle);
    border: 1px solid var(--border);
    border-bottom: none;
    border-radius: 9px 9px 0 0;
    padding: 7px 10px;
    display: flex; align-items: center; gap: 6px;
    transition: background 0.22s;
}
.b-dot { width: 8px; height: 8px; border-radius: 50%; }
.b-url {
    flex: 1;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 2px 8px;
    font-family: var(--font-mono);
    font-size: 0.68rem;
    color: var(--text-muted);
    transition: background 0.22s;
}
.preview-viewport {
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 9px 9px;
    background: var(--bg);
    position: relative;
    height: 390px;
    overflow: hidden;
    transition: background 0.22s;
}

/* Fake page skeleton */
.fake-line {
    height: 3px;
    background: var(--border);
    border-radius: 99px;
    margin-bottom: 6px;
    transition: background 0.22s;
}

/* Widget preview card */
.prev-widget {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 11px;
    box-shadow: var(--shadow-md);
    width: 180px;
    transition: background 0.22s, border-color 0.22s;
}
.prev-title {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.01em;
    margin-bottom: 2px;
}
.prev-msg {
    font-size: 0.63rem;
    color: var(--text-muted);
    line-height: 1.4;
    margin-bottom: 8px;
}
.prev-circle {
    width: 84px; height: 84px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 7px;
    position: relative;
}
.prev-btn {
    width: 100%;
    border: none;
    border-radius: 99px;
    color: white;
    font-family: var(--font-sans);
    font-size: 0.66rem;
    font-weight: 700;
    padding: 5px 0;
    cursor: default;
    transition: opacity 0.2s;
}
.prev-badge {
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    color: var(--text-muted);
    border-radius: 99px;
    font-family: var(--font-sans);
    font-size: 0.62rem;
    font-weight: 600;
    padding: 2px 9px;
    pointer-events: none;
    letter-spacing: 0.04em;
    white-space: nowrap;
    transition: background 0.22s, border-color 0.22s;
}

/* ── Error toast (dark override) ── */
#formErrorToast {
    background: var(--bg-elevated) !important;
    border-color: var(--danger-subtle-b) !important;
    color: var(--danger) !important;
    border-left: 3px solid var(--danger) !important;
}

/* ── Tooltip ── */
[data-tip] { position: relative; }
[data-tip]::after {
    content: attr(data-tip);
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%) translateY(3px);
    background: var(--text-primary);
    color: var(--bg-elevated);
    font-size: 0.7rem; font-weight: 500;
    white-space: nowrap;
    padding: 4px 8px;
    border-radius: 5px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.16s, transform 0.16s;
    z-index: 9999;
}
[data-tip]:hover::after { opacity:1; transform: translateX(-50%) translateY(0); }

/* ── Layout ── */
.form-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 900px) {
    .form-layout { grid-template-columns: 1fr; }
    .preview-card { position: static; }
}
</style>


{{-- ── Error toast (UNCHANGED logic) ── --}}
@if($errors->any())
<div id="formErrorToast"
     class="fixed top-6 right-6 z-[9999] flex items-start gap-3 px-4 py-3 rounded-xl border shadow-lg max-w-sm w-full transition-all duration-500">
    <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;color:var(--danger)" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    <p class="text-sm font-medium flex-1">Please fix the errors below before saving.</p>
    <button onclick="dismissFormToast()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.1rem;line-height:1;">&times;</button>
</div>
<script>
    var formToastTimer = setTimeout(dismissFormToast, 4000);
    function dismissFormToast() {
        clearTimeout(formToastTimer);
        var t = document.getElementById('formErrorToast');
        if (!t) return;
        t.style.opacity = '0';
        t.style.transform = 'translateX(20px)';
        t.style.transition = 'opacity .28s, transform .28s';
        setTimeout(function(){ t.remove(); }, 300);
    }
</script>
@endif


<form method="post" action="{{ $action }}" id="widgetForm">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="form-layout">

        {{-- ── Fields ── --}}
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-header-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                </div>
                <span class="form-card-title">{{ $method === 'POST' ? 'Create Widget' : 'Edit Widget' }}</span>
            </div>
            <div class="form-card-body">

                {{-- Widget Name --}}
                <div class="field-group">
                    <label class="field-label" for="name" data-tip="A unique name to identify this widget in the dashboard">
                        Widget Name <span class="req">*</span>
                    </label>
                    <input name="name" id="name"
                           value="{{ old('name', $widget->name ?? '') }}"
                           placeholder="e.g. Homepage Login"
                           class="f-input validate-on-blur @error('name') border-red-500 @enderror"
                           required>
                    @error('name')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Mode --}}
                <div class="field-group">
                    <label class="field-label" for="mode" data-tip="Floating: fixed overlay · Embedded: inline in your layout">
                        Mode <span class="req">*</span>
                    </label>
                    <select name="mode" id="mode"
                            class="f-select validate-on-blur @error('mode') border-red-500 @enderror"
                            required>
                        <option value="floating" @selected(old('mode', $widget->mode ?? '') === 'floating')>Floating</option>
                        <option value="embedded" @selected(old('mode', $widget->mode ?? '') === 'embedded')>Embedded</option>
                    </select>
                    @error('mode')<p class="field-err server-error">{{ $message }}</p>@enderror
                    <script>
                        (function() {
                            function togglePosition() {
                                var mode = document.getElementById('mode').value;
                                var posField  = document.getElementById('positionField');
                                var posSelect = document.getElementById('position');
                                if (mode === 'embedded') {
                                    posField.style.display = 'none';
                                    posSelect.removeAttribute('required');
                                } else {
                                    posField.style.display = 'block';
                                    posSelect.setAttribute('required','required');
                                }
                            }
                            document.getElementById('mode').addEventListener('change', togglePosition);
                            togglePosition();
                        })();
                    </script>
                </div>

                {{-- Primary Color --}}
                <div class="field-group">
                    <label class="field-label" for="color" data-tip="Main theme color — used for the scan ring and accent elements">
                        Primary Color <span class="req">*</span>
                    </label>
                    <div class="color-field-wrap @error('theme_color') border-red-500 @enderror">
                        <input type="color" name="theme_color" id="color"
                               value="{{ old('theme_color', $widget->theme_color ?? '#2563eb') }}"
                               class="color-input validate-on-blur" required
                               oninput="document.getElementById('colorHex').textContent = this.value.toUpperCase()">
                        <span id="colorHex" class="color-hex">{{ strtoupper(old('theme_color', $widget->theme_color ?? '#2563EB')) }}</span>
                    </div>
                    @error('theme_color')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Position --}}
                <div id="positionField" class="field-group">
                    <label class="field-label" for="position" data-tip="Corner of the page where the floating widget anchors">
                        Position <span class="req">*</span>
                    </label>
                    @php $pos = old('position', $widget->position ?? 'bottom-right'); @endphp
                    <select name="position" id="position"
                            class="f-select validate-on-blur @error('position') border-red-500 @enderror"
                            required>
                        <option value="top-right"     @selected($pos==='top-right')>Top Right</option>
                        <option value="top-left"      @selected($pos==='top-left')>Top Left</option>
                        <option value="top-center"    @selected($pos==='top-center')>Top Center</option>
                        <option value="bottom-center" @selected($pos==='bottom-center')>Bottom Center</option>
                        <option value="bottom-right"  @selected($pos==='bottom-right')>Bottom Right</option>
                        <option value="bottom-left"   @selected($pos==='bottom-left')>Bottom Left</option>
                    </select>
                    @error('position')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                <div class="field-divider"></div>

                {{-- Allowed Domains --}}
                <div class="field-group">
                    <label class="field-label" for="allowed_domains_field" data-tip="One domain per line. Widget loads only on these domains.">
                        Allowed Domains <span class="req">*</span>
                    </label>
                    <textarea name="allowed_domains" id="allowed_domains_field"
                              class="f-textarea validate-domain validate-on-blur @error('allowed_domains') border-red-500 @enderror"
                              rows="3" placeholder="example.com&#10;sub.example.com" required>{{ old('allowed_domains', $widget->allowed_domains ?? '') }}</textarea>
                    @error('allowed_domains')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Allowed Pages --}}
                <div class="field-group">
                    <label class="field-label" for="allowed_pages_field" data-tip="Optional: restrict to specific paths. Leave blank to allow all pages.">
                        Allowed Pages
                    </label>
                    <textarea name="allowed_pages" id="allowed_pages_field"
                              class="f-textarea validate-page @error('allowed_pages') border-red-500 @enderror"
                              rows="3" placeholder="/pricing&#10;/checkout">{{ old('allowed_pages', $widget->allowed_pages ?? '') }}</textarea>
                    @error('allowed_pages')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Request Limit --}}
                <div class="field-group">
                    <label class="field-label" for="api_limit" data-tip="Maximum number of face recognition API calls this widget may make">
                        Request Limit <span class="req">*</span>
                    </label>
                    <input type="number" name="api_limit" id="api_limit"
                           value="{{ old('api_limit', $widget->api_limit ?? 1000) }}"
                           min="1" placeholder="1000"
                           class="f-input validate-on-blur @error('api_limit') border-red-500 @enderror" required>
                    @error('api_limit')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                <div class="field-divider"></div>

                {{-- Welcome Title --}}
                <div class="field-group">
                    <label class="field-label" for="welcome_title" data-tip="Heading displayed at the top of the widget card">
                        Welcome Title <span class="req">*</span>
                    </label>
                    <input name="welcome_title" id="welcome_title"
                           value="{{ old('welcome_title', $widget->welcome_title ?? 'Face Authentication') }}"
                           placeholder="Face Authentication"
                           class="f-input validate-on-blur @error('welcome_title') border-red-500 @enderror" required>
                    @error('welcome_title')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Welcome Message --}}
                <div class="field-group">
                    <label class="field-label" for="welcome_message" data-tip="Short description shown below the title">
                        Welcome Message <span class="req">*</span>
                    </label>
                    <textarea name="welcome_message" id="welcome_message"
                              class="f-textarea validate-on-blur @error('welcome_message') border-red-500 @enderror"
                              rows="2" placeholder="Click below to verify your identity" required>{{ old('welcome_message', $widget->welcome_message ?? 'Click below to verify your identity') }}</textarea>
                    @error('welcome_message')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Button Text --}}
                <div class="field-group">
                    <label class="field-label" for="button_text" data-tip="Label on the action button inside the widget">
                        Button Text <span class="req">*</span>
                    </label>
                    <input name="button_text" id="button_text"
                           value="{{ old('button_text', $widget->button_text ?? 'Face Login') }}"
                           placeholder="Face Login"
                           class="f-input validate-on-blur @error('button_text') border-red-500 @enderror" required>
                    @error('button_text')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Button Color --}}
                <div class="field-group">
                    <label class="field-label" for="button_color" data-tip="Background color of the action button">
                        Button Color <span class="req">*</span>
                    </label>
                    <div class="color-field-wrap @error('button_color') border-red-500 @enderror">
                        <input type="color" name="button_color" id="button_color"
                               value="{{ old('button_color', $widget->button_color ?? '#2563eb') }}"
                               class="color-input validate-on-blur" required
                               oninput="document.getElementById('btnColorHex').textContent = this.value.toUpperCase()">
                        <span id="btnColorHex" class="color-hex">{{ strtoupper(old('button_color', $widget->button_color ?? '#2563EB')) }}</span>
                    </div>
                    @error('button_color')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                <div class="field-divider"></div>

                {{-- Show Start Button toggle --}}
                {{-- Controls whether the widget shows a manual Start button or
                     auto-starts the camera as soon as the widget loads.
                     true  = show button (user clicks to begin)
                     false = no button, camera auto-starts after 1.5s --}}
                <div class="field-group">
                    <label class="field-label" data-tip="Show a Start button, or auto-start the camera when the widget loads">
                        Start Button
                    </label>
                    <label class="toggle-field" onclick="handleShowButtonToggle(this)">
                        {{-- Hidden input sends 0 when checkbox is unchecked --}}
                        <input type="hidden" name="show_start_button" value="0">
                        <input type="checkbox"
                               name="show_start_button"
                               id="showStartButtonCheckbox"
                               value="1"
                               @checked(old('show_start_button', $widget->show_start_button ?? true))
                               class="toggle-input-hidden @error('show_start_button') border-red-500 @enderror">
                        <div class="toggle-track {{ old('show_start_button', $widget->show_start_button ?? true) ? 'is-on' : '' }}"
                             id="showButtonToggleTrack">
                            <div class="toggle-thumb"></div>
                        </div>
                        <span class="toggle-text" id="showButtonToggleText">
                            @if(old('show_start_button', $widget->show_start_button ?? true))
                                Button visible — user clicks to start
                            @else
                                Auto-start — camera begins on load
                            @endif
                        </span>
                    </label>
                    @error('show_start_button')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Widget Auth Type toggle --}}
                {{-- Choose between Login and Register authentication modes
                     register (default) = new user registration (/api/init_register)
                     login = existing user login (/api/init_login) --}}
                <div class="field-group">
                    <label class="field-label" data-tip="Choose whether this widget is for user registration or login">
                        Authentication Mode
                    </label>
                    <label class="toggle-field" onclick="handleAuthTypeToggle(this)">
                        {{-- Hidden input sends register as default --}}
                        <input type="hidden" name="widget_auth_type" value="register">
                        <input type="checkbox"
                               name="widget_auth_type"
                               id="authTypeCheckbox"
                               value="login"
                               @checked(old('widget_auth_type', $widget->widget_auth_type ?? 'register') === 'login')
                               class="toggle-input-hidden @error('widget_auth_type') border-red-500 @enderror">
                        <div class="toggle-track {{ old('widget_auth_type', $widget->widget_auth_type ?? 'register') === 'login' ? 'is-on' : '' }}"
                             id="authTypeToggleTrack">
                            <div class="toggle-thumb"></div>
                        </div>
                        <span class="toggle-text" id="authTypeToggleText">
                            @if(old('widget_auth_type', $widget->widget_auth_type ?? 'register') === 'login')
                                Login — existing user authentication
                            @else
                                Register — new user registration
                            @endif
                        </span>
                    </label>
                    @error('widget_auth_type')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>


                {{-- Active toggle --}}
                <div class="field-group" style="margin-bottom:1.5rem;">
                    <label class="field-label" data-tip="Enable or disable the widget from loading on your site">Status</label>
                    <label class="toggle-field" onclick="handleToggle(this)">
                        {{-- Hidden input sends 0 when checkbox is unchecked --}}
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="isActiveCheckbox"
                               value="1"
                               @checked(old('is_active', $widget->is_active ?? true))
                               class="toggle-input-hidden @error('is_active') border-red-500 @enderror">
                        <div class="toggle-track {{ old('is_active', $widget->is_active ?? true) ? 'is-on' : '' }}" id="toggleTrack">
                            <div class="toggle-thumb"></div>
                        </div>
                        <span class="toggle-text" id="toggleText">
                            {{ old('is_active', $widget->is_active ?? true) ? 'Active — widget is live' : 'Inactive — widget is disabled' }}
                        </span>
                    </label>
                    @error('is_active')<p class="field-err server-error">{{ $message }}</p>@enderror
                </div>

                {{-- Submit --}}
                <button type="submit" id="submitBtn" class="submit-btn" data-tip="Save widget configuration">
                    <svg id="submitSpinner" class="hidden animate-spin" style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg id="submitIcon" style="width:15px;height:15px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span id="submitBtnText">Save Widget</span>
                </button>
            </div>
        </div>

        {{-- ── Live Preview ── --}}
        <div>
            <div class="preview-card">
                <div class="preview-card-header">
                    <div class="preview-card-header-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <span class="preview-card-title">Live Preview</span>
                </div>
                <div class="preview-card-body">
                    <p style="font-size:0.775rem;color:var(--text-muted);margin-bottom:10px;line-height:1.5;">
                        Reflects your settings in real time.
                    </p>

                    <div class="browser-chrome">
                        <span class="b-dot" style="background:#fc5f5a;"></span>
                        <span class="b-dot" style="background:#fdbc40;"></span>
                        <span class="b-dot" style="background:#34c849;"></span>
                        <span class="b-url">your-website.com</span>
                    </div>

                    <div id="previewBox" class="preview-viewport">
                        <div style="padding:14px 14px 0;pointer-events:none;">
                            <div class="fake-line" style="width:42%;margin-bottom:7px;height:4px;"></div>
                            <div class="fake-line" style="width:68%;"></div>
                            <div class="fake-line" style="width:53%;"></div>
                            <div class="fake-line" style="width:76%;height:24px;margin-top:8px;border-radius:5px;"></div>
                            <div class="fake-line" style="width:58%;margin-top:8px;"></div>
                            <div class="fake-line" style="width:40%;"></div>
                        </div>

                        <div id="previewWidgetWrapper" class="absolute transition-all duration-300" style="width:180px;">
                            <div class="prev-widget">
                                <h3 id="prev-title" class="prev-title">
                                    {{ old('welcome_title', $widget->welcome_title ?? 'Face Authentication') }}
                                </h3>
                                <p id="prev-msg" class="prev-msg">
                                    {{ old('welcome_message', $widget->welcome_message ?? 'Click below to verify your identity') }}
                                </p>
                                <div id="prev-circle" class="prev-circle" style="border:3px solid #2563eb;background:rgba(37,99,235,0.06);">
                                    <svg viewBox="0 0 100 100" style="position:absolute;inset:0;width:100%;height:100%;opacity:.18;" xmlns="http://www.w3.org/2000/svg">
                                        <ellipse cx="50" cy="40" rx="18" ry="22" fill="#64748b"/>
                                        <ellipse cx="50" cy="80" rx="28" ry="22" fill="#64748b"/>
                                    </svg>
                                    <div id="prev-scanline" style="position:absolute;left:0;right:0;height:1px;background:#2563eb;opacity:.65;top:20%;animation:previewScan 2s ease-in-out infinite;"></div>
                                </div>
                                <p style="text-align:center;font-size:0.6rem;color:var(--text-muted);margin:0 0 7px;">Ready to scan</p>
                                {{-- Button preview — shown/hidden based on show_start_button toggle --}}
                                <button id="prev-btn" type="button" class="prev-btn" style="background:#2563eb;">
                                    {{ old('button_text', $widget->button_text ?? 'Face Login') }}
                                </button>
                            </div>
                        </div>

                        <div id="prev-badge" class="prev-badge">bottom right</div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end form-layout --}}
</form>


<script>
/* ── Status toggle ── */
function handleToggle(label) {
    var cb    = document.getElementById('isActiveCheckbox');
    var track = document.getElementById('toggleTrack');
    var text  = document.getElementById('toggleText');
    cb.checked = !cb.checked;
    track.classList.toggle('is-on', cb.checked);
    text.textContent = cb.checked ? 'Active — widget is live' : 'Inactive — widget is disabled';
}

/* ── Show Start Button toggle ── */
function handleShowButtonToggle(label) {
    var cb      = document.getElementById('showStartButtonCheckbox');
    var track   = document.getElementById('showButtonToggleTrack');
    var text    = document.getElementById('showButtonToggleText');
    var prevBtn = document.getElementById('prev-btn');
    cb.checked  = !cb.checked;
    track.classList.toggle('is-on', cb.checked);
    text.textContent = cb.checked
        ? 'Button visible — user clicks to start'
        : 'Auto-start — camera begins on load';
    if (prevBtn) {
        prevBtn.style.opacity        = cb.checked ? '1' : '0.3';
        prevBtn.style.textDecoration = cb.checked ? 'none' : 'line-through';
        prevBtn.title = cb.checked ? '' : 'Button hidden — auto-start mode';
    }
}

/* ── Widget Auth Type toggle ── */
function handleAuthTypeToggle(label) {
    var cb      = document.getElementById('authTypeCheckbox');
    var track   = document.getElementById('authTypeToggleTrack');
    var text    = document.getElementById('authTypeToggleText');
    cb.checked  = !cb.checked;
    track.classList.toggle('is-on', cb.checked);
    text.textContent = cb.checked
        ? 'Login — existing user authentication'
        : 'Register — new user registration';
}


document.addEventListener('DOMContentLoaded', function () {

    // ── 1. LIVE WIDGET PREVIEW ──────────────────────────────────────
    const colorInput            = document.getElementById('color');
    const buttonColorInput      = document.getElementById('button_color');
    const textInput             = document.getElementById('button_text');
    const positionInput         = document.getElementById('position');
    const titleInput            = document.getElementById('welcome_title');
    const messageInput          = document.getElementById('welcome_message');
    const showBtnCheckbox       = document.getElementById('showStartButtonCheckbox');

    const prevCircle   = document.getElementById('prev-circle');
    const prevScanline = document.getElementById('prev-scanline');
    const prevBtn      = document.getElementById('prev-btn');
    const prevTitle    = document.getElementById('prev-title');
    const prevMsg      = document.getElementById('prev-msg');
    const prevWrapper  = document.getElementById('previewWidgetWrapper');
    const prevBadge    = document.getElementById('prev-badge');

    var positionMap = {
        'top-left'     : { top:'12px',   left:'12px',  bottom:'auto', right:'auto',  transform:'' },
        'top-center'   : { top:'12px',   left:'50%',   bottom:'auto', right:'auto',  transform:'translateX(-50%)' },
        'top-right'    : { top:'12px',   right:'12px', bottom:'auto', left:'auto',   transform:'' },
        'bottom-left'  : { bottom:'12px',left:'12px',  top:'auto',    right:'auto',  transform:'' },
        'bottom-center': { bottom:'12px',left:'50%',   top:'auto',    right:'auto',  transform:'translateX(-50%)' },
        'bottom-right' : { bottom:'12px',right:'12px', top:'auto',    left:'auto',   transform:'' },
    };

    function updatePreview() {
        var theme  = colorInput.value       || '#2563eb';
        var btnClr = buttonColorInput.value || theme;
        var pos    = positionInput.value    || 'bottom-right';
        var showBtn = showBtnCheckbox.checked;

        prevCircle.style.borderColor  = theme;
        prevScanline.style.background = theme;
        prevBtn.style.background      = btnClr;

        prevTitle.textContent = titleInput.value   || 'Face Authentication';
        prevMsg.textContent   = messageInput.value || 'Click below to verify your identity';
        prevBtn.textContent   = textInput.value    || 'Face Login';

        // Reflect show_start_button state in preview
        prevBtn.style.opacity        = showBtn ? '1' : '0.3';
        prevBtn.style.textDecoration = showBtn ? 'none' : 'line-through';

        prevWrapper.style.top = prevWrapper.style.bottom = prevWrapper.style.left = prevWrapper.style.right = 'auto';
        prevWrapper.style.transform = '';

        var s = positionMap[pos] || positionMap['bottom-right'];
        Object.keys(s).forEach(function(k){ prevWrapper.style[k] = s[k]; });
        prevBadge.textContent = pos.replace('-', ' ');
    }

    colorInput.addEventListener('input',       updatePreview);
    buttonColorInput.addEventListener('input', updatePreview);
    textInput.addEventListener('input',        updatePreview);
    positionInput.addEventListener('change',   updatePreview);
    titleInput.addEventListener('input',       updatePreview);
    messageInput.addEventListener('input',     updatePreview);
    var _showBtnEl = document.getElementById('showStartButtonCheckbox');
    if (_showBtnEl) _showBtnEl.addEventListener('change', updatePreview);
    updatePreview();

    // ── 2. FORM VALIDATION (UNCHANGED) ─────────────────────────────
    const form = document.getElementById('widgetForm');

    function showCustomError(field, message) {
        field.classList.add('border-red-500');
        const serverError = field.parentElement.querySelector('.server-error');
        if (serverError) serverError.style.display = 'none';
        let dynamicError = field.parentElement.querySelector('.dynamic-error');
        if (!dynamicError) {
            const p = document.createElement('p');
            p.className = 'field-err dynamic-error';
            p.innerText = message;
            field.parentElement.appendChild(p);
        } else {
            dynamicError.innerText = message;
        }
    }

    function clearError(field) {
        field.classList.remove('border-red-500');
        const e = field.parentElement.querySelector('.dynamic-error');
        if (e) e.remove();
    }

    document.querySelectorAll('.validate-on-blur').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim() === '') showCustomError(this, 'This field is required.');
            else clearError(this);
        });
        field.addEventListener('input', function() {
            if (this.value.trim() !== '') clearError(this);
        });
    });

    document.querySelectorAll('.validate-domain').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim() === '') { showCustomError(this, 'This field is required.'); return; }
            const domains = this.value.split(/[\r\n,]+/);
            let hasError = false;
            for (let d of domains) {
                if (d.trim() !== '' && /\s/.test(d.trim())) { hasError = true; break; }
            }
            hasError ? showCustomError(this, 'Domains cannot contain spaces. One per line.') : clearError(this);
        });
        field.addEventListener('input', function() { clearError(this); });
    });

    document.querySelectorAll('.validate-page').forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim() === '') { clearError(this); return; }
            const pages = this.value.split(/[\r\n,]+/);
            for (let p of pages) {
                let pg = p.trim();
                if (pg !== '' && !pg.startsWith('/') && !pg.startsWith('*')) {
                    showCustomError(this, 'Paths must start with / or * (e.g. /checkout).');
                    return;
                }
            }
            clearError(this);
        });
        field.addEventListener('input', function() { clearError(this); });
    });

    form.addEventListener('submit', function(e) {
        let hasErrors = false;
        document.querySelectorAll('.validate-on-blur').forEach(field => {
            if (field.value.trim() === '') { showCustomError(field, 'This field is required.'); hasErrors = true; }
        });
        if (document.querySelectorAll('.dynamic-error').length > 0) hasErrors = true;
        if (hasErrors) {
            e.preventDefault();
            const first = document.querySelector('.border-red-500');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        const btn     = document.getElementById('submitBtn');
        const spinner = document.getElementById('submitSpinner');
        const icon    = document.getElementById('submitIcon');
        const btnText = document.getElementById('submitBtnText');
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        spinner.classList.remove('hidden');
        if (icon) icon.classList.add('hidden');
        btnText.textContent = 'Saving…';
    });

});
</script>