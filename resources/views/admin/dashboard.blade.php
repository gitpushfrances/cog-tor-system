<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Admin Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ $stats['current_school_year'] ? 'Active: ' . $stats['current_school_year']->year : 'No active school year set' }}
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

            {{-- Navigation Sections --}}

            {{-- USER MANAGEMENT --}}
            <div class="mb-6">
                <h3 class="mb-3 text-xs font-semibold tracking-wider text-gray-400 uppercase">User Management</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <a href="{{ route('admin.users.index') }}" class="p-5 transition bg-white border-l-4 border-blue-500 rounded-lg shadow hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">👥</span>
                            <div>
                                <div class="font-semibold text-gray-800">Users</div>
                                <div class="text-xs text-gray-500">Manage faculty, heads of department, registrar accounts</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- ACADEMIC SETUP --}}
            <div class="mb-6">
                <h3 class="mb-3 text-xs font-semibold tracking-wider text-gray-400 uppercase">Academic Setup</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <a href="{{ route('admin.departments.index') }}" class="p-5 transition bg-white border-l-4 border-purple-500 rounded-lg shadow hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🏢</span>
                            <div>
                                <div class="font-semibold text-gray-800">Departments</div>
                                <div class="text-xs text-gray-500">Manage academic departments</div>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('admin.courses.index') }}" class="p-5 transition bg-white border-l-4 border-indigo-500 rounded-lg shadow hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📚</span>
                            <div>
                                <div class="font-semibold text-gray-800">Courses</div>
                                <div class="text-xs text-gray-500">Manage degree programs</div>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('admin.subjects.index') }}" class="p-5 transition bg-white border-l-4 rounded-lg shadow hover:shadow-md border-cyan-500">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📖</span>
                            <div>
                                <div class="font-semibold text-gray-800">Subjects</div>
                                <div class="text-xs text-gray-500">Manage subjects and assign faculty</div>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('admin.school-years.index') }}" class="p-5 transition bg-white border-l-4 border-green-500 rounded-lg shadow hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📅</span>
                            <div>
                                <div class="font-semibold text-gray-800">School Years</div>
                                <div class="text-xs text-gray-500">Set active school year</div>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('admin.semesters.index') }}" class="p-5 transition bg-white border-l-4 border-teal-500 rounded-lg shadow hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🗓️</span>
                            <div>
                                <div class="font-semibold text-gray-800">Semesters</div>
                                <div class="text-xs text-gray-500">Set active semester</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Recent Users Table --}}
            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Recent Users</h3>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">View all →</a>
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
                                    {{ $user->roles->first()->name ?? 'No Role' }}
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
