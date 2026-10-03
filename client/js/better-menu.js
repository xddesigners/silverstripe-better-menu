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
