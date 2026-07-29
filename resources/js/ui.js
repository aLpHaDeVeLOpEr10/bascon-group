/**
 * BASCON — UI runtime
 * ---------------------------------------------------------------------------
 * Replaces bootstrap.js and the previous bascon-ui.js.
 *
 * Bootstrap's JavaScript is no longer loaded. An audit of every view showed
 * the application only ever used two of its plugins:
 *
 *     44 x  data-toggle="tab"
 *     16 x  data-dismiss="modal"
 *     15 x  $('#id').modal('show')
 *     21 x  $('#id').modal('hide')
 *
 * (plus one stray data-toggle="popover" and one data-toggle="tooltip" that
 * were never wired to anything.)
 *
 * Rather than rewrite the ~90 AJAX handlers scattered through the views, the
 * two plugin APIs are reimplemented below against jQuery with the same call
 * signatures. Every existing `$('#updateModal').modal('show')` keeps working
 * untouched; only the markup and styling around it changed.
 *
 * Everything else here is shell behaviour: navigation, dropdowns, search,
 * password toggles, toasts and the route progress bar.
 */

const doc = document;

const $$ = (sel, ctx = doc) => Array.from(ctx.querySelectorAll(sel));
const $1 = (sel, ctx = doc) => ctx.querySelector(sel);
const on = (el, evt, fn, opts) => el && el.addEventListener(evt, fn, opts);

const MOBILE = '(max-width: 1023px)';
const isMobile = () => window.matchMedia(MOBILE).matches;

const store = {
    get(key) {
        try { return window.localStorage.getItem(key); } catch { return null; }
    },
    set(key, value) {
        try { window.localStorage.setItem(key, value); } catch { /* private mode */ }
    },
};

/* ========================================================================== */
/* Bootstrap plugin replacements                                              */
/* ========================================================================== */

/**
 * Modal.
 *
 * Keeps Bootstrap's public surface: $(el).modal('show' | 'hide' | 'toggle').
 * Being passed an options object (or nothing) opens the modal, matching the
 * old behaviour closely enough for every call site in this app.
 */
const Modal = {
    openStack: [],

    backdrop() {
        let el = $1('#ui-modal-backdrop');
        if (!el) {
            el = doc.createElement('div');
            el.id = 'ui-modal-backdrop';
            el.className = 'modal-backdrop';
            doc.body.appendChild(el);
        }
        return el;
    },

    show(el) {
        if (!el || this.openStack.includes(el)) return;

        this.lastFocused = doc.activeElement;
        this.openStack.push(el);

        const backdrop = this.backdrop();
        backdrop.style.display = 'block';
        el.style.display = 'block';
        el.removeAttribute('aria-hidden');
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-modal', 'true');

        // Force a reflow so the transition from the closed state actually runs.
        void el.offsetWidth;

        backdrop.classList.add('is-open');
        el.classList.add('is-open');
        doc.body.style.overflow = 'hidden';

        const focusable = $1(
            'input:not([type=hidden]):not([disabled]), select, textarea, button, [href]',
            el,
        );
        if (focusable) focusable.focus();
    },

    hide(el) {
        if (!el) return;

        const index = this.openStack.indexOf(el);
        if (index !== -1) this.openStack.splice(index, 1);

        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');

        const backdrop = this.backdrop();
        if (!this.openStack.length) {
            backdrop.classList.remove('is-open');
            doc.body.style.overflow = '';
        }

        window.setTimeout(() => {
            if (!el.classList.contains('is-open')) el.style.display = 'none';
            if (!this.openStack.length && !backdrop.classList.contains('is-open')) {
                backdrop.style.display = 'none';
            }
        }, 220);

        if (this.lastFocused && !this.openStack.length) {
            try { this.lastFocused.focus(); } catch { /* element may be gone */ }
        }
    },

    hideTop() {
        const top = this.openStack[this.openStack.length - 1];
        if (top) this.hide(top);
    },
};

/**
 * Tabs.
 *
 * Keeps Bootstrap's declarative contract: a link with data-toggle="tab" and an
 * href pointing at a pane id, `.active` on the owning <li> and on the pane.
 * $(link).tab('show') is also supported.
 */
