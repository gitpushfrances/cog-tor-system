<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dean Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-4">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Total Students</div>
                        <div class="text-3xl font-bold">{{ $stats['total_students'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Active Enrollments</div>
                        <div class="text-3xl font-bold">{{ $stats['active_enrollments'] }}</div>
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

            <!-- Pending Grade Submissions -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold">Pending Grade Submissions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Subject</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Submitted By</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($pending_submissions as $submission)
                                <tr>
                                    <td class="px-6 py-4">{{ $submission->grade->enrollment->student->getFullName() }}</td>
                                    <td class="px-6 py-4">{{ $submission->grade->enrollment->subject->subject_code }}</td>
                                    <td class="px-6 py-4">{{ $submission->submittedBy->name }}</td>
                                    <td class="px-6 py-4">{{ $submission->submitted_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No pending submissions</td>
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
