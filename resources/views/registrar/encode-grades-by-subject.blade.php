@php
    $select = 'flex:1;min-width:130px;border:1px solid #d4c9b4;border-radius:8px;padding:8px 10px;font-size:0.875rem;outline:none;background:#fff;';
    $input  = 'width:90px;border:1px solid #d4c9b4;border-radius:6px;padding:6px 8px;font-size:0.875rem;text-align:center;outline:none;';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Encode Grades</h2>
            <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">By Subject: encode a whole class from one grade sheet</p>
        </div>
    </x-slot>

    <div class="mx-auto space-y-5 max-w-7xl">

        @include('registrar.partials.encode-tabs')

        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:8px;font-size:0.875rem;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:0.875rem;">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:0.875rem;">{{ $errors->first() }}</div>
        @endif

        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.06);overflow:hidden;">

            {{-- Filters: each change reloads so the next list narrows --}}
            <form method="GET" action="{{ route('registrar.encode-grades.by-subject') }}" id="subjectFilterForm"
                  style="padding:16px 24px;border-bottom:1px solid #f0ebe0;display:flex;gap:10px;flex-wrap:wrap;">
                <select name="school_year_id" style="{{ $select }}" onchange="this.form.semester_id.value='';this.form.subject_id.value='';this.form.submit()">
                    <option value="">School Year</option>
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" @selected($selectedSchoolYear == $sy->id)>{{ $sy->year_code }}</option>
                    @endforeach
                </select>
                <select name="semester_id" style="{{ $select }}" onchange="this.form.subject_id.value='';this.form.submit()">
                    <option value="">Semester</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" @selected($selectedSemester == $sem->id)>{{ $sem->semester_name }}</option>
                    @endforeach
                </select>
                <select name="department_id" style="{{ $select }}" onchange="this.form.course_id.value='';this.form.subject_id.value='';this.form.submit()">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected($selectedDepartment == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
                <select name="course_id" style="{{ $select }}" onchange="this.form.subject_id.value='';this.form.submit()">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" title="{{ $course->name }}" @selected($selectedCourse == $course->id)>{{ $course->code }}</option>
                    @endforeach
                </select>
                <select name="subject_id" style="{{ $select }}flex:2;min-width:220px;" onchange="this.form.submit()" @disabled(! $selectedSemester)>
                    <option value="">{{ $selectedSemester ? ($subjects->isEmpty() ? 'No classes this semester' : 'Select Subject') : 'Select a semester first' }}</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" @selected($selectedSubject == $subj->id)>{{ $subj->code }} — {{ $subj->name }} ({{ $subj->course->code ?? '' }})</option>
                    @endforeach
                </select>
                @if($selectedDepartment || $selectedCourse || $selectedSubject)
                    <a href="{{ route('registrar.encode-grades.by-subject', array_filter(['school_year_id' => $selectedSchoolYear, 'semester_id' => $selectedSemester])) }}"
                       style="padding:8px 16px;border:1px solid #e2d9c8;border-radius:8px;font-size:0.8rem;color:#6b5f4a;text-decoration:none;display:flex;align-items:center;">Clear</a>
                @endif
            </form>

            @if(! $subjectModel)
                <div style="padding:48px 24px;text-align:center;color:#b8a88a;font-size:0.875rem;">
                    <i class="fa-solid fa-book-open" style="font-size:1.6rem;display:block;margin-bottom:8px;"></i>
                    Choose a school year, semester and subject to load its class list.
                </div>
            @else
                <div style="padding:16px 24px;border-bottom:1px solid #f0ebe0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <div style="font-weight:700;color:#1a1a2e;">{{ $subjectModel->code }} — {{ $subjectModel->name }}</div>
                        <div style="font-size:0.8rem;color:#8a7a60;">
                            {{ $semesterModel->semester_name }} · {{ $semesterModel->schoolYear->year_code ?? '' }} · {{ $subjectModel->units }} units · {{ $classList->count() }} student(s)
                        </div>
                    </div>
                    <div style="font-size:0.75rem;color:#8a7a60;">Leave a row blank if the grade is not available yet.</div>
                </div>

                @php $gradesLocked = $semesterModel->isGradeLocked(); @endphp
                @if($gradesLocked)
                    <div style="padding:16px 24px 0;">
                        @include('registrar.partials.grade-lock-banner', ['schoolYear' => $semesterModel->schoolYear->year_code ?? ''])
                    </div>
                @endif

                <form method="POST" action="{{ route('registrar.encode-grades.by-subject.store') }}" id="subjectEncodeForm"
                      data-school-year="{{ $semesterModel->schoolYear->year_code ?? '' }}">
                    @csrf
                    <input type="hidden" name="school_year_id" value="{{ $selectedSchoolYear }}">
                    <input type="hidden" name="semester_id" value="{{ $semesterModel->id }}">
                    <input type="hidden" name="department_id" value="{{ $selectedDepartment }}">
                    <input type="hidden" name="course_id" value="{{ $selectedCourse }}">
                    <input type="hidden" name="subject_id" value="{{ $subjectModel->id }}">

                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                            <thead>
                                <tr style="background:#f5f0e8;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;">
                                    <th style="padding:10px 16px;text-align:left;">#</th>
                                    <th style="padding:10px 16px;text-align:left;">Student No.</th>
                                    <th style="padding:10px 16px;text-align:left;">Name</th>
                                    <th style="padding:10px 16px;text-align:left;">Course / Year</th>
                                    <th style="padding:10px 16px;text-align:center;">Grade (1.00–5.00)</th>
                                    <th style="padding:10px 16px;text-align:center;">Re-Exam</th>
                                    <th style="padding:10px 16px;text-align:center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classList as $i => $enrollment)
                                    @php
                                        $student     = $enrollment->student;
                                        $grade       = $enrollment->grade;
                                        $isFinalized = $grade?->status === 'finalized';
                                        $gradeValue  = old("grades.{$student->id}", $grade?->grade);
                                        $reExamValue = old("re_exam.{$student->id}", $grade?->re_exam_grade);
                                    @endphp
                                    <tr style="border-top:1px solid #f0ebe0;">
                                        <td style="padding:10px 16px;color:#8a7a60;">{{ $i + 1 }}</td>
                                        <td style="padding:10px 16px;font-family:monospace;font-size:0.8rem;color:#6b5f4a;">{{ $student->student_number }}</td>
                                        <td style="padding:10px 16px;font-weight:600;color:#1a1a2e;">{{ strtoupper($student->last_name) }}, {{ $student->first_name }} {{ $student->middle_name ? mb_substr($student->middle_name, 0, 1) . '.' : '' }}</td>
                                        <td style="padding:10px 16px;color:#4a4535;">{{ $student->course->code ?? '' }} · Year {{ $student->year_level }}</td>
                                        <td style="padding:10px 16px;text-align:center;">
                                            <input type="number" name="grades[{{ $student->id }}]" value="{{ $gradeValue }}"
                                                   min="1.00" max="5.00" step="0.01" placeholder="—" style="{{ $input }}"
                                                   data-finalized="{{ $isFinalized ? $grade->grade : '' }}"
                                                   data-original="{{ $grade ? $grade->grade : '' }}"
                                                   @if($gradesLocked && $grade) data-locked readonly @endif>
                                        </td>
                                        <td style="padding:10px 16px;text-align:center;">
                                            <input type="number" name="re_exam[{{ $student->id }}]" value="{{ $reExamValue }}"
                                                   min="1.00" max="5.00" step="0.01" placeholder="optional" style="{{ $input }}"
                                                   data-finalized="{{ $isFinalized ? ($grade->re_exam_grade ?? '-') : '' }}"
                                                   data-original="{{ $grade ? ($grade->re_exam_grade ?? '-') : '' }}"
                                                   @if($gradesLocked && $grade) data-locked readonly @endif>
                                        </td>
                                        <td style="padding:10px 16px;text-align:center;">
                                            @if($isFinalized)
                                                <span style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;">Finalized</span>
                                            @elseif($grade)
                                                <span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;">{{ ucfirst(str_replace('_', ' ', $grade->status)) }}</span>
                                            @else
                                                <span style="color:#b8a88a;font-size:0.75rem;">No grade yet</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div style="padding:16px 24px;border-top:1px solid #f0ebe0;display:flex;justify-content:flex-end;">
                        <button type="button" onclick="confirmSubjectSave()"
                                style="background:#c9a84c;color:#fff;padding:10px 22px;border-radius:8px;font-size:0.85rem;font-weight:600;border:none;cursor:pointer;">
                            <i class="fa-solid fa-floppy-disk"></i> Save &amp; Finalize Class Grades
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        function confirmSubjectSave() {
            const form = document.getElementById('subjectEncodeForm');
            const gradeInputs = Array.from(form.querySelectorAll('input[name^="grades["]'));
            const filled = gradeInputs.filter((input) => input.value !== '').length;

            if (filled === 0) {
                Swal.fire({ icon: 'warning', title: 'No grades entered', text: 'Enter at least one grade before saving.', confirmButtonColor: '#c9a84c' });
                return;
            }

            // Changing a grade that is already finalized needs the registrar's password.
            const sameGrade = (a, b) => (a === '' ? null : Number(a)) === (b === '' || b === '-' ? null : Number(b));
            const changesFinalized = Array.from(form.querySelectorAll('input[data-finalized]'))
                .some((input) => input.dataset.finalized !== '' && !sameGrade(input.value, input.dataset.finalized));

            const summary = `${filled} of ${gradeInputs.length} student(s) will be saved and set to <strong>Finalized</strong>. Blank rows are skipped.`;

            // Past school year: changing an existing grade needs a reason and the password (blank rows are skipped).
            const changesLocked = gradeInputs.some((input) => {
                if (!input.hasAttribute('data-locked') || input.value === '') return false;
                const reExam = form.querySelector(`input[name="${input.name.replace('grades[', 're_exam[')}"]`);
                return !sameGrade(input.value, input.dataset.original) || (reExam && !sameGrade(reExam.value, reExam.dataset.original));
            });

            if (changesLocked) {
                submitWithPasswordAndReason(form, {
                    title: 'Correct past school year grades?',
                    html: summary + `<br><br>SY <strong>${form.dataset.schoolYear}</strong> is closed. Your reason is saved in the activity log with the old and new grades.`,
                });
                return;
            }

            if (changesFinalized) {
                submitWithPassword(form, {
                    title: 'Change finalized grades?',
                    html: summary + '<br><br>Some grades are already <strong>Finalized</strong>; the change is recorded in the activity log.',
                    confirmButtonText: 'Yes, Save Changes',
                    confirmButtonColor: '#c9a84c',
                });
                return;
            }

            Swal.fire({
                title: 'Save & Finalize Class Grades?',
                html: summary,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c9a84c',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Save & Finalize',
                reverseButtons: true,
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        }
    </script>
</x-app-layout>
