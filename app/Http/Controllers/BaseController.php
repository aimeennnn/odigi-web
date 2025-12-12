<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\RoleHelper;

abstract class BaseController extends Controller
{
    /**
     * Check menu access and logout if unauthorized
     * 
     * @param string $menu
     * @param string $action
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function checkMenuAccess(string $menu, string $action = 'view', Request $request = null)
    {
        // Check menu access
        if (!RoleHelper::hasMenuAccess($menu)) {
            $this->forceLogout($request);
            return redirect()->route('login')->with('error', "Akses ditolak. Anda tidak memiliki izin untuk mengakses menu " . ucfirst(str_replace('menu_', '', $menu)) . ".");
        }

        // Check specific action permission
        $feature = str_replace('menu_', '', $menu);
        $hasPermission = false;

        switch ($action) {
            case 'create':
                $hasPermission = RoleHelper::canCreate($feature);
                break;
            case 'edit':
                $hasPermission = RoleHelper::canEdit($feature);
                break;
            case 'delete':
                $hasPermission = RoleHelper::canDelete($feature);
                break;
            case 'view':
            default:
                $hasPermission = RoleHelper::canView($feature);
                break;
        }

        if (!$hasPermission) {
            $this->forceLogout($request);
            return redirect()->route('login')->with('error', "Akses ditolak. Anda tidak memiliki izin untuk {$action} data " . ucfirst($feature) . ".");
        }

        return null; // Access granted
    }

    /**
     * Force logout and invalidate session
     * 
     * @param Request|null $request
     */
    protected function forceLogout(Request $request = null)
    {
        // Update status online menjadi false sebelum logout
        $user = auth()->user();
        if ($user) {
            \App\Models\User::where('id', $user->id)->update(['online' => false]);
        }
        
        auth()->logout();
        if ($request) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
