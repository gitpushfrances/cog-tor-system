<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Dean Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">Grade review and approval management</p>
    </x-slot>

    <div class="py-6">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Total Students</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['total_students'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Active Enrollments</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['active_enrollments'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Pending Review</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_grades'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Approved Grades</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved_grades'] }}</div>
                </div>
            </div>

            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">
                        Pending Grade Submissions
                        @if($stats['pending_grades'] > 0)
                            <span class="ml-2 px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                {{ $stats['pending_grades'] }} pending
                            </span>
                        @endif
                    </h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Subject</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Grade</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Submitted By</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($pending_submissions as $submission)
                        <tr>
                            <td class="px-6 py-4 text-sm">{{ $submission->grade->enrollment->student->getFullName() }}</td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $submission->grade->enrollment->subject->code }}</td>
                            <td class="px-6 py-4 text-sm font-bold">{{ number_format($submission->grade->grade, 2) }}</td>
                            <td class="px-6 py-4 text-sm">{{ $submission->submittedBy->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $submission->submitted_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('dean.submissions.review', $submission) }}"
                                   class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
                                    Review
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                ✅ No pending submissions. All caught up.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
