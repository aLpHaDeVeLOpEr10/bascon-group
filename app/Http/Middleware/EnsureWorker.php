<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the construction area: signed in, and not a Client.
 *
 * Mirrors Construction::__construct (Construction.php:13-26) — redirect to
 * login when not signed in, and bounce Clients to their own dashboard. Users
 * with an empty role are treated as workers, which is how the legacy check
 * behaved (it only compared against the exact string 'Client').
 */
class EnsureWorker
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

        if ($user->isClient()) {
            return redirect(url('client/construction_payments'));
        }

        return $next($request);
    }
}
