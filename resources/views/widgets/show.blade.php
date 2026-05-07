@extends('layouts.app')

@section('content')

<style>
    /* ── Breadcrumb ── */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 1.25rem;
    }
    .breadcrumb a {
        color: var(--text-muted);
        text-decoration: none;
        transition: color 0.15s;
    }
    .breadcrumb a:hover { color: var(--accent); }
    .breadcrumb-sep { color: var(--border-strong); }

    /* ── Page title row ── */
    .show-page-title {
        font-size: clamp(1.3rem, 2.5vw, 1.6rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-primary);
        line-height: 1.2;
    }

    /* ── Header buttons ── */
    .header-btn {
        font-family: var(--font-sans);
        font-size: 0.825rem;
        font-weight: 600;
        padding: 7px 14px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--bg-elevated);
        color: var(--text-secondary);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.14s;
        box-shadow: var(--shadow-xs);
    }
    .header-btn:hover { background: var(--bg-subtle); color: var(--text-primary); border-color: var(--border-strong); }
    .header-btn-danger { }
    .header-btn-danger:hover {
        background: var(--danger-subtle);
        border-color: var(--danger-subtle-b);
        color: var(--danger);
    }

    /* ── Section card ── */
    .section-card {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: background 0.22s, border-color 0.22s;
    }
    .section-header {
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 9px;
        transition: border-color 0.22s;
    }
    .section-header-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        background: var(--accent-subtle);
        border: 1px solid var(--accent-subtle-b);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        color: var(--accent);
        transition: background 0.22s;
    }
    .section-header-title {
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--text-primary);
    }
    .section-body { padding: 1.4rem; }

    /* ── Stat grid ── */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        margin-bottom: 1.4rem;
        transition: border-color 0.22s;
    }
    @media (max-width: 640px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 360px) { .stat-grid { grid-template-columns: 1fr; } }

    .stat-item {
        padding: 1rem 1.1rem;
        border-right: 1px solid var(--border);
        transition: border-color 0.22s, background 0.14s;
    }
    .stat-item:last-child { border-right: none; }
    .stat-item:hover { background: var(--bg-hover); }
    @media (max-width: 640px) {
        .stat-item:nth-child(2) { border-right: none; }
        .stat-item:nth-child(3) { border-right: 1px solid var(--border); border-top: 1px solid var(--border); }
        .stat-item:nth-child(4) { border-right: none; border-top: 1px solid var(--border); }
    }

    .stat-label {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 5px;
    }
    .stat-value {
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-primary);
        line-height: 1;
    }
    .stat-value-sm {
        font-size: 1.1rem;
    }

    /* ── Progress bar ── */
    .progress-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .progress-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        white-space: nowrap;
    }
    .progress-track {
        flex: 1;
        height: 6px;
        background: var(--bg-subtle);
        border-radius: 99px;
        overflow: hidden;
        border: 1px solid var(--border);
    }
    .progress-fill {
        height: 100%;
        border-radius: 99px;
        background: var(--accent);
        transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
    }
    .progress-pct {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-secondary);
        white-space: nowrap;
        min-width: 36px;
        text-align: right;
    }

    /* ── Embed code section ── */
    .embed-textarea {
        width: 100%;
        font-family: var(--font-mono);
        font-size: 0.78rem;
        line-height: 1.65;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 0.85rem 1rem;
        color: var(--text-secondary);
        resize: none;
        transition: all 0.15s;
        tab-size: 4;
    }
    .embed-textarea:focus {
        outline: none;
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
        background: var(--bg-elevated);
    }

    .embed-step-label {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 7px;
        display: block;
    }

    .embed-info {
        background: var(--accent-subtle);
        border: 1px solid var(--accent-subtle-b);
        border-radius: var(--radius);
        padding: 10px 13px;
        font-size: 0.8rem;
        color: var(--accent);
        line-height: 1.55;
        margin-bottom: 14px;
    }
    .embed-info code {
        font-family: var(--font-mono);
        font-size: 0.75rem;
        background: rgba(37,99,235,0.12);
        border-radius: 4px;
        padding: 1px 5px;
    }

    /* ── Copy buttons ── */
    .copy-btn-primary {
        width: 100%;
        font-family: var(--font-sans);
        font-size: 0.825rem;
        font-weight: 600;
        background: var(--accent);
        color: var(--accent-text);
        border: none;
        border-radius: 8px;
        padding: 9px 14px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.14s;
        box-shadow: 0 1px 2px rgba(37,99,235,0.2);
        margin-top: 12px;
    }
    .copy-btn-primary:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 4px 10px rgba(37,99,235,0.22); }
    .copy-btn-primary:active { transform: translateY(0); }

    .copy-btn-secondary {
        width: 100%;
        font-family: var(--font-sans);
        font-size: 0.8rem;
        font-weight: 600;
        background: var(--bg-elevated);
        color: var(--text-secondary);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 8px 14px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.14s;
        box-shadow: var(--shadow-xs);
        margin-top: 10px;
    }
    .copy-btn-secondary:hover { background: var(--bg-subtle); color: var(--text-primary); border-color: var(--border-strong); }

    .copy-confirm {
        text-align: center;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--success);
        margin-top: 7px;
        display: none;
    }

    /* ── Reset snippet section ── */
    .reset-section {
        margin-top: 1.4rem;
        border-top: 1px solid var(--border);
        padding-top: 1.2rem;
    }
    .reset-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .reset-section-desc {
        font-size: 0.78rem;
        color: var(--text-muted);
        line-height: 1.55;
        margin-bottom: 10px;
    }
    .reset-section-desc code {
        font-family: var(--font-mono);
        font-size: 0.74rem;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 1px 5px;
        color: var(--text-secondary);
    }

    /* ── Main layout ── */
    .show-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 900px) { .show-layout { grid-template-columns: 1fr; } }

    /* ── Delete modal ── */
    .modal-backdrop {
        position: fixed; inset: 0; z-index: 500;
        display: none; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.35);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    .modal-backdrop.open { display: flex; }
    .modal-box {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-modal);
        width: 100%; max-width: 400px; margin: 1rem;
        overflow: hidden;
        animation: modalPop 0.22s cubic-bezier(0.34,1.5,0.64,1) both;
        transition: background 0.22s, border-color 0.22s;
    }
    @keyframes modalPop {
        from { opacity:0; transform: scale(0.93) translateY(10px); }
        to   { opacity:1; transform: scale(1) translateY(0); }
    }
    .modal-body { padding: 1.75rem 1.75rem 1.25rem; text-align: center; }
    .modal-icon-wrap {
        width: 48px; height: 48px; border-radius: 12px;
        background: var(--danger-subtle); border: 1px solid var(--danger-subtle-b);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.1rem;
    }
    .modal-title { font-size: 1rem; font-weight: 700; letter-spacing: -0.02em; color: var(--text-primary); margin-bottom: 6px; }
    .modal-desc  { font-size: 0.845rem; color: var(--text-secondary); line-height: 1.55; }
    .modal-desc strong { color: var(--text-primary); font-weight: 600; }
    .modal-footer { display: flex; border-top: 1px solid var(--border); transition: border-color 0.22s; }
    .modal-btn { flex:1; padding:13px; font-family:var(--font-sans); font-size:0.845rem; font-weight:600; background:none; border:none; cursor:pointer; transition:background 0.14s; display:inline-flex; align-items:center; justify-content:center; gap:6px; }
    .modal-btn-cancel { color: var(--text-secondary); }
    .modal-btn-cancel:hover { background: var(--bg-subtle); }
    .modal-btn-divider { width:1px; background:var(--border); transition:background 0.22s; }
    .modal-btn-confirm { color:var(--danger); }
    .modal-btn-confirm:hover { background:var(--danger-subtle); }
    .modal-btn-confirm:disabled { opacity:0.6; cursor:not-allowed; }
