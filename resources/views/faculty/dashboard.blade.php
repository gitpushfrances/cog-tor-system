<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Faculty Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-4">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Assigned Subjects</div>
                        <div class="text-3xl font-bold">{{ $stats['assigned_subjects'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Total Grades</div>
                        <div class="text-3xl font-bold">{{ $stats['total_grades'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Pending Grades</div>
                        <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_grades'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Approved Grades</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['approved_grades'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Grades -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold">Recent Grades Submitted</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Subject</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Grade</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($recent_grades as $grade)
                                <tr>
                                    <td class="px-6 py-4">{{ $grade->enrollment->student->getFullName() }}</td>
                                    <td class="px-6 py-4">{{ $grade->enrollment->subject->subject_code }}</td>
                                    <td class="px-6 py-4">{{ number_format($grade->grade, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $grade->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $grade->status === 'approved_by_dean' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $grade->status === 'finalized' ? 'bg-blue-100 text-blue-800' : '' }}">
                                            {{ ucfirst(str_replace('_', ' ', $grade->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No grades submitted yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
