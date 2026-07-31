<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $activeSemester = Semester::active()->first();

        $dateFilter = $request->get('date_filter', 'all');
        $groupBy    = $request->get('group_by', 'none');
        $dateFrom   = $request->get('date_from');
        $dateTo     = $request->get('date_to');

        $query = Enrollment::with(['student.course.department', 'subject.course', 'semester'])
            ->when($activeSemester, fn($q) => $q->where('semester_id', $activeSemester->id));

        if ($dateFilter === 'today') {
            $query->whereDate('enrollment_date', today());
        } elseif ($dateFilter === 'week') {
            $query->where('enrollment_date', '>=', now()->subWeek());
        } elseif ($dateFilter === 'month') {
            $query->where('enrollment_date', '>=', now()->subMonth());
        } elseif ($dateFilter === 'custom') {
            if ($dateFrom) {
                $query->whereDate('enrollment_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('enrollment_date', '<=', $dateTo);
            }
        }

        $grouped = null;
        $enrollments = null;

        if ($groupBy !== 'none') {
            $all = $query->latest('enrollment_date')->get();

            $grouped = match ($groupBy) {
                'subject'    => $all->groupBy(fn($e) => $e->subject->code . ' — ' . $e->subject->name),
                'department' => $all->groupBy(fn($e) => $e->student->course->department->name ?? 'Unassigned'),
                'year_level' => $all->groupBy(fn($e) => 'Year ' . $e->student->year_level),
                default      => null,
            };
        } else {
            $enrollments = $query->latest('enrollment_date')->paginate(20)->withQueryString();
        }

        $students = Student::active()->orderBy('last_name')->get();
        $subjects = Subject::active()->orderBy('code')->get();

        $enrolledMap = Enrollment::where('semester_id', $activeSemester?->id)
            ->get()
            ->groupBy('student_id')
            ->map(fn($rows) => $rows->pluck('subject_id')->toArray());

        $studentCourseMap = $students->pluck('course_id', 'id');

        return view('registrar.enrollments.index', compact(
            'enrollments', 'grouped', 'students', 'subjects', 'activeSemester', 'enrolledMap',
            'studentCourseMap', 'dateFilter', 'groupBy', 'dateFrom', 'dateTo'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $activeSemester = Semester::active()->firstOrFail();

        $subject = Subject::findOrFail($request->subject_id);
        $student = Student::findOrFail($request->student_id);

        $isCrossCourse = $subject->course_id !== $student->course_id;

        $alreadyEnrolled = Enrollment::where([
            'student_id'  => $student->id,
            'subject_id'  => $subject->id,
            'semester_id' => $activeSemester->id,
        ])->exists();

        if ($alreadyEnrolled) {
            return back()
                ->withInput(['student_id' => $student->id])
                ->with('error', 'Student is already enrolled in this subject for the active semester.');
        }

        Enrollment::create([
            'student_id'      => $student->id,
            'subject_id'      => $subject->id,
            'semester_id'     => $activeSemester->id,
            'enrolled_by'     => auth()->id(),
            'enrollment_date' => now(),
            'status'          => 'enrolled',
        ]);

        $message = "{$student->getFullName()} has been enrolled in {$subject->code} — {$subject->name} for {$activeSemester->semester_name}.";

        if ($isCrossCourse) {
            $message .= ' (Irregular enrollment — subject is outside their course.)';
        }

        return back()
            ->withInput(['student_id' => $student->id])
            ->with('success', $message);
    }

    public function destroy(Enrollment $enrollment)
    {
        if ($enrollment->grade()->exists()) {
            return back()->with('error', 'Cannot remove enrollment — a grade record already exists.');
        }

        $enrollment->delete();

        return back()->with('success', 'Enrollment removed.');
    }

    public function bulkCreate(Request $request)
    {
        $activeSemester = Semester::active()->first();

        $subjectId = $request->get('subject_id');
        $courseId  = $request->get('course_id');
        $yearLevel = $request->get('year_level');
        $search    = $request->get('search');

        $subjects = Subject::active()->orderBy('code')->get();
        $courses  = Course::active()->orderBy('name')->get();

        $students         = collect();
        $selectedSubject  = null;
        $tooMany          = false;
        $enrolledStudentIds = [];

        if ($subjectId && $activeSemester) {
            $selectedSubject = Subject::find($subjectId);

            $query = Student::active()->with('course')
                ->when($courseId, fn($q) => $q->where('course_id', $courseId))
                ->when($yearLevel, fn($q) => $q->where('year_level', $yearLevel))
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('student_number', 'like', "%{$search}%");
                    });
                });

            if ($query->count() > 200) {
                $tooMany = true;
            } else {
                $students = $query->orderBy('last_name')->get();
            }

            if ($selectedSubject) {
                $enrolledStudentIds = Enrollment::where('subject_id', $selectedSubject->id)
                    ->where('semester_id', $activeSemester->id)
                    ->pluck('student_id')
                    ->toArray();
            }
        }

        return view('registrar.enrollments.bulk', compact(
            'subjects', 'courses', 'students', 'selectedSubject', 'activeSemester',
            'subjectId', 'courseId', 'yearLevel', 'search', 'enrolledStudentIds', 'tooMany'
        ));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'subject_id'    => 'required|exists:subjects,id',
            'student_ids'   => 'required|array|min:1|max:200',
            'student_ids.*' => 'exists:students,id',
        ]);

        $activeSemester = Semester::active()->first();

        if (!$activeSemester) {
            return back()->with('error', 'No active semester set. Contact Admin.');
        }

        $subject = Subject::findOrFail($request->subject_id);
        $enrolled = 0;
        $skipped = [];

        DB::transaction(function () use ($request, $subject, $activeSemester, &$enrolled, &$skipped) {
            foreach ($request->student_ids as $studentId) {
                $student = Student::find($studentId);

                if (!$student || !$student->isActive()) {
                    $skipped[] = ($student->getFullName() ?? "Student #{$studentId}") . ' — not active';
                    continue;
                }

                $alreadyEnrolled = Enrollment::where([
                    'student_id'  => $student->id,
                    'subject_id'  => $subject->id,
                    'semester_id' => $activeSemester->id,
                ])->exists();

                if ($alreadyEnrolled) {
                    $skipped[] = $student->getFullName() . ' — already enrolled';
                    continue;
                }

                Enrollment::create([
                    'student_id'      => $student->id,
                    'subject_id'      => $subject->id,
                    'semester_id'     => $activeSemester->id,
                    'enrolled_by'     => auth()->id(),
                    'enrollment_date' => now(),
                    'status'          => 'enrolled',
                ]);

                $enrolled++;
            }
        });

        return redirect()
            ->route('registrar.enrollments.bulk-create', ['subject_id' => $subject->id])
            ->with('bulk_report', [
                'subject'  => $subject->getFullName(),
                'enrolled' => $enrolled,
                'skipped'  => $skipped,
            ]);
    }
}