</style>


{{-- ── Breadcrumb + heading ── --}}
<div class="breadcrumb">
    <a href="{{ route('widgets.index') }}">Widgets</a>
    <span class="breadcrumb-sep">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" d="M9 18l6-6-6-6"/>
        </svg>
    </span>
    <span>{{ $widget->name }}</span>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="show-page-title">{{ $widget->name }}</h1>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('widgets.edit', $widget->id) }}" class="header-btn" data-tip="Open edit form">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Edit
        </a>
        <button type="button" onclick="openDeleteModal()" class="header-btn header-btn-danger" data-tip="Permanently delete this widget">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m4-3h2a1 1 0 011 1v1H9V5a1 1 0 011-1h2z"/>
            </svg>
            Delete
        </button>
        <form id="deleteForm" action="{{ route('widgets.destroy', $widget->id) }}" method="post" class="hidden">
            @csrf @method('DELETE')
        </form>
    </div>
</div>


{{-- ── Layout ── --}}
<div class="show-layout">

    {{-- Stats column --}}
    <div class="section-card">
        <div class="section-header">
            <div class="section-header-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <span class="section-header-title">Usage Overview</span>
        </div>
        <div class="section-body">
            <div class="stat-grid">
                <div class="stat-item" data-tip="Total API calls made">
                    <div class="stat-label">API Hits</div>
                    <div class="stat-value">{{ number_format($widget->api_hits) }}</div>
                </div>
                <div class="stat-item" data-tip="Maximum allowed API calls">
                    <div class="stat-label">API Limit</div>
                    <div class="stat-value">{{ number_format($widget->api_limit) }}</div>
                </div>
                <div class="stat-item" data-tip="Widget render mode">
                    <div class="stat-label">Mode</div>
                    <div class="stat-value stat-value-sm" style="text-transform:capitalize;">{{ $widget->mode }}</div>
                </div>
                <div class="stat-item" data-tip="Whether widget is publicly active">
                    <div class="stat-label">Status</div>
                    <div class="stat-value stat-value-sm" style="color:{{ $widget->is_active ? 'var(--success)' : 'var(--text-muted)' }}">
                        {{ $widget->is_active ? 'Active' : 'Inactive' }}
                    </div>
                </div>
            </div>

            @php $pct = min(100, intval(($widget->api_hits / max(1, $widget->api_limit)) * 100)); @endphp
            <div class="progress-row" data-tip="{{ $pct }}% of limit used">
                <span class="progress-label">Usage</span>
                <div class="progress-track">
                    <div class="progress-fill" style="width:{{ $pct }}%"></div>
                </div>
                <span class="progress-pct">{{ $pct }}%</span>
            </div>
            <p style="font-size:0.78rem;color:var(--text-muted);margin-top:8px;">
                {{ number_format($widget->api_limit - $widget->api_hits) }} calls remaining
            </p>
        </div>
    </div>

    {{-- Embed code column --}}
    <div class="section-card">
        <div class="section-header">
            <div class="section-header-icon" style="background:var(--bg-subtle);border-color:var(--border);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text-secondary)">
                    <path stroke-linecap="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <span class="section-header-title">Embed Code</span>
        </div>
        <div class="section-body">
            <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.55;">
                Paste this snippet into your page to load the widget and receive recognition results.
            </p>

            @if($widget->mode === 'floating')
            {{-- FLOATING --}}
            <label class="embed-step-label" data-tip="Paste in &lt;head&gt; or before &lt;/body&gt;">Complete Widget Code</label>
            <textarea id="embedCode" class="embed-textarea" rows="13" readonly
