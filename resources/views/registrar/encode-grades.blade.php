<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Encode Grades</h2>
                <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">Registrar direct grade entry from physical records</p>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('registrar.excel.masterlist-template') }}"
                   style="padding:8px 16px;border:1px solid #e2d9c8;border-radius:8px;font-size:0.8rem;color:#6b5f4a;text-decoration:none;display:flex;align-items:center;gap:6px;white-space:nowrap;background:#fff;">
                    <i class="fa-solid fa-download"></i> Download Masterlist
                </a>
                <form action="{{ route('registrar.excel.masterlist-import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label style="padding:8px 16px;border:1px solid #e2d9c8;border-radius:8px;font-size:0.8rem;color:#6b5f4a;display:flex;align-items:center;gap:6px;white-space:nowrap;background:#fff;cursor:pointer;">
                        <i class="fa-solid fa-file-import"></i> Import Masterlist
                        <input type="file" name="file" accept=".xlsx,.xls" style="display:none;" onchange="this.form.submit()">
                    </label>
                </form>
            </div>
        </div>
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

        @if(session('import_report'))
            <script>
                window.__importReport = @json(session('import_report'));
            </script>
        @endif

        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.06);overflow:hidden;">

            {{-- Filter bar --}}
            <style>
                .swal2-container { z-index: 20000 !important; }
                .filter-select {
                    appearance: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    background-color: #fff;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%238a7a60' stroke-width='1.2' fill='none'/%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-size: 10px 6px;
                    background-position: right 12px center;
                    padding-right: 28px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            </style>
            <form method="GET" action="{{ route('registrar.encode-grades') }}" id="filterForm">
                <div style="padding:16px 24px;border-bottom:1px solid #f0ebe0;display:flex;gap:10px;flex-wrap:wrap;">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="search name or student number..."
                           style="flex:2;min-width:200px;border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.875rem;outline:none;">

                    <select name="school_year_id" class="filter-select"
                        style="flex:1;min-width:120px;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;">
                        <option value="">School Year</option>
                        @foreach($schoolYears as $sy)
                            <option value="{{ $sy->id }}" {{ $selectedSchoolYear == $sy->id ? 'selected' : '' }}>
                                {{ $sy->year_code }}
                            </option>
                        @endforeach
                    </select>

                    <select name="semester_id" class="filter-select"
                        style="flex:1;min-width:120px;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;">
                        <option value="">Semester</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemester == $sem->id ? 'selected' : '' }}>
                                {{ $sem->semester_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="department_id" class="filter-select"
                        style="flex:1;min-width:120px;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;">
                        <option value="">Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $selectedDepartment == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="course_id" class="filter-select"
                        style="flex:1;min-width:100px;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;">
                        <option value="">Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" title="{{ $course->name }}" {{ $selectedCourse == $course->id ? 'selected' : '' }}>
                                {{ $course->code }}
                            </option>
                        @endforeach
                    </select>

                    @if($selectedCourse && $yearLevels->isNotEmpty())
                    <select name="year_level" class="filter-select"
                        style="flex:1;min-width:100px;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;">
                        <option value="">Year Level</option>
                        @foreach($yearLevels as $yl)
                            <option value="{{ $yl }}" {{ (string)$selectedYearLevel === (string)$yl ? 'selected' : '' }}>
                                Year {{ $yl }}
                            </option>
                        @endforeach
                    </select>
                    @endif

                    <button type="submit"
                        style="background:#c9a84c;color:#fff;padding:8px 18px;border-radius:8px;font-size:0.8rem;font-weight:600;border:none;cursor:pointer;white-space:nowrap;">
                        Search
                    </button>

                    @if($search || $selectedDepartment || $selectedCourse || $selectedSchoolYear || $selectedSemester || $selectedStudent)
                        <a href="{{ route('registrar.encode-grades') }}"
                           style="padding:8px 16px;border:1px solid #e2d9c8;border-radius:8px;font-size:0.8rem;color:#6b5f4a;text-decoration:none;display:flex;align-items:center;white-space:nowrap;">
                            Clear
                        </a>
                    @endif
                </div>

                <input type="hidden" name="student_id" id="studentIdInput" value="{{ $selectedStudent }}">

                {{-- Student list --}}
                @if($students->isEmpty())
                    <div style="padding:48px;text-align:center;color:#b8a88a;font-size:0.875rem;">
                        No students found.
                    </div>
                @else
                <div style="max-height:480px;overflow-y:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                        <thead>
                            <tr style="background:#f5f0e8;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;position:sticky;top:0;">
                                <th style="padding:10px 24px;text-align:left;font-weight:700;">Student No.</th>
                                <th style="padding:10px 24px;text-align:left;font-weight:700;">Student</th>
                                <th style="padding:10px 24px;text-align:left;font-weight:700;">Department</th>
                                <th style="padding:10px 24px;text-align:left;font-weight:700;">Program</th>
                                <th style="padding:10px 24px;text-align:center;font-weight:700;">Year</th>
                                <th style="padding:10px 24px;text-align:right;font-weight:700;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $s)
                            <tr onclick="selectStudent({{ $s->id }})"
                                style="cursor:pointer;border-top:1px solid #f0ebe0;background:{{ $selectedStudent == $s->id ? '#faf3e0' : 'transparent' }};"
                                onmouseover="this.style.background='#faf8f4'"
                                onmouseout="this.style.background='{{ $selectedStudent == $s->id ? '#faf3e0' : 'transparent' }}'">
                                <td style="padding:12px 24px;color:#6b5f4a;font-family:monospace;font-size:0.8rem;">{{ $s->student_number }}</td>
                                <td style="padding:12px 24px;font-weight:600;color:#1a1a2e;">{{ $s->getFullName() }}</td>
                                <td style="padding:12px 24px;color:#4a4535;font-size:0.8rem;">{{ $s->course?->department?->name ?? '—' }}</td>
                                <td style="padding:12px 24px;color:#4a4535;font-size:0.8rem;">{{ $s->course?->code ?? '—' }}</td>
                                <td style="padding:12px 24px;text-align:center;color:#4a4535;font-size:0.8rem;">{{ $s->year_level ?? '—' }}</td>
                                <td style="padding:12px 24px;text-align:right;white-space:nowrap;">
                                    <button type="button" onclick="event.stopPropagation(); viewHistory({{ $s->id }}, false)"
                                        title="View grades"
                                        style="background:#f5f0e8;border:1px solid #e2d9c8;border-radius:6px;padding:5px 10px;cursor:pointer;color:#4a4535;margin-right:6px;">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button type="button" onclick="event.stopPropagation(); viewHistory({{ $s->id }}, true)"
                                        title="Encode / edit grades"
                                        style="background:#f5f0e8;border:1px solid #e2d9c8;border-radius:6px;padding:5px 10px;cursor:pointer;color:#4a4535;">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:16px 24px;border-top:1px solid #f0ebe0;">{{ $students->links() }}</div>
                @endif
            </form>
        </div>

        {{-- History Modal --}}
        <div id="historyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:12px;width:1080px;max-width:96vw;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 28px;border-bottom:1px solid #e2d9c8;background:#faf8f4;">
                    <div>
                        <div id="historyName" style="font-weight:700;color:#1a1a2e;font-size:1.05rem;"></div>
                        <div id="historyNumber" style="font-size:0.8rem;color:#8a7a60;font-family:monospace;"></div>
                        <div id="historyMeta" style="font-size:0.78rem;color:#8a7a60;margin-top:2px;"></div>
                    </div>
                    <button onclick="closeHistory()" style="background:none;border:none;font-size:1.3rem;color:#8a7a60;cursor:pointer;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div style="overflow-y:auto;flex:1;padding:20px 28px;" id="historyBody"></div>
            </div>
        </div>

        {{-- Grade Edit/Encode Modal (stacks above History Modal) --}}
        <div id="gradeEditModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:1100;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:12px;width:420px;max-width:92vw;overflow:hidden;">
                <div style="padding:18px 24px;border-bottom:1px solid #e2d9c8;background:#faf8f4;">
                    <div id="gradeEditTitle" style="font-weight:700;color:#1a1a2e;font-size:0.95rem;"></div>
                    <div id="gradeEditSubtitle" style="font-size:0.78rem;color:#8a7a60;margin-top:2px;"></div>
                </div>
                <div style="padding:24px;">
                    <label style="font-size:0.75rem;font-weight:600;color:#4a4535;display:block;margin-bottom:6px;">Grade (1.00–5.00)</label>
                    <input type="number" id="gradeEditInput" min="1.00" max="5.00" step="0.25" placeholder="e.g. 1.75"
                           style="width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:10px 12px;font-size:1rem;text-align:center;outline:none;">
                    <div id="gradeEditError" style="color:#dc2626;font-size:0.78rem;margin-top:8px;display:none;"></div>
                </div>
                <div style="padding:14px 24px;border-top:1px solid #f0ebe0;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="closeGradeEditModal()"
                        style="padding:8px 18px;border:1px solid #e2d9c8;border-radius:8px;font-size:0.85rem;color:#6b5f4a;background:#fff;cursor:pointer;transition:background 0.15s;"
                        onmouseover="this.style.background='#f5f0e8'" onmouseout="this.style.background='#fff'">
                        Cancel
                    </button>
                    <button type="button" onclick="submitGradeEditModal()"
                        style="padding:8px 20px;border:none;border-radius:8px;font-size:0.85rem;font-weight:600;color:#fff;background:#c9a84c;cursor:pointer;transition:background 0.15s;"
                        onmouseover="this.style.background='#b8963e'" onmouseout="this.style.background='#c9a84c'">
                        Save
                    </button>
                </div>
            </div>
        </div>

        {{-- Grade Input Table --}}
        @if($selectedStudentModel && $subjects->isNotEmpty())
        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="margin-bottom:16px;">
                <div style="font-size:1rem;font-weight:700;color:#1a1a2e;">
                    {{ $selectedStudentModel->getFullName() }}
                    <span style="font-weight:400;color:#8a7a60;font-size:0.85rem;">— {{ $selectedStudentModel->student_number }}</span>
                </div>
                <div style="font-size:0.8rem;color:#8a7a60;margin-top:2px;">
                    {{ $selectedSemesterModel->semester_name ?? '' }} &bull; {{ $selectedSemesterModel->schoolYear->year_code ?? '' }}
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
        @elseif($selectedStudent && (!$selectedSemester || !$selectedYearLevel))
        <div style="background:#fff;border:1px solid #d97706;border-radius:12px;padding:24px;text-align:center;">
            <i class="fa-solid fa-circle-info" style="font-size:1.3rem;display:block;margin-bottom:8px;color:#d97706;"></i>
            <div style="font-weight:600;color:#1a1a2e;margin-bottom:4px;">
                Select School Year, Semester, and Year Level to encode grades for
                <span style="color:#c9a84c;">{{ $selectedStudentModel?->getFullName() ?? 'this student' }}</span>.
            </div>
            @if($targetSemesterHint)
                <div style="font-size:0.85rem;color:#8a7a60;margin-bottom:8px;">
                    This subject belongs to <strong>{{ $targetSemesterHint }}</strong> — set the matching real School Year, Semester, and Year Level for this student's record.
                </div>
            @endif
            <a href="{{ route('registrar.encode-grades') }}" style="font-size:0.8rem;color:#8a7a60;text-decoration:underline;">
                Not encoding a grade right now? Clear selection
            </a>
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

function selectStudent(id) {
    document.getElementById('studentIdInput').value = id;
    document.getElementById('filterForm').submit();
}

let currentHistoryStudentId = null;
let historyEditable = false;

function viewHistory(id, editable = false) {
    currentHistoryStudentId = id;
    historyEditable = editable;
    fetch(`/registrar/students/${id}/grade-history`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('historyName').textContent = data.name;
            document.getElementById('historyNumber').textContent = data.number;
            document.getElementById('historyMeta').textContent = `${data.course ?? '—'} • ${data.department ?? '—'}`;

            const groups = {};
            data.rows.forEach(r => {
                const yKey = r.year_level ?? '—';
                const sKey = r.semester ?? '—';
                groups[yKey] = groups[yKey] || {};
                groups[yKey][sKey] = groups[yKey][sKey] || [];
                groups[yKey][sKey].push(r);
            });

            let html = '';
            Object.keys(groups).sort().forEach(year => {
                html += `<div style="margin-bottom:20px;">
                    <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#c9a84c;margin-bottom:8px;">
                        Year ${year}
                    </div>`;

                Object.keys(groups[year]).forEach(sem => {
                    const rows = groups[year][sem];
                    html += `<div style="margin-bottom:12px;">
                        <div style="font-size:0.75rem;font-weight:600;color:#4a4535;margin-bottom:6px;padding-left:2px;">${sem}</div>
                        <table style="width:100%;table-layout:fixed;border-collapse:collapse;font-size:0.875rem;border:1px solid #f0ebe0;border-radius:8px;overflow:hidden;">
                            <thead>
                                <tr style="background:#f5f0e8;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;">
                                    <th style="padding:8px 16px;text-align:left;width:${historyEditable ? '10%' : '12%'};">Code</th>
                                    <th style="padding:8px 16px;text-align:left;width:${historyEditable ? '22%' : '26%'};">Subject</th>
                                    <th style="padding:8px 16px;text-align:center;width:${historyEditable ? '7%' : '8%'};">Units</th>
                                    <th style="padding:8px 16px;text-align:center;width:${historyEditable ? '8%' : '10%'};">Grade</th>
                                    <th style="padding:8px 16px;text-align:left;width:${historyEditable ? '20%' : '24%'};">Encoded By</th>
                                    <th style="padding:8px 16px;text-align:left;width:${historyEditable ? '15%' : '20%'};">Timestamp</th>
                                    ${historyEditable ? '<th style="padding:8px 16px;text-align:right;width:18%;">Action</th>' : ''}
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.map(r => renderHistoryRow(r)).join('')}
                            </tbody>
                        </table>
                    </div>`;
                });
                html += `</div>`;
            });

            document.getElementById('historyBody').innerHTML = html || '<p style="text-align:center;color:#b8a88a;padding:20px;">No subjects found for this course curriculum.</p>';
            document.getElementById('historyModal').style.display = 'flex';
        });
}

function renderHistoryRow(r) {
    const actionCell = historyEditable ? `
            <td style="padding:10px 16px;text-align:right;" id="hist-action-${r.subject_id}">
                ${r.encoded
                    ? `<span style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;margin-right:6px;">${r.status}</span>
                       <button type="button" onclick="openGradeEditModal(${r.subject_id}, '${r.code}', '${(r.subject || '').replace(/'/g, "\\'")}', '${r.grade}')"
                           style="background:#f5f0e8;border:1px solid #e2d9c8;color:#4a4535;padding:4px 12px;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;transition:background 0.15s;"
                           onmouseover="this.style.background='#e2d9c8'" onmouseout="this.style.background='#f5f0e8'">
                           Edit
                       </button>`
                    : `<button type="button" onclick="openGradeEditModal(${r.subject_id}, '${r.code}', '${(r.subject || '').replace(/'/g, "\\'")}', null)"
                           style="background:#c9a84c;color:#fff;padding:4px 14px;border-radius:6px;font-size:0.75rem;font-weight:600;border:none;cursor:pointer;transition:background 0.15s;"
                           onmouseover="this.style.background='#b8963e'" onmouseout="this.style.background='#c9a84c'">
                           Encode
                       </button>`
                }
            </td>` : '';

    return `
        <tr id="hist-row-${r.subject_id}" style="border-top:1px solid #f0ebe0;">
            <td style="padding:10px 16px;font-family:monospace;font-size:0.8rem;color:#6b5f4a;">${r.code}</td>
            <td style="padding:10px 16px;font-weight:600;color:#1a1a2e;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.subject}</td>
            <td style="padding:10px 16px;text-align:center;color:#4a4535;">${r.units}</td>
            <td style="padding:10px 16px;text-align:center;" id="hist-grade-${r.subject_id}">
                ${r.encoded ? `<span style="font-weight:700;">${r.grade}</span>` : '—'}
            </td>
            <td style="padding:10px 16px;font-size:0.8rem;color:#1a1a2e;" id="hist-name-${r.subject_id}">
                ${r.encoded
                    ? `${r.encoded_by ?? '—'}<br><span style="font-size:0.7rem;color:#8a7a60;">${r.encoded_email ?? ''}</span>`
                    : '—'}
            </td>
            <td style="padding:10px 16px;font-size:0.75rem;color:#8a7a60;" id="hist-time-${r.subject_id}">
                ${r.encoded ? (r.encoded_at ?? '—') : '—'}
            </td>
            ${actionCell}
        </tr>
    `;
}

