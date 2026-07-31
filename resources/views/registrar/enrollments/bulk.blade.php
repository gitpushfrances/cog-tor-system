<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Bulk Enrollment</h2>
        <p class="mt-1 text-sm text-gray-500">Enroll multiple students into a subject at once</p>
    </x-slot>

    <div class="mx-auto space-y-6 max-w-7xl">

        {{-- Tabs --}}
        <div class="flex gap-2 border-b border-gray-200">
            <a href="{{ route('registrar.enrollments.index') }}" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">Enroll Individually</a>
            <span class="px-4 py-2 text-sm font-medium border-b-2" style="color:#c9a84c;border-color:#c9a84c;">Bulk Enroll</span>
        </div>

        {{-- Active Semester Banner --}}
        @if($activeSemester)
            <div class="px-4 py-3 text-sm border rounded-lg bg-amber-50 border-amber-200 text-amber-800">
                <i class="mr-1 fa-solid fa-calendar-check"></i>
                Active Semester: <strong>{{ $activeSemester->semester_name }} — {{ $activeSemester->schoolYear->year_code }}</strong>
            </div>
        @else
            <div class="px-4 py-3 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
                <i class="mr-1 fa-solid fa-triangle-exclamation"></i>
                No active semester set. Contact Admin to set an active semester before enrolling.
            </div>
        @endif

        @if($activeSemester)
        {{-- Step 1 + Filters --}}
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
            <form method="GET" action="{{ route('registrar.enrollments.bulk-create') }}" class="space-y-4">
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600">Step 1 — Subject</label>
                    <select name="subject_id" onchange="this.form.submit()" style="max-width:460px;"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="">Select a subject...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ (string) $subjectId === (string) $subject->id ? 'selected' : '' }}>
                                {{ $subject->code }} — {{ $subject->name }} ({{ $subject->units }} units, Yr {{ $subject->year_level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($selectedSubject)
                <div class="grid grid-cols-1 gap-3 pt-4 border-t border-gray-100 sm:grid-cols-4">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">Course</label>
                        <select name="course_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ (string) $courseId === (string) $course->id ? 'selected' : '' }}>{{ $course->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">Year Level</label>
                        <select name="year_level" onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <option value="">All Years</option>
                            @for($y = 1; $y <= 5; $y++)
                                <option value="{{ $y }}" {{ (string) $yearLevel === (string) $y ? 'selected' : '' }}>Year {{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block mb-1 text-xs font-medium text-gray-600">Search</label>
                        <div class="flex gap-2">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Name or student number..."
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-lg hover:bg-gray-800">Filter</button>
                        </div>
                    </div>
                </div>
                @endif
            </form>
        </div>

        {{-- Step 2 — Review List --}}
        @if($selectedSubject)
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">
                    Step 2 — Review Students for {{ $selectedSubject->code }} — {{ $selectedSubject->name }}
                </h3>
            </div>

            @if($tooMany)
                <div class="px-6 py-12 text-sm text-center text-gray-500">
                    <i class="block mb-2 text-2xl fa-solid fa-triangle-exclamation" style="color:#c9a84c;"></i>
                    Too many students found for these filters (200+). Please narrow by Course or Year Level before continuing.
                </div>
            @elseif($students->isEmpty())
                <div class="px-6 py-12 text-sm text-center text-gray-400">
                    <i class="block mb-2 text-2xl fa-solid fa-users"></i>
                    No students match your filters.
                </div>
            @else
                <form method="POST" action="{{ route('registrar.enrollments.bulk-store') }}" id="bulk-enroll-form">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">

                    {{-- Live Summary --}}
                    <div class="grid grid-cols-2 gap-3 px-6 py-4 border-b border-gray-100 sm:grid-cols-4">
                        <div class="p-3 text-center rounded-lg bg-gray-50">
                            <div class="text-lg font-bold text-gray-700">{{ $students->count() }}</div>
                            <div class="text-xs text-gray-500">Found</div>
                        </div>
                        <div class="p-3 text-center rounded-lg" style="background:#fef3e2;">
                            <div class="text-lg font-bold" style="color:#92610c;" id="summary-selected">0</div>
                            <div class="text-xs" style="color:#92610c;">Selected</div>
                        </div>
                        <div class="p-3 text-center rounded-lg bg-blue-50">
                            <div class="text-lg font-bold text-blue-700">{{ count($enrolledStudentIds) }}</div>
                            <div class="text-xs text-blue-600">Already Enrolled</div>
                        </div>
                        <div class="flex items-center justify-center gap-2 p-3 text-center rounded-lg bg-gray-50">
                            <button type="button" onclick="selectAll()" class="text-xs font-medium hover:underline" style="color:#c9a84c;">Select All</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" onclick="clearAll()" class="text-xs font-medium text-gray-500 hover:underline">Clear All</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-xs tracking-wider text-gray-500 uppercase bg-gray-50">
                                <tr>
                                    <th class="w-10 px-6 py-3 text-left"></th>
                                    <th class="px-6 py-3 text-left">Student No.</th>
                                    <th class="px-6 py-3 text-left">Name</th>
                                    <th class="px-6 py-3 text-left">Course</th>
                                    <th class="px-6 py-3 text-left">Year</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($students as $student)
                                    @php
                                        $isEnrolled = in_array($student->id, $enrolledStudentIds);
                                        $isCrossCourse = $student->course_id !== $selectedSubject->course_id;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-3">
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                                class="student-checkbox" onchange="updateSummary()"
                                                {{ $isEnrolled ? 'disabled' : 'checked' }}>
                                        </td>
                                        <td class="px-6 py-3 text-gray-600">{{ $student->student_number }}</td>
                                        <td class="px-6 py-3 font-medium text-gray-800">{{ $student->getFullName() }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ $student->course->code ?? '—' }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ $student->year_level }}</td>
                                        <td class="px-6 py-3">
                                            @if($isEnrolled)
                                                <span class="px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Already Enrolled</span>
                                            @elseif($isCrossCourse)
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full" style="background:#fef3e2;color:#92610c;">Different Course</span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-medium text-green-700 bg-green-100 rounded-full">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100">
                        <button type="button" onclick="confirmBulkEnroll()"
                            style="background:#c9a84c;color:#fff;padding:10px 24px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;transition:background 0.15s;"
                            onmouseover="this.style.background='#a8872e'"
                            onmouseout="this.style.background='#c9a84c'">
                            <i class="mr-1 fa-solid fa-users"></i> Enroll Selected
                        </button>
                    </div>
                </form>
            @endif
        </div>
        @endif
        @endif

    </div>

@push('scripts')
<script>
    function updateSummary() {
        const checked = document.querySelectorAll('.student-checkbox:checked').length;
        const el = document.getElementById('summary-selected');
        if (el) el.textContent = checked;
    }

    function selectAll() {
        document.querySelectorAll('.student-checkbox:not(:disabled)').forEach(cb => cb.checked = true);
        updateSummary();
    }

    function clearAll() {
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
        updateSummary();
    }

    document.addEventListener('DOMContentLoaded', updateSummary);

    function confirmBulkEnroll() {
        const form = document.getElementById('bulk-enroll-form');
        const selected = document.querySelectorAll('.student-checkbox:checked').length;

        if (selected === 0) {
            Swal.fire({ icon: 'warning', title: 'No Students Selected', text: 'Please select at least one student to enroll.' });
            return;
        }

        Swal.fire({
            title: 'Confirm Bulk Enrollment',
            html: `<span style="font-size:0.9rem;color:#374151;">You are about to enroll <strong>${selected}</strong> student(s) into <strong>{{ $selectedSubject->code ?? '' }}</strong> for <strong>{{ $activeSemester->semester_name ?? '' }}</strong>. Continue?</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c9a84c',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Enroll',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    @if(session('bulk_report'))
    document.addEventListener('DOMContentLoaded', function () {
        const report = @json(session('bulk_report'));
        let skippedHtml = '';
        if (report.skipped && report.skipped.length > 0) {
            skippedHtml = '<div style="margin-top:12px;text-align:left;max-height:150px;overflow-y:auto;font-size:0.8rem;color:#6b7280;border-top:1px solid #e5e7eb;padding-top:8px;">' +
                '<strong>Skipped:</strong><ul style="margin-top:4px;padding-left:18px;">' +
                report.skipped.map(s => `<li>${s}</li>`).join('') + '</ul></div>';
        }
        Swal.fire({
            icon: report.enrolled > 0 ? 'success' : 'info',
            title: 'Bulk Enrollment Result',
            html: `<span style="font-size:0.9rem;color:#374151;"><strong>${report.enrolled}</strong> enrolled into <strong>${report.subject}</strong>.` +
                  (report.skipped.length > 0 ? ` <strong>${report.skipped.length}</strong> skipped.` : '') +
                  `</span>` + skippedHtml,
            confirmButtonColor: '#c9a84c',
        });
    });
    @endif
</script>
@endpush
</x-app-layout>
