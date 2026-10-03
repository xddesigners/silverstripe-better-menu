<?php

namespace XD\BetterMenu\Extension;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\List\GroupedList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\View\Requirements;
use XD\BetterMenu\BetterMenu;

/**
 * Better Menu — groups and restyles the native CMS left menu.
 *
 * Works out of the box with just the icon/colour styling (no grouping needed). When groups are
 * configured, grouping is server-side via a `LeftAndMain_MenuList.ss` override (only that
 * include), so the native sidebar — collapse toggle + version indicator — stays intact.
 * Per-section icons come from `menu_icon_class` (set here from config); colours/opacity and the
 * profile/logout icons are layered on with CSS/JS. See {@link BetterMenu}.
 *
 * @extends Extension<LeftAndMain>
 */
class LeftAndMainExtension extends Extension
{
    /**
     * Apply per-section icons (menu_icon_class) from the config before the menu is built.
     */
    protected function onInit(): void
    {
        foreach ($this->collectBetterMenu()['items'] as $class => $opts) {
            if (!empty($opts['icon']) && is_string($class)) {
                Config::modify()->set($class, 'menu_icon_class', $opts['icon']);
            }
        }
    }

    protected function onAfterInit(): void
    {
        // Font Awesome (FA6 Free from cdnjs, or a custom/Pro URL).
        $css = BetterMenu::fontAwesomeCss();
        if ($css !== '') {
            Requirements::css($css);
        }

        $decls = 'top:0!important;bottom:0!important;height:auto!important;margin-top:0!important;'
            . 'width:1.25em!important;font-size:16px!important;text-align:left!important;'
            . 'display:flex!important;align-items:center!important;justify-content:flex-start!important;';

        // Global icon colour / opacity.
        $color = $this->safeColor((string) BetterMenu::config()->get('icon_color'));
        if ($color !== '') {
            $decls .= 'color:' . $color . '!important;';
        }
        $opacity = BetterMenu::config()->get('icon_opacity');
        if ($opacity !== null) {
            $decls .= 'opacity:' . (float) $opacity . '!important;';
        }

        $cssOut = '.cms-menu__list li .menu__icon[class*="fa-"]{' . $decls . '}'
            // Left-align the profile name with the menu titles.
            . '.cms-login-status__profile-link{padding-left:40px!important;}'
            . '.cms-login-status__profile-text{padding-left:0!important;}';

        // Per-item colour / opacity overrides, keyed by the menu item id (#Menu-<Code>).
        foreach ($this->collectBetterMenu()['items'] as $class => $opts) {
            $itemCss = '';
            $c = $this->safeColor((string) ($opts['color'] ?? ''));
            if ($c !== '') {
                $itemCss .= 'color:' . $c . '!important;';
            }
            if (isset($opts['opacity']) && $opts['opacity'] !== null && $opts['opacity'] !== '') {
                $itemCss .= 'opacity:' . (float) $opts['opacity'] . '!important;';
            }
            if ($itemCss !== '') {
                $code = str_replace('\\', '-', (string) $class);
                $cssOut .= '#Menu-' . $code . ' .menu__icon{' . $itemCss . '}';
            }
        }

        Requirements::customCSS($cssOut, 'better-menu-style');

        // Swap the hardcoded profile + logout icons for the configured Font Awesome classes.
        $swaps = array_filter([
            '.cms-login-status__profile-icon' => trim((string) BetterMenu::config()->get('profile_icon')),
            '.cms-login-status__logout-link .font-icon-logout' => trim((string) BetterMenu::config()->get('logout_icon')),
        ]);
        if ($swaps) {
            Requirements::customScript(
                '(function(){var m=' . json_encode($swaps) . ';function a(){'
                . 'Object.keys(m).forEach(function(sel){var el=document.querySelector(sel);if(!el)return;'
                . 'Array.prototype.slice.call(el.classList).forEach(function(x){if(x.indexOf("font-icon")===0)el.classList.remove(x);});'
                . 'm[sel].split(/\\s+/).forEach(function(x){if(x)el.classList.add(x);});});}'
                . 'if(document.readyState!=="loading"){a();}else{document.addEventListener("DOMContentLoaded",a);}})();',
                'better-menu-swap'
            );
        }
    }