function showTab(link) {
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href || !href.startsWith('#') || href === '#') return;

    let pane;
    try { pane = doc.querySelector(href); } catch { return; }
    if (!pane) return;

    // Deactivate siblings within this strip only, so nested tabs are safe.
    const item = link.closest('li');
    const strip = link.closest('ul');
    if (strip && item) {
        Array.from(strip.children).forEach((li) => li.classList.remove('active'));
        item.classList.add('active');
        Array.from(strip.children).forEach((li) => {
            const a = $1('a', li);
            if (a) a.setAttribute('aria-selected', li === item ? 'true' : 'false');
        });
    }

    const group = pane.parentNode;
    if (group) {
        Array.from(group.children).forEach((child) => {
            if (child.classList.contains('tab-pane')) child.classList.remove('active');
        });
    }
    pane.classList.add('active');
}

function installJqueryShims() {
    const jq = window.jQuery;
    if (!jq || !jq.fn) return;

    jq.fn.modal = function (action) {
        return this.each(function () {
            if (action === 'hide') Modal.hide(this);
            else if (action === 'toggle') {
                this.classList.contains('is-open') ? Modal.hide(this) : Modal.show(this);
            } else {
                // 'show', an options object, or nothing at all.
                Modal.show(this);
            }
        });
    };

    jq.fn.tab = function (action) {
        return this.each(function () {
            if (action === 'show' || action === undefined) showTab(this);
        });
    };
}

function initDelegatedHandlers() {
    // Tabs
    on(doc, 'click', (e) => {
        const link = e.target.closest('[data-toggle="tab"]');
        if (!link) return;
        e.preventDefault();
        showTab(link);
    });

    // Modal dismissal
    on(doc, 'click', (e) => {
        const dismiss = e.target.closest('[data-dismiss="modal"]');
        if (dismiss) {
            e.preventDefault();
            Modal.hide(dismiss.closest('.modal'));
            return;
        }

        // Clicking the padding around .modal-dialog closes the modal.
        const modal = e.target.closest('.modal');
        if (modal && !e.target.closest('.modal-dialog')) Modal.hide(modal);
    });

    on(doc, 'keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (Modal.openStack.length) {
            Modal.hideTop();
            return;
        }
        closeAllMenus();
        closeDrawer();
    });

    // Keep focus inside an open modal.
    on(doc, 'keydown', (e) => {
        if (e.key !== 'Tab' || !Modal.openStack.length) return;

        const modal = Modal.openStack[Modal.openStack.length - 1];
        const items = $$(
            'a[href], button:not([disabled]), input:not([type=hidden]):not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])',
            modal,
        ).filter((el) => el.offsetParent !== null);
        if (!items.length) return;

        const first = items[0];
        const last = items[items.length - 1];

        if (e.shiftKey && doc.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && doc.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });
}

/**
 * Several views render a tab strip where no <li> and no pane carries `active`,
 * so the card sat empty until the user guessed to click. Open the first tab of
 * any strip that has nothing selected. This never fires the pages' own click
 * handlers (#total_misc, #total_pay and friends) because it does not dispatch
 * a click event.
 */
function initDefaultTab() {
    $$('.ui-tabs').forEach((strip) => {
        if ($1('li.active', strip)) return;

        const link = $1('li > a[data-toggle="tab"][href^="#"]', strip);
        if (!link) return;

        let pane;
        try { pane = doc.querySelector(link.getAttribute('href')); } catch { return; }
        if (!pane || !pane.parentNode) return;
        if ($1('.tab-pane.active', pane.parentNode)) return;

        showTab(link);
    });
}

/* ========================================================================== */
/* Shell                                                                      */
/* ========================================================================== */

/* --------------------------------------------------------------- theme */

const THEME_KEY = 'bascon:theme';

/* Light is the default: this is a daytime back-office, and every legacy view
   was written against a light sheet. Dark is opt-in and remembered. The class
   itself is set by the inline snippet in partials/head, which runs before the
   first paint — this module only handles the switching. */
