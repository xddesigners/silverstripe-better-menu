# Silverstripe Better Menu

Polish the Silverstripe CMS **left menu**: replace the section icons (Pages, Files, Reports,
Security, Archive, Settings, and any `ModelAdmin`) with **Font Awesome** ones, set their colour
and opacity, swap the profile/logout icons, and tidy the alignment — all configured in YAML.

Out of the box it loads Font Awesome Free and applies a curated set of icons to the core CMS
sections. Everything is overridable, and Font Awesome can be disabled or swapped for your Pro kit.

![The CMS left menu with Font Awesome section icons](docs/images/menu.png)

## Requirements

- Silverstripe Framework **6** / Admin **3**
- PHP **8.3+**

## Installation

```bash
composer require xddesigners/silverstripe-better-menu
```

Run `dev/build?flush=1` once.

## How it works

Each CMS menu section renders its icon from the admin controller's `menu_icon_class` config. The
module loads Font Awesome into the CMS and overrides `menu_icon_class` for the classes in its
`menu_icons` map (via a `LeftAndMain` extension), so no template or JS changes are needed.

## Configuring the icons

Set icons per admin class in your project YAML (e.g. `app/_config/better-menu.yml`). The map is
deep-merged with the module's defaults, so you only list what you want to change:

```yaml
XD\BetterMenu\BetterMenu:
  menu_icons:
    'App\Admin\ProductAdmin': 'fa-solid fa-box'
    'App\Admin\OrderAdmin': 'fa-solid fa-receipt'
    # Keep the built-in webfont for one section by using a font-icon name:
    'SilverStripe\Reports\ReportAdmin': 'font-icon-chart-line'
```

The value is either Font Awesome classes (e.g. `fa-solid fa-box`) or a CMS `font-icon-*` name.

### Curated defaults

The module ships these defaults (override any of them as above):

| Section  | Class                                             | Icon                      |
|----------|---------------------------------------------------|---------------------------|
| Pages    | `SilverStripe\CMS\Controllers\CMSMain`            | `fa-solid fa-folder-tree`     |
| Files    | `SilverStripe\AssetAdmin\Controller\AssetAdmin`   | `fa-solid fa-image` |
| Reports  | `SilverStripe\Reports\ReportAdmin`                | `fa-solid fa-chart-simple`  |
| Security | `SilverStripe\Admin\SecurityAdmin`                | `fa-solid fa-user-pen`       |
| Archive  | `SilverStripe\VersionedAdmin\ArchiveAdmin`        | `fa-solid fa-box-archive` |
| Settings | `SilverStripe\SiteConfig\SiteConfigLeftAndMain`   | `fa-solid fa-gear`        |
| ModelAdmin (default) | `SilverStripe\Admin\ModelAdmin`       | `fa-solid fa-table-list`  |

## Profile & logout icons

The user and logout icons at the top of the CMS menu aren't menu-icon-classes, so give them
Font Awesome classes separately (swapped in client-side). The profile name is also left-aligned
with the menu titles.

```yaml
XD\BetterMenu\BetterMenu:
  profile_icon: 'fa-solid fa-circle-user'
  logout_icon: 'fa-solid fa-right-from-bracket'
```

## Icon colour & opacity

The menu icons default to the SilverStripe dark-blue header colour at full opacity. Override the
global colour and/or opacity:

```yaml
XD\BetterMenu\BetterMenu:
  icon_color: '#005a93'   # any CSS colour (default: SS dark blue); empty keeps the CMS default
  icon_opacity: 0.8       # 0–1 (default: 0.8)
```

## Font Awesome: disable or use Pro

Font Awesome Free is loaded from cdnjs by default. Turn it off, or point at your own Pro kit/CSS:

```yaml
XD\BetterMenu\BetterMenu:
  # Disable all Font Awesome loading (use CMS font-icons only in your map):
  include_fontawesome_free: false

  # …or load Font Awesome Pro instead, then use Pro classes (fa-thin, fa-duotone, …) in menu_icons:
  fontawesome_css: 'https://kit.fontawesome.com/XXXX.css'
```

If you also use [silverstripe-better-tabs](https://github.com/xddesigners/silverstripe-better-tabs),
both load the same cdnjs stylesheet — Silverstripe de-duplicates it, so it's only fetched once.

## Caveat

A section that sets a `menu_icon` **image** (rather than a `menu_icon_class`) keeps that image —
the class override doesn't apply to it. The core sections above all use a class, so they theme
cleanly.

## License

BSD-3-Clause.
