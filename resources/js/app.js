import './bootstrap';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

window.Alpine = Alpine;
window.Sortable = Sortable;

/* ==========================================================================
 *  Toast notifications (locale-aware)
 * --------------------------------------------------------------------------
 *  Usage:
 *      window.toast.success('تم الحفظ');
 *      window.toast.error('...');
 *      window.toast.info('...');
 *
 *  A single <div id="toast-host"> at the end of <body> hosts the messages.
 *  Toasts slide in from the appropriate edge for the document direction
 *  (LTR from the right, RTL from the left) and auto-dismiss after 3.5s.
 * ========================================================================== */
(function initToasts() {
    const ensureHost = () => {
        let host = document.getElementById('toast-host');
        if (!host) {
            host = document.createElement('div');
            host.id = 'toast-host';
            host.className = 'toast-host';
            host.setAttribute('role', 'status');
            host.setAttribute('aria-live', 'polite');
            document.body.appendChild(host);
        }
        return host;
    };

    const show = (kind, message, timeoutMs = 3500) => {
        if (!message) return;
        const host = ensureHost();
        const el = document.createElement('div');
        el.className = `toast toast-${kind}`;
        el.innerHTML = `
            <span class="toast-icon" aria-hidden="true">
                ${kind === 'success' ? '✓' : kind === 'error' ? '✕' : 'ℹ'}
            </span>
            <span class="toast-text"></span>
            <button class="toast-close" aria-label="close" type="button">×</button>
        `;
        el.querySelector('.toast-text').textContent = message;
        host.appendChild(el);

        // animate in next frame
        requestAnimationFrame(() => el.classList.add('toast-visible'));

        const dismiss = () => {
            el.classList.remove('toast-visible');
            el.addEventListener('transitionend', () => el.remove(), { once: true });
        };
        el.querySelector('.toast-close').addEventListener('click', dismiss);
        setTimeout(dismiss, timeoutMs);
    };

    window.toast = {
        success: (m) => show('success', m),
        error:   (m) => show('error', m, 5000),
        info:    (m) => show('info', m),
    };
})();

/* ==========================================================================
 *  kiloSubmit — AJAX form submission helper
 * --------------------------------------------------------------------------
 *  Submits an <form> via fetch(), includes CSRF token and file uploads,
 *  and dispatches success/error toasts using translations it pulls from
 *  the global `window.KiloI18n` object (set by the layout).
 *
 *  Usage in Blade:
 *      <form data-ajax
 *            data-ajax-success-toast
 *            action="..."
 *            method="POST">
 *
 *  Options (via data-* attributes):
 *    data-ajax                         → enables AJAX handling
 *    data-ajax-success="route.name"    → optional; reload page on success
 *    data-ajax-success-toast           → show a success toast (message from JSON)
 *    data-ajax-reload                  → reload page after toast finishes
 *    data-ajax-redirect="/url"         → redirect to URL after success
 *
 *  Fallback: if `data-ajax` is absent or JS is disabled the browser submits
 *  the form normally.
 * ========================================================================== */
async function kiloSubmit(form) {
    const t      = window.KiloI18n || {};
    const token  = document.querySelector('meta[name="csrf-token"]')?.content;
    const method = (form.getAttribute('method') || 'POST').toUpperCase();
    const action = form.getAttribute('action');

    const formData = new FormData(form);
    // Laravel honors _method spoofing on POST.
    if (method !== 'POST' && method !== 'GET') {
        formData.append('_method', method);
    }

    const submitBtn = form.querySelector('[type="submit"]');
    const originalBtnHtml = submitBtn ? submitBtn.innerHTML : null;
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.dataset.loading = '1';
        submitBtn.innerHTML = `<span class="btn-spinner"></span><span>${t.saving || 'Saving…'}</span>`;
    }

    try {
        const res = await fetch(action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
        });

        let payload = {};
        try { payload = await res.json(); } catch (e) { /* non-JSON (HTML redirect) */ }

        if (res.ok && payload.success !== false) {
            const msg = payload.message || t.saved || 'Saved';
            window.toast.success(msg);

            if (form.dataset.ajaxRedirect) {
                setTimeout(() => { window.location.href = form.dataset.ajaxRedirect; }, 600);
            } else if (form.hasAttribute('data-ajax-reload')) {
                setTimeout(() => window.location.reload(), 600);
            }

            // Fire a custom event consumers can listen to for local state updates.
            form.dispatchEvent(new CustomEvent('ajax:success', {
                detail: payload,
                bubbles: true,
            }));

            return payload;
        }

        // === Error path ===
        if (res.status === 422 && payload.errors) {
            // Surface the first field error as the toast; underline the field.
            const firstField = Object.keys(payload.errors)[0];
            const firstMsg   = payload.errors[firstField][0];
            window.toast.error(firstMsg || t.validationFailed || 'Validation failed');

            form.dispatchEvent(new CustomEvent('ajax:validation-error', {
                detail: payload.errors,
                bubbles: true,
            }));
        } else {
            const msg = payload.message || t.saveFailed || 'Save failed';
            window.toast.error(msg);
        }

        form.dispatchEvent(new CustomEvent('ajax:error', {
            detail: { status: res.status, payload },
            bubbles: true,
        }));
    } catch (err) {
        window.toast.error(t.networkError || 'Network error');
        form.dispatchEvent(new CustomEvent('ajax:error', { detail: { error: err }, bubbles: true }));
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            delete submitBtn.dataset.loading;
            submitBtn.innerHTML = originalBtnHtml;
        }
    }
}
window.kiloSubmit = kiloSubmit;