function applyTheme(theme) {
    const dark = theme === 'dark';

    doc.documentElement.classList.toggle('dark', dark);

    const meta = $1('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', dark ? '#0a0a0d' : '#f4f4f5');

    $$('[data-theme-toggle]').forEach((btn) => {
        btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
        btn.setAttribute('title', dark ? 'Switch to light theme' : 'Switch to dark theme');
    });
}

function setTheme(theme) {
    store.set(THEME_KEY, theme);
    applyTheme(theme);
}

function currentTheme() {
    return doc.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

function initTheme() {
    // Pages that opt out (the sign-in screens) keep the light palette no
    // matter what is stored. Returning before the storage listener is bound
    // matters as much as the rest: without it, toggling the theme in another
    // tab would repaint a locked page behind the user's back.
    if (doc.documentElement.hasAttribute('data-theme-lock')) return;

    applyTheme(currentTheme());

    $$('[data-theme-toggle]').forEach((btn) =>
        on(btn, 'click', (e) => {
            e.preventDefault();
            setTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        }));

    // Keep tabs of the same app in step with one another.
    on(window, 'storage', (e) => {
        if (e.key === THEME_KEY && e.newValue) applyTheme(e.newValue);
    });
}

/* ------------------------------------------------------------- sidebar */

const SIDEBAR_KEY = 'bascon:sidebar-collapsed';

function setCollapsed(collapsed) {
    doc.body.classList.toggle('sidebar-collapsed', collapsed);
    store.set(SIDEBAR_KEY, collapsed ? '1' : '0');
    $$('[data-sidebar-toggle]').forEach((btn) =>
        btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true'));
}

function openDrawer() {
    doc.body.classList.add('sidebar-open');
    $$('[data-sidebar-drawer]').forEach((b) => b.setAttribute('aria-expanded', 'true'));
    const first = $1('#app-sidebar a, #app-sidebar button');
    if (first) first.focus();
}

function closeDrawer() {
    if (!doc.body.classList.contains('sidebar-open')) return;
    doc.body.classList.remove('sidebar-open');
    $$('[data-sidebar-drawer]').forEach((b) => {
        b.setAttribute('aria-expanded', 'false');
        b.focus();
    });
}

function initSidebar() {
    if (store.get(SIDEBAR_KEY) === '1') doc.body.classList.add('sidebar-collapsed');

    $$('[data-sidebar-toggle]').forEach((btn) =>
        on(btn, 'click', (e) => {
            e.preventDefault();
            setCollapsed(!doc.body.classList.contains('sidebar-collapsed'));
        }));

    $$('[data-sidebar-drawer]').forEach((btn) =>
        on(btn, 'click', (e) => {
            e.preventDefault();
            doc.body.classList.contains('sidebar-open') ? closeDrawer() : openDrawer();
        }));

    on($1('[data-sidebar-backdrop]'), 'click', closeDrawer);

    $$('#app-sidebar a[href]').forEach((link) =>
        on(link, 'click', () => { if (isMobile()) closeDrawer(); }));

    // Nested menu accordion.
    $$('[data-nav-toggle]').forEach((trigger) =>
        on(trigger, 'click', (e) => {
            e.preventDefault();

            const group = trigger.closest('.ui-nav-group');
            if (!group) return;

            // There is nowhere to expand into on the collapsed desktop rail.
            if (doc.body.classList.contains('sidebar-collapsed') && !isMobile()) {
                setCollapsed(false);
            }

            const open = !group.classList.contains('is-open');
            group.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }));

    const mq = window.matchMedia(MOBILE);
    const reset = () => doc.body.classList.remove('sidebar-open');
    mq.addEventListener ? mq.addEventListener('change', reset) : mq.addListener(reset);
}

/* ---------------------------------------------------------------- money */

/**
 * Formats an amount as Pakistani rupees: money(644000) -> "PKR 644,000".
 *
 * Every figure in this application is PKR, but the running totals were built
 * by string-concatenating raw numbers into a heading, so they read as bare
 * quantities and are hard to scan at six or seven digits.
 *
 * The server-side twin is the @money Blade directive in AppServiceProvider —
 * the same total is rendered by whichever of the two owns a given page, so the
 * two formats have to match. Values arrive from AJAX as numbers or numeric
 * strings and occasionally as null; anything non-numeric formats as zero,
 * because these totals sit in views with no error path.
 */
function money(value) {
    const n = typeof value === 'number' ? value : parseFloat(value);
    const safe = Number.isFinite(n) ? n : 0;

    // Whole rupees unless the figure genuinely carries paisa.
    const decimals = safe % 1 === 0 ? 0 : 2;

    return 'PKR ' + safe.toLocaleString('en-PK', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

/* ------------------------------------------------------------- row links */

/**
 * Makes a whole listing row open a record.
 *
 * A row carrying data-row-href navigates when clicked. Three things are
 * deliberately left alone:
 *
 *   - anything interactive inside the row (the edit and delete buttons, the
 *     cell that is already a link, a checkbox) keeps its own behaviour;
 *   - a click that ends a text selection, so a row can still be read and
 *     copied from without being yanked away;
 *   - modifier and middle clicks, which open a new tab the way a real link
 *     would.
 *
 * The handler is delegated because DataTables destroys and rebuilds its rows
 * on every sort, search and page change — anything bound per-row would have to
 * be rebound on each draw.
 *
 * This is a mouse affordance, not the accessible path to the record: rows are
 * not focusable and are not announced as links. Keyboard and screen-reader
 * users follow the real anchor that each of these tables already renders in
 * its identifying column, which is why that anchor is left in place.
 */
const ROW_LINK_INTERACTIVE = 'a, button, input, select, textarea, label, [onclick], [contenteditable]';

function rowLinkTarget(e) {
    const el = e.target;
    if (!el || typeof el.closest !== 'function') return null;

    const row = el.closest('[data-row-href]');
    if (!row || el.closest(ROW_LINK_INTERACTIVE)) return null;

    return row.getAttribute('data-row-href') || null;
}

function initRowLinks() {
    on(doc, 'click', (e) => {
        const href = rowLinkTarget(e);
        if (!href) return;

        const selection = window.getSelection && window.getSelection();
        if (selection && !selection.isCollapsed) return;

        if (e.metaKey || e.ctrlKey || e.shiftKey) window.open(href, '_blank', 'noopener');
        else window.location.assign(href);
    });

    on(doc, 'auxclick', (e) => {
        if (e.button !== 1) return;
        const href = rowLinkTarget(e);
        if (href) window.open(href, '_blank', 'noopener');
    });
}

/* ----------------------------------------------------------------- menus */

function closeAllMenus(except) {
    $$('.ui-menu.is-open').forEach((menu) => {
        if (menu === except) return;
        menu.classList.remove('is-open');
        const trigger = $1(`[aria-controls="${menu.id}"]`);
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    });
}

function initMenus() {
    $$('[data-menu-trigger]').forEach((trigger) => {
        const menu = doc.getElementById(trigger.getAttribute('aria-controls'));
        if (!menu) return;

        on(trigger, 'click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const open = !menu.classList.contains('is-open');
            closeAllMenus(menu);
            menu.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                const first = $1('a, button', menu);
                if (first) first.focus();
            }
        });

        on(menu, 'click', (e) => e.stopPropagation());
    });

    on(doc, 'click', () => closeAllMenus());
}

/* ---------------------------------------------------------------- search */

/** Filters the navigation the signed-in user can already see. No backend. */
function initSearch() {
    const wrap = $1('[data-search]');
    if (!wrap) return;

    const input = $1('input', wrap);
    const clear = $1('[data-search-clear]', wrap);
    const results = $1('[data-search-results]', wrap);
    if (!input || !results) return;

    const index = $$('#app-sidebar a[href]')
        .filter((a) => {
            const href = a.getAttribute('href');
            return href && href !== '#' && a.textContent.trim();
        })
        .map((a) => {
            const group = a.closest('.ui-nav-group');
            const label = group ? $1('[data-nav-label]', group) : null;
            return {
                href: a.getAttribute('href'),
                label: a.textContent.trim(),
                group: label ? label.textContent.trim() : '',
            };
        });

    const render = (items, query) => {
        results.innerHTML = '';

        if (!items.length) {
            const empty = doc.createElement('p');
            empty.className = 'px-3 py-6 text-center text-xs text-neutral-400';
            empty.textContent = `No pages match “${query}”`;
            results.appendChild(empty);
            return;
        }

        items.slice(0, 8).forEach((item, i) => {
            const a = doc.createElement('a');
            a.href = item.href;
            a.className = 'ui-menu-item' + (i === 0 ? ' is-highlighted bg-neutral-100' : '');
            a.textContent = item.group && item.group !== item.label
                ? `${item.group} · ${item.label}`
                : item.label;
            results.appendChild(a);
        });
    };

    const update = () => {
        const q = input.value.trim().toLowerCase();
        if (clear) clear.classList.toggle('hidden', !q);

        if (!q) {
            results.classList.remove('is-open');
            input.setAttribute('aria-expanded', 'false');
            return;
        }

        render(
            index.filter((i) => `${i.label} ${i.group}`.toLowerCase().includes(q)),
            input.value.trim(),
        );
        results.classList.add('is-open');
        input.setAttribute('aria-expanded', 'true');
    };

    on(input, 'input', update);
    on(input, 'focus', update);

    on(clear, 'click', (e) => {
        e.preventDefault();
        input.value = '';
        update();
        input.focus();
    });

    on(input, 'keydown', (e) => {
        const links = $$('a', results);
        if (!links.length) {
            if (e.key === 'Escape') { input.value = ''; update(); input.blur(); }
            return;
        }

        const current = $1('a.is-highlighted', results);
        let idx = links.indexOf(current);

        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (current) current.classList.remove('is-highlighted', 'bg-neutral-100');
            idx = e.key === 'ArrowDown'
                ? (idx + 1) % links.length
                : (idx - 1 + links.length) % links.length;
            links[idx].classList.add('is-highlighted', 'bg-neutral-100');
        } else if (e.key === 'Enter' && current) {
            e.preventDefault();
            current.click();
        } else if (e.key === 'Escape') {
            input.value = '';
            update();
            input.blur();
        }
    });

    on(doc, 'click', (e) => {
        if (!wrap.contains(e.target)) results.classList.remove('is-open');
    });

    // "/" focuses search, the way Linear and GitHub do it.
    on(doc, 'keydown', (e) => {
        if (e.key !== '/' || e.metaKey || e.ctrlKey || e.altKey) return;
        const tag = (e.target.tagName || '').toLowerCase();
        if (['input', 'textarea', 'select'].includes(tag) || e.target.isContentEditable) return;
        e.preventDefault();
        input.focus();
    });
}

/* ------------------------------------------------------------ form extras */

function initPasswordToggles() {
    $$('[data-toggle-password]').forEach((btn) => {
        const target = doc.querySelector(btn.getAttribute('data-toggle-password'));
        if (!target) return;

        const show = $1('[data-icon-show]', btn);
        const hide = $1('[data-icon-hide]', btn);

        on(btn, 'click', (e) => {
            e.preventDefault();
            const visible = target.getAttribute('type') === 'text';
            target.setAttribute('type', visible ? 'password' : 'text');
            btn.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            btn.setAttribute('aria-pressed', visible ? 'false' : 'true');
            if (show) show.classList.toggle('hidden', !visible);
            if (hide) hide.classList.toggle('hidden', visible);
        });
    });
}

/**
 * Opt-in per form: <form data-loading-on-submit>. Purely visual — it never
 * blocks the submit or the view's own AJAX handler, and it releases itself so
 * AJAX forms that stay on the page do not end up with a stuck spinner.
 */
function initSubmitLoading() {
    $$('form[data-loading-on-submit]').forEach((form) =>
        on(form, 'submit', () => {
            const btn = $1('button[type="submit"], input[type="submit"]', form);
            if (!btn || btn.classList.contains('is-loading')) return;

            btn.classList.add('is-loading');
            btn.setAttribute('aria-busy', 'true');

            window.setTimeout(() => {
                btn.classList.remove('is-loading');
                btn.removeAttribute('aria-busy');
            }, 6000);
        }));
}

function initDropzones() {
    $$('.ui-dropzone').forEach((zone) => {
        const input = $1('input[type="file"]', zone);
        const name = $1('[data-file-name]', zone);
        if (!input) return;

        ['dragenter', 'dragover'].forEach((evt) =>
            on(zone, evt, (e) => { e.preventDefault(); zone.classList.add('is-dragging'); }));
        ['dragleave', 'drop'].forEach((evt) =>
            on(zone, evt, (e) => { e.preventDefault(); zone.classList.remove('is-dragging'); }));

        on(input, 'change', () => {
            if (!name) return;
            const files = input.files;
            name.textContent = files && files.length
                ? (files.length === 1 ? files[0].name : `${files.length} files selected`)
                : '';
        });
    });
}

/* ---------------------------------------------------------------- toasts */

const TOAST_ICONS = {
    success: '<path d="M20 6 9 17l-5-5"/>',
    error: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    warning: '<path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
};

function toast(title, { text = '', type = 'success', duration = 4000 } = {}) {
    let host = $1('.ui-toast-host');
    if (!host) {
        host = doc.createElement('div');
        host.className = 'ui-toast-host';
        host.setAttribute('role', 'status');
        host.setAttribute('aria-live', 'polite');
        doc.body.appendChild(host);
    }

    const el = doc.createElement('div');
    el.className = `ui-toast ui-toast-${type}`;
    el.innerHTML = `
        <span class="ui-toast-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                 stroke-linecap="round" stroke-linejoin="round">${TOAST_ICONS[type] || TOAST_ICONS.info}</svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="ui-toast-title"></p>
            ${text ? '<p class="ui-toast-text"></p>' : ''}
        </div>`;

    $1('.ui-toast-title', el).textContent = title;
    if (text) $1('.ui-toast-text', el).textContent = text;

    host.appendChild(el);
    void el.offsetWidth;
    el.classList.add('is-open');

    window.setTimeout(() => {
        el.classList.remove('is-open');
        window.setTimeout(() => el.remove(), 260);
    }, duration);

    return el;
}

/* -------------------------------------------------------- route progress */

function initRouteProgress() {
    const bar = doc.createElement('div');
    bar.className = 'ui-progress';
    bar.setAttribute('aria-hidden', 'true');
    doc.body.appendChild(bar);

    let timer = null;

    const start = () => {
        bar.classList.add('is-active');
        let width = 18;
        bar.style.width = '18%';
        window.clearInterval(timer);
        timer = window.setInterval(() => {
            width = Math.min(width + (90 - width) * 0.12, 90);
            bar.style.width = `${width}%`;
        }, 220);
    };

    // Full page navigations only. The views' own AJAX is untouched.
    on(doc, 'click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || link.target === '_blank') return;
        if (/^(mailto:|tel:|javascript:)/i.test(href)) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
        if (link.hasAttribute('download') || link.hasAttribute('data-no-progress')) return;

        start();
    });

    on(window, 'pageshow', () => {
        window.clearInterval(timer);
        bar.classList.remove('is-active');
        bar.style.width = '0';
    });
}

