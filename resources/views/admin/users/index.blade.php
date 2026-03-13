<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">User Management</h2>
                <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">Manage faculty, heads of departments, and registrar accounts</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               style="background:#c9a84c;color:#fff;padding:8px 18px;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none;transition:background 0.15s;"
               onmouseover="this.style.background='#a8872e'"
               onmouseout="this.style.background='#c9a84c'">
                <i class="fa-solid fa-plus mr-1"></i> Add User
            </a>
        </div>
    </x-slot>

    <div class="mx-auto space-y-5 max-w-7xl">

        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:8px;font-size:0.875rem;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:0.875rem;">
                {{ session('error') }}
            </div>
        @endif

        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.06);">

            <div style="display:flex;align-items:center;justify-content:space-between;padding:0 20px;border-bottom:1px solid #e2d9c8;background:#faf8f4;">
                <div style="display:flex;gap:4px;">
                    @foreach([['all','All',$counts['all']],['faculty','Faculty',$counts['faculty']],['head_of_department','Head of Dept',$counts['head_of_department']],['registrar','Registrar',$counts['registrar']]] as [$key,$label,$count])
                    <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => $key])) }}"
                       style="display:flex;align-items:center;gap:6px;padding:14px 16px;font-size:0.8rem;font-weight:600;text-decoration:none;border-bottom:2px solid {{ $role === $key ? '#c9a84c' : 'transparent' }};color:{{ $role === $key ? '#c9a84c' : '#6b5f4a' }};transition:color 0.15s;">
                        {{ $label }}
                        <span style="background:{{ $role === $key ? '#c9a84c' : '#e2d9c8' }};color:{{ $role === $key ? '#fff' : '#8a7a60' }};padding:1px 7px;border-radius:20px;font-size:0.7rem;font-weight:700;">{{ $count }}</span>
                    </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="role" value="{{ $role }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email..."
                        style="border:1px solid #d4c9b4;border-radius:8px;padding:7px 12px;font-size:0.8rem;width:220px;outline:none;"
                        onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                    <button type="submit"
                        style="background:#c9a84c;color:#fff;padding:7px 14px;border-radius:8px;font-size:0.8rem;font-weight:600;border:none;cursor:pointer;">
                        Search
                    </button>
                    @if($search)
                    <a href="{{ route('admin.users.index', ['role' => $role]) }}"
                       style="font-size:0.78rem;color:#8a7a60;text-decoration:none;">Clear</a>
                    @endif
                </form>
            </div>

            @if($users->isEmpty())
                <div style="padding:48px 24px;text-align:center;color:#b8a88a;font-size:0.875rem;">
                    <i class="fa-solid fa-users" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                    No users found.
                </div>
            @else
            <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f5f0e8;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;">
                        <th style="padding:10px 20px;text-align:left;font-weight:700;">Name</th>
                        <th style="padding:10px 20px;text-align:left;font-weight:700;">Email</th>
                        <th style="padding:10px 20px;text-align:left;font-weight:700;">Role</th>
                        <th style="padding:10px 20px;text-align:left;font-weight:700;">Department</th>
                        <th style="padding:10px 20px;text-align:left;font-weight:700;">Status</th>
                        <th style="padding:10px 20px;text-align:left;font-weight:700;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    @php $userRole = $user->roles->first()->name ?? 'none'; @endphp
                    <tr style="border-top:1px solid #f0ebe0;" onmouseover="this.style.background='#faf8f4'" onmouseout="this.style.background='transparent'">
                        <td style="padding:14px 20px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#e8c96e,#c9a84c);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <span style="color:#fff;font-size:0.8rem;font-weight:700;">{{ strtoupper(substr($user->name,0,1)) }}</span>
                                </div>
                                <span style="font-weight:600;color:#1a1a2e;">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td style="padding:14px 20px;color:#6b5f4a;">{{ $user->email }}</td>
                        <td style="padding:14px 20px;">
                            @php
                                $roleColors = ['faculty'=>['#fef3c7','#92400e'],'head_of_department'=>['#ede9fe','#5b21b6'],'registrar'=>['#d1fae5','#065f46']];
                                $rc = $roleColors[$userRole] ?? ['#f3f4f6','#374151'];
                            @endphp
                            <span style="background:{{ $rc[0] }};color:{{ $rc[1] }};padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:700;">
                                {{ ucfirst($userRole) }}
                            </span>
                        </td>
                        <td style="padding:14px 20px;">
                            @if($user->department)
                                <span style="font-size:0.8rem;color:#1a1a2e;font-weight:500;">{{ $user->department->code }}</span>
                                <div style="font-size:0.7rem;color:#8a7a60;">{{ $user->department->name }}</div>
                            @elseif(in_array($userRole, ['faculty','head_of_department']))
                                <span style="font-size:0.75rem;color:#b8a88a;font-style:italic;">Unassigned</span>
                            @else
                                <span style="font-size:0.75rem;color:#d4c9b4;">—</span>
                            @endif
                        </td>
                        <td style="padding:14px 20px;">
                            @php
                                $statusColors = ['active'=>['#d1fae5','#065f46'],'pending'=>['#fef3c7','#92400e'],'inactive'=>['#fee2e2','#991b1b']];
                                $sc = $statusColors[$user->status] ?? ['#f3f4f6','#374151'];
                            @endphp
                            <span style="background:{{ $sc[0] }};color:{{ $sc[1] }};padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:700;">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td style="padding:14px 20px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                @if($user->status === 'pending')
                                    <form action="{{ route('admin.users.approve', $user) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" style="font-size:0.78rem;color:#059669;font-weight:600;background:none;border:none;cursor:pointer;padding:0;"
                                            onmouseover="this.style.color='#047857'" onmouseout="this.style.color='#059669'">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.users.reject', $user) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" style="font-size:0.78rem;color:#dc2626;font-weight:600;background:none;border:none;cursor:pointer;padding:0;"
                                            onmouseover="this.style.color='#b91c1c'" onmouseout="this.style.color='#dc2626'">Reject</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   style="font-size:0.78rem;color:#c9a84c;font-weight:600;text-decoration:none;"
                                   onmouseover="this.style.color='#a8872e'" onmouseout="this.style.color='#c9a84c'">Edit</a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('Delete {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="font-size:0.78rem;color:#dc2626;font-weight:600;background:none;border:none;cursor:pointer;padding:0;"
                                        onmouseover="this.style.color='#b91c1c'" onmouseout="this.style.color='#dc2626'">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:16px 20px;border-top:1px solid #f0ebe0;">
                {{ $users->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
