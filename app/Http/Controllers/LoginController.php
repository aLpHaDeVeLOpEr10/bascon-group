<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Port of application/controllers/Login.php — the worker/client login.
 *
 * URLs preserved: /login, /login/login_user, /login/logout
 */
class LoginController extends Controller
{
    public function index()
    {
        if (Auth::guard('web')->check()) {
            return redirect(url('construction/show_site'));
        }

        return view('login');
    }

    /**
     * The form field is called "username" but has always been matched against
     * the email column (Login.php:59 → User_model::get_user, which queries
     * WHERE email = ?). Preserved so existing credentials keep working.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $ok = Auth::guard('web')->attempt([
            'email' => $credentials['username'],
            'password' => $credentials['password'],
        ]);

        if (! $ok) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Invalid username or password');
        }

        $request->session()->regenerate();

        $user = Auth::guard('web')->user();

        return redirect(url($user->isClient() ? 'client/total_payment' : 'construction/show_site'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(url('login'));
    }
}
