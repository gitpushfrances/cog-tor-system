<x-app-layout>
    <x-slot name="header">
        <p class="mb-1 text-xs font-medium tracking-widest text-blue-700 uppercase">Eastern Samar State University - Guiuan Campus</p>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Admin Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">
            @if($stats['current_school_year'])
                Active: SY {{ $stats['current_school_year']->year_code }}{{ $activeSemester ? ' · ' . $activeSemester->semester_name : ' · no active semester' }}
            @else
                No active school year set
            @endif
        </p>
    </x-slot>

    <div class="py-6">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Total Users</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['total_users'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Active Users</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['active_users'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Pending Users</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_users'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Total Students</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['total_students'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Active Students</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['active_students'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Total Subjects</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['total_subjects'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Departments</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['total_departments'] }}</div>
                </div>
            </div>

            {{-- Recent Activity (from Admin → Activity Log) --}}
            <div class="mb-6 overflow-hidden bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Recent Activity</h3>
                    <a href="{{ route('admin.activity-log.index') }}" class="text-sm text-blue-600 hover:underline">View all <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                @forelse($recentActivity as $activity)
                    <div style="display:flex;gap:16px;align-items:baseline;padding:10px 24px;border-top:1px solid #f3f4f6;font-size:0.85rem;">
                        <div style="width:110px;flex-shrink:0;color:#6b7280;font-size:0.78rem;" title="{{ $activity->created_at->format('M d, Y g:i:s A') }}">
                            {{ $activity->created_at->isToday() ? $activity->created_at->format('g:i A') : $activity->created_at->format('M d, g:i A') }}
                        </div>
                        <div style="width:170px;flex-shrink:0;color:#111827;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $activity->causer?->name ?? 'System' }}
                        </div>
                        @php
                            // Model events are logged as "created"/"updated"/"deleted": add what was changed.
                            $what  = in_array($activity->description, ['created', 'updated', 'deleted', 'restored'], true)
                                ? ucfirst($activity->description) . ' ' . str_replace('_', ' ', $activity->log_name)
                                : $activity->description;
                            $label = \App\Http\Controllers\Admin\ActivityLogController::recordLabel($activity);
                        @endphp
                        <div style="flex:1;min-width:0;color:#374151;">
                            {{ $what }}
                            @if($label !== '—')
                                <span style="color:#6b7280;">· {{ $label }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding:24px;text-align:center;color:#9ca3af;font-size:0.85rem;">No activity recorded yet.</div>
                @endforelse
            </div>

            {{-- Recent Users Table --}}
            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Recent Users</h3>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">View all <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recent_users as $user)
                        <tr>
                            <td class="px-6 py-4 text-sm">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs text-blue-800 bg-blue-100 rounded-full">
                                    {{ ucwords(str_replace('_', ' ', $user->roles->first()->name ?? 'No Role')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $user->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $user->status === 'inactive' ? 'bg-gray-100 text-gray-600' : '' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
