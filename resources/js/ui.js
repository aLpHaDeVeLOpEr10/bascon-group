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
        // flex, not block: .modal.is-open centres its dialog with flexbox, and
        // an inline display would win over the stylesheet and left the popup
        // pinned to the top of the viewport.
        el.style.display = 'flex';
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
        if (index === -1 && !el.classList.contains('is-open')) return;
        if (index !== -1) this.openStack.splice(index, 1);

        // Fired before the closing transition so a listener can settle its
        // state immediately. Dialog uses it to resolve its promise.
        el.dispatchEvent(new CustomEvent('ui:modal-hide', { bubbles: false }));

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
/**
 * The sliding underline on a tab strip.
 *
 * The stylesheet's fallback is a per-link ::after that scales up in place, so
 * the strip still marks its active tab with no JavaScript at all. Where this
 * runs it takes over: one bar, moved and resized to the active tab, which is
 * the only way the mark can travel BETWEEN tabs rather than fading out in one
 * place and in at another.
 *
 * Both strip shapes use it. On a top-level strip the bar is the 2px underline;
 * on a nested segmented group the stylesheet restyles the very same element
 * into the filled pill that sits behind the active tab. One mechanism, two
 * skins — a second implementation would be two things to keep in step.
 */
function moveTabIndicator(strip) {
    if (!strip) return;

    let bar = $1(':scope > .ui-tabs-indicator', strip);

    if (!bar) {
        bar = doc.createElement('span');
        bar.className = 'ui-tabs-indicator';
        bar.setAttribute('aria-hidden', 'true');
        strip.appendChild(bar);
        // Retires the per-link ::after, so the two cannot both be drawn.
        strip.classList.add('has-indicator');
    }

    const item = $1(':scope > li.active', strip);
    if (!item) {
        bar.style.width = '0';
        return;
    }

    const link = $1('a', item) || item;

    /* Measured from the rectangles rather than offsetLeft.
     *
     * offsetLeft reports against the nearest POSITIONED ancestor, and the
     * items carry position:relative so the segmented pill can sit behind
     * them — which made every link report 0 and parked the bar under the
     * first tab. Rect deltas do not care what is positioned.
     *
     * scrollLeft converts the on-screen delta back into the strip's content
     * coordinates, which is what an absolutely positioned child inside a
     * horizontally scrolling strip is laid out in. */
    const stripBox = strip.getBoundingClientRect();
    const linkBox = link.getBoundingClientRect();

    if (!linkBox.width) return;   // strip is hidden; measured again when shown

    bar.style.width = linkBox.width + 'px';
    bar.style.transform = 'translateX(' + (linkBox.left - stripBox.left + strip.scrollLeft) + 'px)';
}

function initTabIndicators() {
    $$('.ui-tabs').forEach(moveTabIndicator);
}

/**
 * Keeps a pinned totals pill off its tab strip.
 *
 * The pill is parked on the strip's line, which only works while there is
 * room to the right of the tabs. A media query cannot tell: whether it fits
 * depends on how many tabs the strip has and how long the figure is — Sub
 * Total carries seven tabs, so it collides at a width where a four-tab strip
 * is still fine.
 *
 * So it is measured. Too tight, and the host is marked is-stacked and the
 * stylesheet drops the pill back into flow above the table.
 *
 * The stacked pill is sized to its content (w-fit), so the measurement reads
 * the same in both states and cannot oscillate.
 */
