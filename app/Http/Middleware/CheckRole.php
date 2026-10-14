<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Middleware untuk mengecek role user
     * Usage: ->middleware('role:admin,pengelola')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        $user = auth()->user();
        $userRole = is_object($user) && isset($user->role) ? $user->role : null;
        $requestedRoles = collect($roles)
            ->flatMap(fn ($role) => preg_split('/[|,]/', (string) $role))
            ->map(fn ($role) => trim($role))
            ->filter()
            ->values()
            ->all();

        $requestedRoleValues = array_map(fn ($role) => mb_strtolower(trim($role)), $requestedRoles);
        $requestedRoleValues = array_values(array_filter($requestedRoleValues));

        $hasRequestedRole = false;
        if (method_exists($user, 'hasAnyRole')) {
            $hasRequestedRole = $user->hasAnyRole($requestedRoles);
        }

        if ($hasRequestedRole) {
            return $next($request);
        }

        $adminLikeRoles = ['admin', 'pengelola', 'super admin', 'staff'];
        $userHasAdminLikeRole = false;
        if (method_exists($user, 'hasAnyRole')) {
            $userHasAdminLikeRole = $user->hasAnyRole($adminLikeRoles);
        }

        if ($userHasAdminLikeRole && !empty(array_intersect($requestedRoleValues, $adminLikeRoles))) {
            return $next($request);
        }

        if ($userRole && in_array(mb_strtolower(trim($userRole)), $requestedRoleValues, true)) {
            return $next($request);
        }

        if ($userHasAdminLikeRole) {
            return redirect()->route('simk.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}