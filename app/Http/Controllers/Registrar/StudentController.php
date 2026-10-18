<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use App\Models\Major;
use App\Models\Semester;
use App\Support\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('course');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('course_id'))  $query->where('course_id', $request->course_id);
        if ($request->filled('year_level')) $query->where('year_level', $request->year_level);
        if ($request->filled('status'))     $query->where('status', $request->status);

        $students = $query->orderBy('last_name')->paginate(20)->withQueryString();
        $courses  = Course::active()->orderBy('name')->get();

        return view('registrar.students.index', compact('students', 'courses'));
    }

    public function show(Student $student)
    {
        $activeSemester = Semester::active()->first();

        $student->load('course.department');

        $enrollments = $student->enrollments()
            ->with(['subject.course', 'semester.schoolYear'])
            ->get();

        $currentEnrollments = $enrollments->filter(
            fn($e) => $activeSemester && $e->semester_id === $activeSemester->id
        );

        $pastEnrollments = $enrollments
            ->reject(fn($e) => $activeSemester && $e->semester_id === $activeSemester->id)
            ->sortByDesc(fn($e) => $e->semester->start_date)
            ->groupBy(fn($e) => $e->semester->getFullName());

        return view('registrar.students.show', compact(
            'student', 'activeSemester', 'currentEnrollments', 'pastEnrollments'
        ));
    }

    public function create()
    {
        $courses = Course::active()->orderBy('name')->get();
        $majors = Major::active()->orderBy('name')->get();
        return view('registrar.students.create', compact('courses', 'majors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students,student_number',
            'course_id'      => 'required|exists:courses,id',
            'major_id'       => 'nullable|exists:majors,id',
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'last_name'      => 'required|string|max:100',
            'suffix'         => 'nullable|string|max:10',
            'birth_date'     => 'required|date',
            'gender'         => 'required|in:Male,Female',
            'email'          => 'required|email|unique:students,email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'student_type'   => 'required|in:Regular,Irregular,Transferee',
            'status'         => 'required|in:active,inactive,graduated,dropped',
        ]);

        Student::create($validated);

        return redirect()->route('registrar.students.index')
                         ->with('success', 'Student added successfully.');
    }

    public function edit(Student $student)
    {
        $courses = Course::active()->orderBy('name')->get();
        $majors = Major::active()->orderBy('name')->get();
        return view('registrar.students.edit', compact('student', 'courses', 'majors'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students,student_number,' . $student->id,
            'course_id'      => 'required|exists:courses,id',
            'major_id'       => 'nullable|exists:majors,id',
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'last_name'      => 'required|string|max:100',
            'suffix'         => 'nullable|string|max:10',
            'birth_date'     => 'required|date',
            'gender'         => 'required|in:Male,Female',
            'email'          => 'required|email|unique:students,email,' . $student->id,
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'student_type'   => 'required|in:Regular,Irregular,Transferee',
            'status'         => 'required|in:active,inactive,graduated,dropped',
        ]);

        $student->update($validated);

        return redirect()->route('registrar.students.index')
                         ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        if ($student->enrollments()->count() > 0) {
            return redirect()->route('registrar.students.index')
                             ->with('error', 'Cannot delete student with existing enrollments.');
        }

        if ($student->photo_path) {
            Storage::disk('local')->delete($student->photo_path);
        }
        Storage::disk('local')->deleteDirectory('previous-school/' . $student->id);

        if ($student->photo_path) {
            Storage::disk('local')->delete($student->photo_path);
        }
        Storage::disk('local')->deleteDirectory('previous-school/' . $student->id);

        $student->delete();

        return redirect()->route('registrar.students.index')
                         ->with('success', 'Student deleted successfully.');
    }

    /**
     * Save or replace the student's 2x2 ID picture (used on page 1 of the TOR).
     * The image is cropped to a square, resized to 600x600 and stored as JPEG
     * on the private local disk, one file per student.
     */
    public function updatePhoto(Request $request, Student $student)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120|dimensions:min_width=200,min_height=200',
        ], [
            'photo.max'        => 'The picture must not be larger than 5 MB.',
            'photo.dimensions' => 'The picture must be at least 200 x 200 pixels.',
        ]);

        if (! ImageProcessor::available()) {
            return response()->json(['message' => 'The PHP GD extension is not enabled on this server.'], 422);
        }

        $jpeg = ImageProcessor::squareJpeg($request->file('photo')->getRealPath());

        if ($jpeg === null) {
            return response()->json(['message' => 'The picture could not be read. Use a JPG or PNG file.'], 422);
        }

        $path = 'student-photos/' . $student->id . '.jpg';
        Storage::disk('local')->put($path, $jpeg);

        $student->photo_path = $path;
        $student->save();

        return response()->json([
            'message' => 'ID picture saved.',
            'photo'   => $student->photoDataUri(),
        ]);
    }

    /**
     * Save or replace the student's 2x2 ID picture (used on page 1 of the TOR).
     * The image is cropped to a square, resized to 600x600 and stored as JPEG
     * on the private local disk, one file per student.
     */
    public function updatePhoto(Request $request, Student $student)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120|dimensions:min_width=200,min_height=200',
        ], [
            'photo.max'        => 'The picture must not be larger than 5 MB.',
            'photo.dimensions' => 'The picture must be at least 200 x 200 pixels.',
        ]);

        if (! ImageProcessor::available()) {
            return response()->json(['message' => 'The PHP GD extension is not enabled on this server.'], 422);
        }

        $jpeg = ImageProcessor::squareJpeg($request->file('photo')->getRealPath());

        if ($jpeg === null) {
            return response()->json(['message' => 'The picture could not be read. Use a JPG or PNG file.'], 422);
        }

        $path = 'student-photos/' . $student->id . '.jpg';
        Storage::disk('local')->put($path, $jpeg);

        $student->photo_path = $path;
        $student->save();

        return response()->json([
            'message' => 'ID picture saved.',
            'photo'   => $student->photoDataUri(),
        ]);
    }
}