function fitTabTotals() {
    $$('.ui-tab-total-host').forEach((host) => {
        /* The FIRST .ui-total in the host belongs to whichever pane comes
         * first in the markup, which is usually a hidden one — reading that
         * and bailing on its missing offsetParent meant this never measured
         * anything. Take the one actually on screen. */
        const total = $$('.tab-content .ui-total', host)
            .find((el) => el.offsetParent !== null);

        if (!total) return;

        // The innermost visible strip is the one the pill shares a line with.
        const strips = $$('.ui-tabs', host).filter((el) => el.offsetParent !== null);
        const strip = strips[strips.length - 1];
        if (!strip) return;

        const room = host.clientWidth
            - strip.getBoundingClientRect().width
            - total.getBoundingClientRect().width
            - 48;   // the host's own padding, plus a gap so they never touch

        host.classList.toggle('is-stacked', room < 0);
    });
}

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

    if (strip) moveTabIndicator(strip);

    // A strip inside the pane just revealed measured zero while it was
    // display:none, so its own bar has to be placed now that it has a size.
    $$('.ui-tabs', pane).forEach(moveTabIndicator);

    // Switching tabs changes both the strip on show and the figure beside it.
    fitTabTotals();
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

        // Clicking the padding around .modal-dialog closes the modal, unless
        // it was opened as static (Swal's allowOutsideClick: false).
        const modal = e.target.closest('.modal');
        if (!modal || e.target.closest('.modal-dialog')) return;
        if (modal.dataset.backdrop === 'static') return;
        Modal.hide(modal);
    });

    on(doc, 'keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (Modal.openStack.length) {
            const top = Modal.openStack[Modal.openStack.length - 1];
            if (top.dataset.backdrop !== 'static') Modal.hideTop();
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
 * Refetch buttons.
 *
 * `data-reload` names a control whose change handler already knows how to load
 * the thing being shown, so replaying that handler is the whole implementation.
 * There is deliberately no second fetch path here: a copy would drift from the
 * original the first time either one changed.
 *
 * The glyph spins until the page falls idle again, which is the only feedback
 * available — a refetch that returns the same rows looks identical to one that
 * never happened.
 */
function initReloadButtons() {
    on(doc, 'click', (e) => {
        const btn = e.target.closest('[data-reload]');
        if (!btn || btn.classList.contains('is-busy')) return;

        const target = $1(btn.dataset.reload);
        if (!target) return;

        // The first option of these lists is a "Select ..." placeholder. Asking
        // the server for it returns nothing and would blank the table, so with
        // nothing chosen there is nothing to reload.
        if (target.tagName === 'SELECT' && target.selectedIndex <= 0) return;

        btn.classList.add('is-busy');
        target.dispatchEvent(new Event('change', { bubbles: true }));

        const jq = window.jQuery;
        if (!jq) {
            window.setTimeout(() => btn.classList.remove('is-busy'), 400);
            return;
        }

        const done = () => {
            jq(doc).off('ajaxStop', done);
            btn.classList.remove('is-busy');
        };

        // Every one of these handlers loads over jQuery AJAX, so the library's
        // own idle event is the honest end of the request. The checks behind it
        // cover a handler that issued none and one whose request never returns.
        jq(doc).on('ajaxStop', done);
        window.setTimeout(() => { if (!jq.active) done(); }, 60);
        window.setTimeout(done, 15000);
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

/**
 * The toggle's glyph is swapped by CSS off .sidebar-collapsed; only the text
 * that describes it has to be set here.
 */
function syncRailToggles(collapsed) {
    const label = collapsed ? 'Expand sidebar' : 'Collapse sidebar';

    $$('[data-sidebar-toggle]').forEach((btn) => {
        btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        btn.setAttribute('aria-label', label);
        btn.setAttribute('title', label);
    });
}

function setCollapsed(collapsed) {
    // On <html>, not <body>: partials/head restores this before first paint,
    // when <body> has not been parsed yet. Both have to agree on the host.
    doc.documentElement.classList.toggle('sidebar-collapsed', collapsed);
    store.set(SIDEBAR_KEY, collapsed ? '1' : '0');
    syncRailToggles(collapsed);
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
    // partials/head has already done this pre-paint. Repeated here only so
    // ui.js still behaves on a page that does not include that partial. Add
    // only, never remove: a page can also start collapsed by rendering the
    // class itself (@section('collapse-sidebar')), and that must not be undone
    // by a stored preference of expanded.
    if (store.get(SIDEBAR_KEY) === '1') doc.documentElement.classList.add('sidebar-collapsed');

    // The toggle's labels are markup, so they do not know how the page actually
    // started — a page can open collapsed on its own. Sync before anyone reads
    // them. The glyph needs no help; CSS already has it right.
    syncRailToggles(doc.documentElement.classList.contains('sidebar-collapsed'));

    $$('[data-sidebar-toggle]').forEach((btn) =>
        on(btn, 'click', (e) => {
            e.preventDefault();
            setCollapsed(!doc.documentElement.classList.contains('sidebar-collapsed'));
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
            if (doc.documentElement.classList.contains('sidebar-collapsed') && !isMobile()) {
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

        on(zone, 'dragleave', () => zone.classList.remove('is-dragging'));

        /*
         * The drop has to be handled, not just styled.
         *
         * preventDefault() on a bubbling drop cancels the default action for
         * the whole event — and the default action here is the file input
         * accepting the file. Styling the zone on drop therefore stopped the
         * drop from ever landing, and only click-to-browse worked.
         *
         * It cannot simply be dropped either: without preventDefault the
         * browser navigates away to the file. So the files are moved across
         * explicitly, and `change` is dispatched so everything listening for a
         * chosen file — the filename label here, the preview on the profile
         * page — reacts exactly as it would to a click.
         */
        on(zone, 'drop', (e) => {
            e.preventDefault();
            zone.classList.remove('is-dragging');

            const dropped = e.dataTransfer && e.dataTransfer.files;
            if (!dropped || !dropped.length) return;

            // Honour the input's own limits rather than second-guessing them.
            if (!input.multiple && dropped.length > 1) {
                const one = new DataTransfer();
                one.items.add(dropped[0]);
                input.files = one.files;
            } else {
                input.files = dropped;
            }

            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

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

/* --------------------------------------------------------------- dialogs */

/**
 * Dialogs — the local replacement for SweetAlert2.
 *
 * SweetAlert2 was pulled from a CDN as a render-blocking classic script on
 * every page, for 177 Swal.fire() calls spread across fourteen views. The
 * round trip was the slowest thing about opening an edit modal: the popup
 * could not paint until a third-party host answered.
 *
 * Rewriting those 177 call sites was not worth it, so `Swal` below is a
 * drop-in for the part of the v10 API this app actually uses:
 *
 *     Swal.fire(options)            177 x  (30 of them positional)
 *     Swal.close()                   30 x
 *     Swal.showLoading()             15 x
 *
 * Three call sites in admin/add_civil use the older lowercase `swal({...})`
 * with `button:` for the confirm label; both are aliased below.
 *
 * Options honoured: title, text, icon, confirmButtonText, cancelButtonText,
 * showCancelButton, allowOutsideClick, width, onBeforeOpen / willOpen /
 * didOpen. The promise resolves to an object carrying `isConfirmed`, which is
 * the only field any call site reads.
 *
 * Deliberately ignored: confirmButtonColor and cancelButtonColor. They were
 * raw hex passed to a library that had no design system; here the confirm
 * button takes its emphasis from the icon instead, so a destructive confirm
 * reads as destructive. The legacy values had it backwards anyway — every
 * delete prompt in this app asked for confirmation on a blue button and
 * offered a red Cancel.
 *
 * Popups are built on the same .modal markup as the views' own modals, so
 * they share the backdrop, the open stack, the focus trap and Esc handling.
 */

const DIALOG_ICONS = {
    success: '<path d="M20 6 9 17l-5-5"/>',
    error: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    warning: '<path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    question: '<circle cx="12" cy="12" r="10"/><path d="M9.1 9.3a3 3 0 0 1 5.8 1c0 2-2.9 3-2.9 3"/><path d="M12 17h.01"/>',
};

/** Icons whose confirm button should read as destructive. */
const DIALOG_DANGER = new Set(['error', 'warning']);

const DISMISSED = { isConfirmed: false, isDenied: false, isDismissed: true };

const Dialog = {
    /** { el, settle } for the popup on screen, or null. */
    open: null,

    fire(...params) {
        // Swal.fire('Deleted!', 'The site is gone.', 'success')
        const options = typeof params[0] === 'object' && params[0] !== null
            ? params[0]
            : { title: params[0], text: params[1], icon: params[2] };

        // One popup at a time, as SweetAlert did.
        this.close();

        const {
            title = '',
            text = '',
            icon = null,
            showCancelButton = false,
            confirmButtonText = options.button || 'OK',
            cancelButtonText = 'Cancel',
            allowOutsideClick = true,
            width = null,
        } = options;

        const el = doc.createElement('div');
        el.className = 'modal ui-dialog';
        el.setAttribute('role', 'alertdialog');
        el.setAttribute('aria-modal', 'true');
        if (!allowOutsideClick) el.dataset.backdrop = 'static';

        const iconKey = icon && DIALOG_ICONS[icon] ? icon : null;
        const danger = DIALOG_DANGER.has(iconKey);

        el.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="ui-dialog-body">
                        ${iconKey ? `
                            <span class="ui-dialog-icon is-${iconKey}" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                     stroke-linecap="round" stroke-linejoin="round">${DIALOG_ICONS[iconKey]}</svg>
                            </span>` : ''}
                        <h2 class="ui-dialog-title"></h2>
                        <p class="ui-dialog-text"></p>
                        <div class="ui-dialog-spinner" hidden></div>
                        <div class="ui-dialog-actions">
                            ${showCancelButton
                                ? '<button type="button" class="ui-btn ui-btn-secondary" data-dialog="cancel"></button>'
                                : ''}
                            <button type="button" class="ui-btn ${danger ? 'ui-btn-danger' : 'ui-btn-primary'}"
                                    data-dialog="confirm"></button>
                        </div>
                    </div>
                </div>
            </div>`;

        // textContent throughout — titles and messages are the only places a
        // server string reaches these popups.
        const titleEl = $1('.ui-dialog-title', el);
        const textEl = $1('.ui-dialog-text', el);
        titleEl.textContent = title || '';
        titleEl.hidden = !title;
        textEl.textContent = text || '';
        textEl.hidden = !text;

        $1('[data-dialog="confirm"]', el).textContent = confirmButtonText;
        const cancelEl = $1('[data-dialog="cancel"]', el);
        if (cancelEl) cancelEl.textContent = cancelButtonText;

        if (width) {
            $1('.modal-dialog', el).style.maxWidth =
                typeof width === 'number' ? `${width}px` : width;
        }

        doc.body.appendChild(el);

        let settle;
        const promise = new Promise((resolve) => { settle = resolve; });
        const entry = { el, settle };
        this.open = entry;

        const finish = (result) => {
            if (this.open === entry) this.open = null;
            entry.settle(result);
            Modal.hide(el);
            window.setTimeout(() => el.remove(), 260);
        };

        on(el, 'click', (e) => {
            const button = e.target.closest('[data-dialog]');
            if (!button) return;

            finish(button.dataset.dialog === 'confirm'
                ? { isConfirmed: true, isDenied: false, isDismissed: false, value: true }
                : { ...DISMISSED, dismiss: 'cancel' });
        });

        // Esc and backdrop clicks go through Modal, which knows nothing about
        // the promise — this is how the result still gets delivered.
        on(el, 'ui:modal-hide', () => {
            if (this.open !== entry) return;
            this.open = null;
            entry.settle({ ...DISMISSED, dismiss: 'backdrop' });
            window.setTimeout(() => el.remove(), 260);
        });

        // v10 fired this with the popup built but not yet shown; the views use
        // it to call showLoading() and kick off their request.
        const willOpen = options.onBeforeOpen || options.willOpen;
        if (typeof willOpen === 'function') willOpen(el);

        Modal.show(el);

        const didOpen = options.didOpen || options.onOpen;
        if (typeof didOpen === 'function') didOpen(el);

        return promise;
    },

    /** Swaps the buttons for a spinner on the popup that is up. */
    showLoading() {
        if (!this.open) return;
        $1('.ui-dialog-actions', this.open.el).hidden = true;
        $1('.ui-dialog-spinner', this.open.el).hidden = false;
    },

    hideLoading() {
        if (!this.open) return;
        $1('.ui-dialog-actions', this.open.el).hidden = false;
        $1('.ui-dialog-spinner', this.open.el).hidden = true;
    },

    isVisible() {
        return Boolean(this.open);
    },

    /** Dismisses the popup that is up. A no-op when there is none. */
    close() {
        if (!this.open) return;

        const { el, settle } = this.open;
        this.open = null;
        settle({ ...DISMISSED, dismiss: 'close' });
        Modal.hide(el);
        window.setTimeout(() => el.remove(), 260);
    },
};

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

/* ------------------------------------------------------ datepicker defaults */

/**
 * One set of options for every date field in the app.
 *
 * The month and year dropdowns matter most: without them the header offers
 * only a one-month step, so reaching a date a year back means twelve clicks.
 *
 * dateFormat is set here as well as at the call sites. Every one of them
 * already passes dd/mm/yy — this is so a field added later cannot silently
 * fall back to jQuery UI's mm/dd/yy default and write the wrong month into a
 * table that has been converted. See NormalizeDates for what that cost.
 */
function initDatepickerDefaults() {
    const jq = window.jQuery;
    if (!jq || !jq.datepicker) return;

    jq.datepicker.setDefaults({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        // Wide enough for a site that started years ago and for a forward-dated
        // instalment, without a dropdown of a hundred entries.
        yearRange: 'c-15:c+5',
        showOtherMonths: true,
        selectOtherMonths: true,
    });
}

/**
 * Attach a picker to every field that carries the class.
 *
 * The legacy pages each wire their own by id — #datepicker, #datepicker1 and
 * friends — so a field added without one of those exact ids silently had no
 * picker at all. Anything marked `.datepicker` now gets one wherever it lives,
 * which is what the class was already implying on the newer forms.
 *
 * Guarded so a page that still calls .datepicker() itself does not end up
 * initialising the same input twice.
 */
function initDatepickers() {
    const jq = window.jQuery;
    if (!jq || !jq.datepicker) return;

    jq('input.datepicker').each(function () {
        if (!jq.data(this, 'datepicker')) jq(this).datepicker();
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
    initReloadButtons();
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
    initDatepickerDefaults();
    initDatepickers();
    initDataTableDefaults();
    initDataTableMoney();

    // After initDefaultTab, so a strip that opened its own first tab is
    // measured too. A second pass on load covers web fonts arriving late and
    // changing every label's width.
    initTabIndicators();
    fitTabTotals();

    on(window, 'load', () => {
        initTabIndicators();
        fitTabTotals();
    });

    let indicatorResize;
    on(window, 'resize', () => {
        window.clearTimeout(indicatorResize);
        indicatorResize = window.setTimeout(() => {
            initTabIndicators();
            fitTabTotals();
        }, 120);
    });
}

// The vendored libraries (jQuery, jQuery UI, DataTables) are classic scripts,
// so they have already executed by the time this module runs. Views bind their
// own code inside $(document).ready, which fires after this — which is also
// why assigning window.Swal below is early enough for every call site.
if (doc.readyState === 'loading') on(doc, 'DOMContentLoaded', init);
else init();

// Small public surface for views that want the shared helpers.
// The views' own scripts are classic (non-module) code, so the money formatter
// has to be reachable by global name for them to call it.
window.money = money;

// Drop-in for the CDN SweetAlert2 the views were written against. Wrapped in
// arrow functions rather than assigned as the object itself so `this` inside
// the methods is Dialog no matter how a view calls them.
window.Swal = {
    fire: (...args) => Dialog.fire(...args),
    close: () => Dialog.close(),
    showLoading: () => Dialog.showLoading(),
    hideLoading: () => Dialog.hideLoading(),
    isVisible: () => Dialog.isVisible(),
};

/*
 * The lowercase alias, which SweetAlert2 shipped as BOTH a callable and an
 * object: `swal({...})` and `swal.fire({...})` are each used in these views —
 * 66 of the latter across twelve of them. Exposing only the function meant
 * every `swal.fire` threw on an undefined property and the handler died
 * silently, so saves appeared to do nothing at all.
 */
window.swal = Object.assign(
    (...args) => Dialog.fire(...args),
    window.Swal,
);

window.BasconUI = {
    money,
    toast,
    dialog: Dialog,
    modal: Modal,
    showTab,
    closeDrawer,
    setSidebarCollapsed: setCollapsed,
    setTheme,
    getTheme: currentTheme,
};
