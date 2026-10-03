<style>
    .adm-page { padding: 2rem 0; }
    .adm-container { max-width: 80rem; margin: 0 auto; padding: 0 1rem; }
    .adm-container-narrow { max-width: 46rem; margin: 0 auto; padding: 0 1rem; }
    .adm-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
    .adm-header-title { display: flex; align-items: center; gap: 1rem; }
    .adm-title { font-weight: 600; font-size: 1.25rem; color: #fff; }
    .adm-muted { color: #9ca3af; }
    .adm-small { font-size: .85rem; }
    .adm-strong { font-weight: 600; }
    .adm-icon-gap { margin-inline-end: .5rem; }
    html[dir="rtl"] .adm-flip { transform: scaleX(-1); }

    .adm-card { background: var(--bg-card, #1e293b); border: 1px solid var(--border-primary, #334155); border-radius: var(--radius-xl, 1rem); padding: 1.5rem; margin-bottom: 2rem; }
    .adm-card-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.25rem; }
    .adm-card-title { font-size: 1.35rem; font-weight: 600; color: #fff; }

    .adm-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .adm-metric { background: var(--bg-card, #1e293b); border: 1px solid var(--border-primary, #334155); border-radius: var(--radius-lg, .75rem); padding: 1.1rem 1.25rem; }
    .adm-metric-value { font-size: 1.9rem; font-weight: 700; color: #fff; line-height: 1.2; }
    .adm-metric-label { color: #9ca3af; font-size: .875rem; }
    .adm-metric a { color: inherit !important; text-decoration: none; }

    .adm-filters { display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-end; }
    .adm-filters .adm-field { flex: 1 1 12rem; }
    .adm-filters .adm-field-wide { flex: 3 1 18rem; }

    .adm-form { display: grid; gap: 1.5rem; }
    .adm-section { background: var(--bg-tertiary, #0f172a); border: 1px solid var(--border-primary, #334155); border-radius: var(--radius-lg, .75rem); padding: 1.5rem; display: grid; gap: 1.25rem; }
    .adm-section-title { font-size: 1.1rem; font-weight: 700; color: #fff; }
    .adm-field { display: grid; gap: .4rem; }
    .adm-label { font-weight: 600; color: #e5e7eb; font-size: .95rem; }
    .adm-input { width: 100%; border: 1px solid #4b5563; border-radius: .6rem; padding: .7rem .9rem; font-size: .95rem; text-align: start; }
    .adm-input[aria-invalid="true"] { border-color: #f87171 !important; }
    .adm-input-ltr { direction: ltr; text-align: left; }
    html[dir="rtl"] .adm-input-ltr { text-align: right; }
    .adm-help { color: #9ca3af; font-size: .8rem; }
    .adm-field-error { color: #f87171; font-size: .82rem; }
    .adm-radio-group { display: flex; flex-wrap: wrap; gap: .75rem; }
    .adm-radio { display: flex; align-items: center; gap: .5rem; padding: .75rem 1rem; border: 1px solid #4b5563; border-radius: .6rem; cursor: pointer; flex: 1 1 12rem; }
    .adm-radio input { width: auto; }
    .adm-check { display: flex; align-items: flex-start; gap: .6rem; cursor: pointer; }
    .adm-check input { width: 1.1rem; height: 1.1rem; margin-top: .2rem; flex-shrink: 0; }

    .adm-actions { display: flex; flex-wrap: wrap; gap: .75rem; }
    .adm-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .65rem 1.2rem; border-radius: .6rem; font-weight: 600; cursor: pointer; text-decoration: none !important; border: 1px solid transparent; font-size: .95rem; }
    .adm-btn:disabled { opacity: .5; cursor: not-allowed; }
    .adm-btn-primary { background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; }
    .adm-btn-secondary { background: #374151 !important; color: #e5e7eb !important; border-color: #4b5563 !important; }
    .adm-btn-danger { background: linear-gradient(135deg, #dc2626, #b91c1c) !important; color: #fff !important; }
    .adm-btn-sm { padding: .4rem .75rem; font-size: .82rem; }

    .adm-table-wrap { overflow-x: auto; }
    .adm-table { width: 100%; border-collapse: collapse; }
    .adm-table th { text-align: start; color: #d1d5db; font-weight: 600; padding: .75rem; border-bottom: 1px solid #4b5563; white-space: nowrap; }
    .adm-table td { text-align: start; padding: .75rem; border-bottom: 1px solid var(--border-primary, #334155); vertical-align: top; }
    .adm-row-inactive { opacity: .7; }

    .adm-avatar { width: 2.75rem; height: 2.75rem; border-radius: 50%; object-fit: contain; flex-shrink: 0; border: 2px solid #475569; }
    .adm-avatar-placeholder { width: 2.75rem; height: 2.75rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; font-weight: 700; flex-shrink: 0; }
    .adm-cell-flex { display: flex; align-items: center; gap: .75rem; }
    .adm-code { background: #0f172a; color: #cbd5e1; padding: .25rem .6rem; border-radius: .4rem; font-family: monospace; font-size: .85rem; direction: ltr; unicode-bidi: isolate; }
    .adm-ltr { direction: ltr; unicode-bidi: isolate; }

    .adm-badge { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .65rem; border-radius: 999px; font-size: .78rem; font-weight: 600; white-space: nowrap; }
    .adm-badge-green { background: rgba(34,197,94,.12); color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
    .adm-badge-red { background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.3); }
    .adm-badge-amber { background: rgba(245,158,11,.12); color: #fbbf24; border: 1px solid rgba(245,158,11,.3); }
    .adm-badge-gray { background: rgba(148,163,184,.12); color: #cbd5e1; border: 1px solid rgba(148,163,184,.3); }

    .adm-icon-actions { display: flex; flex-wrap: wrap; gap: .4rem; }
    .adm-icon-btn { width: 2.25rem; height: 2.25rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .5rem; border: 1px solid #4b5563 !important; cursor: pointer; text-decoration: none !important; background: #1f2937 !important; }
    .adm-icon-btn:hover { background: #374151 !important; }
    .adm-icon-btn.adm-danger { color: #f87171 !important; border-color: rgba(239,68,68,.4) !important; }
    .adm-icon-btn.adm-ok { color: #4ade80 !important; border-color: rgba(34,197,94,.4) !important; }
    .adm-icon-btn.adm-warn { color: #fbbf24 !important; border-color: rgba(245,158,11,.4) !important; }

    .adm-notes { max-width: 16rem; white-space: pre-line; color: #e5e7eb; font-size: .85rem; overflow-wrap: anywhere; }
    .adm-notes-details summary { cursor: pointer; color: #93c5fd; font-size: .8rem; margin-top: .3rem; }
    .adm-notes-details form { margin-top: .5rem; display: grid; gap: .5rem; min-width: 14rem; }

    .adm-alert { display: flex; align-items: flex-start; gap: .5rem; padding: 1rem; border-radius: .75rem; margin-bottom: 1.25rem; }
    .adm-alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
    .adm-alert-error { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }
    .adm-error-list { list-style: disc; padding-inline-start: 1.25rem; margin-top: .25rem; }

    .adm-empty { text-align: center; padding: 3rem 1rem; color: #9ca3af; }
    .adm-pagination { margin-top: 1rem; }

    .adm-modal { position: fixed; inset: 0; background: rgba(0,0,0,.8); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 1rem; }
    .adm-modal.is-open { display: flex; }
    .adm-modal-box { background: var(--bg-card, #1e293b); border: 1px solid #475569; border-radius: 1rem; max-width: 34rem; width: 100%; max-height: 90vh; overflow-y: auto; }
    .adm-modal-head { padding: 1.5rem 1.5rem .5rem; text-align: center; }
    .adm-modal-body { padding: 0 1.5rem 1rem; display: grid; gap: 1rem; }
    .adm-modal-foot { padding: 1rem 1.5rem 1.5rem; border-top: 1px solid #334155; display: flex; flex-wrap: wrap; gap: .75rem; justify-content: flex-end; }
    .adm-warning-box { background: rgba(220,38,38,.06); border: 1px solid rgba(220,38,38,.25); border-radius: .75rem; padding: 1rem; color: #d1d5db; }
    .adm-warning-box ul { list-style: disc; padding-inline-start: 1.25rem; color: #9ca3af; font-size: .9rem; margin-top: .4rem; }
    .adm-owner-note { border-radius: .6rem; padding: .75rem; font-size: .88rem; }
    .adm-owner-note-delete { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }
    .adm-owner-note-keep { background: rgba(59,130,246,.08); border: 1px solid rgba(59,130,246,.3); color: #93c5fd; }
</style>