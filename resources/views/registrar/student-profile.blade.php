<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('registrar.dashboard') }}" class="inline-block mb-2 text-sm text-indigo-600 hover:underline">← Back to Dashboard</a>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $student->getFullName() }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $student->student_number }} &bull;
                    {{ $student->course->name ?? 'N/A' }} &bull;
                    Year {{ $student->year_level }} &bull;
                    <span class="capitalize">{{ $student->status }}</span>
                </p>
                @if($cumulativeGwa)
                    <p class="mt-1 text-sm font-semibold text-indigo-700">Cumulative GWA: {{ number_format($cumulativeGwa, 1) }}</p>
                @endif
            </div>

            {{-- TOR Button --}}
            <div>
                <form method="POST" action="{{ route('registrar.students.tor.generate', $student) }}" id="torForm">
                    @csrf
                    <button type="button" onclick="confirmTor()"
                            class="inline-flex items-center gap-2 px-5 py-2 mt-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <i class="mr-1 fa-regular fa-file-lines"></i> Generate TOR
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="px-4 py-6 mx-auto max-w-7xl">

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif

        @if($grouped->isEmpty())
            <div class="p-8 text-center text-gray-400 bg-white rounded-lg shadow">
                No finalized grades found for this student.
            </div>
        @else
            @foreach($grouped as $yearGroup)
                <div class="mb-8">
                    {{-- School Year Header --}}
                    <h3 class="mb-3 text-sm font-bold tracking-wider text-gray-500 uppercase">
                        School Year {{ $yearGroup['schoolYear']->year_code }}
                    </h3>

                    @foreach($yearGroup['semesters'] as $semGroup)
                        <div class="mb-4 overflow-hidden bg-white rounded-lg shadow">
                            {{-- Semester Header --}}
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                                <div>
                                    <span class="font-semibold text-gray-800">{{ $semGroup['semester']->semester_name }}</span>
                                    <span class="ml-3 text-sm text-gray-500">
                                        {{ $semGroup['enrollments']->count() }} subjects &bull;
                                        {{ $semGroup['totalUnits'] }} units &bull;
                                        GWA: <strong>{{ $semGroup['semesterGwa'] ? number_format($semGroup['semesterGwa'], 2) : 'N/A' }}</strong>
                                    </span>
                                </div>

                                {{-- COG + Undo Finalize Buttons --}}
                                <div class="flex items-center gap-2">
                                    @if($semGroup['cogRecord'] && $semGroup['cogRecord']->hasFile())
                                        <a href="{{ route('registrar.cog.download', $semGroup['cogRecord']) }}"
                                           class="px-3 py-1 text-xs font-semibold text-indigo-700 border border-indigo-300 rounded hover:bg-indigo-50">
                                            <i class="fa-solid fa-download mr-1"></i> Download COG
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('registrar.students.cog.generate', $student) }}"
                                          class="cogForm" data-semester="{{ $semGroup['semester']->semester_name }}">
                                        @csrf
                                        <input type="hidden" name="semester_id" value="{{ $semGroup['semester']->id }}">
                                        <button type="button"
                                                onclick="confirmCog(this)"
                                                class="px-3 py-1 text-xs font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700">
                                            Generate COG
                                        </button>
                                    </form>

                                    {{-- Undo Finalize --}}
                                    <form method="POST"
                                          action="{{ route('registrar.submissions.unfinalize-subject', $semGroup['semester']->id) }}"
                                          class="unfinalizeForm"
                                          data-semester="{{ $semGroup['semester']->semester_name }}">
                                        @csrf
                                        <button type="button"
                                                onclick="confirmUnfinalize(this)"
                                                class="px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded hover:bg-red-600">
                                            <i class="fa-solid fa-rotate-left mr-1"></i> Undo Finalize
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Grades Table --}}
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr class="text-xs font-medium text-left text-gray-400 uppercase">
                                        <th class="px-6 py-3">Subject Code</th>
                                        <th class="px-6 py-3">Subject Name</th>
                                        <th class="px-6 py-3">Units</th>
                                        <th class="px-6 py-3">Grade</th>
                                        <th class="px-6 py-3">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($semGroup['enrollments'] as $enrollment)
                                        <tr class="{{ $enrollment->grade->grade == 5.00 ? 'bg-red-50' : '' }}">
                                            <td class="px-6 py-3 font-mono text-sm">{{ $enrollment->subject->code }}</td>
                                            <td class="px-6 py-3 text-sm">{{ $enrollment->subject->name }}</td>
                                            <td class="px-6 py-3 text-sm">{{ $enrollment->subject->units }}</td>
                                            <td class="px-6 py-3 text-sm font-bold
                                                {{ $enrollment->grade->grade == 5.00 ? 'text-red-600' : 'text-gray-800' }}">
                                                {{ number_format($enrollment->grade->grade, 1) }}
                                            </td>
                                            <td class="px-6 py-3 text-sm text-gray-500">{{ $enrollment->grade->remarks ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
    <script>
        function confirmTor() {
            Swal.fire({
                title: 'Generate TOR?',
                text: 'Generate complete TOR for {{ $student->getFullName() }}? This includes all finalized semesters.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Generate TOR',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('torForm').submit();
                }
            });
        }

        function confirmCog(btn) {
            const form = btn.closest('form');
            const semName = form.dataset.semester;
            Swal.fire({
                title: 'Generate COG?',
                text: 'Generate Certificate of Grades for ' + semName + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Generate COG',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function confirmUnfinalize(btn) {
            const form = btn.closest('form');
            const semName = form.dataset.semester;
            Swal.fire({
                title: 'Undo Finalization?',
                html: `This will revert all finalized grades for <strong>${semName}</strong> back to approved status.<br><br>Faculty will <strong>not</strong> be able to edit them — only the registrar can re-finalize.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Undo Finalize',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
