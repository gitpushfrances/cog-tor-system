<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Registrar Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">Grade finalization and document generation</p>
    </x-slot>

    <div class="py-8">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="flex items-center gap-3 px-4 py-3 mb-6 text-green-800 bg-green-50 border border-green-200 rounded-lg">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 px-4 py-3 mb-6 text-red-800 bg-red-50 border border-red-200 rounded-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            {{-- Stats Row --}}
            <div class="grid grid-cols-2 gap-4 mb-8 md:grid-cols-4">
                <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Pending</span>
                        <i class="fa-regular fa-clock text-amber-400"></i>
                    </div>
                    <div class="text-3xl font-bold text-amber-500">{{ $stats['pending_finalization'] }}</div>
                    <div class="mt-1 text-xs text-gray-400">Awaiting finalization</div>
                </div>
                <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Finalized</span>
                        <i class="fa-solid fa-lock text-emerald-400"></i>
                    </div>
                    <div class="text-3xl font-bold text-emerald-500">{{ $stats['finalized_grades'] }}</div>
                    <div class="mt-1 text-xs text-gray-400">Grades locked</div>
                </div>
                <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase">COG</span>
                        <i class="fa-regular fa-file-lines text-blue-400"></i>
                    </div>
                    <div class="text-3xl font-bold text-blue-500">{{ $stats['cog_generated'] }}</div>
                    <div class="mt-1 text-xs text-gray-400">Generated</div>
                </div>
                <div class="p-5 bg-white border border-gray-100 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase">TOR</span>
                        <i class="fa-regular fa-file-pdf text-indigo-400"></i>
                    </div>
                    <div class="text-3xl font-bold text-indigo-500">{{ $stats['tor_generated'] }}</div>
                    <div class="mt-1 text-xs text-gray-400">Generated</div>
                </div>
            </div>

            {{-- Two Column Layout --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- LEFT: Student Directory --}}
                <div class="lg:col-span-2">
                    <div class="bg-white border border-gray-100 rounded-xl shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-800">Student Directory</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">Click a student to generate COG or TOR</p>
                                </div>
                                <form method="GET" action="{{ route('registrar.dashboard') }}" class="flex gap-2">
                                    <div class="relative">
                                        <input type="text" name="search" value="{{ $search ?? '' }}"
                                               placeholder="Search name or ID..."
                                               class="w-56 pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                                    </div>
                                    <button type="submit"
                                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                        Search
                                    </button>
                                    @if($search ?? false)
                                        <a href="{{ route('registrar.dashboard') }}"
                                           class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                            Clear
                                        </a>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-50">
                            @forelse($students as $student)
                                <a href="{{ route('registrar.students.profile', $student) }}"
                                   class="flex items-center gap-4 px-6 py-4 hover:bg-indigo-50 transition-colors group">
                                    <div class="flex items-center justify-center w-9 h-9 text-sm font-bold text-indigo-600 bg-indigo-100 rounded-full flex-shrink-0 group-hover:bg-indigo-200 transition-colors">
                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-gray-800 truncate">{{ $student->getFullName() }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ $student->student_number }} &bull; {{ $student->course->name ?? 'N/A' }} &bull; Year {{ $student->year_level }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                            {{ $student->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ ucfirst($student->status) }}
                                        </span>
                                        <i class="fa-solid fa-chevron-right text-gray-300 group-hover:text-indigo-400 transition-colors text-xs"></i>
                                    </div>
                                </a>
                            @empty
                                <div class="px-6 py-12 text-center">
                                    <i class="fa-solid fa-magnifying-glass text-4xl text-gray-200 mb-3"></i>
                                    <p class="text-sm text-gray-400">No students found{{ $search ? ' for "' . $search . '"' : '' }}.</p>
                                </div>
                            @endforelse
                        </div>

                        @if($students->hasPages())
                            <div class="px-6 py-4 border-t border-gray-100">
                                {{ $students->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- RIGHT: Finalization Queue --}}
                <div class="lg:col-span-1">
                    <div class="bg-white border border-gray-100 rounded-xl shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-800">Finalization Queue</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">Dean-approved grades ready to lock</p>
                                </div>
                                @if($stats['pending_finalization'] > 0)
                                    <span class="px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-700 rounded-full">
                                        {{ $stats['pending_finalization'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="divide-y divide-gray-50">
                            @forelse($pending_submissions as $submission)
                                <div class="px-6 py-4">
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800 leading-tight">
                                                {{ $submission->grade->enrollment->student->getFullName() }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                {{ $submission->grade->enrollment->subject->code }}
                                            </div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-700 flex-shrink-0">
                                            {{ number_format($submission->grade->grade, 2) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-400">
                                            {{ $submission->reviewed_at->format('M d, Y') }}
                                        </span>
                                        <form method="POST" action="{{ route('registrar.submissions.finalize', $submission) }}"
                                              id="finalizeForm{{ $submission->id }}">
                                            @csrf
                                            <button type="button"
                                                    onclick="confirmFinalize({{ $submission->id }})"
                                                    class="px-3 py-1 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                                                <i class="fa-solid fa-lock mr-1"></i> Finalize
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-10 text-center">
                                    <i class="fa-solid fa-circle-check text-4xl text-emerald-200 mb-3"></i>
                                    <p class="text-sm text-gray-400">All caught up.</p>
                                    <p class="text-xs text-gray-300 mt-1">No pending finalizations.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function confirmFinalize(id) {
            Swal.fire({
                title: 'Finalize Grade?',
                text: 'This will permanently lock the grade. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Finalize',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('finalizeForm' + id).submit();
                }
            });
        }
    </script>
</x-app-layout>