/* ------------------------------------------------------ DataTables defaults */

/** Defaults only — every per-table config in the views still wins. */
function initDataTableDefaults() {
    const jq = window.jQuery;
    if (!jq?.fn?.dataTable) return;

    jq.extend(true, jq.fn.dataTable.defaults, {
        // DataTables' default layout ('lfrtip') drops its controls straight
        // into the page as bare floats. Naming two containers lets the length
        // menu, search and export buttons sit in a proper toolbar above the
        // table, and the count and pager in a footer below it — the strip the
        // stylesheet then paints. The dozen views that configure exports pass
        // the same two containers in their own `dom` string.
        dom: '<"ui-dt-bar"lf>rt<"ui-dt-foot"ip>',

        language: {
            search: '',
            searchPlaceholder: 'Search…',
            lengthMenu: 'Show _MENU_',
            info: 'Showing _START_–_END_ of _TOTAL_',
            infoEmpty: 'No entries to show',
            infoFiltered: '(filtered from _MAX_)',
            zeroRecords: 'No matching records found',
            emptyTable: 'No data available yet',
            processing: 'Loading…',
            paginate: { first: '«', previous: 'Previous', next: 'Next', last: '»' },
        },
    });
}

/* ------------------------------------------------------ DataTables money */

/**
 * Column data keys that hold an amount of money.
 *
 * The listings are built from the legacy schema's own field names, so this is
 * a list of those names rather than anything cleverer. Two of them are the
 * same idea spelled differently — `instalmet` is a typo in the site-settings
 * payloads, `instalment` is the client side — and both are live, so both are
 * here. Add a key when a new amount column appears.
 *
 * Deliberately absent: `quantity` (a count of units), `source`, `detail`,
 * `description`, `date`.
 */
