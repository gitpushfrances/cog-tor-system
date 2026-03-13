<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role   = $request->get('role', 'all');
        $search = $request->get('search');

        $query = User::with(['roles', 'department'])
            ->where('id', '!=', auth()->id())
            ->latest();

        if ($role !== 'all') {
            $query->whereHas('roles', fn($q) => $q->where('name', $role));
        }

        if ($search) {
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            );
        }

        $users = $query->paginate(15)->withQueryString();

        $counts = [
            'all'       => User::where('id', '!=', auth()->id())->count(),
            'faculty'   => User::where('id', '!=', auth()->id())->whereHas('roles', fn($q) => $q->where('name', 'faculty'))->count(),
            'head_of_department' => User::where('id', '!=', auth()->id())->whereHas('roles', fn($q) => $q->where('name', 'head_of_department'))->count(),
            'registrar' => User::where('id', '!=', auth()->id())->whereHas('roles', fn($q) => $q->where('name', 'registrar'))->count(),
        ];

        return view('admin.users.index', compact('users', 'counts', 'role', 'search'));
    }

    public function create()
    {
        $roles       = Role::whereIn('name', ['faculty', 'head_of_department', 'registrar'])->get();
        $departments = Department::active()->orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'role'          => 'required|exists:roles,name',
            'status'        => 'required|in:active,pending,inactive',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'status'        => $validated['status'],
            'department_id' => in_array($validated['role'], ['faculty', 'head_of_department']) ? ($validated['department_id'] ?? null) : null,
            'approved_by'   => $validated['status'] === 'active' ? auth()->id() : null,
            'approved_at'   => $validated['status'] === 'active' ? now() : null,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles       = Role::whereIn('name', ['faculty', 'head_of_department', 'registrar'])->get();
        $departments = Department::active()->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:8|confirmed',
            'role'          => 'required|exists:roles,name',
            'status'        => 'required|in:active,pending,inactive',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user->update([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => $validated['password'] ? Hash::make($validated['password']) : $user->password,
            'status'        => $validated['status'],
            'department_id' => in_array($validated['role'], ['faculty', 'head_of_department']) ? ($validated['department_id'] ?? null) : null,
            'approved_by'   => $validated['status'] === 'active' && !$user->approved_by ? auth()->id() : $user->approved_by,
            'approved_at'   => $validated['status'] === 'active' && !$user->approved_at ? now() : $user->approved_at,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function approve(User $user)
    {
        $user->update([
            'status'      => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User approved successfully.');
    }

    public function reject(User $user)
    {
        $user->update(['status' => 'inactive']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User rejected.');
    }
}
