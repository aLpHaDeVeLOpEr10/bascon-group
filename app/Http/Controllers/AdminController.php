<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Port of application/controllers/Admin.php — the back-office login.
 *
 * URLs preserved: /admin, /admin/login_admin, /admin/logout
 */
class AdminController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            return redirect(url('admin_setting/dashboard'));
        }

        return view('admin.admin_login');
    }

    /**
     * As with the worker login, the "username" field is matched against the
     * email column (Admin.php:44 → User_model::get_admin queries WHERE email).
     * Legacy admin passwords were stored in plaintext; LegacyHashUserProvider
     * accepts them once and rewrites them as bcrypt.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $ok = Auth::guard('admin')->attempt([
            'email' => $credentials['username'],
            'password' => $credentials['password'],
        ]);

        if (! $ok) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Invalid username or password');
        }

        $request->session()->regenerate();

        return redirect(url('admin_setting/dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(url('admin'));
    }
}
