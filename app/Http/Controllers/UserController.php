<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::with(['roles', 'permissions'])->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // ربط الدور
        $user->assignRole($validated['role']);

        return redirect()->route('users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    public function edit(User $user)
{
    return Inertia::render('Users/Edit', [
        'user' => $user->load('roles'),
        'roles' => Role::all(),
    ]);
}

public function update(Request $request, User $user)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'password' => 'nullable|min:6',
        'role' => 'required',
    ]);

    if ($data['password']) {
        $data['password'] = Hash::make($data['password']);
    } else {
        unset($data['password']);
    }

    $user->update($data);
    $user->syncRoles([$data['role']]);

    return redirect()->route('users.index')
        ->with('success', 'تم تحديث المستخدم بنجاح');
}
    public function destroy(User $user): RedirectResponse
    {
       if (Auth::id() === $user->id) {
    return back()->withErrors([
        'delete' => 'لا يمكنك حذف حسابك الحالي',
    ]);
}

        $user->delete();

        return back();
    }

}
