/**
 * Better Menu — collapsible groups for the CMS left menu.
 *
 * A group auto-expands when the active section is inside it and auto-collapses when you
 * navigate to an item outside it. That state is driven entirely by the server render: the
 * template emits `bt-open` only on the active group, and the admin replaces the whole menu
 * on each PJAX navigation, so leaving a group drops its `bt-open`. The chevron toggles the
 * current view by hand (ephemeral — it resets on the next navigation). We only (re-)attach
 * the toggle handler to fresh menu nodes (MutationObserver). Dependency-free.
 */
(function () {
    function initGroups() {
        var groups = document.querySelectorAll('.cms-menu__list .cms-menu__group');
        for (var i = 0; i < groups.length; i++) {
            var li = groups[i];
            if (li.dataset.btGroupInit) {
                continue;
            }
            li.dataset.btGroupInit = '1';

            var toggle = li.querySelector(':scope > .cms-menu__group-toggle');
            if (!toggle) {
                continue;
            }

            (function (li, toggle) {
                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    li.classList.toggle('bt-open');
                });
            })(li, toggle);
        }
    }

    if (document.readyState !== 'loading') {
        initGroups();
    } else {
        document.addEventListener('DOMContentLoaded', initGroups);
    }

    try {
        new MutationObserver(function () { initGroups(); })
            .observe(document.documentElement, { childList: true, subtree: true });
    } catch (e) { /* no MutationObserver */ }
})();

/**
 * Live badge polling (opt-in). Runs only when the server exposed `window.__betterMenuBadge`
 * (i.e. BetterMenu.badge_poll_interval > 0). Fetches the badge endpoint on the interval and
 * updates each menu row's count in place — adding the pill when a count rises from zero and
 * removing it when it drops back — and pauses while the browser tab is hidden.
 */
(function () {
    var cfg = window.__betterMenuBadge;
    if (!cfg || !cfg.url || !(cfg.interval > 0)) {
        return;
    }

    function apply(data) {
        Object.keys(data).forEach(function (code) {
            var li = document.getElementById('Menu-' + code);
            if (!li) {
                return;
            }
            var link = li.querySelector(':scope > a');
            if (!link) {
                return;
            }
            var badge = link.querySelector('.cms-menu__badge');
            var label = data[code];
            if (label) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cms-menu__badge';
                    link.appendChild(badge);
                }
                if (badge.textContent !== label) {
                    badge.textContent = label;
                }
            } else if (badge) {
                badge.parentNode.removeChild(badge);
            }
        });
    }

    function poll() {
        if (document.hidden) {
            return;
        }
        fetch(cfg.url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) { if (d && typeof d === 'object') { apply(d); } })
            .catch(function () { /* ignore transient errors */ });
    }

    setInterval(poll, cfg.interval * 1000);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            poll();
        }
    });
    poll();
})();
