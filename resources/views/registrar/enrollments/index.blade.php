<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Enrollment Management</h2>
        <p class="mt-1 text-sm text-gray-500">Enroll students into subjects for the active semester</p>
    </x-slot>

    <div class="mx-auto space-y-6 max-w-7xl">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
                {{ session('error') }}
            </div>
        @endif

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

        {{-- Enroll Form --}}
        @if($activeSemester)
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
            <h3 class="mb-4 text-sm font-semibold text-gray-700">Enroll a Student</h3>
            <form method="POST" action="{{ route('registrar.enrollments.store') }}" id="enroll-form" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-48">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Student</label>
                    <select name="student_id" id="student-select" required>
                        <option value="">Select student...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>{{ $student->student_number }} — {{ $student->getFullName() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-48">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Subject</label>
                    <select name="subject_id" id="subject-select" required>
                        <option value="">Select student first...</option>
                        @foreach($subjects as $subject)
                              <option value="{{ $subject->id }}"
                                  data-label="{{ $subject->code }} — {{ $subject->name }} ({{ $subject->units }} units, Yr {{ $subject->year_level }})"
                                  data-course="{{ $subject->course_id }}">
                                  {{ $subject->code }} — {{ $subject->name }} ({{ $subject->units }} units, Yr {{ $subject->year_level }})
                              </option>
                          @endforeach
                    </select>
                </div>

                <button type="submit"
                    style="background:#c9a84c;color:#fff;padding:8px 20px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;transition:background 0.15s;white-space:nowrap;"
                    onmouseover="this.style.background='#a8872e'"
                    onmouseout="this.style.background='#c9a84c'">
                    <i class="mr-1 fa-solid fa-plus"></i> Enroll
                </button>
            </form>
        </div>
        @endif

        {{-- Enrollments Table --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">
                    Enrollments
                    <span class="ml-2 text-xs font-normal text-gray-400">
                        {{ $grouped ? $grouped->flatten()->count() : $enrollments->total() }} total
                    </span>
                </h3>

                <form method="GET" action="{{ route('registrar.enrollments.index') }}" id="enrollment-filters" class="flex flex-wrap items-center gap-2">
                    <select name="date_filter" id="date-filter-select" onchange="handleDateFilterChange(this)"
                        class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>All Dates</option>
                        <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $dateFilter === 'week' ? 'selected' : '' }}>Past Week</option>
                        <option value="month" {{ $dateFilter === 'month' ? 'selected' : '' }}>Past Month</option>
                        <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>

                    <span id="custom-range-fields" class="flex items-center gap-1" style="{{ $dateFilter === 'custom' ? '' : 'display:none;' }}">
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                            class="px-2 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <span class="text-xs text-gray-400">to</span>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                            class="px-2 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <button type="submit"
                            class="px-3 py-1.5 text-xs font-medium text-white bg-gray-700 rounded-lg hover:bg-gray-800">
                            Apply
                        </button>
                    </span>

                    <select name="group_by" onchange="this.form.submit()"
                        class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="none" {{ $groupBy === 'none' ? 'selected' : '' }}>No Grouping</option>
                        <option value="subject" {{ $groupBy === 'subject' ? 'selected' : '' }}>Group by Subject</option>
                        <option value="department" {{ $groupBy === 'department' ? 'selected' : '' }}>Group by Department</option>
                        <option value="year_level" {{ $groupBy === 'year_level' ? 'selected' : '' }}>Group by Year Level</option>
                    </select>
                </form>
            </div>

            @php
                $isEmpty = $grouped ? $grouped->isEmpty() : $enrollments->isEmpty();
            @endphp

            @if($isEmpty)
                <div class="px-6 py-12 text-sm text-center text-gray-400">
                    <i class="block mb-2 text-2xl fa-solid fa-clipboard-list"></i>
                    No enrollments found for the selected filters.
                </div>
            @else
                @php $rowsToRender = $grouped ? $grouped : ['' => $enrollments]; @endphp

                @foreach($rowsToRender as $groupLabel => $rows)
                    @if($groupLabel !== '')
                        <div class="flex items-center gap-2 px-6 py-3 text-xs font-bold tracking-wide uppercase border-t border-b"
                             style="background:#fef3e2; border-color:#f5d9a8; color:#92610c;">
                            <i class="fa-solid fa-layer-group"></i>
                            {{ $groupLabel }}
                            <span class="px-2 py-0.5 text-xs font-semibold text-white rounded-full" style="background:#c9a84c;">{{ $rows->count() }}</span>
                        </div>
                    @endif

                    <table class="w-full text-sm">
                        @if($loop->first)
                        <thead class="text-xs tracking-wider text-gray-500 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">Student</th>
                                <th class="px-6 py-3 text-left">Subject</th>
                                <th class="px-6 py-3 text-left">Semester</th>
                                <th class="px-6 py-3 text-left">Enrolled On</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        @endif
                        <tbody class="divide-y divide-gray-100">
                            @foreach($rows as $enrollment)
                            <tr class="transition hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-800">{{ $enrollment->student->getFullName() }}</div>
                                    <div class="text-xs text-gray-400">{{ $enrollment->student->student_number }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-800">{{ $enrollment->subject->code }}</div>
                                    <div class="text-xs text-gray-400">{{ $enrollment->subject->name }}</div>
                                </td>
                                <td class="px-6 py-3 text-gray-600">{{ $enrollment->semester->semester_name }}</td>
                                <td class="px-6 py-3 text-xs text-gray-500">
                                    {{ $enrollment->enrollment_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $enrollment->status === 'enrolled' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    @if(!$enrollment->grade()->exists())
                                    <form id="delete-enrollment-{{ $enrollment->id }}"
                                        method="POST" action="{{ route('registrar.enrollments.destroy', $enrollment) }}">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                            onclick="confirmRemoveEnrollment({{ $enrollment->id }}, '{{ addslashes($enrollment->student->getFullName()) }}', '{{ addslashes($enrollment->subject->code) }}')"
                                            style="background:transparent;color:#ef4444;font-size:0.75rem;font-weight:500;border:none;cursor:pointer;padding:0;transition:color 0.15s;"
                                            onmouseover="this.style.color='#b91c1c'"
                                            onmouseout="this.style.color='#ef4444'">
                                            Remove
                                        </button>
                                    </form>
                                    @else
                                        <span class="text-xs text-gray-400">Has grade</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach

                @if(!$grouped)
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $enrollments->links() }}
                </div>
                @endif
            @endif
        </div>

    </div>

@push('scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/css/tom-select.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/js/tom-select.complete.min.js"></script>
<script>
    const enrolledMap = @json($enrolledMap ?? []);
    const studentCourseMap = @json($studentCourseMap ?? []);

    const studentSelect = document.getElementById('student-select');
    const subjectSelect = document.getElementById('subject-select');
    const enrollForm = document.getElementById('enroll-form');

    const allSubjectOptions = Array.from(subjectSelect.querySelectorAll('option[value]:not([value=""])'))
        .map(opt => ({ id: opt.value, label: opt.getAttribute('data-label'), courseId: opt.getAttribute('data-course') }));

    const studentTom = new TomSelect(studentSelect, {
        placeholder: 'Search by name or student number...',
    });

    const subjectTom = new TomSelect(subjectSelect, {
        placeholder: 'Select student first...',
        disabled: true,
    });

    function currentStudentCourseId() {
        return studentCourseMap[parseInt(studentSelect.value)] ?? studentCourseMap[studentSelect.value];
    }

    studentTom.on('change', function (studentId) {
        const enrolledSubjects = enrolledMap[parseInt(studentId)] || enrolledMap[studentId] || [];

        subjectTom.clear(true);
        subjectTom.clearOptions();

        if (!studentId) {
            subjectTom.disable();
            return;
        }

        const studentCourseId = studentCourseMap[parseInt(studentId)] ?? studentCourseMap[studentId];

        allSubjectOptions.forEach(subject => {
            const isEnrolled = enrolledSubjects.includes(parseInt(subject.id));
            const isDifferentCourse = parseInt(subject.courseId) !== parseInt(studentCourseId);
            subjectTom.addOption({
                value: subject.id,
                text: subject.label + (isEnrolled ? ' (Already Enrolled)' : (isDifferentCourse ? ' (Different Course)' : '')),
                disabled: isEnrolled,
            });
        });

        subjectTom.enable();
        subjectTom.refreshOptions(false);
    });

    if (studentSelect.value) studentTom.trigger('change', studentSelect.value);

    enrollForm.addEventListener('submit', function (e) {
        const selectedSubjectId = subjectSelect.value;
        if (!selectedSubjectId) return;

        const subject = allSubjectOptions.find(s => s.id == selectedSubjectId);
        const studentCourseId = currentStudentCourseId();
        const isDifferentCourse = subject && parseInt(subject.courseId) !== parseInt(studentCourseId);

        if (isDifferentCourse && !enrollForm.dataset.confirmed) {
            e.preventDefault();
            Swal.fire({
                title: 'Enroll as Irregular?',
                html: `<span style="font-size:0.9rem;color:#374151;">${subject.label} is outside this student's course. Enrolling will mark this as an <strong>irregular</strong> enrollment. Continue?</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c9a84c',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Enroll',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    enrollForm.dataset.confirmed = 'true';
                    enrollForm.submit();
                }
            });
        }
    });

    function handleDateFilterChange(select) {
        const customFields = document.getElementById('custom-range-fields');
        if (select.value === 'custom') {
            customFields.style.display = 'flex';
        } else {
            customFields.style.display = 'none';
            select.form.submit();
        }
    }

    function confirmRemoveEnrollment(id, studentName, subjectCode) {
        Swal.fire({
            title: 'Remove Enrollment?',
            html: `<span style="font-size:0.9rem;color:#374151;">Remove <strong>${studentName}</strong> from <strong>${subjectCode}</strong>? This cannot be undone.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Remove',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-enrollment-' + id).submit();
            }
        });
    }
</script>
@endpush
</x-app-layout>
