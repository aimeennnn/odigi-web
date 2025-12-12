<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
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
        
        // Debug log untuk troubleshooting
        \Illuminate\Support\Facades\Log::info('CheckMenuAccess Debug', [
            'user_id' => $user->id,
            'username' => $user->username,
            'requested_menu' => $menu,
            'authorized_menus' => $authorizedMenus,
            'user_roles' => $user->getRolesArray()
        ]);

        // Allowlist: Level 1-4 otomatis boleh mengakses menu inti secara read-only
        $userLevel = (string) ($user->level ?? '0');
        $allowListedForKomiteLevels = ['menu_register','menu_data','menu_bank','menu_slik','menu_komite'];
        if (in_array($userLevel, ['1','2','3','4']) && in_array($menu, $allowListedForKomiteLevels)) {
            return $next($request);
        }

        // Jika menu tidak ditemukan dalam authorized_menus
        if (!in_array($menu, $authorizedMenus)) {
            \Illuminate\Support\Facades\Log::warning('Access denied (no logout)', [
                'user_id' => $user->id,
                'username' => $user->username,
                'requested_menu' => $menu,
                'authorized_menus' => $authorizedMenus
            ]);
            // JANGAN logout. Arahkan ke dashboard agar sesi tetap aktif
            return redirect()->route('dashboard.index')->with('error', 'Akses ditolak untuk menu ini.');
        }

        return $next($request);
    }
}
