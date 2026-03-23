<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.permissions.users', compact('users'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $permissions = Permission::all()->groupBy('module');

        $userPermissions = UserPermission::where('user_id', $id)
            ->pluck('permission_id')
            ->toArray();

        return view(
            'admin.permissions.assign',
            compact('user', 'permissions', 'userPermissions')
        );
    }

    public function store(Request $request, $id)
    {
        UserPermission::where('user_id', $id)->delete();

        if ($request->permissions) {
            foreach ($request->permissions as $permission) {
                UserPermission::create([
                    'user_id' => $id,
                    'permission_id' => $permission,
                ]);
            }
        }

        return back()->with('success', 'Permissions Updated');
    }
}
