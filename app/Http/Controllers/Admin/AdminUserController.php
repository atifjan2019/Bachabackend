<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = AdminUser::orderBy('username')->paginate(20);
        return view('admin.admin-users.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admin-users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:admin_users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:admin_users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        AdminUser::create([
            'username'       => $data['username'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'login_attempts' => 0,
        ]);

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user "' . $data['username'] . '" created.');
    }

    public function edit(string $id)
    {
        $admin = AdminUser::findOrFail($id);
        return view('admin.admin-users.edit', compact('admin'));
    }

    public function update(Request $request, string $id)
    {
        $admin = AdminUser::findOrFail($id);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('admin_users', 'username')->ignore($admin->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('admin_users', 'email')->ignore($admin->id)],
            // Password optional on edit — only changes when a new one is provided.
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'unlock'   => ['nullable', 'boolean'],
        ]);

        $admin->username = $data['username'];
        $admin->email    = $data['email'];

        if (! empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        // Optionally clear a lockout from failed login attempts.
        if ($request->boolean('unlock')) {
            $admin->login_attempts = 0;
            $admin->locked_until = null;
        }

        $admin->save();

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user "' . $admin->username . '" updated.');
    }

    public function destroy(string $id)
    {
        $admin = AdminUser::findOrFail($id);

        if ((int) $admin->id === (int) Auth::guard('admin')->id()) {
            return redirect()->route('admin.admin-users.index')
                ->with('error', 'You cannot delete your own account while logged in.');
        }

        if (AdminUser::count() <= 1) {
            return redirect()->route('admin.admin-users.index')
                ->with('error', 'Cannot delete the last remaining admin user.');
        }

        $name = $admin->username;
        $admin->delete();

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin user "' . $name . '" deleted.');
    }
}
