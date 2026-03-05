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

            {{-- Student Search --}}
            <div class="mb-6">
                <h3 class="mb-3 text-xs font-semibold tracking-wider text-gray-400 uppercase">Generate COG / TOR</h3>
                <div class="p-5 bg-white rounded-lg shadow">
                    <form method="GET" action="{{ route('registrar.dashboard') }}" class="flex gap-3">
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               placeholder="Search by student name or student number..."
                               class="flex-1 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <button type="submit"
                                class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            Search
                        </button>
                        @if($search)
                            <a href="{{ route('registrar.dashboard') }}"
                               class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Clear
                            </a>
                        @endif
                    </form>

                    @if($search && $students->isEmpty())
                        <p class="mt-4 text-sm text-gray-400">No students found for "{{ $search }}".</p>
                    @endif

                    @if($students->isNotEmpty())
                        <div class="grid grid-cols-1 gap-3 mt-4 md:grid-cols-2">
                            @foreach($students as $student)
                                <a href="{{ route('registrar.students.profile', $student) }}"
                                   class="flex items-center gap-4 p-4 transition border border-gray-200 rounded-lg hover:border-indigo-400 hover:bg-indigo-50">
                                    <div class="flex items-center justify-center w-10 h-10 font-bold text-indigo-700 bg-indigo-100 rounded-full">
                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800">{{ $student->getFullName() }}</div>
                                        <div class="text-xs text-gray-500">{{ $student->student_number }} &bull; {{ $student->course->name ?? 'N/A' }} &bull; Year {{ $student->year_level }}</div>
                                    </div>
                                    <span class="ml-auto text-xs font-medium px-2 py-0.5 rounded-full
                                        {{ $student->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
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
