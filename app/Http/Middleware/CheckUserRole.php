<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRoles = $user->getRolesArray();

        // SUPERADMIN selalu memiliki semua akses
        if ($user->username === 'SUPERADMIN') {
            return $next($request);
        }

        // Cek apakah user memiliki role yang diperlukan
        if (!isset($userRoles[$role]) || !$userRoles[$role]) {
            abort(403, 'Anda tidak memiliki akses untuk fitur ini.');
        }

        return $next($request);
    }
}
