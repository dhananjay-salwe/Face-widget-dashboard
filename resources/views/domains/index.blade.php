@extends('layouts.app')

@section('content')

<style>
    /* ── Page header ── */
    .domains-page-title {
        font-size: clamp(1.3rem, 2.5vw, 1.6rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-primary);
        line-height: 1.2;
    }

    /* ── Section card ── */
    .section-card {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 1.25rem;
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
    }
    .section-header-title {
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--text-primary);
    }
    .section-body { padding: 1.4rem; }

    /* ── Add domain form ── */
    .add-domain-row {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .domain-input {
        flex: 1;
        font-family: var(--font-mono);
        font-size: 0.875rem;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--bg-subtle);
        color: var(--text-primary);
        outline: none;
        transition: all 0.15s;
    }
    .domain-input:focus {
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
        background: var(--bg-elevated);
    }
    .btn-add-domain {
        font-family: var(--font-sans);
        font-size: 0.825rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        background: var(--accent);
        color: var(--accent-text);
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.14s;
    }
    .btn-add-domain:hover { background: var(--accent-hover); }

    /* ── Domain table ── */
    .domain-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    .domain-table th {
        padding: 8px 12px;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border);
    }
    .domain-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        color: var(--text-secondary);
    }
    .domain-table tr:last-child td { border-bottom: none; }
    .domain-table tr:hover td { background: var(--bg-hover); }

    /* ── Status badges ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        padding: 3px 8px;
        border-radius: 999px;
    }
    .badge-verified {
        background: var(--success-subtle, #dcfce7);
        color: var(--success, #16a34a);
        border: 1px solid #bbf7d0;
    }
    .badge-pending {
        background: var(--warning-subtle, #fef9c3);
        color: var(--warning, #ca8a04);
        border: 1px solid #fef08a;
    }

    /* ── Verification panel ── */
    .verify-panel {
        display: none;
        margin-top: 1rem;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }
    .verify-panel.open { display: block; }

    .verify-tabs {
        display: flex;
        border-bottom: 1px solid var(--border);
    }
    .verify-tab {
        flex: 1;
        padding: 10px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        background: none;
        border: none;
        cursor: pointer;
        transition: all 0.14s;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
    }
    .verify-tab.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
        background: var(--bg-elevated);
    }
    .verify-tab:hover:not(.active) { color: var(--text-secondary); background: var(--bg-hover); }

    .verify-pane {
        display: none;
        padding: 1.2rem;
    }
    .verify-pane.active { display: block; }

    .verify-instruction {
        font-size: 0.82rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    .verify-instruction strong { color: var(--text-primary); }

    .code-block {
        font-family: var(--font-mono);
        font-size: 0.78rem;
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 10px 14px;
        color: var(--text-secondary);
        white-space: pre-wrap;
        word-break: break-all;
        margin-bottom: 1rem;
        position: relative;
    }
    .copy-inline-btn {
        position: absolute;
        top: 6px; right: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 5px;
        border: 1px solid var(--border);
        background: var(--bg-subtle);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.14s;
    }
    .copy-inline-btn:hover { background: var(--bg-elevated); color: var(--text-primary); }

    .btn-verify {
        font-family: var(--font-sans);
        font-size: 0.825rem;
        font-weight: 600;
        padding: 8px 18px;
        border-radius: 8px;
        border: none;
        background: var(--accent);
        color: var(--accent-text);
        cursor: pointer;
        transition: background 0.14s;
    }
    .btn-verify:hover { background: var(--accent-hover); }

    /* ── Action buttons ── */
    .btn-sm {
        font-family: var(--font-sans);
        font-size: 0.775rem;
        font-weight: 600;
        padding: 5px 11px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: var(--bg-elevated);
        color: var(--text-secondary);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.14s;
    }
    .btn-sm:hover { background: var(--bg-subtle); color: var(--text-primary); }
    .btn-sm-danger:hover {
        background: var(--danger-subtle);
        border-color: var(--danger-subtle-b);
        color: var(--danger);
    }

    /* ── Alert ── */
    .alert {
        padding: 11px 15px;
        border-radius: var(--radius);
        font-size: 0.83rem;
        margin-bottom: 1.1rem;
        line-height: 1.5;
    }
    .alert-success { background: #dcfce7; border: 1px solid #bbf7d0; color: #15803d; }
    .alert-error   { background: #fee2e2; border: 1px solid #fecaca; color: #dc2626; }

    /* ── Info box ── */
    .info-box {
        background: var(--accent-subtle);
        border: 1px solid var(--accent-subtle-b);
        border-radius: var(--radius);
        padding: 11px 14px;
        font-size: 0.8rem;
        color: var(--accent);
        line-height: 1.55;
        margin-bottom: 1.2rem;
    }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    .empty-state svg { margin-bottom: 0.75rem; opacity: 0.4; }

    /* ── Delete confirm modal ── */
    .modal-backdrop {
        position: fixed; inset: 0; z-index: 500;
        display: none; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.35);
        backdrop-filter: blur(4px);
    }
    .modal-backdrop.open { display: flex; }
    .modal-box {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-modal);
        width: 100%; max-width: 380px; margin: 1rem;
        overflow: hidden;
        animation: modalPop 0.22s cubic-bezier(0.34,1.5,0.64,1) both;
    }
    @keyframes modalPop {
        from { opacity:0; transform: scale(0.93) translateY(10px); }
        to   { opacity:1; transform: scale(1) translateY(0); }
    }
    .modal-body { padding: 1.6rem 1.6rem 1rem; text-align: center; }
    .modal-icon { width:44px; height:44px; border-radius:10px; background:var(--danger-subtle); border:1px solid var(--danger-subtle-b); display:flex; align-items:center; justify-content:center; margin: 0 auto 1rem; }
    .modal-title { font-size:.95rem; font-weight:700; color:var(--text-primary); margin-bottom:5px; }
    .modal-desc  { font-size:.83rem; color:var(--text-secondary); line-height:1.5; }
    .modal-footer { display:flex; border-top:1px solid var(--border); }
    .modal-btn { flex:1; padding:12px; font-family:var(--font-sans); font-size:.83rem; font-weight:600; background:none; border:none; cursor:pointer; transition:background 0.14s; }
    .modal-btn-cancel:hover { background:var(--bg-subtle); color:var(--text-secondary); }
    .modal-btn-divider { width:1px; background:var(--border); }
    .modal-btn-confirm { color:var(--danger); }
    .modal-btn-confirm:hover { background:var(--danger-subtle); }
</style>

{{-- ── Heading ── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="domains-page-title">Verified Domains</h1>
        <p style="font-size:0.82rem;color:var(--text-muted);margin-top:4px;">
            Verify domain ownership before embedding widgets on your websites.
        </p>
    </div>
</div>

{{-- ── Flash messages ── --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- ── Add domain ── --}}
<div class="section-card">
    <div class="section-header">
        <div class="section-header-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
            </svg>
        </div>
        <span class="section-header-title">Add a Domain</span>
    </div>
    <div class="section-body">
        <div class="info-box">
            You must verify ownership of a domain before widgets can be used on it. Enter your domain below — no scheme (https://), just the bare domain like <strong>example.com</strong>.
        </div>
        <form method="POST" action="{{ route('domains.store') }}">
            @csrf
            <div class="add-domain-row">
                <input
                    type="text"
                    name="domain"
                    class="domain-input"
                    placeholder="example.com"
                    value="{{ old('domain') }}"
                    autocomplete="off"
                    spellcheck="false"
                />
                <button type="submit" class="btn-add-domain">
                    Add Domain
                </button>
            </div>
            @error('domain')
                <p style="font-size:0.78rem;color:var(--danger);margin-top:6px;">{{ $message }}</p>
            @enderror
        </form>
    </div>
</div>

{{-- ── Domain list ── --}}
<div class="section-card">
    <div class="section-header">
        <div class="section-header-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <span class="section-header-title">Your Domains</span>
    </div>

    @if($domains->isEmpty())
        <div class="empty-state">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M2 12h20"/>
                <path stroke-linecap="round" d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
            </svg>
            <p>No domains added yet.</p>
            <p style="margin-top:4px;font-size:0.78rem;">Add your first domain above to get started.</p>
        </div>
    @else
        <table class="domain-table">
            <thead>
                <tr>
                    <th>Domain</th>
                    <th>Status</th>
                    <th>Verified Via</th>
                    <th>Added</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($domains as $domain)
                <tr>
                    <td>
                        <span style="font-family:var(--font-mono);font-weight:600;color:var(--text-primary);font-size:0.85rem;">
                            {{ $domain->domain }}
                        </span>
                    </td>
                    <td>
                        @if($domain->verified)
                            <span class="badge badge-verified">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                                Verified
                            </span>
                        @else
                            <span class="badge badge-pending">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4M12 16h.01"/></svg>
                                Pending
                            </span>
                        @endif
                    </td>
                    <td style="font-size:0.78rem;text-transform:capitalize;">
                        {{ $domain->verified_via ?? '—' }}
                    </td>
                    <td style="font-size:0.78rem;">
                        {{ $domain->created_at->format('d M Y') }}
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            @if(!$domain->verified)
                                <button
                                    type="button"
                                    class="btn-sm"
                                    onclick="toggleVerifyPanel('panel-{{ $domain->id }}')"
                                >
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Verify
                                </button>
                            @endif


                            {{-- ADD THIS RIGHT AFTER THE @endif: --}}
                            @if(!$domain->verified)
                                <form method="POST" action="{{ route('domains.force.verify', $domain->id) }}" style="display:inline;">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn-sm"
                                        style="color:var(--warning);border-color:var(--warning);background:var(--warning-subtle);"
                                        onclick="return confirm('Force verify {{ $domain->domain }} without checking? Use for testing only.')"
                                    >
                                        ⚡ Force Verify
                                    </button>
                                </form>
                            @endif

                            <!-- The revoke button here  -->
                            @if($domain->verified)
                                <form method="POST" action="{{ route('domains.revoke', $domain->id) }}" style="display:inline;">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn-sm"
                                        style="color:var(--danger);border-color:var(--danger-subtle-b);background:var(--danger-subtle);"
                                        onclick="return confirm('Revoke verification for {{ $domain->domain }}? The widget will stop loading on this domain until re-verified.')"
                                    >
                                        ↩ Revoke
                                    </button>
                                </form>
                            @endif
                            <!-- end revoke -->





                            <button
                                type="button"
                                class="btn-sm btn-sm-danger"
                                onclick="openDeleteModal({{ $domain->id }}, '{{ $domain->domain }}')"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m4-3h2a1 1 0 011 1v1H9V5a1 1 0 011-1h2z"/></svg>
                                Remove
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- ── Inline verification panel ── --}}
                @if(!$domain->verified)
                <tr>
                    <td colspan="5" style="padding: 0; border-bottom: 1px solid var(--border);">
                        <div id="panel-{{ $domain->id }}" class="verify-panel" style="margin: 0; border-radius: 0; border: none; border-top: 1px solid var(--border);">

                            {{-- Tabs --}}
                            <div class="verify-tabs">
                                <button class="verify-tab active" onclick="switchTab('{{ $domain->id }}', 'meta', this)">
                                    📄 Meta Tag
                                </button>
                                <button class="verify-tab" onclick="switchTab('{{ $domain->id }}', 'dns', this)">
                                    🌐 DNS TXT Record
                                </button>
                            </div>

                            {{-- Meta Tag pane --}}
                            <!-- <div id="tab-meta-{{ $domain->id }}" class="verify-pane active">
                                <p class="verify-instruction">
                                    <strong>Step 1:</strong> Copy the meta tag below and paste it inside the <code>&lt;head&gt;</code> of your website's homepage (<strong>{{ $domain->domain }}</strong>).
                                </p>
                                <div class="code-block" id="meta-code-{{ $domain->id }}">{{ $domain->metaTagHtml() }}<button class="copy-inline-btn" onclick="copyText('meta-code-{{ $domain->id }}', this)">Copy</button></div>
                                <p class="verify-instruction">
                                    <strong>Step 2:</strong> Once the tag is live, click <strong>Verify</strong>.
                                </p>
                                <form method="POST" action="{{ route('domains.verify.meta', $domain->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-verify">Verify via Meta Tag</button>
                                </form>
                            </div> -->


                            <div id="tab-meta-{{ $domain->id }}" class="verify-pane active">

                                <p class="verify-instruction">
                                    <strong>Step 1:</strong> Paste this tag inside the <code>&lt;head&gt;</code>
                                    of <strong>any page</strong> on <strong>{{ $domain->domain }}</strong>
                                    — your homepage <em>or</em> the specific page where the widget is embedded:
                                </p>

                                <div class="code-block" id="meta-code-{{ $domain->id }}">{{ $domain->metaTagHtml() }}<button class="copy-inline-btn" onclick="copyBlock('meta-code-{{ $domain->id }}', this)">Copy</button></div>

                                <p class="verify-instruction">
                                    <strong>Step 2 (optional):</strong>
                                    If you placed the tag on a <strong>specific page</strong> rather than the homepage
                                    (e.g. <code>https://{{ $domain->domain }}/customer-dashboard</code>),
                                    enter that URL below. Leave blank to check the homepage.
                                </p>

                                {{-- Optional specific-page URL input --}}
                                <div style="margin-bottom:1rem;">
                                    <input
                                        type="url"
                                        id="verify-url-{{ $domain->id }}"
                                        placeholder="https://{{ $domain->domain }}/your-page  (optional)"
                                        style="
                                            width: 100%;
                                            font-family: var(--font-mono);
                                            font-size: 0.8rem;
                                            padding: 7px 12px;
                                            border-radius: var(--radius);
                                            border: 1px solid var(--border);
                                            background: var(--bg-elevated);
                                            color: var(--text-primary);
                                            outline: none;
                                            transition: border-color 0.15s, box-shadow 0.15s;
                                            box-shadow: var(--shadow-xs);
                                        "
                                        onfocus="this.style.borderColor='var(--border-focus)';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'"
                                        onblur="this.style.borderColor='var(--border)';this.style.boxShadow='var(--shadow-xs)'"
                                    />
                                    <p style="font-size:0.72rem;color:var(--text-muted);margin-top:4px;padding-left:2px;">
                                        💡 The tag can live on any page of your site — homepage or the page with the widget.
                                    </p>
                                </div>

                                <p class="verify-instruction">
                                    <strong>Step 3:</strong> Once the tag is live, click <strong>Verify</strong>.
                                    We'll check the URL above first, then fall back to your homepage.
                                </p>

                                {{-- Submit form — injects verify_url before submitting --}}
                                <form
                                    method="POST"
                                    action="{{ route('domains.verify.meta', $domain->id) }}"
                                    style="display:inline;"
                                    onsubmit="injectVerifyUrl(this, 'verify-url-{{ $domain->id }}')"
                                >
                                    @csrf
                                    {{-- verify_url is injected here dynamically by injectVerifyUrl() --}}
                                    <button type="submit" class="btn-cta" style="font-size:0.8rem;padding:7px 16px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Verify via Meta Tag
                                    </button>
                                </form>

                            </div>




                            {{-- DNS TXT pane --}}
                            <div id="tab-dns-{{ $domain->id }}" class="verify-pane">
                                <p class="verify-instruction">
                                    <strong>Step 1:</strong> Log in to your DNS provider (e.g. Cloudflare, GoDaddy, Namecheap) and add a new <strong>TXT record</strong>:
                                </p>
                                <table style="font-size:0.78rem;margin-bottom:1rem;border-collapse:collapse;width:100%;">
                                    <tr>
                                        <td style="padding:5px 10px 5px 0;color:var(--text-muted);font-weight:600;white-space:nowrap;">Name / Host</td>
                                        <td>
                                            <div class="code-block" id="dns-name-{{ $domain->id }}" style="margin:0;padding:5px 10px;display:inline-block;width:100%;">@<button class="copy-inline-btn" onclick="copyText('dns-name-{{ $domain->id }}', this)">Copy</button></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:5px 10px 5px 0;color:var(--text-muted);font-weight:600;white-space:nowrap;">Value</td>
                                        <td>
                                            <div class="code-block" id="dns-value-{{ $domain->id }}" style="margin:0;padding:5px 10px;display:inline-block;width:100%;">{{ $domain->dnsTxtValue() }}<button class="copy-inline-btn" onclick="copyText('dns-value-{{ $domain->id }}', this)">Copy</button></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:5px 10px 5px 0;color:var(--text-muted);font-weight:600;white-space:nowrap;">TTL</td>
                                        <td style="font-size:0.78rem;color:var(--text-muted);padding:5px 0;">Auto or 3600</td>
                                    </tr>
                                </table>
                                <p class="verify-instruction">
                                    <strong>Step 2:</strong> DNS changes can take <strong>5–48 hours</strong> to propagate. Once live, click Verify.
                                </p>
                                <form method="POST" action="{{ route('domains.verify.dns', $domain->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-verify">Verify via DNS</button>
                                </form>
                            </div>

                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ── Delete confirm modal ── --}}
<div id="deleteModal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-body">
            <div class="modal-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--danger)">
                    <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m4-3h2a1 1 0 011 1v1H9V5a1 1 0 011-1h2z"/>
                </svg>
            </div>
            <p class="modal-title">Remove Domain</p>
            <p class="modal-desc">Remove <strong id="deleteDomainName"></strong>? Any widgets tied to this domain will stop passing domain verification.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteModal()" class="modal-btn modal-btn-cancel">Cancel</button>
            <div class="modal-btn-divider"></div>
            <form id="deleteForm" method="POST" style="flex:1;display:flex;">
                @csrf @method('DELETE')
                <button type="submit" class="modal-btn modal-btn-confirm" style="width:100%;">Remove</button>
            </form>
        </div>
    </div>
</div>

<script>
    /* ── Verify panel toggle ── */
    function toggleVerifyPanel(panelId) {
        var panel = document.getElementById(panelId);
        if (!panel) return;
        panel.classList.toggle('open');
    }

    /* ── Tab switching ── */
    function switchTab(domainId, tab, btn) {
        var tabs  = btn.closest('.verify-tabs').querySelectorAll('.verify-tab');
        var panes = btn.closest('.verify-panel').querySelectorAll('.verify-pane');
        tabs.forEach(function (t) { t.classList.remove('active'); });
        panes.forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        var target = document.getElementById('tab-' + tab + '-' + domainId);
        if (target) target.classList.add('active');
    }

    /* ── Copy to clipboard ── */
    function copyText(elId, btn) {
        var el   = document.getElementById(elId);
        var text = el.innerText.replace('Copy', '').trim();
        navigator.clipboard.writeText(text).then(function () {
            var orig = btn.textContent;
            btn.textContent = '✓';
            setTimeout(function () { btn.textContent = orig; }, 2000);
        });
    }

    /* ── Delete modal ── */
    var _pendingDeleteId = null;

    function openDeleteModal(id, name) {
        _pendingDeleteId = id;
        document.getElementById('deleteDomainName').textContent = name;
        document.getElementById('deleteForm').action = '/domains/' + id;
        document.getElementById('deleteModal').classList.add('open');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('open');
    }
    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDeleteModal();
    });







// for meta tag only

    function injectVerifyUrl(form, inputId) {
        var input = document.getElementById(inputId);
        if (!input || !input.value.trim()) return; // blank = homepage, nothing to inject
        var hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = 'verify_url';
        hidden.value = input.value.trim();
        form.appendChild(hidden);
    }
</script>

@endsection
