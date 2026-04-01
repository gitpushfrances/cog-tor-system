<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
});

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('admin'))              return redirect()->route('admin.dashboard');
    if ($user->hasRole('head_of_department')) return redirect()->route('head_of_department.dashboard');
    if ($user->hasRole('faculty'))            return redirect()->route('faculty.dashboard');
    if ($user->hasRole('registrar'))          return redirect()->route('registrar.dashboard');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
    Route::get('backup', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('admin.backup.index');
    Route::post('backup/run', [App\Http\Controllers\Admin\BackupController::class, 'run'])->name('admin.backup.run');
    Route::get('backup/download/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('admin.backup.download');
    Route::delete('backup/delete/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('admin.backup.delete');
    Route::post('backup/restore', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('admin.backup.restore');
Route::middleware(['auth', 'status', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/approve', [App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [App\Http\Controllers\Admin\UserController::class, 'reject'])->name('users.reject');

    // Department Management
    Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
    Route::post('/departments/{department}/deactivate', [App\Http\Controllers\Admin\DepartmentController::class, 'deactivate'])->name('departments.deactivate');

    // Course Management
    Route::resource('courses', App\Http\Controllers\Admin\CourseController::class);

    // Subject Management
    Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class);

    // School Year Management
    Route::resource('school-years', App\Http\Controllers\Admin\SchoolYearController::class);
    Route::post('/school-years/{schoolYear}/set-active', [App\Http\Controllers\Admin\SchoolYearController::class, 'setActive'])->name('school-years.set-active');

    // Semester Management
    Route::resource('semesters', App\Http\Controllers\Admin\SemesterController::class);
    Route::post('/semesters/{semester}/set-active', [App\Http\Controllers\Admin\SemesterController::class, 'setActive'])->name('semesters.set-active');
});

// Head of Department Routes
Route::middleware(['auth', 'status', 'role:head_of_department'])->prefix('head-of-department')->name('head_of_department.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\HeadOfDepartment\HeadOfDepartmentController::class, 'index'])->name('dashboard');

    // Grade Submissions
    Route::get('/submissions/{submission}/review', [App\Http\Controllers\HeadOfDepartment\HeadOfDepartmentController::class, 'review'])->name('submissions.review');
    Route::post('/submissions/{submission}/approve', [App\Http\Controllers\HeadOfDepartment\HeadOfDepartmentController::class, 'approve'])->name('submissions.approve');
    Route::post('/submissions/{submission}/reject', [App\Http\Controllers\HeadOfDepartment\HeadOfDepartmentController::class, 'reject'])->name('submissions.reject');

    // Enrollment Management
    Route::get('/enrollments', [App\Http\Controllers\HeadOfDepartment\EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('/enrollments', [App\Http\Controllers\HeadOfDepartment\EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('/enrollments/{enrollment}', [App\Http\Controllers\HeadOfDepartment\EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

    // Subject Assignment
    Route::get('/assignments', [App\Http\Controllers\HeadOfDepartment\SubjectAssignmentController::class, 'index'])->name('assignments.index');
    Route::put('/assignments/{subject}', [App\Http\Controllers\HeadOfDepartment\SubjectAssignmentController::class, 'update'])->name('assignments.update');

    // Student Management
    Route::resource('students', App\Http\Controllers\HeadOfDepartment\StudentController::class);
    Route::get('/excel/student-template', [App\Http\Controllers\HeadOfDepartment\ExcelController::class, 'studentTemplate'])->name('excel.student-template');
    Route::get('/excel/export-students', [App\Http\Controllers\HeadOfDepartment\ExcelController::class, 'exportStudents'])->name('excel.export-students');
    Route::post('/excel/import-students', [App\Http\Controllers\HeadOfDepartment\ExcelController::class, 'importStudents'])->name('excel.import-students');
});

// Faculty Routes
Route::middleware(['auth', 'status', 'role:faculty'])->prefix('faculty')->name('faculty.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Faculty\FacultyController::class, 'index'])->name('dashboard');
    Route::get('/subjects', [App\Http\Controllers\Faculty\FacultyController::class, 'subjects'])->name('subjects');
    Route::get('/subjects/{subject}/grades/template', [App\Http\Controllers\Faculty\ExcelController::class, 'downloadTemplate'])->name('subjects.grades.template');
    Route::post('/subjects/{subject}/grades/upload', [App\Http\Controllers\Faculty\ExcelController::class, 'uploadGrades'])->name('subjects.grades.upload');
    Route::get('/subjects/{subject}/grades', [App\Http\Controllers\Faculty\GradeController::class, 'index'])->name('subjects.grades');
    Route::post('/subjects/{subject}/grades', [App\Http\Controllers\Faculty\GradeController::class, 'store'])->name('subjects.grades.store');
    Route::get('/subjects/{subject}/grades/{grade}/edit', [App\Http\Controllers\Faculty\GradeController::class, 'edit'])->name('subjects.grades.edit');
    Route::put('/subjects/{subject}/grades/{grade}', [App\Http\Controllers\Faculty\GradeController::class, 'update'])->name('subjects.grades.update');
    Route::post('/subjects/{subject}/submit', [App\Http\Controllers\Faculty\GradeController::class, 'submit'])->name('subjects.grades.submit');
    Route::post('/subjects/{subject}/resubmit', [App\Http\Controllers\Faculty\GradeController::class, 'resubmit'])->name('subjects.grades.resubmit');
});

// Registrar Routes
Route::middleware(['auth', 'status', 'role:registrar'])->prefix('registrar')->name('registrar.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Registrar\RegistrarController::class, 'index'])->name('dashboard');
    Route::post('/submissions/{submission}/finalize', [App\Http\Controllers\Registrar\RegistrarController::class, 'finalize'])->name('submissions.finalize');
    Route::post('/submissions/finalize-subject/{subjectId}', [App\Http\Controllers\Registrar\RegistrarController::class, 'finalizeSubject'])->name('submissions.finalize-subject');
Route::post('/submissions/unfinalize-subject/{subjectId}', [App\Http\Controllers\Registrar\RegistrarController::class, 'unfinalizeSubject'])->name('submissions.unfinalize-subject');
    Route::get('/students/{student}/profile', [App\Http\Controllers\Registrar\DocumentController::class, 'studentProfile'])->name('students.profile');
    Route::get('/students/{student}/cog', [App\Http\Controllers\Registrar\DocumentController::class, 'cogForm'])->name('students.cog');
    Route::post('/students/{student}/cog', [App\Http\Controllers\Registrar\DocumentController::class, 'generateCog'])->name('students.cog.generate');
    Route::get('/students/{student}/tor', [App\Http\Controllers\Registrar\DocumentController::class, 'torForm'])->name('students.tor');
    Route::post('/students/{student}/tor', [App\Http\Controllers\Registrar\DocumentController::class, 'generateTor'])->name('students.tor.generate');
    Route::get('/cog/{cog}/download', [App\Http\Controllers\Registrar\DocumentController::class, 'downloadCog'])->name('cog.download');
    Route::get('/tor/{tor}/download', [App\Http\Controllers\Registrar\DocumentController::class, 'downloadTor'])->name('tor.download');
});

require __DIR__.'/auth.php';
