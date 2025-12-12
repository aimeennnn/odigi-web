<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the user ID from the route parameter
        $userId = $request->route('user');
        
        if ($userId) {
            // If it's a model binding, get the ID
            if (is_object($userId)) {
                $userId = $userId->id;
            }
            
            // Check if the user being modified is super admin
            $user = \App\Models\User::find($userId);
            
            if ($user && $user->username === 'SUPERADMIN') {
                // Block any modification attempts on super admin
                if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
                    return back()->with('error', 'Super Admin tidak dapat diubah atau dihapus!');
                }
            }
        }
        
        return $next($request);
    }
}
