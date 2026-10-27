<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        // Deletes and high-risk changes require the user's own password (RequireActionPassword).
        $this->middleware('action.password')->only(['update', 'destroy']);
    }

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
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
            );
        }

        $users = $query->paginate(15)->withQueryString();

        $counts = [
            'all'       => User::where('id', '!=', auth()->id())->count(),
            'registrar' => User::where('id', '!=', auth()->id())->whereHas('roles', fn($q) => $q->where('name', 'registrar'))->count(),
        ];

        return view('admin.users.index', compact('users', 'counts', 'role', 'search'));
    }

    public function create()
    {
        $roles       = Role::whereIn('name', ['registrar'])->get();
        $departments = Department::active()->orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(self::identityRules() + [
            'password'      => 'required|string|min:8|confirmed',
            'role'          => ['required', Rule::in(['registrar'])],
            'status'        => 'required|in:active,inactive',
        ], self::identityMessages());

        $user = User::create([
            'username'      => $validated['username'],
            'last_name'     => $validated['last_name'],
            'first_name'    => $validated['first_name'],
            'middle_name'   => $validated['middle_name'] ?? null,
            'password'      => Hash::make($validated['password']),
            'status'        => $validated['status'],
            'approved_by'   => $validated['status'] === 'active' ? auth()->id() : null,
            'approved_at'   => $validated['status'] === 'active' ? now() : null,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        if ($blocked = $this->guardManageable($user)) {
            return $blocked;
        }

        $roles       = Role::whereIn('name', ['registrar'])->get();
        $departments = Department::active()->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        if ($blocked = $this->guardManageable($user)) {
            return $blocked;
        }

        $validated = $request->validate(self::identityRules($user) + [
            'password'      => 'nullable|string|min:8|confirmed',
            'role'          => ['required', Rule::in(['registrar'])],
            'status'        => 'required|in:active,inactive',
        ], self::identityMessages());

        $before = ['name' => $user->name, 'username' => $user->username, 'status' => $user->status, 'role' => $user->roles->pluck('name')->implode(', ')];
        $passwordChanged = filled($validated['password'] ?? null);

        $user->update([
            'username'      => $validated['username'],
            'last_name'     => $validated['last_name'],
            'first_name'    => $validated['first_name'],
            'middle_name'   => $validated['middle_name'] ?? null,
            'password'      => $passwordChanged ? Hash::make($validated['password']) : $user->password,
            'status'        => $validated['status'],
            'approved_by'   => $validated['status'] === 'active' && !$user->approved_by ? auth()->id() : $user->approved_by,
            'approved_at'   => $validated['status'] === 'active' && !$user->approved_at ? now() : $user->approved_at,
        ]);

        $user->syncRoles([$validated['role']]);

        $after = ['name' => $user->name, 'username' => $user->username, 'status' => $user->status, 'role' => $validated['role']];
        $old = array_diff_assoc($before, $after);
        $new = array_intersect_key($after, $old);
        if ($passwordChanged) {
            $old['password'] = '(hidden)';
            $new['password'] = 'changed';
        }
        if ($new) {
            $this->logAccount($user, 'updated', 'Updated account ' . $user->username, ['old' => $old, 'attributes' => $new]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete yourself.');
        }
        if ($blocked = $this->guardManageable($user)) {
            return $blocked;
        }

        $username = $user->username;
        try {
            $user->delete();
        } catch (QueryException $e) {
            // Accounts that enrolled students, encoded grades or generated documents are part of the record.
            return redirect()->route('admin.users.index')
                ->with('error', "{$user->name} has records in the system (enrollments, grades or documents) and cannot be deleted. Set the account to Inactive instead.");
        }

        $this->logAccount(null, 'deleted', 'Deleted account ' . $username, ['username' => $username]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /** Name parts and username (panel: Last / First / Middle name; sign in with a username, not an email). */
    public static function identityRules(?User $user = null): array
    {
        return [
            'last_name'   => 'required|string|max:100',
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'username'    => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user?->id)],
        ];
    }

    public static function identityMessages(): array
    {
        return [
            'username.regex'  => 'The username can use letters, numbers, dot, dash and underscore only (no spaces).',
            'username.unique' => 'This username is already taken.',
        ];
    }

    /**
     * Your own account and other admin accounts are not managed on this page,
     * so an admin can never be demoted to registrar, locked out or deleted from here.
     */
    private function guardManageable(User $user): ?RedirectResponse
    {
        if ($user->id === auth()->id() || $user->hasRole('admin')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Admin accounts cannot be changed here. Change your own name, username or password under Profile.');
        }

        return null;
    }

    private function logAccount(?User $user, string $event, string $description, array $properties = []): void
    {
        $log = activity('user')->causedBy(auth()->user())->event($event)->withProperties($properties);
        if ($user) {
            $log->performedOn($user);
        }
        $log->log($description);
    }
}
