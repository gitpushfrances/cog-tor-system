<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Registrar Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">Grade finalization and document generation</p>
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
                    <div class="mb-1 text-xs text-gray-500 uppercase">Pending Finalization</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_finalization'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Finalized Grades</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['finalized_grades'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">COG Generated</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['cog_generated'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">TOR Generated</div>
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['tor_generated'] }}</div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mb-6">
                <h3 class="mb-3 text-xs font-semibold tracking-wider text-gray-400 uppercase">Quick Actions</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <a href="{{ route('registrar.students') }}" class="p-5 transition bg-white border-l-4 border-indigo-500 rounded-lg shadow hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📄</span>
                            <div>
                                <div class="font-semibold text-gray-800">Generate COG / TOR</div>
                                <div class="text-xs text-gray-500">Select a student to generate documents</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Pending Finalization Table --}}
            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">
                        Grades Pending Finalization
                        @if($stats['pending_finalization'] > 0)
                            <span class="ml-2 px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                {{ $stats['pending_finalization'] }} pending
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
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Reviewed By</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Date Approved</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($pending_submissions as $submission)
                        <tr>
                            <td class="px-6 py-4 text-sm">{{ $submission->grade->enrollment->student->getFullName() }}</td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $submission->grade->enrollment->subject->code }}</td>
                            <td class="px-6 py-4 text-sm font-bold">{{ number_format($submission->grade->grade, 2) }}</td>
                            <td class="px-6 py-4 text-sm">{{ $submission->reviewedBy->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $submission->reviewed_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('registrar.submissions.finalize', $submission) }}"
                                      onsubmit="return confirm('Finalize this grade? This cannot be undone.')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white bg-green-600 rounded hover:bg-green-700">
                                        Finalize
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                ✅ No pending finalizations.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
