<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes the first two URL segments case-insensitive, the way CodeIgniter's
 * router was.
 *
 * The legacy views are wildly inconsistent about casing and relied on CI not
 * caring. All of these appear in the codebase and must keep working:
 *
 *   admin_sidebar.php:43   admin_setting/add_user
 *   routes used elsewhere  admin_setting/Add_user
 *   show_expense.php:411   Admin_setting/get_expense_data
 *   show_expense.php:449   admin_setting/update_expense
 *   Client.php:23          Construction/add_site
 *
 * Only segments 0 and 1 (controller and method) are lowercased. Later segments
 * carry data — show_bricks/63/Cement, show_labour/63/Tile%20fixing — and
 * lowercasing those would break the category lookups, since `type` values in
 * the database are capitalised.
 *
 * This runs as global middleware so it happens before route dispatch.
 */
class NormalizeLegacyUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        if ($path === '/' || $path === '') {
            return $next($request);
        }

        $segments = explode('/', $path);
        $changed = false;

        foreach ([0, 1] as $i) {
            if (! isset($segments[$i])) {
                continue;
            }

            $lower = strtolower($segments[$i]);

            if ($lower !== $segments[$i]) {
                $segments[$i] = $lower;
                $changed = true;
            }
        }

        if (! $changed) {
            return $next($request);
        }

        $query = $request->getQueryString();
        $request->server->set('REQUEST_URI', '/'.implode('/', $segments).($query ? '?'.$query : ''));

        // Re-initialise so Symfony recomputes the cached pathInfo/requestUri.
        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
        );

        return $next($request);
    }
}
