<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Edit User</h2>
        <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">{{ $user->name }} — {{ $user->email }}</p>
    </x-slot>

    <div style="max-width:640px;margin:0 auto;">
        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:28px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">

            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf @method('PUT')

                <div style="display:grid;gap:20px;">

                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#4a4535;margin-bottom:6px;">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.875rem;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                        @error('name')<p style="color:#dc2626;font-size:0.75rem;margin-top:4px;">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#4a4535;margin-bottom:6px;">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.875rem;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                        @error('email')<p style="color:#dc2626;font-size:0.75rem;margin-top:4px;">{{ $message }}</p>@enderror
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#4a4535;margin-bottom:6px;">New Password <span style="color:#8a7a60;font-weight:400;">(leave blank to keep)</span></label>
                            <input type="password" name="password"
                                style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.875rem;outline:none;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                            @error('password')<p style="color:#dc2626;font-size:0.75rem;margin-top:4px;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#4a4535;margin-bottom:6px;">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.875rem;outline:none;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#4a4535;margin-bottom:6px;">Role</label>
                            <select name="role" id="role" required onchange="toggleDepartment(this.value)"
                                style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.875rem;outline:none;background:#fff;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                                @foreach($roles as $roleOption)
                                    <option value="{{ $roleOption->name }}"
                                        {{ old('role', $user->roles->first()->name ?? '') === $roleOption->name ? 'selected' : '' }}>
                                        {{ ucfirst($roleOption->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')<p style="color:#dc2626;font-size:0.75rem;margin-top:4px;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#4a4535;margin-bottom:6px;">Status</label>
                            <select name="status" required
                                style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.875rem;outline:none;background:#fff;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ old('status', $user->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    @php $currentRole = old('role', $user->roles->first()->name ?? ''); @endphp
                    <div id="department-field" style="display:{{ in_array($currentRole, ['faculty','head_of_department']) ? 'block' : 'none' }};">
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#4a4535;margin-bottom:6px;">
                            Department
                            <span style="color:#8a7a60;font-weight:400;">(required for Faculty and Head of Department)</span>
                        </label>
                        <select name="department_id"
                            style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.875rem;outline:none;background:#fff;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                            <option value="">— No Department —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->code }} — {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')<p style="color:#dc2626;font-size:0.75rem;margin-top:4px;">{{ $message }}</p>@enderror
                    </div>

                </div>

                <div style="display:flex;gap:12px;margin-top:28px;padding-top:20px;border-top:1px solid #e2d9c8;">
                    <button type="submit"
                        style="background:#c9a84c;color:#fff;padding:9px 24px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;"
                        onmouseover="this.style.background='#a8872e'" onmouseout="this.style.background='#c9a84c'">
                        Update User
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        style="background:#f5f0e8;color:#4a4535;padding:9px 24px;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none;border:1px solid #e2d9c8;"
                        onmouseover="this.style.background='#e2d9c8'" onmouseout="this.style.background='#f5f0e8'">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleDepartment(role) {
        const field = document.getElementById('department-field');
        field.style.display = (role === 'faculty' || role === 'head_of_department') ? 'block' : 'none';
    }
    </script>
</x-app-layout>
