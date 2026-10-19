<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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
    if ($user->hasRole('admin'))     return redirect()->route('admin.dashboard');
    if ($user->hasRole('registrar')) return redirect()->route('registrar.dashboard');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Self-deletion disabled: accounts are part of the record. Admin → Users sets an account to Inactive.
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'status', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');

    // Backup & Restore — now protected inside admin middleware
    Route::get('/backup', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backup.index');
    Route::get('/activity-log', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::post('/backup/run', [App\Http\Controllers\Admin\BackupController::class, 'run'])->name('backup.run');
    Route::get('/backup/download/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/delete/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('backup.delete');
    Route::post('/backup/restore', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backup.restore');

    // User Management
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/approve', [App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [App\Http\Controllers\Admin\UserController::class, 'reject'])->name('users.reject');

    // Department Management
    Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
    Route::post('/departments/{department}/deactivate', [App\Http\Controllers\Admin\DepartmentController::class, 'deactivate'])->name('departments.deactivate');

    // Course Management
    Route::resource('courses', App\Http\Controllers\Admin\CourseController::class);

    // Major Management
    Route::resource('majors', App\Http\Controllers\Admin\MajorController::class);

    // Subject Management
    Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class);

    // School Year Management
    Route::resource('school-years', App\Http\Controllers\Admin\SchoolYearController::class);
    Route::post('/school-years/{schoolYear}/set-active', [App\Http\Controllers\Admin\SchoolYearController::class, 'setActive'])->name('school-years.set-active');

    // Semester Management
    Route::resource('semesters', App\Http\Controllers\Admin\SemesterController::class);
    Route::post('/semesters/{semester}/set-active', [App\Http\Controllers\Admin\SemesterController::class, 'setActive'])->name('semesters.set-active');

    Route::get('/document-settings', [App\Http\Controllers\Admin\DocumentSettingController::class, 'edit'])->name('document-settings.edit');
    Route::put('/document-settings', [App\Http\Controllers\Admin\DocumentSettingController::class, 'update'])->name('document-settings.update');
});

// Head of Department Routes
Route::middleware(['auth', 'status', 'role:head_of_department_disabled'])->prefix('head-of-department')->name('head_of_department.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\HeadOfDepartment\HeadOfDepartmentController::class, 'index'])->name('dashboard');

    Route::get('/submissions/{submission}/review', [App\Http\Controllers\HeadOfDepartment\HeadOfDepartmentController::class, 'review'])->name('submissions.review');
    Route::post('/submissions/{submission}/approve', [App\Http\Controllers\HeadOfDepartment\HeadOfDepartmentController::class, 'approve'])->name('submissions.approve');
    Route::post('/submissions/{submission}/reject', [App\Http\Controllers\HeadOfDepartment\HeadOfDepartmentController::class, 'reject'])->name('submissions.reject');

    Route::get('/enrollments', [App\Http\Controllers\HeadOfDepartment\EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('/enrollments', [App\Http\Controllers\HeadOfDepartment\EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('/enrollments/{enrollment}', [App\Http\Controllers\HeadOfDepartment\EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

    Route::get('/assignments', [App\Http\Controllers\HeadOfDepartment\SubjectAssignmentController::class, 'index'])->name('assignments.index');
    Route::put('/assignments/{subject}', [App\Http\Controllers\HeadOfDepartment\SubjectAssignmentController::class, 'update'])->name('assignments.update');

    Route::resource('students', App\Http\Controllers\HeadOfDepartment\StudentController::class);
    Route::get('/excel/student-template', [App\Http\Controllers\HeadOfDepartment\ExcelController::class, 'studentTemplate'])->name('excel.student-template');
    Route::get('/excel/export-students', [App\Http\Controllers\HeadOfDepartment\ExcelController::class, 'exportStudents'])->name('excel.export-students');
    Route::post('/excel/import-students', [App\Http\Controllers\HeadOfDepartment\ExcelController::class, 'importStudents'])->name('excel.import-students');
});

// Faculty Routes
Route::middleware(['auth', 'status', 'role:faculty_disabled'])->prefix('faculty')->name('faculty.')->group(function () {
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
    Route::post('/students/{student}/cog/preview', [App\Http\Controllers\Registrar\DocumentController::class, 'cogPreview'])->name('students.cog.preview');
    Route::get('/students/{student}/tor', [App\Http\Controllers\Registrar\DocumentController::class, 'torForm'])->name('students.tor');
    Route::post('/students/{student}/tor', [App\Http\Controllers\Registrar\DocumentController::class, 'generateTor'])->name('students.tor.generate');
    Route::post('/students/{student}/tor/preview', [App\Http\Controllers\Registrar\DocumentController::class, 'torPreview'])->name('students.tor.preview');
    Route::get('/cog/{cog}/download', [App\Http\Controllers\Registrar\DocumentController::class, 'downloadCog'])->name('cog.download');
    Route::get('/tor/{tor}/download', [App\Http\Controllers\Registrar\DocumentController::class, 'downloadTor'])->name('tor.download');
    Route::get('/encode-grades', [App\Http\Controllers\Registrar\RegistrarController::class, 'encodeGradesForm'])->name('encode-grades');
    Route::post('/encode-grades', [App\Http\Controllers\Registrar\RegistrarController::class, 'encodeGrades'])->name('encode-grades.store');
    Route::get('/encode-grades/by-subject', [App\Http\Controllers\Registrar\RegistrarController::class, 'encodeBySubjectForm'])->name('encode-grades.by-subject');
    Route::post('/encode-grades/by-subject', [App\Http\Controllers\Registrar\RegistrarController::class, 'encodeBySubject'])->name('encode-grades.by-subject.store');
    Route::get('/students/{student}/grade-history', [App\Http\Controllers\Registrar\RegistrarController::class, 'studentGradeHistory'])->name('students.grade-history');
    Route::post('/students/{student}/quick-grade', [App\Http\Controllers\Registrar\RegistrarController::class, 'quickEncodeGrade'])->name('students.quick-grade');
    Route::post('/students/{student}/semesters/{semester}/unfinalize', [App\Http\Controllers\Registrar\RegistrarController::class, 'unfinalizeSemester'])->name('students.semesters.unfinalize');

    // Student Management
    Route::get('/students', [App\Http\Controllers\Registrar\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [App\Http\Controllers\Registrar\StudentController::class, 'create'])->name('students.create');
    Route::get('/students/bulk', [App\Http\Controllers\Registrar\StudentBulkController::class, 'create'])->name('students.bulk');
    Route::get('/students/bulk/template', [App\Http\Controllers\Registrar\StudentBulkController::class, 'template'])->name('students.bulk.template');
    Route::post('/students/bulk/preview', [App\Http\Controllers\Registrar\StudentBulkController::class, 'preview'])->name('students.bulk.preview');
    Route::post('/students/bulk', [App\Http\Controllers\Registrar\StudentBulkController::class, 'store'])->name('students.bulk.store');
    Route::post('/students', [App\Http\Controllers\Registrar\StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/details', [App\Http\Controllers\Registrar\StudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [App\Http\Controllers\Registrar\StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [App\Http\Controllers\Registrar\StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [App\Http\Controllers\Registrar\StudentController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/{student}/photo', [App\Http\Controllers\Registrar\StudentController::class, 'updatePhoto'])->name('students.photo.update');

    // Previous School Records (scanned TOR/COG from another school; Transferee and Irregular only)
    Route::get('/students/{student}/previous-school-records', [App\Http\Controllers\Registrar\PreviousSchoolRecordController::class, 'index'])->name('students.previous-school-records.index');
    Route::post('/students/{student}/previous-school-records', [App\Http\Controllers\Registrar\PreviousSchoolRecordController::class, 'store'])->name('students.previous-school-records.store');
    Route::post('/previous-school-records/{record}/pages', [App\Http\Controllers\Registrar\PreviousSchoolRecordController::class, 'addPages'])->name('previous-school-records.pages.store');
    Route::delete('/previous-school-records/{record}', [App\Http\Controllers\Registrar\PreviousSchoolRecordController::class, 'destroy'])->name('previous-school-records.destroy');
    Route::delete('/previous-school-pages/{page}', [App\Http\Controllers\Registrar\PreviousSchoolRecordController::class, 'destroyPage'])->name('previous-school-pages.destroy');
    Route::get('/previous-school-pages/{page}/image', [App\Http\Controllers\Registrar\PreviousSchoolRecordController::class, 'image'])->name('previous-school-pages.image');

    // Enrollment Management
    Route::get('/enrollments', [App\Http\Controllers\Registrar\EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('/enrollments', [App\Http\Controllers\Registrar\EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('/enrollments/{enrollment}', [App\Http\Controllers\Registrar\EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    Route::get('/enrollments/bulk', [App\Http\Controllers\Registrar\EnrollmentController::class, 'bulkCreate'])->name('enrollments.bulk-create');
    Route::post('/enrollments/bulk', [App\Http\Controllers\Registrar\EnrollmentController::class, 'bulkStore'])->name('enrollments.bulk-store');

    // Excel Import/Export
    // Old one-step student import (no preview, weaker checks) replaced by Students → Bulk Add. Code kept.
    // Route::get('/excel/student-template', [App\Http\Controllers\Registrar\ExcelController::class, 'studentTemplate'])->name('excel.student-template');
    Route::get('/excel/export-students', [App\Http\Controllers\Registrar\ExcelController::class, 'exportStudents'])->name('excel.export-students');
    // Route::post('/excel/import-students', [App\Http\Controllers\Registrar\ExcelController::class, 'importStudents'])->name('excel.import-students');
    // Masterlist import is disabled (client: grades are encoded in the system). Code kept in
    // ExcelController@importMasterlist / MasterlistImport; re-enable by restoring these two routes.
    // Route::get('/excel/masterlist-template', [App\Http\Controllers\Registrar\ExcelController::class, 'masterlistTemplate'])->name('excel.masterlist-template');
    // Route::post('/excel/masterlist-import', [App\Http\Controllers\Registrar\ExcelController::class, 'importMasterlist'])->name('excel.masterlist-import');
    Route::get('/excel/masterlist-export', [App\Http\Controllers\Registrar\ExcelController::class, 'exportMasterlist'])->name('excel.masterlist-export');
    Route::get('/documents', [App\Http\Controllers\Registrar\DocumentController::class, 'documentsIndex'])->name('documents.index');
    Route::get('/cog/{cog}/preview', [App\Http\Controllers\Registrar\DocumentController::class, 'previewCog'])->name('cog.preview');
    Route::get('/tor/{tor}/preview', [App\Http\Controllers\Registrar\DocumentController::class, 'previewTor'])->name('tor.preview');
});

require __DIR__.'/auth.php';
