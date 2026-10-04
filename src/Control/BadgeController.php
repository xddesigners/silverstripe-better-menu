<?php

namespace XD\BetterMenu\Control;

use SilverStripe\Admin\CMSMenu;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;
use XD\BetterMenu\BetterMenu;

/**
 * Read-only JSON endpoint for live badge counts: `admin/better-menu/badges`.
 *
 * Returns `{ "<dashed-controller-class>": "<label>" }` for the badge-configured sections the
 * current CMS user can access — the same codes used for the `#Menu-<code>` list items — so the
 * client can refresh the badges in place. Polled by the module's JS when
 * `BetterMenu.badge_poll_interval` > 0.
 *
 * Auth-guarded (a logged-in user is required) and limited to sections already visible in the
 * user's menu. The request never carries a callable; only the server's own configured callables
 * are invoked, so there's no arbitrary-call surface.
 */
class BadgeController extends Controller
{
    private static array $allowed_actions = [
        'badges',
    ];

    public function badges(HTTPRequest $request): HTTPResponse
    {
        $response = HTTPResponse::create();
        $response->addHeader('Content-Type', 'application/json');
        $response->addHeader('Cache-Control', 'no-store');

        if (!Security::getCurrentUser()) {
            return $response->setStatusCode(403)->setBody(json_encode(['error' => 'unauthorised']));
        }

        // Sections the current user can actually see (mirrors the rendered menu).
        $viewable = [];
        foreach (CMSMenu::get_viewable_menu_items() as $item) {
            if (!empty($item->controller)) {
                $viewable[$item->controller] = true;
            }
        }

        $out = [];
        foreach (BetterMenu::badgeCallables() as $class => $callable) {
            if (!isset($viewable[$class])) {
                continue;
            }
            $out[str_replace('\\', '-', (string) $class)] = BetterMenu::badgeLabel((string) $callable);
        }

        return $response->setBody(json_encode($out));
    }
}