    /**
     * Grouped version of the native CMS main menu, rendered by this module's
     * LeftAndMain_MenuList.ss override. With no groups configured every item is "its own group"
     * and renders as a normal row — i.e. the menu looks native (just restyled). Groups with 2+
     * present items become collapsible parents.
     */
    public function GroupedMainMenu(): ArrayList
    {
        $items = $this->getOwner()->MainMenu();
        $groups = $this->collectBetterMenu()['groups'];

        // Map each configured menu-item Code -> group + sort metadata.
        $itemsToGroup = [];
        foreach ($groups as $group) {
            if (empty($group['codes'])) {
                continue;
            }
            $priority = $group['priority'] ?? $group['groupSort'];
            $itemSort = 0;
            foreach ($group['codes'] as $code) {
                $itemsToGroup[$code] = [
                    'Group' => $group['title'],
                    'Priority' => $priority,
                    'SortOrder' => $itemSort++,
                ];
            }
        }

        // Tag each live menu item with its group (or itself when ungrouped).
        foreach ($items as $item) {
            if (isset($itemsToGroup[$item->Code])) {
                $item->Group = $itemsToGroup[$item->Code]['Group'];
                $item->Priority = $itemsToGroup[$item->Code]['Priority'];
                $item->SortOrder = $itemsToGroup[$item->Code]['SortOrder'];
            } else {
                $item->Group = $item->Code;
                $priority = $item->MenuItem->priority ?? null;
                $item->Priority = is_numeric($priority) ? $priority : -1;
                $item->SortOrder = 0;
            }
        }

        // Look up a group's config (icon, alphabetical) by title.
        $byTitle = [];
        foreach ($groups as $group) {
            $byTitle[$group['title']] = $group;
        }

        $result = ArrayList::create();
        $grouped = GroupedList::create($items->sort(['Priority' => 'DESC']))->groupBy('Group');

        foreach ($grouped as $groupName => $children) {
            if ($children->count() > 1 && isset($byTitle[$groupName])) {
                $cfg = $byTitle[$groupName];
                $active = false;
                foreach ($children as $child) {
                    if ($child->LinkingMode === 'current') {
                        $active = true;
                    }
                }
                $code = str_replace(' ', '_', (string) $groupName);
                $result->push(ArrayData::create([
                    'Title' => _t('XD\\BetterMenu\\Group.' . $code, $groupName),
                    'IconClass' => $cfg['icon'] ?? 'font-icon-menu-modeladmin',
                    'Code' => DBField::create_field(DBText::class, $code),
                    'Link' => $children->first()->Link,
                    'LinkingMode' => $active ? 'current' : 'link',
                    'Children' => !empty($cfg['alphabetical']) ? $children->sort('Title') : $children->sort('SortOrder'),
                ]));
            } else {
                $result->push($children->first());
            }
        }

        return $result;
    }

    /**
     * Normalise the module config into a single model:
     *   ['groups' => [ ['title','icon','priority','alphabetical','codes'[],'groupSort'] ],
     *    'items'  => [ '<Class>' => ['icon','color','opacity'] ] ]
     * Reads the rich `BetterMenu.menu` tree, the legacy `LeftAndMain.menu_groups`, and the
     * simple `BetterMenu.menu_icons` map.
     */
    protected function collectBetterMenu(): array
    {
        $groups = [];
        $items = [];
        $groupSort = 0;

        // 1) Rich tree: ordered list of group/section nodes.
        foreach ((array) BetterMenu::config()->get('menu') as $node) {
            if (!is_array($node)) {
                continue;
            }
            if (isset($node['group'])) {
                $codes = [];
                foreach (($node['children'] ?? []) as $child) {
                    if (is_string($child)) {
                        $class = $child;
                        $opts = [];
                    } elseif (is_array($child) && isset($child['section'])) {
                        $class = $child['section'];
                        $opts = $child;
                    } else {
                        continue;
                    }
                    $codes[] = str_replace('\\', '-', (string) $class);
                    $items[$class] = $this->mergeItem($items[$class] ?? [], $opts);
                }
                $groups[] = [
                    'title' => (string) $node['group'],
                    'icon' => $node['icon'] ?? null,
                    'priority' => $node['priority'] ?? null,
                    'alphabetical' => (bool) ($node['alphabetical'] ?? false),
                    'codes' => $codes,
                    'groupSort' => $groupSort--,
                ];
            } elseif (isset($node['section'])) {
                $items[$node['section']] = $this->mergeItem($items[$node['section']] ?? [], $node);
            }
        }

        // 2) Legacy LeftAndMain.menu_groups (grouped-cms-menu compatibility).
        foreach ((array) Config::inst()->get(LeftAndMain::class, 'menu_groups') as $title => $settings) {
            $codes = $settings['items'] ?? [];
            if (!is_array($codes) || !count($codes)) {
                continue;
            }
            $groups[] = [
                'title' => (string) $title,
                'icon' => $settings['icon_class'] ?? null,
                'priority' => $settings['priority'] ?? null,
                'alphabetical' => (bool) ($settings['alphabetical'] ?? false),
                'codes' => $codes,
                'groupSort' => $groupSort--,
            ];
        }

        // 3) Simple menu_icons map (icon only), as a fallback for un-styled items.
        foreach ((array) BetterMenu::config()->get('menu_icons') as $class => $icon) {
            if (is_string($class) && is_string($icon) && $icon !== '' && empty($items[$class]['icon'])) {
                $items[$class]['icon'] = $icon;
            }
        }

        return ['groups' => $groups, 'items' => $items];
    }

    private function mergeItem(array $existing, array $opts): array
    {
        foreach (['icon', 'color', 'opacity'] as $k) {
            if (array_key_exists($k, $opts) && $opts[$k] !== null && $opts[$k] !== '') {
                $existing[$k] = $opts[$k];
            }
        }
        return $existing;
    }

    private function safeColor(string $color): string
    {
        return (string) preg_replace('/[^a-zA-Z0-9#(),.%\s\/-]/', '', trim($color));
    }
}
