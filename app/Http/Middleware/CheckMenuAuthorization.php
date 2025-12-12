<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $menu): Response
    {
        $user = auth()->user();

        if (!$user) {
            // Jika belum login, redirect ke login
            return redirect()->route('login');
        }

        // Superadmin selalu boleh
        if (strtoupper($user->username) === 'SUPERADMIN') {
            return $next($request);
        }

        // Ambil menu yang diotorisasi menggunakan helper method
        $authorizedMenus = $user->getAuthorizedMenusArray();

        // Jika menu tidak ditemukan dalam authorized_menus
        if (!in_array($menu, $authorizedMenus)) {
            // Jangan logout paksa; redirect aman ke dashboard
            return redirect()->route('dashboard.index')->with('error', 'Akses ditolak untuk menu ini.');
        }

        return $next($request);
    }
}