><script>
(function () {
    var s = document.createElement('script');
    s.src   = '{{ route('widget.script', $widget->id) }}';
    s.async = true;
    (document.head || document.documentElement).appendChild(s);

    window.addEventListener('message', function (event) {
        if (!event.data || event.data.type !== 'FACE_RECOGNITION_COMPLETE') return;
        var faceId = event.data.faceId;
        console.log('Face ID received:', faceId);
        document.getElementById('face_id').value = faceId;
    });
})();
</script></textarea>

            @else
            {{-- EMBEDDED --}}
            <div class="embed-info">
                <strong>Step 1:</strong> Place the <code>&lt;div&gt;</code> where you want the widget.<br>
                <strong>Step 2:</strong> Paste the <code>&lt;script&gt;</code> before <code>&lt;/body&gt;</code>.
            </div>

            <label class="embed-step-label" data-tip="Paste at the exact position you want the widget to appear">① Placement div</label>
            <textarea id="embedDivCode" class="embed-textarea" rows="3" readonly
><div id="face-widget-container" style="display:inline-block;"></div></textarea>
            <button onclick="copyCode('embedDivCode', 'copyDivConfirm')" class="copy-btn-secondary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-4 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Copy div tag
            </button>
            <p id="copyDivConfirm" class="copy-confirm">✓ Copied!</p>

            <label class="embed-step-label" style="margin-top:16px;" data-tip="Paste this before your closing body tag">② Script tag</label>
            <textarea id="embedCode" class="embed-textarea" rows="11" readonly
