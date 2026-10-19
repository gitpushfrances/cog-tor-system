<?php

namespace App\Http\Controllers\Registrar;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\GradeSubmission;
use App\Models\CogRecord;
use App\Models\TorRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\SchoolYear;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Middleware\RequireActionPassword;

class RegistrarController extends Controller
{
    public function __construct()
    {
        // Deletes and high-risk changes require the user's own password (RequireActionPassword).
        $this->middleware('action.password')->only(['unfinalizeSubject']);
    }

    /** Minimum length of the reason required to change a locked (past school year) grade. */
    private const OVERRIDE_REASON_MIN = 10;

    public function finalizeSubject(Request $request, int $subjectId)
    {
        $registrarId = auth()->id();

        $submissions = GradeSubmission::whereHas('grade.enrollment', fn($q) =>
                $q->where('subject_id', $subjectId))
            ->approved()
            ->whereNull('finalized_at')
            ->get();

        try {
            DB::transaction(function () use ($submissions, $registrarId) {
                foreach ($submissions as $sub) {
                    $sub->update([
                        'finalized_at' => now(),
                        'finalized_by' => $registrarId,
                    ]);
                    $sub->grade->update(['status' => 'finalized']);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Finalize Subject failed, transaction rolled back', [
                'subject_id' => $subjectId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('registrar.dashboard', ['tab' => 'finalization'])
                ->with('error', 'Something went wrong while finalizing grades. No grades were changed. Please try again.');
        }

        return redirect()->route('registrar.dashboard', ['tab' => 'finalization'])
            ->with('success', count($submissions) . ' grades finalized successfully.');
    }

    public function finalize(Request $request, \App\Models\GradeSubmission $submission)
    {
        $submission->update([
            'finalized_at' => now(),
            'finalized_by' => auth()->id(),
        ]);

        $submission->grade->update(['status' => 'finalized']);

        return redirect()->route('registrar.dashboard')->with('success', 'Grade finalized successfully.');
    }

    public function unfinalizeSubject(Request $request, int $subjectId)
    {
        $submissions = GradeSubmission::whereHas('grade.enrollment', fn($q) =>
                $q->where('subject_id', $subjectId))
            ->whereNotNull('finalized_at')
            ->get();

        if ($submissions->isEmpty()) {
            return redirect()->back()->with('error', 'No finalized grades found for this subject.');
        }

        $submissions->load('grade.enrollment.semester.schoolYear');
        if ($submissions->contains(fn ($sub) => $sub->grade?->enrollment?->semester?->isGradeLocked() ?? true)) {
            return redirect()->back()->with('error', 'Some of these grades belong to a closed school year. Undo them per student from the student profile.');
        }

        try {
            DB::transaction(function () use ($submissions) {
                foreach ($submissions as $sub) {
                    $sub->update([
                        'finalized_at' => null,
                        'finalized_by' => null,
                    ]);
                    $sub->grade->update(['status' => 'approved_by_head_of_department']);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Unfinalize Subject failed, transaction rolled back', [
                'subject_id' => $subjectId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Something went wrong while unfinalizing grades. No grades were changed. Please try again.');
        }

        return redirect()->back()
            ->with('success', count($submissions) . ' grade(s) unfinalized and returned to approved status.');
    }

    /** Undo Finalize on a student profile: this student's finalized grades in one semester. */
    public function unfinalizeSemester(Request $request, Student $student, Semester $semester)
    {
        $grades = Grade::with(['submission', 'enrollment.subject'])
            ->where('status', 'finalized')
            ->whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id)->where('semester_id', $semester->id))
            ->get();

        if ($grades->isEmpty()) {
            return back()->with('error', 'No finalized grades found for this semester.');
        }

        $locked = $semester->isGradeLocked();
        if ($locked && ($error = $this->overrideReasonError($request, $semester))) {
            return back()->with('password_error', $error);
        }
        if ($error = RequireActionPassword::verify($request)) {
            return RequireActionPassword::reject($request, $error);
        }

        try {
            DB::transaction(function () use ($grades) {
                foreach ($grades as $grade) {
                    $grade->submission?->update(['finalized_at' => null, 'finalized_by' => null]);
                    $grade->update(['status' => 'approved_by_head_of_department']);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Unfinalize Semester failed, transaction rolled back', [
                'student_id' => $student->id, 'semester_id' => $semester->id, 'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong while unfinalizing grades. No grades were changed. Please try again.');
        }

        if ($locked) {
            foreach ($grades as $grade) {
                $this->logOverride($request, $semester, $grade, 'Undid finalization of a past school year grade',
                    ['status' => 'finalized'], ['status' => 'approved_by_head_of_department']);
            }
        }

        return back()->with('success', $grades->count() . " grade(s) for {$semester->semester_name} unfinalized and returned to approved status.");
    }

    public function encodeGradesForm(Request $request)
    {
        $schoolYears = SchoolYear::whereIn('status', ['active', 'completed'])
            ->orderBy('year_code', 'desc')
            ->get();
        $departments = Department::orderBy('name')->get();

        $selectedSchoolYear = $request->input('school_year_id')
            ?? optional(SchoolYear::where('status', 'active')->first())->id;
        $selectedSemester   = $request->input('semester_id');
        $selectedDepartment = $request->input('department_id');
        $selectedCourse     = $request->input('course_id');
        $selectedStudent    = $request->input('student_id');
        $search             = $request->input('search');
        $targetSemesterHint = $request->input('target_semester');
        $selectedYearLevel  = $request->input('year_level');

        $semesters = $selectedSchoolYear
            ? Semester::where('school_year_id', $selectedSchoolYear)
                ->whereIn('status', ['active', 'completed'])
                ->orderBy('semester_order')
                ->get()
            : collect();

        $courses = Course::active()
            ->when($selectedDepartment, fn($q) => $q->where('department_id', $selectedDepartment))
            ->orderBy('name')
            ->get();

        $subjects  = collect();
        $yearLevels = collect();
        $existingGrades = collect();
        $selectedStudentModel = null;
        $selectedSemesterModel = null;

        if ($selectedCourse) {
            $yearLevels = Subject::where('course_id', $selectedCourse)
                ->active()
                ->distinct()
                ->orderBy('year_level')
                ->pluck('year_level');
        }

        if ($selectedCourse && $selectedSemester && $selectedYearLevel) {
            $selectedSemesterModel = Semester::find($selectedSemester);

            $subjects = Subject::where('course_id', $selectedCourse)
                ->where('semester', $selectedSemesterModel->semester_name ?? '')
                ->where('year_level', $selectedYearLevel)
                ->active()
                ->orderBy('name')
                ->get();
        } elseif ($selectedSemester) {
            $selectedSemesterModel = Semester::find($selectedSemester);
        }

        // Student list loads independently — filters just narrow it, never gate it.
        $students = Student::with('course.department')
            ->active()
            ->when($selectedDepartment, fn($q) => $q->whereHas('course', fn($q) =>
                $q->where('department_id', $selectedDepartment)))
            ->when($selectedCourse, fn($q) => $q->where('course_id', $selectedCourse))
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%");
            }))
            ->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        $activeSemesterGlobal = Semester::where('status', 'active')->first();
        $enrolledThisTermIds = $activeSemesterGlobal
            ? Enrollment::where('semester_id', $activeSemesterGlobal->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->pluck('student_id')
                ->unique()
                ->toArray()
            : [];

        if ($selectedStudent) {
            $selectedStudentModel = Student::find($selectedStudent);
        }

        if ($selectedStudent && $selectedSemester) {
            $existingGrades = Enrollment::with(['grade', 'subject'])
                ->where('student_id', $selectedStudent)
                ->where('semester_id', $selectedSemester)
                ->get()
                ->keyBy('subject_id');
        }

        return view('registrar.encode-grades', compact(
            'schoolYears', 'departments', 'semesters', 'courses',
            'subjects', 'yearLevels', 'students', 'existingGrades',
            'selectedSchoolYear', 'selectedSemester', 'selectedDepartment',
            'selectedCourse', 'selectedStudent', 'selectedStudentModel',
            'selectedSemesterModel', 'search', 'targetSemesterHint', 'selectedYearLevel',
            'activeSemesterGlobal', 'enrolledThisTermIds'
        ));
    }

    public function studentGradeHistory(Student $student)
    {
        $student->load('course.department');

        $subjects = Subject::where('course_id', $student->course_id)
            ->active()
            ->orderBy('year_level')
            ->orderBy('semester')
            ->orderBy('name')
            ->get();

        // Latest semester first, so each subject shows the most recent attempt.
        $enrollmentsBySubject = Enrollment::with(['grade', 'semester.schoolYear'])
            ->where('student_id', $student->id)
            ->get()
            ->sortByDesc(fn ($e) => [$e->semester?->schoolYear?->year_code, $e->semester?->semester_order])
            ->unique('subject_id')
            ->keyBy('subject_id');

        $gradeIds = $enrollmentsBySubject->pluck('grade.id')->filter()->values();

        $submissionsByGradeId = GradeSubmission::whereIn('grade_id', $gradeIds)
            ->get()
            ->keyBy('grade_id');

        $userLookup = \App\Models\User::whereIn(
            'id',
            $submissionsByGradeId->pluck('finalized_by')->filter()->unique()
        )->get(['id', 'name', 'email'])->keyBy('id');

        $rows = $subjects->map(function ($subject) use ($enrollmentsBySubject, $submissionsByGradeId, $userLookup) {
            $enrollment = $enrollmentsBySubject->get($subject->id);
            $grade = $enrollment?->grade;
            $submission = $grade ? $submissionsByGradeId->get($grade->id) : null;
            $encoder = $submission ? $userLookup->get($submission->finalized_by) : null;

            return [
                'subject_id'    => $subject->id,
                'year_level'    => $subject->year_level,
                'semester'      => $subject->semester,
                'code'          => $subject->code,
                'subject'       => $subject->name,
                'units'         => $subject->units,
                'grade'         => $grade?->grade !== null ? number_format($grade->grade, 2) : null,
                're_exam'       => $grade?->re_exam_grade !== null ? number_format($grade->re_exam_grade, 2) : null,
                'status'        => $grade?->status,
                'encoded'       => (bool) $grade,
                'encoded_by'    => $encoder?->name,
                'encoded_email' => $encoder?->email,
                'encoded_at'    => $submission?->finalized_at?->format('M d, Y g:i A'),
                'semester_id'   => $grade ? $enrollment->semester_id : null,
                'school_year'   => $grade ? $enrollment->semester?->schoolYear?->year_code : null,
                'locked'        => $grade ? (bool) $enrollment->semester?->isGradeLocked() : false,
            ];
        });

        return response()->json([
            'name'       => $student->getFullName(),
            'number'     => $student->student_number,
            'student_id' => $student->id,
            'course_id'  => $student->course_id,
            'course'     => $student->course?->code,
            'department' => $student->course?->department?->name,
            'rows'       => $rows,
        ]);
    }
    public function quickEncodeGrade(Request $request, Student $student)
    {
        $request->validate([
            'subject_id'    => 'required|exists:subjects,id',
            'semester_id'   => 'nullable|exists:semesters,id',
            'grade'         => 'required|numeric|min:1.00|max:5.00',
            're_exam_grade' => 'nullable|numeric|min:1.00|max:5.00',
        ]);

        $subject = Subject::findOrFail($request->input('subject_id'));

        // Editing an existing grade saves it in its own semester, not the active one.
        if ($request->filled('semester_id')) {
            $semester = Semester::with('schoolYear')->findOrFail($request->input('semester_id'));
            $hasGrade = Enrollment::where(['student_id' => $student->id, 'subject_id' => $subject->id, 'semester_id' => $semester->id])
                ->whereHas('grade')->exists();
            if (! $hasGrade) {
                return response()->json(['error' => 'This grade no longer exists in that semester. Reload and try again.'], 422);
            }

            return $this->saveQuickGrade($request, $student, $subject, $semester);
        }

        $schoolYear = SchoolYear::where('status', 'active')->first();
        if (!$schoolYear) {
            return response()->json(['error' => 'No active school year is configured.'], 422);
        }

        $semester = Semester::where('school_year_id', $schoolYear->id)
            ->where('semester_name', $subject->semester)
            ->first();

        if (!$semester) {
            return response()->json([
                'error' => "No semester named \"{$subject->semester}\" exists under {$schoolYear->year_code}. Set it up first, or encode this one manually via the filter above."
            ], 422);
        }

        return $this->saveQuickGrade($request, $student, $subject, $semester);
    }

    private function saveQuickGrade(Request $request, Student $student, Subject $subject, Semester $semester)
    {
        $changed = $this->changedGrades(
            Enrollment::with('grade')->where(['student_id' => $student->id, 'subject_id' => $subject->id, 'semester_id' => $semester->id])->get(),
            fn ($e) => [$request->input('grade'), $request->input('re_exam_grade')]
        );
        if ($rejection = $this->guardGradeChanges($request, $semester, $changed)) {
            return $rejection;
        }

        $registrarId = auth()->id();

        try {
            $grade = DB::transaction(fn () => $this->saveFinalizedGrade(
                $student->id, $subject->id, $semester->id,
                $request->input('grade'), $request->input('re_exam_grade'), $registrarId
            ));
        } catch (\Throwable $e) {
            Log::error('Quick Encode Grade failed, transaction rolled back', [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Something went wrong while saving the grade. No changes were made. Please try again.',
            ], 500);
        }

        $this->logOverrides($request, $semester, $changed);

        return response()->json([
            'success'       => true,
            'grade'         => number_format($grade->grade, 2),
            're_exam_grade' => $grade->re_exam_grade !== null ? number_format($grade->re_exam_grade, 2) : null,
            'status'        => $grade->status,
            'encoded_by'    => auth()->user()->name,
            'encoded_email' => auth()->user()->email,
            'encoded_at'    => now()->format('M d, Y g:i A'),
            'semester_id'   => $semester->id,
            'school_year'   => $semester->schoolYear?->year_code,
            'locked'        => $semester->isGradeLocked(),
        ]);
    }

    public function encodeGrades(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|exists:students,id',
            'semester_id' => 'required|exists:semesters,id',
            'grades'      => 'required|array|min:1',
            'grades.*'    => 'required|numeric|min:1.00|max:5.00',
            're_exam'     => 'nullable|array',
            're_exam.*'   => 'nullable|numeric|min:1.00|max:5.00',
        ]);

        $studentId   = $request->input('student_id');
        $semesterId  = $request->input('semester_id');
        $gradesInput = $request->input('grades'); // [ subject_id => grade_value ]
        $reExamInput = $request->input('re_exam', []); // [ subject_id => re_exam_value|null ]
        $registrarId = auth()->id();

        $semester = Semester::with('schoolYear')->findOrFail($semesterId);
        $changed  = $this->changedGrades(
            Enrollment::with('grade')->where('student_id', $studentId)->where('semester_id', $semesterId)
                ->whereIn('subject_id', array_keys($gradesInput))->get(),
            fn ($e) => [$gradesInput[$e->subject_id] ?? null, $reExamInput[$e->subject_id] ?? null]
        );
        if ($rejection = $this->guardGradeChanges($request, $semester, $changed)) {
            return $rejection;
        }

        try {
            DB::transaction(function () use ($gradesInput, $reExamInput, $studentId, $semesterId, $registrarId) {
                foreach ($gradesInput as $subjectId => $gradeValue) {
                    if (!Subject::whereKey($subjectId)->exists()) {
                        Log::warning('Encode Grades: skipped unknown subject_id', [
                            'subject_id' => $subjectId,
                            'student_id' => $studentId,
                        ]);
                        continue;
                    }

                    $this->saveFinalizedGrade((int) $studentId, (int) $subjectId, (int) $semesterId, $gradeValue, $reExamInput[$subjectId] ?? null, $registrarId);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Encode Grades failed, transaction rolled back', [
                'student_id' => $studentId,
                'semester_id' => $semesterId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Something went wrong while saving grades. No grades were changed. Please try again.');
        }

        $this->logOverrides($request, $semester, $changed);

        $student = Student::find($studentId);

        return redirect()
            ->route('registrar.students.profile', $student)
            ->with('success', 'Grades encoded and finalized successfully. You can now generate the COG below.');
    }

    public function index(Request $request)
    {
        $stats = [
            'pending_finalization' => GradeSubmission::approved()->whereNull('finalized_at')->count(),
            'finalized_grades'     => GradeSubmission::finalized()->count(),
            'cog_generated'        => CogRecord::count(),
            'tor_generated'        => TorRecord::count(),
        ];

        // Group pending submissions by subject
        $pending_submissions = GradeSubmission::with([
                'grade.enrollment.student',
                'grade.enrollment.subject',
                'reviewedBy'
            ])
            ->approved()
            ->whereNull('finalized_at')
            ->latest()
            ->get()
            ->groupBy(fn($s) => $s->grade->enrollment->subject_id);

        $search   = $request->input('search');
        $students = Student::with('course')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('student_number', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return view('registrar.dashboard', compact('stats', 'pending_submissions', 'search', 'students'));
    }

    /** True when the submitted values would change a grade that is already finalized. */
    /**
     * Existing grades this save would change, with their values before the save.
     * $newValues($enrollment) returns [grade, re_exam] for that enrollment.
     */
    private function changedGrades(Collection $enrollments, callable $newValues): Collection
    {
        return $enrollments
            ->filter(fn ($e) => $e->grade !== null)
            ->map(function ($e) use ($newValues) {
                [$newGrade, $newReExam] = $newValues($e);

                return $this->gradeDiffers($e->grade, $newGrade, $newReExam)
                    ? ['grade' => $e->grade, 'old' => ['grade' => $e->grade->grade, 're_exam_grade' => $e->grade->re_exam_grade, 'status' => $e->grade->status]]
                    : null;
            })
            ->filter()
            ->values();
    }

    /**
     * Past school year: any change to an existing grade needs a reason and the password.
     * Active school year: changing a finalized grade needs the password.
     * Returns the rejection response, or null when the save may go ahead.
     */
    private function guardGradeChanges(Request $request, Semester $semester, Collection $changed): ?Response
    {
        if ($changed->isEmpty()) {
            return null;
        }

        if ($semester->isGradeLocked()) {
            if ($error = $this->overrideReasonError($request, $semester)) {
                return RequireActionPassword::reject($request, $error);
            }
        } elseif (! $changed->contains(fn ($c) => $c['old']['status'] === 'finalized')) {
            return null;
        }

        if ($error = RequireActionPassword::verify($request)) {
            return RequireActionPassword::reject($request, $error);
        }

        return null;
    }

    private function overrideReasonError(Request $request, Semester $semester): ?string
    {
        if (mb_strlen(trim((string) $request->input('override_reason'))) >= self::OVERRIDE_REASON_MIN) {
            return null;
        }

        return 'Grades for SY ' . ($semester->schoolYear?->year_code ?? '?') . ' are locked. Give a reason (at least '
            . self::OVERRIDE_REASON_MIN . ' characters) to correct them. Nothing was changed.';
    }

    /** One "override" activity entry per changed grade of a locked semester. */
    private function logOverrides(Request $request, Semester $semester, Collection $changed): void
    {
        if (! $semester->isGradeLocked()) {
            return;
        }

        foreach ($changed as $change) {
            $grade = $change['grade']->fresh();
            $this->logOverride($request, $semester, $grade, 'Corrected a past school year grade', $change['old'], [
                'grade' => $grade->grade, 're_exam_grade' => $grade->re_exam_grade, 'status' => $grade->status,
            ]);
        }
    }

    private function logOverride(Request $request, Semester $semester, Grade $grade, string $description, array $old, array $new): void
    {
        // Keep only the fields that actually changed.
        $changedKeys = array_keys(array_filter($new, fn ($value, $key) => (string) $value !== (string) ($old[$key] ?? ''), ARRAY_FILTER_USE_BOTH));
        $old = array_intersect_key($old, array_flip($changedKeys));
        $new = array_intersect_key($new, array_flip($changedKeys));

        activity('grade')
            ->causedBy($request->user())
            ->performedOn($grade)
            ->event('override')
            ->withProperties([
                'old'        => $old,
                'attributes' => $new,
                'reason'     => trim((string) $request->input('override_reason')),
                'term'       => $semester->semester_name . ' ' . ($semester->schoolYear?->year_code ?? ''),
            ])
            ->log($description);
    }

    private function gradeDiffers(Grade $grade, $newGrade, $newReExam): bool
    {
        $normalize = fn ($value) => filled($value) ? round((float) $value, 2) : null;

        return $normalize($grade->grade) !== $normalize($newGrade)
            || $normalize($grade->re_exam_grade) !== $normalize($newReExam);
    }

    /**
     * Saves one registrar-encoded grade as finalized: enrollment (kept if it exists),
     * grade (faculty_id null; remarks preserved) and its finalized submission.
     */
    private function saveFinalizedGrade(int $studentId, int $subjectId, int $semesterId, $gradeValue, $reExamValue, int $registrarId): Grade
    {
        $enrollment = Enrollment::updateOrCreate(
            ['student_id' => $studentId, 'subject_id' => $subjectId, 'semester_id' => $semesterId],
            ['enrolled_by' => $registrarId, 'enrollment_date' => now()->toDateString(), 'status' => 'enrolled']
        );

        $grade = Grade::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            ['faculty_id' => null, 'grade' => $gradeValue, 're_exam_grade' => filled($reExamValue) ? $reExamValue : null, 'status' => 'finalized']
        );

        GradeSubmission::updateOrCreate(
            ['grade_id' => $grade->id],
            [
                'submitted_by' => $registrarId,
                'reviewed_by'  => $registrarId,
                'finalized_by' => $registrarId,
                'submitted_at' => now(),
                'reviewed_at'  => now(),
                'finalized_at' => now(),
                'dean_action'  => 'approved_by_head_of_department',
                'dean_remarks' => 'Direct entry by Registrar',
            ]
        );

        return $grade;
    }

    /**
     * Encode by Subject: pick a subject taught in a semester and encode the grades
     * of every student enrolled in it (one teacher's grade sheet) at once.
     */
    public function encodeBySubjectForm(Request $request)
    {
        $schoolYears = SchoolYear::whereIn('status', ['active', 'completed'])->orderBy('year_code', 'desc')->get();
        $departments = Department::orderBy('name')->get();

        $selectedSchoolYear = $request->input('school_year_id') ?? optional(SchoolYear::where('status', 'active')->first())->id;
        $selectedSemester   = $request->input('semester_id');
        $selectedDepartment = $request->input('department_id');
        $selectedCourse     = $request->input('course_id');
        $selectedSubject    = $request->input('subject_id');

        $semesters = $selectedSchoolYear
            ? Semester::where('school_year_id', $selectedSchoolYear)->whereIn('status', ['active', 'completed'])->orderBy('semester_order')->get()
            : collect();
        if ($selectedSemester && ! $semesters->contains('id', (int) $selectedSemester)) {
            $selectedSemester = null;
        }

        $courses = Course::active()
            ->when($selectedDepartment, fn ($q) => $q->where('department_id', $selectedDepartment))
            ->orderBy('name')
            ->get();

        // Only subjects that actually have students enrolled in the chosen semester.
        $subjects = $selectedSemester
            ? Subject::with('course')
                ->whereHas('enrollments', fn ($q) => $q->where('semester_id', $selectedSemester)->where('status', '!=', 'dropped'))
                ->when($selectedCourse, fn ($q) => $q->where('course_id', $selectedCourse))
                ->when($selectedDepartment && ! $selectedCourse, fn ($q) => $q->whereHas('course', fn ($c) => $c->where('department_id', $selectedDepartment)))
                ->orderBy('code')
                ->get()
            : collect();
        if ($selectedSubject && ! $subjects->contains('id', (int) $selectedSubject)) {
            $selectedSubject = null;
        }

        $subjectModel  = $selectedSubject ? $subjects->firstWhere('id', (int) $selectedSubject) : null;
        $semesterModel = $selectedSemester ? $semesters->firstWhere('id', (int) $selectedSemester) : null;

        $classList = $subjectModel
            ? Enrollment::with(['student.course', 'grade'])
                ->where('subject_id', $subjectModel->id)
                ->where('semester_id', $semesterModel->id)
                ->where('status', '!=', 'dropped')
                ->get()
                ->sortBy(fn ($e) => mb_strtolower($e->student->last_name . ' ' . $e->student->first_name))
                ->values()
            : collect();

        return view('registrar.encode-grades-by-subject', compact(
            'schoolYears', 'semesters', 'departments', 'courses', 'subjects', 'classList',
            'selectedSchoolYear', 'selectedSemester', 'selectedDepartment', 'selectedCourse', 'selectedSubject',
            'subjectModel', 'semesterModel'
        ));
    }

    public function encodeBySubject(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'subject_id'  => 'required|exists:subjects,id',
            'grades'      => 'required|array',
            'grades.*'    => 'nullable|numeric|min:1.00|max:5.00',
            're_exam'     => 'nullable|array',
            're_exam.*'   => 'nullable|numeric|min:1.00|max:5.00',
        ]);

        $semesterId = (int) $request->input('semester_id');
        $subjectId  = (int) $request->input('subject_id');
        $reExams    = $request->input('re_exam', []);

        // Blank rows are skipped (grade not yet available); a re-exam needs a grade.
        $grades = array_filter($request->input('grades', []), fn ($value) => filled($value));
        foreach ($reExams as $studentId => $reExam) {
            if (filled($reExam) && ! array_key_exists($studentId, $grades)) {
                return back()->withInput()->with('error', 'A re-exam grade needs a final grade in the same row.');
            }
        }
        if (empty($grades)) {
            return back()->withInput()->with('error', 'Enter at least one grade.');
        }

        // Only students enrolled in this subject for this semester can be graded here.
        $enrollments = Enrollment::with('grade')
            ->where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->where('status', '!=', 'dropped')
            ->whereIn('student_id', array_keys($grades))
            ->get()
            ->keyBy('student_id');

        if ($enrollments->count() !== count($grades)) {
            return back()->withInput()->with('error', 'Some rows are not enrolled in this subject for this semester. Reload the class list and try again.');
        }

        $semester = Semester::with('schoolYear')->findOrFail($semesterId);
        $changed  = $this->changedGrades($enrollments, fn ($e) => [$grades[$e->student_id], $reExams[$e->student_id] ?? null]);
        if ($rejection = $this->guardGradeChanges($request, $semester, $changed)) {
            return $rejection;
        }

        $registrarId = auth()->id();

        try {
            DB::transaction(function () use ($grades, $reExams, $subjectId, $semesterId, $registrarId) {
                foreach ($grades as $studentId => $gradeValue) {
                    $this->saveFinalizedGrade((int) $studentId, $subjectId, $semesterId, $gradeValue, $reExams[$studentId] ?? null, $registrarId);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Encode by Subject failed, transaction rolled back', [
                'subject_id'  => $subjectId,
                'semester_id' => $semesterId,
                'error'       => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Something went wrong while saving grades. No grades were changed. Please try again.');
        }

        $this->logOverrides($request, $semester, $changed);

        $subject = Subject::find($subjectId);

        return redirect()
            ->route('registrar.encode-grades.by-subject', $request->only(['school_year_id', 'semester_id', 'department_id', 'course_id', 'subject_id']))
            ->with('success', count($grades) . " grade(s) for {$subject->code} saved and finalized.");
    }
}
