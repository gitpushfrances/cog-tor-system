<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('registrar.dashboard') }}"
           style="font-size:0.8rem;color:#2563eb;text-decoration:none;display:inline-block;margin-bottom:8px;">
            ← Back to Dashboard
        </a>
        <p style="font-size:0.75rem;font-weight:600;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Eastern Samar State University - Guiuan Campus</p>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Encode Grades</h2>
        <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">Registrar direct grade entry from physical records</p>
    </x-slot>

    <div class="mx-auto space-y-5 max-w-7xl">

        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:8px;font-size:0.875rem;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:0.875rem;">
                {{ session('error') }}
            </div>
        @endif

        {{-- Step 1 + 2: All filters in ONE form — no hidden inputs needed --}}
        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;margin-bottom:16px;">
                Step 1 — Select Academic Context
            </div>

            <form method="GET" action="{{ route('registrar.encode-grades') }}" id="filterForm">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;margin-bottom:12px;">

                    {{-- School Year --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:#4a4535;display:block;margin-bottom:4px;">School Year</label>
                        <select name="school_year_id" onchange="document.getElementById('filterForm').submit()"
                            style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;background:#fff;">
                            <option value="">— Select —</option>
                            @foreach($schoolYears as $sy)
                                <option value="{{ $sy->id }}" {{ $selectedSchoolYear == $sy->id ? 'selected' : '' }}>
                                    {{ $sy->year_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:#4a4535;display:block;margin-bottom:4px;">Semester</label>
                        <select name="semester_id" onchange="document.getElementById('filterForm').submit()"
                            style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;background:#fff;">
                            <option value="">— Select —</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}" {{ $selectedSemester == $sem->id ? 'selected' : '' }}>
                                    {{ $sem->semester_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:#4a4535;display:block;margin-bottom:4px;">Department</label>
                        <select name="department_id" onchange="document.getElementById('filterForm').submit()"
                            style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;background:#fff;">
                            <option value="">— Select —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $selectedDepartment == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Course --}}
                    <div>
                        <label style="font-size:0.75rem;font-weight:600;color:#4a4535;display:block;margin-bottom:4px;">Course</label>
                        <select name="course_id" onchange="document.getElementById('filterForm').submit()"
                            style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;background:#fff;">
                            <option value="">— Select —</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ $selectedCourse == $course->id ? 'selected' : '' }}>
                                    {{ $course->code }} - {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- Step 2: Student selector — same form, shows only when students exist --}}
                @if($students->isNotEmpty())
                <div style="border-top:1px solid #f0ebe0;margin-top:16px;padding-top:16px;">
                    <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;margin-bottom:10px;">
                        Step 2 — Select Student
                    </div>
                    <select name="student_id" onchange="document.getElementById('filterForm').submit()"
                        style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;background:#fff;">
                        <option value="">— Select Student —</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ $selectedStudent == $s->id ? 'selected' : '' }}>
                                {{ $s->getFullName() }} — {{ $s->student_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

            </form>
        </div>

        {{-- Step 3: Grade Input Table --}}
        @if($selectedStudentModel && $subjects->isNotEmpty())
        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div>
                    <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;">
                        Step 3 — Encode Grades
                    </div>
                    <div style="font-size:1rem;font-weight:700;color:#1a1a2e;margin-top:4px;">
                        {{ $selectedStudentModel->getFullName() }}
                        <span style="font-weight:400;color:#8a7a60;font-size:0.85rem;">— {{ $selectedStudentModel->student_number }}</span>
                    </div>
                    <div style="font-size:0.8rem;color:#8a7a60;margin-top:2px;">
                        {{ $selectedSemesterModel->semester_name ?? '' }} &bull; {{ $selectedSemesterModel->schoolYear->year_code ?? '' }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('registrar.encode-grades.store') }}" id="encodeForm">
                @csrf
                <input type="hidden" name="student_id"  value="{{ $selectedStudent }}">
                <input type="hidden" name="semester_id" value="{{ $selectedSemester }}">

                <table style="width:100%;border-collapse:collapse;font-size:0.875rem;margin-bottom:20px;">
                    <thead>
                        <tr style="background:#f5f0e8;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;">
                            <th style="padding:10px 16px;text-align:left;font-weight:700;">Subject Code</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:700;">Subject Name</th>
                            <th style="padding:10px 16px;text-align:center;font-weight:700;">Units</th>
                            <th style="padding:10px 16px;text-align:center;font-weight:700;">Grade (1.00–5.00)</th>
                            <th style="padding:10px 16px;text-align:center;font-weight:700;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                        @php
                            $existing = $existingGrades->get($subject->id);
                            $existingGrade = $existing?->grade?->grade ?? '';
                            $isFinalized = $existing?->grade?->status === 'finalized';
                        @endphp
                        <tr style="border-top:1px solid #f0ebe0;" onmouseover="this.style.background='#faf8f4'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 16px;font-family:monospace;font-size:0.8rem;color:#6b5f4a;">
                                {{ $subject->code }}
                            </td>
                            <td style="padding:12px 16px;font-weight:600;color:#1a1a2e;">
                                {{ $subject->name }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;color:#4a4535;">
                                {{ $subject->units }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <input
                                    type="number"
                                    name="grades[{{ $subject->id }}]"
                                    value="{{ $existingGrade }}"
                                    min="1.00" max="5.00" step="0.25"
                                    placeholder="e.g. 1.75"
                                    style="width:100px;border:1px solid #d4c9b4;border-radius:6px;padding:6px 10px;font-size:0.875rem;text-align:center;outline:none;"
                                    onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'"
                                    required>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @if($isFinalized)
                                    <span style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;">
                                        Finalized
                                    </span>
                                @elseif($existing)
                                    <span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;">
                                        {{ ucfirst(str_replace('_', ' ', $existing->grade->status ?? 'Pending')) }}
                                    </span>
                                @else
                                    <span style="color:#b8a88a;font-size:0.75rem;">No grade yet</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="display:flex;justify-content:flex-end;gap:8px;">
                    <a href="{{ route('registrar.encode-grades') }}"
                       style="padding:10px 20px;border:1px solid #e2d9c8;border-radius:8px;font-size:0.875rem;color:#6b5f4a;text-decoration:none;font-weight:600;">
                        Clear
                    </a>
                    <button type="button" onclick="confirmSave()"
                        style="background:#c9a84c;color:#fff;padding:10px 24px;border-radius:8px;font-size:0.875rem;font-weight:700;border:none;cursor:pointer;"
                        onmouseover="this.style.background='#b8963e'" onmouseout="this.style.background='#c9a84c'">
                        <i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>
                        Save & Finalize Grades
                    </button>
                </div>
            </form>
        </div>
        @elseif($selectedStudent && $subjects->isEmpty())
        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:32px;text-align:center;color:#b8a88a;font-size:0.875rem;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:1.5rem;display:block;margin-bottom:8px;color:#d97706;"></i>
            No subjects found for the selected course and semester. Make sure subjects are configured by the Admin.
        </div>
        @endif

    </div>

<script>
function confirmSave() {
    const inputs = document.querySelectorAll('input[name^="grades["]');
    let allFilled = true;
    inputs.forEach(input => {
        if (!input.value) allFilled = false;
    });

    if (!allFilled) {
        Swal.fire({
            title: 'Incomplete Grades',
            text: 'Please fill in all grade fields before saving.',
            icon: 'warning',
            confirmButtonColor: '#c9a84c',
        });
        return;
    }

    Swal.fire({
        title: 'Save & Finalize Grades?',
        html: 'All grades will be saved and set to <strong>Finalized</strong> immediately.<br><br>This cannot be undone from the faculty or HoD side.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#c9a84c',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Save & Finalize',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('encodeForm').submit();
        }
    });
}
</script>
</x-app-layout>
