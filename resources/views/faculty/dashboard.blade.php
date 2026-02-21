<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Faculty Dashboard</h2>
    </x-slot>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm text-gray-500">Assigned Subjects</div>
                    <div class="text-3xl font-bold">{{ $stats['assigned_subjects'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm text-gray-500">Total Grades Encoded</div>
                    <div class="text-3xl font-bold">{{ $stats['total_grades'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm text-gray-500">Pending Approval</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_grades'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm text-gray-500">Approved by Dean</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved_grades'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <a href="{{ route('faculty.subjects') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                    <h3 class="font-semibold text-lg mb-1">í³š My Subjects</h3>
                    <p class="text-sm text-gray-500">View assigned subjects and encode grades</p>
                </a>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="font-semibold text-lg mb-4">Recent Grades</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recent_grades as $grade)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $grade->enrollment->student->last_name }}, {{ $grade->enrollment->student->first_name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $grade->enrollment->subject->code }}</td>
                                <td class="px-6 py-4 text-sm font-bold">{{ number_format($grade->grade, 2) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $grade->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $grade->status === 'approved_by_dean' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $grade->status === 'finalized' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $grade->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-gray-400">No grades encoded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
