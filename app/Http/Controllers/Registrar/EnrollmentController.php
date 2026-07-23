<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Semester;
use Illuminate\Http\Request;

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
}
