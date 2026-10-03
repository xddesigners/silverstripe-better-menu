/**
 * Better Menu — collapsible groups for the CMS left menu.
 *
 * Clicking a group's chevron toggles it open/closed (the header link still navigates), the
 * open/closed state persists per group in localStorage, and it re-applies after the admin's
 * PJAX re-renders (MutationObserver). Dependency-free (no jQuery/entwine).
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

            var key = 'btCmsGroup_' + (li.id || i);
            try {
                var saved = localStorage.getItem(key);
                if (saved === 'open') {
                    li.classList.add('opened');
                } else if (saved === 'closed') {
                    li.classList.remove('opened');
                }
            } catch (e) { /* storage unavailable */ }

            var toggle = li.querySelector(':scope > .cms-menu__group-toggle');
            if (!toggle) {
                continue;
            }

            (function (li, key, toggle) {
                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var open = li.classList.toggle('opened');
                    try {
                        localStorage.setItem(key, open ? 'open' : 'closed');
                    } catch (e) { /* ignore */ }
                });
            })(li, key, toggle);
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
