@extends('layouts.app')

@section('content')

<style>
    /* ── Page header ── */
    .page-eyebrow {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 5px;
    }
    .page-title {
        font-size: clamp(1.35rem, 2.5vw, 1.65rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-primary);
        line-height: 1.2;
    }

    /* ── Search ── */
    .search-wrap {
        position: relative;
    }
    .search-wrap svg {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
        width: 14px; height: 14px;
    }
    .search-input {
        font-family: var(--font-sans);
        font-size: 0.845rem;
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        color: var(--text-primary);
        padding: 7px 12px 7px 32px;
        width: 220px;
        transition: border-color 0.15s, box-shadow 0.15s, width 0.2s;
        box-shadow: var(--shadow-xs);
    }
    .search-input::placeholder { color: var(--text-muted); }
    .search-input:focus {
        outline: none;
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        width: 260px;
    }
    [data-theme="dark"] .search-input:focus {
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }

    /* ── Table card ── */
    .table-card {
        background: var(--bg-elevated);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: background 0.22s, border-color 0.22s;
    }
    .table-card table {
        width: 100%;
        border-collapse: collapse;
    }
    .table-card thead th {
        padding: 11px 16px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-muted);
        background: var(--bg-subtle);
        border-bottom: 1px solid var(--border);
        text-align: left;
        white-space: nowrap;
        transition: background 0.22s;
    }
    .table-card thead th:last-child { text-align: right; }

    .table-card tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.12s;
        animation: rowFadeIn 0.25s ease both;
    }
    .table-card tbody tr:last-child { border-bottom: none; }
    .table-card tbody tr:hover { background: var(--bg-hover); }

    @keyframes rowFadeIn {
        from { opacity:0; transform: translateY(5px); }
        to   { opacity:1; transform: translateY(0); }
    }
    .table-card tbody tr:nth-child(1)  { animation-delay: .03s; }
    .table-card tbody tr:nth-child(2)  { animation-delay: .06s; }
    .table-card tbody tr:nth-child(3)  { animation-delay: .09s; }
    .table-card tbody tr:nth-child(4)  { animation-delay: .12s; }
    .table-card tbody tr:nth-child(5)  { animation-delay: .15s; }
    .table-card tbody tr:nth-child(6)  { animation-delay: .18s; }
    .table-card tbody tr:nth-child(7)  { animation-delay: .21s; }
    .table-card tbody tr:nth-child(8)  { animation-delay: .24s; }
    .table-card tbody tr:nth-child(9)  { animation-delay: .27s; }
    .table-card tbody tr:nth-child(10) { animation-delay: .30s; }

    .table-card tbody td {
        padding: 13px 16px;
        font-size: 0.855rem;
        color: var(--text-secondary);
        vertical-align: middle;
    }

    /* ── Widget name cell ── */
    .widget-name {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-primary);
        letter-spacing: -0.01em;
    }

    /* ── Badges ── */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 9px;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        border: 1px solid;
    }
    .badge-mode {
        background: var(--bg-subtle);
        color: var(--text-secondary);
        border-color: var(--border);
    }
    .badge-active {
        background: var(--success-subtle);
        color: var(--success);
        border-color: transparent;
    }
    .badge-active::before {
        content: '';
        width: 5px; height: 5px;
        border-radius: 50%;
        background: var(--success);
        display: inline-block;
    }
    .badge-inactive {
        background: var(--bg-subtle);
        color: var(--text-muted);
        border-color: var(--border);
    }
    .badge-inactive::before {
        content: '';
        width: 5px; height: 5px;
        border-radius: 50%;
        background: var(--text-muted);
        display: inline-block;
    }

    /* ── Color cell ── */
    .color-cell {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--bg-subtle);
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 3px 9px;
        font-family: var(--font-mono);
        font-size: 0.75rem;
        color: var(--text-secondary);
        transition: background 0.22s;
    }
    .color-dot {
        width: 11px; height: 11px;
        border-radius: 50%;
        border: 1px solid rgba(0,0,0,0.1);
        flex-shrink: 0;
    }

    /* ── Hits mini bar ── */
    .hits-cell { display: flex; flex-direction: column; gap: 4px; }
    .hits-label { font-size: 0.78rem; color: var(--text-secondary); }
    .hits-bar { width: 72px; height: 3px; background: var(--bg-subtle); border-radius: 99px; overflow: hidden; }
    .hits-fill { height: 100%; border-radius: 99px; background: var(--accent); transition: width 0.6s ease; }

    /* ── Action buttons ── */
    .tbl-actions { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }
    .tbl-btn {
        font-family: var(--font-sans);
        font-size: 0.795rem;
        font-weight: 600;
        padding: 5px 11px;
        border-radius: 7px;
        border: 1px solid var(--border);
        background: var(--bg-elevated);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 5px;
        transition: all 0.14s ease;
        box-shadow: var(--shadow-xs);
    }
    .tbl-btn-edit {
        color: var(--text-secondary);
    }
    .tbl-btn-edit:hover {
        background: var(--accent-subtle);
        border-color: var(--accent-subtle-b);
        color: var(--accent);
    }
    .tbl-btn-delete {
        color: var(--text-secondary);
    }
    .tbl-btn-delete:hover {
        background: var(--danger-subtle);
        border-color: var(--danger-subtle-b);
        color: var(--danger);
    }

    /* ── Empty state ── */
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
        color: var(--text-muted);
    }
    .empty-state svg { margin: 0 auto 12px; opacity: 0.4; }
    .empty-state p { font-size: 0.875rem; }

    /* ── Delete modal ── */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 500;
        display: none;
        align-items: center;
        justify-content: center;
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
        width: 100%;
        max-width: 400px;
        margin: 1rem;
        overflow: hidden;
        animation: modalPop 0.22s cubic-bezier(0.34,1.5,0.64,1) both;
        transition: background 0.22s, border-color 0.22s;
    }
    @keyframes modalPop {
        from { opacity:0; transform: scale(0.93) translateY(10px); }
        to   { opacity:1; transform: scale(1) translateY(0); }
    }

    .modal-body {
        padding: 1.75rem 1.75rem 1.25rem;
        text-align: center;
    }
    .modal-icon-wrap {
        width: 48px; height: 48px;
        border-radius: 12px;
        background: var(--danger-subtle);
        border: 1px solid var(--danger-subtle-b);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.1rem;
    }
    .modal-title {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--text-primary);
        margin-bottom: 6px;
    }
    .modal-desc {
        font-size: 0.845rem;
        color: var(--text-secondary);
        line-height: 1.55;
    }
    .modal-desc strong { color: var(--text-primary); font-weight: 600; }

    .modal-footer {
        display: flex;
        border-top: 1px solid var(--border);
        transition: border-color 0.22s;
    }
    .modal-btn {
        flex: 1;
        padding: 13px;
        font-family: var(--font-sans);
        font-size: 0.845rem;
        font-weight: 600;
        background: none;
        border: none;
        cursor: pointer;
        transition: background 0.14s;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    }
    .modal-btn-cancel { color: var(--text-secondary); }
    .modal-btn-cancel:hover { background: var(--bg-subtle); }
    .modal-btn-divider { width: 1px; background: var(--border); transition: background 0.22s; }
    .modal-btn-confirm { color: var(--danger); }
    .modal-btn-confirm:hover { background: var(--danger-subtle); }
    .modal-btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }

    /* ── Responsive ── */
    .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    @media (max-width: 640px) {
        .page-controls { flex-direction: column; align-items: flex-start !important; }
        .search-input, .search-input:focus { width: 100%; }
        .search-wrap { width: 100%; }
    }