// Delegated listener: any <form data-ajax> on the page gets handled.
document.addEventListener('submit', (e) => {
    const form = e.target.closest('form[data-ajax]');
    if (!form) return;
    e.preventDefault();
    kiloSubmit(form);
});

/* ==========================================================================
 *  kiloAction — trigger a non-form AJAX POST (for confirm/delete buttons)
 * --------------------------------------------------------------------------
 *  Usage:
 *      <button type="button"
 *              data-ajax-action
 *              data-url="/menu-item/5"
 *              data-method="DELETE"
 *              data-confirm="Delete this item?"
 *              data-on-success-remove=".card">
 *          Delete
 *      </button>
 *
 *  `data-on-success-remove` removes the closest matching ancestor from the DOM
 *  on success (useful for inline delete buttons).
 * ========================================================================== */
async function kiloAction(btn) {
    const t       = window.KiloI18n || {};
    const url     = btn.dataset.url;
    const method  = (btn.dataset.method || 'POST').toUpperCase();
    const confirmMsg = btn.dataset.confirm;

    if (confirmMsg && !window.confirm(confirmMsg)) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const body  = new FormData();
    if (method !== 'POST' && method !== 'GET') body.append('_method', method);

    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.dataset.loading = '1';

    try {
        const res = await fetch(url, {
            method: 'POST',
            body,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
        });
        let payload = {};
        try { payload = await res.json(); } catch (e) {}

        if (res.ok && payload.success !== false) {
            window.toast.success(payload.message || t.saved || 'Saved');

            const removeSel = btn.dataset.onSuccessRemove;
            if (removeSel) {
                const target = btn.closest(removeSel);
                if (target) {
                    target.style.transition = 'opacity .25s, transform .25s';
                    target.style.opacity = 0;
                    target.style.transform = 'scale(.95)';
                    setTimeout(() => target.remove(), 260);
                }
            }
            if (btn.dataset.onSuccessReload !== undefined) {
                setTimeout(() => window.location.reload(), 600);
            }
            btn.dispatchEvent(new CustomEvent('ajax:success', { detail: payload, bubbles: true }));
        } else {
            window.toast.error(payload.message || t.saveFailed || 'Failed');
        }
    } catch (err) {
        window.toast.error(t.networkError || 'Network error');
    } finally {
        btn.disabled = false;
        delete btn.dataset.loading;
        btn.innerHTML = originalHtml;
    }
}
window.kiloAction = kiloAction;

document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-ajax-action]');
    if (!btn) return;
    e.preventDefault();
    kiloAction(btn);
});

/* ==========================================================================
 *  x-sortable Alpine directive (SortableJS wrapper)
 * ========================================================================== */
Alpine.directive('sortable', (el, { expression }, { evaluate }) => {
    const handleSelector = el.dataset.sortableHandle || '.drag-handle';
    const handleExists   = el.querySelector(handleSelector) !== null;

    new Sortable(el, {
        animation: 150,
        ghostClass:    'sortable-ghost',
        dragClass:     'sortable-drag',
        handle:        handleExists ? handleSelector : undefined,
        // forceFallback bypasses native browser DnD — required for CSS grid containers
        // and for any sortable list inside a <details> element.
        forceFallback: el.hasAttribute('data-sortable-fallback'),
        onEnd: () => {
            const order = Array.from(el.children).map((row) => row.dataset.id);
            Array.from(el.children).forEach((row, idx) => {
                const positionInputs = row.querySelectorAll('input[data-position-input]');
                positionInputs.forEach((inp) => { inp.value = idx; });
            });
            // data-sortable-url: call saveOrder directly (avoids Alpine evaluate scope issues)
            const url = el.dataset.sortableUrl;
            if (url) {
                window.saveOrder(order, url);
            } else if (expression) {
                // Fallback for existing x-sortable expressions (e.g. option-groups editor)
                evaluate(expression, { scope: { order } });
            }
        },
    });
});

/* ==========================================================================
 *  saveOrder — persist a drag-and-drop sort result to the server
 * --------------------------------------------------------------------------
 *  Called from x-sortable expressions in Blade templates:
 *      x-sortable="window.saveOrder(order, '/route/url')"
 *
 *  `order` is an array of string IDs provided by the x-sortable directive.
 * ========================================================================== */
async function saveOrder(order, url) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({ order: order.map(Number) }),
            credentials: 'same-origin',
        });
        const payload = await res.json().catch(() => ({}));
        if (res.ok && payload.success !== false) {
            window.toast.success(payload.message || 'Order saved');
        } else {
            window.toast.error(payload.message || 'Failed to save order');
        }
    } catch (e) {
        window.toast.error((window.KiloI18n || {}).networkError || 'Network error');
    }
}
window.saveOrder = saveOrder;

Alpine.start();