let editingSubjectId = null;

function openGradeEditModal(subjectId, code, subjectName, currentValue) {
    editingSubjectId = subjectId;
    document.getElementById('historyModal').style.display = 'none';
    document.getElementById('gradeEditTitle').textContent = currentValue ? 'Edit Grade' : 'Encode Grade';
    document.getElementById('gradeEditSubtitle').textContent = `${code} — ${subjectName}`;
    document.getElementById('gradeEditInput').value = currentValue ?? '';
    document.getElementById('gradeEditError').style.display = 'none';
    document.getElementById('gradeEditModal').style.display = 'flex';
    document.getElementById('gradeEditInput').focus();
}

function closeGradeEditModal() {
    document.getElementById('gradeEditModal').style.display = 'none';
    editingSubjectId = null;
    document.getElementById('historyModal').style.display = 'flex';
}

function submitGradeEditModal() {
    const input = document.getElementById('gradeEditInput');
    const errorBox = document.getElementById('gradeEditError');
    const value = input.value;
    const isEdit = document.getElementById('gradeEditTitle').textContent === 'Edit Grade';

    if (!value || value < 1 || value > 5) {
        errorBox.textContent = 'Enter a grade between 1.00 and 5.00.';
        errorBox.style.display = 'block';
        return;
    }
    errorBox.style.display = 'none';

    fetch(`/registrar/students/${currentHistoryStudentId}/quick-grade`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ subject_id: editingSubjectId, grade: value }),
    })
    .then(res => res.json().then(data => ({ ok: res.ok, data })))
    .then(({ ok, data }) => {
        if (!ok) {
            closeGradeEditModal();
            Swal.fire({
                title: 'Could Not Save',
                text: data.error || 'Something went wrong. Please try again.',
                icon: 'error',
                confirmButtonColor: '#c9a84c',
            });
            return;
        }

        const subjectId = editingSubjectId;
        document.getElementById(`hist-grade-${subjectId}`).innerHTML = `<span style="font-weight:700;">${data.grade}</span>`;
        document.getElementById(`hist-name-${subjectId}`).innerHTML = `${data.encoded_by}<br><span style="font-size:0.7rem;color:#8a7a60;">${data.encoded_email ?? ''}</span>`;
        document.getElementById(`hist-time-${subjectId}`).innerHTML = data.encoded_at;
        document.getElementById(`hist-action-${subjectId}`).innerHTML = `
            <span style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;margin-right:6px;">${data.status}</span>
            <button type="button" onclick="openGradeEditModal(${subjectId}, '', '', '${data.grade}')"
                style="background:#f5f0e8;border:1px solid #e2d9c8;color:#4a4535;padding:4px 12px;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;transition:background 0.15s;"
                onmouseover="this.style.background='#e2d9c8'" onmouseout="this.style.background='#f5f0e8'">
                Edit
            </button>
        `;
        closeGradeEditModal();

        Swal.fire({
            title: isEdit ? 'Grade Updated' : 'Grade Encoded',
            text: isEdit
                ? `The grade was updated to ${data.grade} and finalized.`
                : `The grade ${data.grade} was saved and finalized.`,
            icon: 'success',
            confirmButtonColor: '#c9a84c',
            timer: 2200,
            timerProgressBar: true,
        });
    })
    .catch((err) => {
        console.error('Grade save error:', err);
        closeGradeEditModal();

        const message = err instanceof TypeError
            ? 'Could not reach the server. Check your connection and try again.'
            : 'The grade was saved, but the page display failed to update. Please refresh.';

        Swal.fire({
            title: err instanceof TypeError ? 'Network Error' : 'Saved — Refresh Needed',
            text: message,
            icon: err instanceof TypeError ? 'error' : 'warning',
            confirmButtonColor: '#c9a84c',
        });
    });
}

