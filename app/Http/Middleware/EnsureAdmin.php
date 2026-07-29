<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the whole admin area.
 *
 * This replaces Admin_setting::__construct (Admin_setting.php:14-16), which
 * rendered the login view but never called exit()/redirect() — so execution
 * fell straight through into the requested method. Every one of the 80 admin
 * endpoints ran for anonymous callers, including delete_user, delete_payment
 * and all the update_* writes; /admin_setting/get_users returned every user
 * row with md5 hashes and the plaintext copies in `for_admin`.
 *
 * Here the request stops: JSON callers get 401, browsers get the login page.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(url('admin'));
        }

        return $next($request);
    }
}