><script src="{{ route('widget.script', $widget->id) }}"></script>
<script>
window.addEventListener('message', function (event) {
    if (!event.data || event.data.type !== 'FACE_RECOGNITION_COMPLETE') return;
    var faceId = event.data.faceId;
    console.log('Face ID received:', faceId);
    document.getElementById('face_id').value = faceId;
});
</script></textarea>
            @endif

            <button onclick="copyEmbed()" class="copy-btn-primary" data-tip="Copy embed code to clipboard">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Copy to Clipboard
            </button>
            <p id="copyConfirm" class="copy-confirm">✓ Copied! Paste it into your page.</p>

            {{-- ── Reset / Reload snippet ───────────────────────────────────
                 
                 CONDITIONAL DISPLAY LOGIC:
                 
                 If show_start_button = true (widget has a manual start button):
                   → HIDE reset snippet (users have button for manual trigger)
                   → Only show embed code
                 
                 If show_start_button = false (widget auto-starts):
                   → SHOW reset snippet (users need manual way to restart)
                   → Helps users who want to add their own button
                 
                 This improves UX by showing only relevant options per use case.
            ── --}}
            @if(!$widget->show_start_button)
            <div class="reset-section">
                <p class="reset-section-title">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--accent)">
                        <path stroke-linecap="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset / Scan Again
                </p>
                <p class="reset-section-desc">
                    Since your widget auto-starts, use these snippets to reset it manually or add a button. 
                    Call <code>window.FaceWidget.reset()</code> from anywhere on your page to reset
                    the widget and start a new scan — no page reload needed.
                </p>
                <label class="embed-step-label">Reset snippet</label>
                <textarea id="resetCode" class="embed-textarea" rows="16" readonly
><script>
// Example 1: reset on a button click
document.getElementById('yourButtonId')
    .addEventListener('click', function () {
        window.FaceWidget.reset();
    });

// Example 2: reset after form submit
document.getElementById('yourFormId')
    .addEventListener('submit', function () {
        window.FaceWidget.reset();
    });

// Example 3: call directly from anywhere
window.FaceWidget.reset();
</script></textarea>
                <button onclick="copyCode('resetCode', 'copyResetConfirm')" class="copy-btn-secondary">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-4 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Copy reset snippet
                </button>
                <p id="copyResetConfirm" class="copy-confirm">✓ Copied!</p>
            </div>
            @else
            {{-- OLD CODE: Reset section always shown (now conditionally hidden)
            <div class="reset-section">
                ...reset code...
            </div>
            --}}
            @endif

        </div>
    </div>

</div>{{-- end layout --}}


{{-- ── Delete modal ── --}}
<div id="deleteModal" class="modal-backdrop" style="background:rgba(0,0,0,0.35);">
    <div class="modal-box">
        <div class="modal-body">
            <div class="modal-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--danger)">
                    <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m4-3h2a1 1 0 011 1v1H9V5a1 1 0 011-1h2z"/>
                </svg>
            </div>
            <p class="modal-title">Delete Widget</p>
            <p class="modal-desc">
                Are you sure you want to delete
                <strong>{{ $widget->name }}</strong>?
                This action cannot be undone.
            </p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteModal()" class="modal-btn modal-btn-cancel">Cancel</button>
            <div class="modal-btn-divider"></div>
            <button onclick="confirmDelete()" id="deleteConfirmBtn" class="modal-btn modal-btn-confirm">
                <svg id="deleteSpinner" class="hidden animate-spin" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="deleteConfirmText">Delete</span>
            </button>
        </div>
    </div>
</div>

<script>
    /* ── Copy helpers ── */
    function copyEmbed() { copyCode('embedCode', 'copyConfirm'); }
    function copyCode(elId, confirmId) {
        var code = document.getElementById(elId).value;
        navigator.clipboard.writeText(code).then(function() {
            var msg = document.getElementById(confirmId);
            if (msg) { msg.style.display = 'block'; setTimeout(function(){ msg.style.display = 'none'; }, 3000); }
        });
    }

    /* ── Delete modal ── */
    function openDeleteModal() {
        document.getElementById('deleteModal').classList.add('open');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('open');
    }

    function confirmDelete() {
        document.getElementById('deleteConfirmBtn').disabled     = true;
        document.getElementById('deleteSpinner').classList.remove('hidden');
        document.getElementById('deleteConfirmText').textContent = 'Deleting…';

        var form  = document.getElementById('deleteForm');
        var token = form.querySelector('input[name="_token"]').value;

        $.ajax({
            url: form.action,
            method: 'POST',
            data: { _method: 'DELETE', _token: token },
            success: function () {
                window.location.href = '{{ route('widgets.index') }}';
            },
            error: function () {
                document.getElementById('deleteConfirmBtn').disabled     = false;
                document.getElementById('deleteSpinner').classList.add('hidden');
                document.getElementById('deleteConfirmText').textContent = 'Delete';
                closeDeleteModal();
                alert('Failed to delete widget. Please try again.');
            }
        });
    }

    document.getElementById('deleteModal').addEventListener('click', function(e){
        if (e.target === this) closeDeleteModal();
    });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>

@endsection