</style>


{{-- ── Page header ── --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-6 page-controls">
    <div>
        <p class="page-eyebrow">Management Console</p>
        <h1 class="page-title">Widgets</h1>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <div class="search-wrap" data-tip="Filter by widget name">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
            </svg>
            <input type="text" id="widgetSearch" placeholder="Search widgets…" class="search-input">
        </div>

        <a href="{{ route('widgets.create') }}" class="btn-cta" data-tip="Create a new widget">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
            New Widget
        </a>
    </div>
</div>


{{-- ── Table ── --}}
<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th data-tip="Widget display name">Name</th>
                    <th data-tip="Render mode">Mode</th>
                    <th data-tip="Theme color">Color</th>
                    <th data-tip="API usage vs. limit">Hits / Limit</th>
                    <th data-tip="Live status">Status</th>
                    <th data-tip="Available actions" style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($widgets as $w)
                <tr>
                    <td><span class="widget-name">{{ $w->name }}</span></td>
                    <td><span class="badge badge-mode">{{ ucfirst($w->mode) }}</span></td>
                    <td>
                        <span class="color-cell" data-tip="Theme: {{ $w->theme_color }}">
                            <span class="color-dot" style="background:{{ $w->theme_color }}"></span>
                            {{ $w->theme_color }}
                        </span>
                    </td>
                    <td>
                        @php $pct = min(100, ($w->api_limit > 0 ? intval(($w->api_hits/$w->api_limit)*100) : 0)); @endphp
                        <div class="hits-cell" data-tip="{{ $pct }}% consumed">
                            <span class="hits-label">{{ number_format($w->api_hits) }} / {{ number_format($w->api_limit) }}</span>
                            <div class="hits-bar">
                                <div class="hits-fill" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($w->is_active)
                            <span class="badge badge-active" data-tip="Widget is live">Active</span>
                        @else
                            <span class="badge badge-inactive" data-tip="Widget is disabled">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="tbl-actions">
                            <a href="{{ route('widgets.show', $w->id) }}" class="tbl-btn tbl-btn-edit" data-tip="View & update widget">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                            </a>

                            <button type="button"
                                    onclick="openDeleteModal('{{ $w->id }}', '{{ addslashes($w->name) }}')"
                                    class="tbl-btn tbl-btn-delete"
                                    data-tip="Permanently delete">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m4-3h2a1 1 0 011 1v1H9V5a1 1 0 011-1h2z"/>
                                </svg>
                                Delete
                            </button>

                            {{-- Hidden delete form --}}
                            <form id="deleteForm-{{ $w->id }}"
                                  action="{{ route('widgets.destroy', $w->id) }}"
                                  method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                <path stroke-linecap="round" d="M9 9h6M9 12h4"/>
                            </svg>
                            <p style="font-weight:600;color:var(--text-secondary);margin-bottom:3px;">No widgets yet</p>
                            <p>Create your first widget to get started.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-5">
    {{ $widgets->links() }}
</div>


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
                <strong><span id="deleteWidgetName"></span></strong>?
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
    /* ── Delete modal ── */
    var pendingDeleteId = null;

    function openDeleteModal(id, name) {
        pendingDeleteId = id;
        document.getElementById('deleteWidgetName').textContent  = name;
        document.getElementById('deleteConfirmBtn').disabled     = false;
        document.getElementById('deleteSpinner').classList.add('hidden');
        document.getElementById('deleteConfirmText').textContent = 'Delete';
        document.getElementById('deleteModal').classList.add('open');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('open');
        pendingDeleteId = null;
    }
    function confirmDelete() {
        if (!pendingDeleteId) return;

        document.getElementById('deleteConfirmBtn').disabled     = true;
        document.getElementById('deleteSpinner').classList.remove('hidden');
        document.getElementById('deleteConfirmText').textContent = 'Deleting…';

        var form  = document.getElementById('deleteForm-' + pendingDeleteId);
        var token = form.querySelector('input[name="_token"]').value;

        $.ajax({
            url: form.action,
            method: 'POST',
            data: { _method: 'DELETE', _token: token },
            success: function () {
                var row = form.closest('tr');
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity    = '0';
                    row.style.transform  = 'translateX(20px)';
                    setTimeout(function () { row.remove(); }, 300);
                }
                closeDeleteModal();
            },
            error: function () {
                document.getElementById('deleteConfirmBtn').disabled     = false;
                document.getElementById('deleteSpinner').classList.add('hidden');
                document.getElementById('deleteConfirmText').textContent = 'Delete';
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

    /* ── Search ── */
    document.getElementById('widgetSearch').addEventListener('input', function () {
        var term = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(function (row) {
            var name = row.querySelector('td')?.textContent.toLowerCase() ?? '';
            row.style.display = name.includes(term) ? '' : 'none';
        });
    });
</script>

@endsection