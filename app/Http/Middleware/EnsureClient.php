<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the client area.
 *
 * Client::__construct (Client.php:16-25) had no "not logged in" branch at all —
 * it read $session['id'] straight into a query, so an anonymous request hit a
 * null-property error rather than a login redirect. That is fixed here.
 *
 * It also redirected Workers away to the construction area; preserved below.
 */
class EnsureClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(url('login'));
        }

        if ($user->isWorker()) {
            return redirect(url('construction/show_site'));
        }

        return $next($request);
    }
}
