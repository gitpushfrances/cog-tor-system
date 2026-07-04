<?php

namespace App\Http\Controllers\Registrar;

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

class RegistrarController extends Controller
{
    public function finalizeSubject(Request $request, $subjectId)
    {
        $submissions = GradeSubmission::whereHas('grade.enrollment', fn($q) =>
                $q->where('subject_id', $subjectId))
            ->approved()
            ->whereNull('finalized_at')
            ->get();

        foreach ($submissions as $sub) {
            $sub->update([
                'finalized_at' => now(),
                'finalized_by' => auth()->id(),
            ]);
            $sub->grade->update(['status' => 'finalized']);
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

    public function unfinalizeSubject(Request $request, $subjectId)
    {
        $submissions = GradeSubmission::whereHas('grade.enrollment', fn($q) =>
                $q->where('subject_id', $subjectId))
            ->whereNotNull('finalized_at')
            ->get();

        if ($submissions->isEmpty()) {
            return redirect()->back()->with('error', 'No finalized grades found for this subject.');
        }

        foreach ($submissions as $sub) {
            $sub->update([
                'finalized_at' => null,
                'finalized_by' => null,
            ]);
            $sub->grade->update(['status' => 'approved_by_head_of_department']);
        }

        return redirect()->back()
            ->with('success', count($submissions) . ' grade(s) unfinalized and returned to approved status.');
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

        $semesters = $selectedSchoolYear
            ? Semester::where('school_year_id', $selectedSchoolYear)
                ->whereIn('status', ['active', 'completed'])
                ->orderBy('semester_order')
                ->get()
            : collect();

        $courses = $selectedDepartment
            ? Course::where('department_id', $selectedDepartment)->active()->orderBy('name')->get()
            : collect();

        $subjects  = collect();
        $students  = collect();
        $existingGrades = collect();
        $selectedStudentModel = null;
        $selectedSemesterModel = null;

        if ($selectedCourse && $selectedSemester) {
            $selectedSemesterModel = Semester::find($selectedSemester);

            $subjects = Subject::where('course_id', $selectedCourse)
                ->where('semester', $selectedSemesterModel->semester_name ?? '')
                ->active()
                ->orderBy('name')
                ->get();

            $students = Student::where('course_id', $selectedCourse)
                ->active()
                ->orderBy('last_name')
                ->get();
        }

        if ($selectedStudent && $selectedSemester) {
            $selectedStudentModel = Student::find($selectedStudent);

            $existingGrades = Enrollment::with(['grade', 'subject'])
                ->where('student_id', $selectedStudent)
                ->where('semester_id', $selectedSemester)
                ->get()
                ->keyBy('subject_id');
        }

        return view('registrar.encode-grades', compact(
            'schoolYears', 'departments', 'semesters', 'courses',
            'subjects', 'students', 'existingGrades',
            'selectedSchoolYear', 'selectedSemester', 'selectedDepartment',
            'selectedCourse', 'selectedStudent', 'selectedStudentModel',
            'selectedSemesterModel'
        ));
    }

    public function encodeGrades(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|exists:students,id',
            'semester_id' => 'required|exists:semesters,id',
            'grades'      => 'required|array|min:1',
            'grades.*'    => 'required|numeric|min:1.00|max:5.00',
        ]);

        $studentId   = $request->input('student_id');
        $semesterId  = $request->input('semester_id');
        $gradesInput = $request->input('grades'); // [ subject_id => grade_value ]
        $registrarId = auth()->id();

        foreach ($gradesInput as $subjectId => $gradeValue) {
            $subject = Subject::find($subjectId);
            if (!$subject) continue;

            // Upsert enrollment
            $enrollment = Enrollment::updateOrCreate(
                [
                    'student_id'  => $studentId,
                    'subject_id'  => $subjectId,
                    'semester_id' => $semesterId,
                ],
                [
                    'enrolled_by'     => $registrarId,
                    'enrollment_date' => now()->toDateString(),
                    'status'          => 'enrolled',
                ]
            );

            // Upsert grade — faculty_id null (Registrar encoded, not Faculty)
            $grade = Grade::updateOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'faculty_id' => null,
                    'grade'      => $gradeValue,
                    'status'     => 'finalized',
                    // remarks intentionally preserved — do not null out
                ]
            );

            // Create GradeSubmission so stats + unfinalize work correctly
            GradeSubmission::updateOrCreate(
                ['grade_id' => $grade->id],
                [
                    'submitted_by'  => $registrarId,
                    'reviewed_by'   => $registrarId,
                    'finalized_by'  => $registrarId,
                    'submitted_at'  => now(),
                    'reviewed_at'   => now(),
                    'finalized_at'  => now(),
                    'dean_action'   => 'approved_by_head_of_department',
                    'dean_remarks'  => 'Direct entry by Registrar',
                ]
            );
        }

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
}
