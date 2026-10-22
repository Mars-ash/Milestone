<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoLoginAdmin
{
    /**
     * Handle an incoming request.
     * Otomatis mengautentikasi single admin saat config auth.auto_login aktif.
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('auth.auto_login', false) && ! Auth::check()) {
            // Jika pengguna secara manual menekan tombol logout, biarkan tetap di halaman login
            if ($request->session()->get('logged_out_manually') === true && $request->is('login')) {
                return $next($request);
            }

            if ($request->session()->get('logged_out_manually') === true && ! $request->is('login')) {
                $request->session()->forget('logged_out_manually');
            }

            $admin = User::where('admin_slot', true)->first();
            if ($admin) {
                Auth::login($admin);
            }
        }

        return $next($request);
    }
}
