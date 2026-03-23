<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\UserPermission;
use Closure;

class CheckPermission
{
    public function handle($request, Closure $next, $key)
    {
        $permission = Permission::where('unique_key', $key)->first();

        $has = UserPermission::where('user_id', auth()->id())
            ->where('permission_id', $permission->id ?? 0)
            ->exists();

        if (!$has) {
            abort(403, 'No Permission');
        }

        return $next($request);
    }
}
