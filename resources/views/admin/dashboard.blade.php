<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Quick Navigation -->
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                <a href="{{ route('admin.users.index') }}" class="p-6 transition bg-white rounded-lg shadow hover:shadow-lg">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">👥 User Management</h3>
                    <p class="text-sm text-gray-600">Manage faculty, dean, and registrar accounts</p>
                </a>
                <a href="{{ route('admin.departments.index') }}" class="p-6 transition bg-white rounded-lg shadow hover:shadow-lg">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">🏢 Departments</h3>
                    <p class="text-sm text-gray-600">Manage academic departments</p>
                </a>
                <a href="{{ route('admin.courses.index') }}" class="p-6 transition bg-white rounded-lg shadow hover:shadow-lg">
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">📚 Courses</h3>
                    <p class="text-sm text-gray-600">Manage degree programs</p>
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-4">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Total Users</div>
                        <div class="text-3xl font-bold">{{ $stats['total_users'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Active Users</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['active_users'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Pending Users</div>
                        <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_users'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Total Students</div>
                        <div class="text-3xl font-bold">{{ $stats['total_students'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold">Recent Users</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
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
                                    <td class="px-6 py-4">{{ $user->name }}</td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs text-blue-800 bg-blue-100 rounded-full">
                                            {{ $user->roles->first()->name ?? 'No Role' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
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
        </div>
    </div>
</x-app-layout>
