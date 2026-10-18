<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use App\Models\Major;
use App\Models\Semester;
use App\Support\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function __construct()
    {
        // Deletes and high-risk changes require the user's own password (RequireActionPassword).
        $this->middleware('action.password')->only(['destroy']);
    }

    private const PHOTO_RULE = 'image|mimes:jpg,jpeg,png|max:5120|dimensions:min_width=200,min_height=200';

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
            'birth_date'     => 'required|date|before:today|after:1950-01-01',
            'gender'         => 'required|in:Male,Female',
            'email'          => 'required|email|unique:students,email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'student_type'   => 'required|in:Regular,Irregular,Transferee',
            'status'         => 'required|in:active,inactive,graduated,dropped',
        ] + $this->backgroundRules(), $this->validationMessages());

        $student = Student::create(collect($validated)->except('photo')->all());

        if ($request->hasFile('photo') && ($error = $this->savePhoto($student, $request->file('photo')))) {
            return redirect()->route('registrar.students.edit', $student)
                             ->with('error', "Student added, but the ID picture was not saved: {$error}");
        }

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
            'birth_date'     => 'required|date|before:today|after:1950-01-01',
            'gender'         => 'required|in:Male,Female',
            'email'          => 'required|email|unique:students,email,' . $student->id,
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'year_level'     => 'required|integer|min:1|max:5',
            'student_type'   => 'required|in:Regular,Irregular,Transferee',
            'status'         => 'required|in:active,inactive,graduated,dropped',
        ] + $this->backgroundRules(), $this->validationMessages());

        $student->update(collect($validated)->except('photo')->all());

        if ($request->hasFile('photo') && ($error = $this->savePhoto($student, $request->file('photo')))) {
            return back()->with('error', "Student updated, but the ID picture was not saved: {$error}");
        }

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
        $request->validate(['photo' => 'required|' . self::PHOTO_RULE], $this->validationMessages());

        if ($error = $this->savePhoto($student, $request->file('photo'))) {
            return response()->json(['message' => $error], 422);
        }

        return response()->json([
            'message' => 'ID picture saved.',
            'photo'   => $student->photoDataUri(),
        ]);
    }

    /** Crops to a square, resizes and stores the ID picture; returns an error message or null. */
    private function savePhoto(Student $student, UploadedFile $file): ?string
    {
        if (! ImageProcessor::available()) {
            return 'The PHP GD extension is not enabled on this server.';
        }

        $jpeg = ImageProcessor::squareJpeg($file->getRealPath());

        if ($jpeg === null) {
            return 'The picture could not be read. Use a JPG or PNG file.';
        }

        $path = 'student-photos/' . $student->id . '.jpg';
        Storage::disk('local')->put($path, $jpeg);

        $student->photo_path = $path;
        $student->save();

        activity('student')->performedOn($student)->event('updated')->log('Updated ID picture');

        return null;
    }

    /** Place of birth, preliminary education and the ID picture — all optional. */
    private function backgroundRules(): array
    {
        $year = 'nullable|integer|digits:4|min:1950|max:' . now()->year;

        return [
            'place_of_birth'              => 'nullable|string|max:255',
            'elementary_school'           => 'nullable|string|max:255',
            'elementary_graduation_year'  => $year,
            'high_school'                 => 'nullable|string|max:255',
            'high_school_graduation_year' => $year,
            'photo'                       => 'nullable|' . self::PHOTO_RULE,
        ];
    }

    private function validationMessages(): array
    {
        return [
            'birth_date.before'                  => 'The birth date must be before today.',
            'birth_date.after'                   => 'The birth date must be after January 1, 1950.',
            'elementary_graduation_year.digits'  => 'Enter a 4-digit year, e.g. 2012.',
            'high_school_graduation_year.digits' => 'Enter a 4-digit year, e.g. 2018.',
            'photo.max'                          => 'The picture must not be larger than 5 MB.',
            'photo.dimensions'                   => 'The picture must be at least 200 x 200 pixels.',
        ];
    }
}
