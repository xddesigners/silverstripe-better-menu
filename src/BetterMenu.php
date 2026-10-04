<?php

namespace XD\BetterMenu;

use SilverStripe\Core\Config\Configurable;

/**
 * Options for better-menu — replaces the CMS left main-menu icons with Font Awesome ones.
 *
 * Configure in YAML (e.g. `app/_config/better-menu.yml`):
 *
 *     XD\BetterMenu\BetterMenu:
 *       menu_icons:
 *         'App\Admin\ProductAdmin': 'fa-solid fa-box'
 *       # Font Awesome is on by default; disable it, or point at your Pro kit:
 *       # include_fontawesome_free: false
 *       # fontawesome_css: 'https://kit.fontawesome.com/XXXX.css'
 */
class BetterMenu
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
     * Map of admin controller class name => a callable (as a string) returning the count shown in
     * that section's left-menu badge, e.g. `'App\Admin\OrderAdmin::newOrderCount'`. Invoked
     * server-side on each menu render; `0` (or less) shows no badge. The callable may return an
     * int, a numeric string, or any Countable / SS_List (its `count()` is used). Per-item badge
     * colour goes in the `menu` tree (`badge_color`); the default colour is below.
     *
     * @config
     */
    private static array $menu_badges = [];

    /**
     * Default background colour for the count badges (any CSS colour).
     *
     * @config
     */
    private static string $badge_color = '#e74c3c';

    /**
     * Text colour for the count badges.
     *
     * @config
     */
    private static string $badge_text_color = '#ffffff';

    /**
     * Cap shown on a badge; counts above it render as "<max>+" (e.g. 99 → "99+"). 0 disables it.
     *
     * @config
     */
    private static int $badge_max = 99;

    /**
     * Replace the profile (user) icon at the top of the CMS menu with Font Awesome classes
     * (e.g. `fa-solid fa-circle-user`). Empty leaves the built-in icon. That icon isn't a
     * Tab/menu-icon-class, so it's swapped client-side.
     *
     * @config
     */
    private static string $profile_icon = '';

    /**
     * Replace the logout (exit) icon at the top of the CMS menu with Font Awesome classes
     * (e.g. `fa-solid fa-right-from-bracket`). Empty leaves the built-in icon. Swapped
     * client-side like the profile icon.
     *
     * @config
     */
    private static string $logout_icon = '';

    /**
     * Global colour for the Font Awesome menu icons (any CSS colour, e.g. `#9aa7b2` or
     * `rgb(255 255 255)`). Empty leaves the CMS default (inherited sidebar colour).
     *
     * @config
     */
    private static string $icon_color = '';

    /**
     * Global opacity for the Font Awesome menu icons (0–1). Null leaves it as-is.
     *
     * @config
     */
    private static ?float $icon_opacity = null;

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
