<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('faculty.subjects') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Subjects</a>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $subject->code }} - {{ $subject->name }}</h2>
                <p class="text-sm text-gray-500">Year {{ $subject->year_level }} &bull; {{ $subject->semester }} Semester &bull; {{ $subject->units }} units</p>
            </div>

            @php
                $subjectStatus = $latestSubmission?->grade?->status ?? 'saved';
                $isPending     = $subjectStatus === 'pending_dean_review';
                $isRejected    = $subjectStatus === 'rejected';
                $isApproved    = $subjectStatus === 'approved_by_dean';
                $isFinalized   = $subjectStatus === 'finalized';
                $isLocked      = $isPending || $isApproved || $isFinalized;
            @endphp

            <div class="flex flex-wrap gap-2">
                @if(!$isLocked)
                    {{-- Download template --}}
                    <a href="{{ route('faculty.subjects.grades.template', $subject) }}"
                       class="px-4 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded hover:bg-gray-200">
                        ↓ Download Grade Template
                    </a>

                    {{-- Upload grades --}}
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                            class="px-4 py-2 text-sm text-white bg-yellow-500 rounded hover:bg-yellow-600">
                        ↑ Upload Grades (Excel)
                    </button>
                @endif

                @if(!$isLocked && !$isRejected)
                    {{-- Submit to Dean --}}
                    <form action="{{ route('faculty.subjects.grades.submit', $subject) }}" method="POST" id="submitForm">
                        @csrf
                        <button type="button" onclick="confirmSubmit()"
                                class="px-4 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700">
                            ✓ Submit to Dean
                        </button>
                    </form>
                @endif

                @if($isRejected)
                    {{-- Resubmit trigger --}}
                    <button onclick="document.getElementById('resubmitModal').classList.remove('hidden')"
                            class="px-4 py-2 text-sm text-white bg-orange-500 rounded hover:bg-orange-600">
                        ↩ Update & Resubmit
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="px-4 py-6 mx-auto max-w-7xl">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="px-4 py-3 mb-4 text-yellow-800 bg-yellow-100 rounded">{{ session('warning') }}</div>
        @endif
        @if(session('import_errors'))
            <div class="px-4 py-3 mb-4 text-red-800 border border-red-200 rounded bg-red-50">
                <p class="mb-1 font-semibold">Upload Errors (these rows were skipped):</p>
                <ul class="text-sm list-disc list-inside">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Rejection Banner — shown prominently when Dean rejects --}}
        @if($isRejected && $latestSubmission?->dean_remarks)
            <div class="px-5 py-4 mb-5 border border-red-300 rounded-lg bg-red-50">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 text-xs font-bold bg-red-200 text-red-800 rounded-full">REJECTED</span>
                    <span class="text-sm font-semibold text-red-800">Dean's Remarks:</span>
                </div>
                <p class="text-sm text-red-900">{{ $latestSubmission->dean_remarks }}</p>
                <p class="mt-2 text-xs text-red-600">Please correct the grades below, then click "Update & Resubmit".</p>
            </div>
        @endif

        {{-- Pending lock notice --}}
        @if($isPending)
            <div class="px-5 py-4 mb-5 border border-yellow-300 rounded-lg bg-yellow-50">
                <span class="text-sm font-semibold text-yellow-800">⏳ Awaiting Dean Review</span>
                <p class="mt-1 text-xs text-yellow-700">Grades are locked while pending Dean approval. You will be notified if rejected.</p>
            </div>
        @endif

        {{-- Approved notice --}}
        @if($isApproved)
            <div class="px-5 py-4 mb-5 border border-green-300 rounded-lg bg-green-50">
                <span class="text-sm font-semibold text-green-800">✅ Approved by Dean</span>
                <p class="mt-1 text-xs text-green-700">Grades have been approved and forwarded to the Registrar for finalization.</p>
            </div>
        @endif

        {{-- Finalized notice --}}
        @if($isFinalized)
            <div class="px-5 py-4 mb-5 border border-blue-300 rounded-lg bg-blue-50">
                <span class="text-sm font-semibold text-blue-800">🔒 Finalized</span>
                <p class="mt-1 text-xs text-blue-700">Grades have been permanently finalized by the Registrar.</p>
            </div>
        @endif

        {{-- Grades Table --}}
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <form action="{{ route('faculty.subjects.grades.store', $subject) }}" method="POST">
                @csrf
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student No.</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Percentage (%)</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Grade</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Remarks</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                            @if(!$isLocked)
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($enrollments as $i => $enrollment)
                        <tr class="{{ $enrollment->grade?->grade == 5.00 ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 text-sm font-medium">
                                {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                                <input type="hidden" name="grades[{{ $i }}][enrollment_id]" value="{{ $enrollment->id }}">
                            </td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $enrollment->student->student_number }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($isLocked || ($enrollment->grade && !in_array($enrollment->grade->status, ['saved', 'rejected'])))
                                    {{ $enrollment->grade?->percentage ?? '—' }}%
                                @else
                                    <input type="number" name="grades[{{ $i }}][percentage]"
                                           value="{{ $enrollment->grade->percentage ?? '' }}"
                                           min="0" max="100" step="0.01"
                                           class="w-24 text-sm border-gray-300 rounded">
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-bold">
                                {{ $enrollment->grade ? number_format($enrollment->grade->grade, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($isLocked || ($enrollment->grade && !in_array($enrollment->grade->status, ['saved', 'rejected'])))
                                    {{ $enrollment->grade?->remarks ?? '—' }}
                                @else
                                    <input type="text" name="grades[{{ $i }}][remarks]"
                                           value="{{ $enrollment->grade->remarks ?? '' }}"
                                           class="w-32 text-sm border-gray-300 rounded">
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($enrollment->grade)
                                    @php $s = $enrollment->grade->status; @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $s === 'saved'               ? 'bg-gray-100 text-gray-700' : '' }}
                                        {{ $s === 'pending_dean_review' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $s === 'approved_by_dean'    ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $s === 'rejected'            ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $s === 'finalized'           ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Not encoded</span>
                                @endif
                            </td>
                            @if(!$isLocked)
                            <td class="px-6 py-4 text-sm">
                                @if($enrollment->grade && in_array($enrollment->grade->status, ['saved', 'rejected']))
                                    <a href="{{ route('faculty.subjects.grades.edit', [$subject, $enrollment->grade]) }}"
                                       class="text-blue-600 hover:underline">Edit</a>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-400">No students enrolled in this subject.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Save button — only when not locked --}}
                @if(!$isLocked)
                <div class="px-6 py-4 border-t bg-gray-50">
                    <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Save Grades
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Upload Grades Modal --}}
    <div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
            <h3 class="mb-1 text-lg font-semibold text-gray-800">Upload Grades from Excel</h3>
            <p class="mb-4 text-sm text-gray-500">Upload a filled grade template. Only saved or rejected grades will be updated.</p>
            <form action="{{ route('faculty.subjects.grades.upload', $subject) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Select File</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded">
                    <p class="mt-1 text-xs text-gray-400">Accepted: .xlsx, .xls, .csv — Max 2MB</p>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="document.getElementById('uploadModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-yellow-500 rounded hover:bg-yellow-600">
                        Upload & Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Resubmit Modal --}}
    <div id="resubmitModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
            <h3 class="mb-1 text-lg font-semibold text-gray-800">Resubmit Grades to Dean</h3>
            <p class="mb-4 text-sm text-gray-500">Explain what corrections you made. The Dean will see this note.</p>
            <form action="{{ route('faculty.subjects.grades.resubmit', $subject) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Your Remarks <span class="text-red-500">*</span></label>
                    <textarea name="faculty_remarks" rows="4" required
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-orange-400"
                              placeholder="Describe what was corrected..."></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="document.getElementById('resubmitModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmResubmit()"
                            class="px-4 py-2 text-sm text-white bg-orange-500 rounded hover:bg-orange-600">
                        Resubmit to Dean
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
        function confirmSubmit() {
            Swal.fire({
                title: 'Submit to Dean?',
                text: 'Submit all saved grades for {{ $subject->code }}? You will not be able to edit until Dean reviews.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Submit',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('submitForm').submit();
                }
            });
        }

        function confirmResubmit() {
            const remarks = document.querySelector('#resubmitModal textarea[name="faculty_remarks"]').value.trim();
            if (!remarks) {
                Swal.fire({
                    title: 'Remarks Required',
                    text: 'Please describe what you corrected before resubmitting.',
                    icon: 'warning',
                    confirmButtonColor: '#d97706'
                });
                return;
            }
            Swal.fire({
                title: 'Resubmit to Dean?',
                text: 'Resubmit corrected grades for {{ $subject->code }}?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Resubmit',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('#resubmitModal form').submit();
                }
            });
        }
    </script>
</x-app-layout>