const MONEY_COLUMNS = new Set([
    'price',
    'payment',
    'total_price',
    'architect_fees',
    'ammount',       // admin/show_expense — the column is spelled this way in the payload
    'amount',
    'instalment',
    'instalmet',
]);

/**
 * Renders every money column in every table as currency.
 *
 * One default rather than a `render` on each of the thirty-odd column
 * definitions across the views: the same handful of field names recur in all
 * of them, so a list of names is both shorter and much harder to forget to
 * update than thirty copies of the same callback.
 *
 * It has to be a columnDefs default specifically. The obvious seam — wrapping
 * each column's getter on the preInit event — does not work here: with
 * deferRender off (the default, and what every table in this app uses),
 * DataTables builds the row elements inside _fnAddData as the data is added,
 * which for a table given a `data` array happens during construction, before
 * preInit fires. The cells are already painted by then. columnDefs are applied
 * during column setup, which is early enough.
 *
 * Only the *display* value is formatted. Sorting, type detection and filtering
 * still see the raw number, so amount columns keep sorting numerically rather
 * than alphabetically by "PKR", and a search for 644000 still matches.
 *
 * Two things are left alone: columns whose view supplies its own `render`
 * (DataTables applies columnDefs first and the `columns` array second, so the
 * view's callback simply replaces this one), and empty values, which stay
 * empty rather than becoming a "PKR 0" that was never in the data.
 */
