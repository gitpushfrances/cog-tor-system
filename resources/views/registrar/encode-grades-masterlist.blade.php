@php
    $select   = 'flex:1;min-width:120px;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;background:#fff;';
    $open     = $semester?->acceptsGrades() ?? false;
    $hasGrid  = $course && $yearLevel && $semester && $subjects->isNotEmpty() && ($male->isNotEmpty() || $female->isNotEmpty());
    $filters  = array_filter(['school_year_id' => $schoolYear?->id, 'semester_id' => $semester?->id, 'course_id' => $course?->id, 'year_level' => $yearLevel, 'section' => $section]);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Encode Grades</h2>
            <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">Masterlist: a whole class in one grid, the same layout as the Excel masterlist</p>
        </div>
    </x-slot>

    <div class="mx-auto space-y-5 max-w-7xl">

        @include('registrar.partials.encode-tabs')

        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:8px;font-size:0.875rem;">{{ session('success') }}</div>
        @endif
        @foreach(array_filter([session('error'), session('password_error'), $errors->first()]) as $message)
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:0.875rem;">{{ $message }}</div>
        @endforeach

        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.06);overflow:hidden;">

            {{-- Filters: each change reloads the grid --}}
            <form method="GET" action="{{ route('registrar.encode-grades.masterlist') }}"
                  style="padding:16px 24px;border-bottom:1px solid #f0ebe0;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <select name="school_year_id" style="{{ $select }}" onchange="this.form.semester_id.value='';this.form.submit()" aria-label="School year">
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" @selected($schoolYear?->id === $sy->id)>SY {{ $sy->year_code }}{{ $sy->status === 'active' ? ' (active)' : '' }}</option>
                    @endforeach
                </select>
                <select name="semester_id" style="{{ $select }}" onchange="this.form.submit()" aria-label="Semester">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" @selected($semester?->id === $sem->id)>{{ $sem->semester_name }}{{ $sem->acceptsGrades() ? ' (open)' : '' }}</option>
                    @endforeach
                </select>
                <select name="course_id" style="{{ $select }}" onchange="this.form.section.value='';this.form.submit()" aria-label="Course">
                    <option value="">Course</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" title="{{ $c->name }}" @selected($course?->id === $c->id)>{{ $c->code }}</option>
                    @endforeach
                </select>
                <select name="year_level" style="{{ $select }}" onchange="this.form.section.value='';this.form.submit()" aria-label="Year level">
                    <option value="">Year Level</option>
                    @for($y = 1; $y <= 5; $y++)
                        <option value="{{ $y }}" @selected($yearLevel === $y)>Year {{ $y }}</option>
                    @endfor
                </select>
                <select name="section" style="{{ $select }}" onchange="this.form.submit()" aria-label="Section">
                    <option value="">All sections</option>
                    @foreach($sections->when($section && ! $sections->contains($section), fn ($list) => $list->push($section)) as $sec)
                        <option value="{{ $sec }}" @selected($section === $sec)>Section {{ $sec }}</option>
                    @endforeach
                </select>
                @if($course && $yearLevel && $schoolYear)
                    <a href="{{ route('registrar.excel.masterlist-export', array_filter(['school_year_id' => $schoolYear->id, 'course_id' => $course->id, 'year_level' => $yearLevel, 'section' => $section])) }}"
                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #c9a84c;border-radius:8px;font-size:0.8rem;font-weight:600;color:#92610c;text-decoration:none;background:#fff;">
                        <i class="fa-solid fa-file-excel"></i> Export to Excel
                    </a>
                @endif
            </form>

            @if(! $course || ! $yearLevel)
                <div style="padding:48px 24px;text-align:center;color:#b8a88a;font-size:0.875rem;">
                    <i class="fa-solid fa-table-cells" style="font-size:1.6rem;display:block;margin-bottom:8px;"></i>
                    Choose a course and year level to load the masterlist.
                </div>
            @elseif(! $semester)
                <div style="padding:48px 24px;text-align:center;color:#b8a88a;font-size:0.875rem;">This school year has no semesters yet.</div>
            @elseif($subjects->isEmpty())
                <div style="padding:48px 24px;text-align:center;color:#b8a88a;font-size:0.875rem;">
                    {{ $course->code }} has no Year {{ $yearLevel }} subjects for the {{ $semester->semester_name }}. Add them in Admin, Departments.
                </div>
            @elseif(! $hasGrid)
                <div style="padding:48px 24px;text-align:center;color:#b8a88a;font-size:0.875rem;">
                    No students are enrolled in {{ $course->code }} Year {{ $yearLevel }} subjects for the {{ $semester->semester_name }} {{ $semester->schoolYear?->year_code }}{{ $section ? ' in Section ' . $section : '' }}.
                    Enroll them first under Enrollment.
                </div>
            @else
                <div style="padding:14px 24px 0;">
                    <div style="font-weight:700;color:#1a1a2e;">{{ $course->code }} Year {{ $yearLevel }}{{ $section ? ' · Section ' . $section : '' }}</div>
                    <div style="font-size:0.8rem;color:#8a7a60;margin-bottom:12px;">
                        {{ $semester->semester_name }} · SY {{ $semester->schoolYear?->year_code }} · {{ $subjects->count() }} subject(s) · {{ $male->count() + $female->count() }} student(s)
                        · grades 1.00 to 5.00, two decimals, not rounded
                    </div>
                    @unless($open)
                        @include('registrar.partials.grade-lock-banner', ['semester' => $semester])
                    @endunless
                </div>

                <form method="POST" action="{{ route('registrar.encode-grades.masterlist.store') }}" id="masterlistForm">
                    @csrf
                    <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                    <input type="hidden" name="expected" value="0">

                    <div class="ml-scroll">
                        <table class="ml-grid">
                            <thead>
                                <tr>
                                    <th class="ml-sticky ml-c1" rowspan="2">#</th>
                                    <th class="ml-sticky ml-c2" rowspan="2">Student No.</th>
                                    <th class="ml-sticky ml-c3" rowspan="2">Name</th>
                                    @foreach($subjects as $subject)
                                        <th colspan="2" class="ml-subject" title="{{ $subject->name }} ({{ $subject->units }} units)">
                                            <span class="ml-code">{{ $subject->code }}</span>
                                            <div class="ml-subject-name">{{ $subject->name }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach($subjects as $subject)
                                        <th class="ml-sub">Grade</th>
                                        <th class="ml-sub ml-sub-re">Re-Ex</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['MALE' => $male, 'FEMALE' => $female] as $label => $group)
                                    @continue($group->isEmpty())
                                    <tr class="ml-group"><td class="ml-sticky ml-c1" colspan="3">{{ $label }} ({{ $group->count() }})</td><td colspan="{{ $subjects->count() * 2 }}"></td></tr>
                                    @foreach($group as $i => $row)
                                        @php $student = $row['student']; @endphp
                                        <tr>
                                            <td class="ml-sticky ml-c1 ml-num">{{ $i + 1 }}</td>
                                            <td class="ml-sticky ml-c2 ml-mono">{{ $student->student_number }}</td>
                                            <td class="ml-sticky ml-c3 ml-name" title="{{ $student->getFullName() }}">
                                                {{ strtoupper($student->last_name) }}, {{ $student->first_name }}{{ $student->middle_name ? ' ' . mb_substr($student->middle_name, 0, 1) . '.' : '' }}
                                                @if($student->section && ! $section)<span class="ml-sec">{{ $student->section }}</span>@endif
                                            </td>
                                            @foreach($subjects as $subject)
                                                @php $enrollment = $row['cells']->get($subject->id); @endphp
                                                @if($enrollment)
                                                    @php $grade = $enrollment->grade; @endphp
                                                    <td class="ml-cell" data-cell>
                                                        <input type="number" name="grades[{{ $enrollment->id }}]" step="0.01" min="1" max="5" inputmode="decimal"
                                                               value="{{ old("grades.{$enrollment->id}", $grade?->grade) }}" data-original="{{ $grade?->grade }}"
                                                               aria-label="{{ $student->student_number }} {{ $subject->code }} grade" @disabled(! $open)>
                                                    </td>
                                                    <td class="ml-cell ml-cell-re" data-cell>
                                                        <input type="number" name="re_exam[{{ $enrollment->id }}]" step="0.01" min="1" max="5" inputmode="decimal"
                                                               value="{{ old("re_exam.{$enrollment->id}", $grade?->re_exam_grade) }}" data-original="{{ $grade?->re_exam_grade }}"
                                                               aria-label="{{ $student->student_number }} {{ $subject->code }} re-exam" @disabled(! $open)>
                                                    </td>
                                                @else
                                                    <td class="ml-none" colspan="2" title="Not enrolled in {{ $subject->code }}">&mdash;</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($open)
                        <div style="padding:14px 24px;border-top:1px solid #f0ebe0;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                            <div style="font-size:0.8rem;color:#8a7a60;">
                                <span id="mlChangeCount">0</span> changed cell(s). Only changed cells are saved; blank cells are skipped. "&mdash;" means not enrolled.
                            </div>
                            <button type="button" onclick="saveMasterlist()"
                                    style="background:#c9a84c;color:#fff;padding:10px 22px;border-radius:8px;font-size:0.85rem;font-weight:600;border:none;cursor:pointer;">
                                <i class="fa-solid fa-floppy-disk"></i> Save &amp; Finalize Changes
                            </button>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>

    <style>
        #main-content { min-width:0; } /* lets the wide grid scroll inside its card instead of widening the page */
        .ml-scroll { overflow:auto; max-height:70vh; border-top:1px solid #f0ebe0; }
        .ml-grid { border-collapse:separate; border-spacing:0; font-size:0.8rem; }
        .ml-grid th, .ml-grid td { border-bottom:1px solid #f0ebe0; border-right:1px solid #f5f0e8; background:#fff; }
        .ml-grid thead th { position:sticky; z-index:2; background:#f5f0e8; color:#6b5f4a; font-weight:700; text-align:center; padding:6px 8px; }
        .ml-grid thead tr:first-child th { top:0; }
        .ml-grid thead tr:nth-child(2) th { top:44px; }
        .ml-subject { min-width:136px; font-size:0.78rem; color:#1a1a2e !important; height:44px; }
        .ml-code { font-family:monospace; }
        .ml-subject-name { font-weight:400; font-size:0.66rem; color:#8a7a60; max-width:130px; margin:0 auto; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .ml-sub { font-size:0.62rem; text-transform:uppercase; letter-spacing:0.06em; font-weight:600 !important; }
        .ml-sub-re, .ml-cell-re { background:#fbf9f5 !important; }
        .ml-sticky { position:sticky; z-index:1; }
        .ml-grid thead .ml-sticky { z-index:3; }
        .ml-c1 { left:0; width:40px; min-width:40px; }
        .ml-c2 { left:40px; width:110px; min-width:110px; }
        .ml-c3 { left:150px; min-width:220px; max-width:220px; box-shadow:2px 0 4px rgba(0,0,0,0.04); }
        .ml-grid tbody td { padding:4px 6px; }
        .ml-num { text-align:center; color:#8a7a60; }
        .ml-mono { font-family:monospace; color:#6b5f4a; white-space:nowrap; }
        .ml-name { font-weight:600; color:#1a1a2e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .ml-sec { display:inline-block; margin-left:6px; padding:0 6px; border-radius:999px; background:#eef4e7; color:#4b6043; font-size:0.65rem; font-weight:700; }
        .ml-group td { background:#faf6ee !important; font-size:0.68rem; font-weight:700; letter-spacing:0.1em; color:#92610c; padding:6px 10px !important; }
        .ml-cell input { width:60px; border:1px solid #d4c9b4; border-radius:6px; padding:4px 4px; font-size:0.8rem; text-align:center; outline:none; background:#fff; }
        .ml-cell input:focus { border-color:#c9a84c; box-shadow:0 0 0 2px rgba(201,168,76,0.2); }
        .ml-cell.ml-changed input { background:#fffbeb; border-color:#d97706; }
        .ml-cell input:disabled { background:#f3f4f6; color:#4b5563; cursor:not-allowed; }
        .ml-none { text-align:center; color:#d1c7b4; }
        .ml-grid input[type=number]::-webkit-inner-spin-button, .ml-grid input[type=number]::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
        .ml-grid input[type=number] { -moz-appearance:textfield; }
    </style>

    @if($hasGrid && $open)
    <script>
        (function () {
            const form = document.getElementById('masterlistForm');
            const counter = document.getElementById('mlChangeCount');
            const num = (v) => (v === '' || v === null || v === undefined ? null : Number(v));
            const changed = (input) => num(input.value) !== num(input.dataset.original);
            let submitting = false;

            // One cell = the grade input and the re-exam input of one student and subject.
            const pairs = () => Array.from(form.querySelectorAll('input[name^="grades["]')).map((grade) => ({
                grade,
                reExam: form.querySelector(`input[name="${grade.name.replace('grades[', 're_exam[')}"]`),
            }));
            const changedPairs = () => pairs().filter((p) => changed(p.grade) || changed(p.reExam));

            function refresh() {
                pairs().forEach((p) => {
                    const on = changed(p.grade) || changed(p.reExam);
                    p.grade.closest('td').classList.toggle('ml-changed', on);
                    p.reExam.closest('td').classList.toggle('ml-changed', on);
                });
                counter.textContent = changedPairs().length;
            }
            form.addEventListener('input', refresh);
            refresh();

            // Enter moves down the column, like a spreadsheet.
            form.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' || e.target.tagName !== 'INPUT') return;
                e.preventDefault();
                const column = Array.from(form.querySelectorAll(`input[name^="${e.target.name.split('[')[0]}["]`))
                    .filter((i) => i.closest('td').cellIndex === e.target.closest('td').cellIndex);
                const next = column[column.indexOf(e.target) + 1];
                if (next) next.focus();
            });

            window.addEventListener('beforeunload', (e) => {
                if (!submitting && changedPairs().length) { e.preventDefault(); e.returnValue = ''; }
            });

            window.saveMasterlist = function () {
                const list = changedPairs();
                if (!list.length) {
                    Swal.fire({ icon: 'info', title: 'Nothing to save', text: 'No grade was entered or changed.', confirmButtonColor: '#c9a84c' });
                    return;
                }
                const invalid = (input) => input.value !== '' && !(/^\d(\.\d{1,2})?$/.test(input.value) && Number(input.value) >= 1 && Number(input.value) <= 5);
                for (const p of list) {
                    const g = num(p.grade.value), r = num(p.reExam.value);
                    let problem = null;
                    if (invalid(p.grade) || invalid(p.reExam)) problem = 'Grades must be from 1.00 to 5.00 with at most two decimals.';
                    else if (g === null && num(p.grade.dataset.original) !== null) problem = 'A saved grade cannot be cleared here.';
                    else if (g === null && r !== null) problem = 'A re-exam grade needs a grade in the same cell.';
                    if (problem) {
                        p.grade.focus();
                        Swal.fire({ icon: 'warning', title: 'Check this cell', text: problem, confirmButtonColor: '#c9a84c' });
                        return;
                    }
                }

                const send = (password) => {
                    // Only changed cells are sent (keeps big classes under the server's input limit).
                    const keep = new Set(list.flatMap((p) => [p.grade, p.reExam]));
                    pairs().forEach((p) => [p.grade, p.reExam].forEach((i) => { i.disabled = !keep.has(i); }));
                    form.querySelector('input[name="expected"]').value = list.length;
                    if (password !== null) {
                        const pw = document.createElement('input');
                        pw.type = 'hidden'; pw.name = 'confirm_password'; pw.value = password;
                        form.appendChild(pw);
                    }
                    submitting = true;
                    form.submit();
                };

                const summary = `<strong>${list.length}</strong> cell(s) will be saved and set to <strong>Finalized</strong>.`;
                if (list.some((p) => num(p.grade.dataset.original) !== null)) {
                    confirmWithPassword({
                        title: 'Change saved grades?',
                        html: summary + '<br><br>Some of these grades were already saved; the change is recorded in the activity log.',
                        confirmButtonText: 'Yes, Save Changes',
                        confirmButtonColor: '#c9a84c',
                    }).then((password) => { if (password !== null) send(password); });
                    return;
                }
                Swal.fire({
                    title: 'Save & Finalize?', html: summary, icon: 'question', showCancelButton: true,
                    confirmButtonColor: '#c9a84c', cancelButtonColor: '#6b7280', confirmButtonText: 'Yes, Save', reverseButtons: true,
                }).then((result) => { if (result.isConfirmed) send(null); });
            };
        })();
    </script>
    @endif
</x-app-layout>
