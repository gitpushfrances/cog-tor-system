<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
    if ($user->hasRole('dean')) return redirect()->route('dean.dashboard');
    if ($user->hasRole('faculty')) return redirect()->route('faculty.dashboard');
    if ($user->hasRole('registrar')) return redirect()->route('registrar.dashboard');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
   // Admin Routes
    Route::middleware(['auth', 'status', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');

        // User Management
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        Route::post('/users/{user}/approve', [App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject', [App\Http\Controllers\Admin\UserController::class, 'reject'])->name('users.reject');

        // Department Management
        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);

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

        // Student Management
        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
        Route::get('/excel/student-template', [App\Http\Controllers\Admin\ExcelController::class, 'studentTemplate'])->name('excel.student-template');
        Route::get('/excel/export-students', [App\Http\Controllers\Admin\ExcelController::class, 'exportStudents'])->name('excel.export-students');
        Route::post('/excel/import-students', [App\Http\Controllers\Admin\ExcelController::class, 'importStudents'])->name('excel.import-students');
    });

    // Dean Routes
    Route::middleware(['auth', 'status', 'role:dean'])->prefix('dean')->name('dean.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Dean\DeanController::class, 'index'])->name('dashboard');
        Route::get('/submissions/{submission}/review', [App\Http\Controllers\Dean\DeanController::class, 'review'])->name('submissions.review');
        Route::post('/submissions/{submission}/approve', [App\Http\Controllers\Dean\DeanController::class, 'approve'])->name('submissions.approve');
        Route::post('/submissions/{submission}/reject', [App\Http\Controllers\Dean\DeanController::class, 'reject'])->name('submissions.reject');
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
    });

    // Registrar Routes
    Route::middleware(['auth', 'status', 'role:registrar'])->prefix('registrar')->name('registrar.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Registrar\RegistrarController::class, 'index'])->name('dashboard');
        Route::post('/submissions/{submission}/finalize', [App\Http\Controllers\Registrar\RegistrarController::class, 'finalize'])->name('submissions.finalize');
        Route::get('/students', [App\Http\Controllers\Registrar\DocumentController::class, 'students'])->name('students');
        Route::get('/students/{student}/cog', [App\Http\Controllers\Registrar\DocumentController::class, 'cogForm'])->name('students.cog');
        Route::post('/students/{student}/cog', [App\Http\Controllers\Registrar\DocumentController::class, 'generateCog'])->name('students.cog.generate');
        Route::get('/students/{student}/tor', [App\Http\Controllers\Registrar\DocumentController::class, 'torForm'])->name('students.tor');
        Route::post('/students/{student}/tor', [App\Http\Controllers\Registrar\DocumentController::class, 'generateTor'])->name('students.tor.generate');
        Route::get('/cog/{cog}/download', [App\Http\Controllers\Registrar\DocumentController::class, 'downloadCog'])->name('cog.download');
        Route::get('/tor/{tor}/download', [App\Http\Controllers\Registrar\DocumentController::class, 'downloadTor'])->name('tor.download');
    });

require __DIR__.'/auth.php';