function initDataTableMoney() {
    const jq = window.jQuery;
    if (!jq?.fn?.dataTable) return;

    jq.extend(true, jq.fn.dataTable.defaults, {
        columnDefs: [{
            targets: '_all',
            render(data, type, row, meta) {
                if (type !== 'display') return data;
                if (data === null || data === undefined || data === '') return data;

                const key = meta.settings.aoColumns[meta.col].mData;

                return typeof key === 'string' && MONEY_COLUMNS.has(key) ? money(data) : data;
            },
        }],
    });
}

/* ========================================================================== */

function init() {
    installJqueryShims();
    initDelegatedHandlers();
    initDefaultTab();
    initTheme();
    initSidebar();
    initRowLinks();
    initMenus();
    initSearch();
    initPasswordToggles();
    initSubmitLoading();
    initDropzones();
    initRouteProgress();
    initDataTableDefaults();
    initDataTableMoney();
}

// The vendored libraries (jQuery, jQuery UI, DataTables, SweetAlert2) are
// classic scripts, so they have already executed by the time this module runs.
// Views bind their own code inside $(document).ready, which fires after this.
if (doc.readyState === 'loading') on(doc, 'DOMContentLoaded', init);
else init();

// Small public surface for views that want the shared helpers.
// The views' own scripts are classic (non-module) code, so the money formatter
// has to be reachable by global name for them to call it.
window.money = money;

window.BasconUI = {
    money,
    toast,
    modal: Modal,
    showTab,
    closeDrawer,
    setSidebarCollapsed: setCollapsed,
    setTheme,
    getTheme: currentTheme,
};
