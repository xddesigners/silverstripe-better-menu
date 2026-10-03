<?php

namespace XD\BetterIcons;

use SilverStripe\Core\Config\Configurable;

/**
 * Options for better-icons — replaces the CMS left main-menu icons with Font Awesome ones.
 *
 * Configure in YAML (e.g. `app/_config/better-icons.yml`):
 *
 *     XD\BetterIcons\BetterIcons:
 *       menu_icons:
 *         'App\Admin\ProductAdmin': 'fa-solid fa-box'
 *       # Font Awesome is on by default; disable it, or point at your Pro kit:
 *       # include_fontawesome_free: false
 *       # fontawesome_css: 'https://kit.fontawesome.com/XXXX.css'
 */
class BetterIcons
{
    use Configurable;

    /**
     * Map of admin controller class name => icon class for its left-menu item. A CMS
     * `font-icon` name or Font Awesome classes (e.g. `fa-solid fa-box`). Deep-merged with the
     * module's curated defaults, so your entries override/extend them.
     *
     * @config
     */
    private static array $menu_icons = [];

    /**
     * Load Font Awesome Free (from cdnjs) into the CMS. On by default for this module (its icons
     * are Font Awesome); set to false to disable all Font Awesome loading. Ignored when
     * `fontawesome_css` is set.
     *
     * @config
     */
    private static bool $include_fontawesome_free = true;

    /**
     * Font Awesome Free version loaded from cdnjs when `include_fontawesome_free` is on.
     *
     * @config
     */
    private static string $fontawesome_version = '6.7.2';

    /**
     * Explicit Font Awesome stylesheet to load in the CMS — a full URL or local path. Point this
     * at your Font Awesome **Pro** kit/CSS to use Pro instead of the bundled Free load; then use
     * Pro classes (e.g. `fa-thin`, `fa-duotone`) in `menu_icons`. Takes precedence over
     * `include_fontawesome_free`.
     *
     * @config
     */
    private static string $fontawesome_css = '';

    /**
     * Resolve the Font Awesome stylesheet URL to load, or '' for none.
     */
    public static function fontAwesomeCss(): string
    {
        $css = trim((string) static::config()->get('fontawesome_css'));
        if ($css !== '') {
            return $css;
        }
        if (static::config()->get('include_fontawesome_free')) {
            $version = (string) static::config()->get('fontawesome_version');
            return "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/{$version}/css/all.min.css";
        }
        return '';
    }
}
