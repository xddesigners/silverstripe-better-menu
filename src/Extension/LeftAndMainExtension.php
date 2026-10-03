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
     * Load the configured Font Awesome stylesheet (Free from cdnjs, or a custom/Pro URL).
     */
    protected function onAfterInit(): void
    {
        $css = BetterIcons::fontAwesomeCss();
        if ($css !== '') {
            Requirements::css($css);
        }
    }
}