function closeHistory() {
    document.getElementById('historyModal').style.display = 'none';
}
</script>
@push('scripts')
<script>
    if (window.__importReport) {
        const r = window.__importReport;

        const styles = `
            <style>
                .isr-stats { display:flex; gap:10px; margin-bottom:18px; }
                .isr-stat { flex:1; background:#fff; border:1px solid #e5e7eb; border-radius:10px;
                    padding:14px 8px; text-align:center; box-shadow:0 1px 2px rgba(0,0,0,.05); }
                .isr-stat-count { font-size:24px; font-weight:700; line-height:1.2; }
                .isr-stat-label { font-size:11px; font-weight:600; color:#374151; margin-top:2px; }
                .isr-stat-sub { font-size:10.5px; color:#9ca3af; margin-top:1px; }
                .isr-section { text-align:left; border-radius:8px; border:1px solid; margin-bottom:10px; overflow:hidden; }
                .isr-section-head { display:flex; align-items:center; justify-content:space-between; padding:9px 14px; font-weight:600; font-size:12.5px; }
                .isr-badge { font-size:11px; font-weight:700; border-radius:999px; padding:1px 9px; color:#fff; }
                .isr-list { list-style:none; margin:0; padding:2px 14px 10px; max-height:140px; overflow-y:auto; }
                .isr-list li { font-size:12.5px; color:#374151; line-height:1.5; padding:6px 0; border-bottom:1px solid rgba(0,0,0,.05); }
                .isr-list li:last-child { border-bottom:none; }
                .isr-success { background:#ECFDF5; border-color:#BBF7D0; }
                .isr-success .isr-section-head { color:#16A34A; }
                .isr-success .isr-badge { background:#16A34A; }
                .isr-warning { background:#FFFBEB; border-color:#FCD34D; }
                .isr-warning .isr-section-head { color:#B45309; }
                .isr-warning .isr-badge { background:#D97706; }
                .isr-error { background:#FEF2F2; border-color:#FCA5A5; }
                .isr-error .isr-section-head { color:#DC2626; }
                .isr-error .isr-badge { background:#DC2626; }
            </style>
        `;

        const formatItem = function (msg) {
            const parts = msg.split(' — ');
            return parts.length > 1
                ? '<li><strong>' + parts[0] + '</strong> — ' + parts.slice(1).join(' — ') + '</li>'
                : '<li>' + msg + '</li>';
        };

        const stat = function (count, label, sub, color) {
            return '<div class="isr-stat"><div class="isr-stat-count" style="color:' + color + ';">' + count + '</div>' +
                '<div class="isr-stat-label">' + label + '</div>' +
                '<div class="isr-stat-sub">' + sub + '</div></div>';
        };

        const section = function (variant, title, items) {
            if (!items.length) return '';
            return '<div class="isr-section isr-' + variant + '">' +
                '<div class="isr-section-head"><span>' + title + '</span><span class="isr-badge">' + items.length + '</span></div>' +
                '<ul class="isr-list">' + items.map(formatItem).join('') + '</ul>' +
            '</div>';
        };

        let icon = 'success', title = 'Import Completed';
        if (r.imported === 0 && !r.warnings.length && !r.errors.length) {
            icon = 'info'; title = 'Nothing to Import';
        } else if (r.errors.length && r.imported === 0) {
            icon = 'error'; title = 'Import Failed';
        } else if (r.errors.length || r.warnings.length) {
            icon = 'warning'; title = 'Import Completed with Warnings';
        }

        let html = styles;

        html += '<div class="isr-stats">' +
            stat(r.imported, 'Imported', r.imported ? 'Successfully saved' : 'No records', '#16A34A') +
            stat(r.warnings.length, 'Warnings', r.warnings.length ? 'Imported, needs review' : 'No issues', '#D97706') +
            stat(r.errors.length, 'Skipped', r.errors.length ? 'Not imported' : 'No issues', '#DC2626') +
        '</div>';

        html += section('success', 'Successfully Imported', r.successes);
        html += section('warning', 'Needs Review', r.warnings);
        html += section('error', 'Failed — Not Imported', r.errors);

        if (!r.successes.length && !r.warnings.length && !r.errors.length) {
            html += '<div style="text-align:center; font-size:13px; color:#6b7280; padding:8px 0 4px;">No records were found in the file to import.</div>';
        }

        Swal.fire({
            title: title,
            html: html,
            icon: icon,
            width: 700,
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Done',
        });
    }
</script>
@endpush
</x-app-layout>
