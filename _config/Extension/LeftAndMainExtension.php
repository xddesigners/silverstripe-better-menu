<?php

namespace XD\BetterIcons\Extension;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use XD\BetterIcons\BetterIcons;

/**
 * Applies the configured left-menu icons (by overriding each admin's `menu_icon_class`) and loads
 * the chosen Font Awesome stylesheet into the CMS. See {@link BetterIcons}.
 *
 * @extends Extension<\SilverStripe\Admin\LeftAndMain>
 */
class LeftAndMainExtension extends Extension
{
    /**
     * Override menu_icon_class for each configured admin before the main menu is built.
     */
    protected function onInit(): void
    {
        $map = BetterIcons::config()->get('menu_icons') ?: [];
        foreach ($map as $class => $iconClass) {
            if (is_string($class) && is_string($iconClass) && $iconClass !== '') {
                // Note: a class that sets a `menu_icon` image still wins over the class.
                Config::modify()->set($class, 'menu_icon_class', $iconClass);
            }
        }
    }

    /**
     * Load the configured Font Awesome stylesheet (Free from cdnjs, or a custom/Pro URL), and
     * vertically centre Font Awesome menu icons.
     */
    protected function onAfterInit(): void
    {
        $css = BetterIcons::fontAwesomeCss();
        if ($css !== '') {
            Requirements::css($css);
        }

        // The admin positions .menu__icon at a fixed `top`/`margin-top` tuned for its own webfont;
        // Font Awesome glyphs have different metrics (and varying widths). Flex-centre them in the
        // full menu-item height and give every icon the same width (fa-fw style) so the icon column
        // is even. Scoped to FA icons, so native font-icons keep their place.
        $decls = 'top:0!important;bottom:0!important;height:auto!important;margin-top:0!important;'
            . 'width:1.25em!important;font-size:16px!important;text-align:left!important;'
            . 'display:flex!important;align-items:center!important;justify-content:flex-start!important;';

        // Optional global colour / opacity.
        $color = preg_replace('/[^a-zA-Z0-9#(),.%\s\/-]/', '', trim((string) BetterIcons::config()->get('icon_color')));
        if ($color !== '') {
            $decls .= 'color:' . $color . '!important;';
        }
        $opacity = BetterIcons::config()->get('icon_opacity');
        if ($opacity !== null) {
            $decls .= 'opacity:' . (float) $opacity . '!important;';
        }

        Requirements::customCSS(
            '.cms-menu__list li .menu__icon[class*="fa-"]{' . $decls . '}'
            // Left-align the profile name with the menu titles.
            . '.cms-login-status__profile-link{padding-left:40px!important;}',
            'better-icons-align'
        );

        // The top profile + logout icons are hardcoded in the login-status template (font-icon-*),
        // not menu-icon-classes — swap them for the configured Font Awesome classes client-side.
        $swaps = array_filter([
            '.cms-login-status__profile-icon' => trim((string) BetterIcons::config()->get('profile_icon')),
            '.cms-login-status__logout-link .font-icon-logout' => trim((string) BetterIcons::config()->get('logout_icon')),
        ]);
        if ($swaps) {
            Requirements::customScript(
                '(function(){var m=' . json_encode($swaps) . ';function a(){'
                . 'Object.keys(m).forEach(function(sel){var el=document.querySelector(sel);if(!el)return;'
                . 'Array.prototype.slice.call(el.classList).forEach(function(x){if(x.indexOf("font-icon")===0)el.classList.remove(x);});'
                . 'm[sel].split(/\\s+/).forEach(function(x){if(x)el.classList.add(x);});});}'
                . 'if(document.readyState!=="loading"){a();}else{document.addEventListener("DOMContentLoaded",a);}})();',
                'better-icons-swap'
            );
        }
    }
}
