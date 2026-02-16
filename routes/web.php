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
    return view('welcome');
});

Route::get('/dashboard', function () {
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
});

// Dean Routes
Route::middleware(['auth', 'status', 'role:dean'])->prefix('dean')->name('dean.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Dean\DeanController::class, 'index'])->name('dashboard');
});

// Faculty Routes
Route::middleware(['auth', 'status', 'role:faculty'])->prefix('faculty')->name('faculty.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Faculty\FacultyController::class, 'index'])->name('dashboard');
});

// Registrar Routes
Route::middleware(['auth', 'status', 'role:registrar'])->prefix('registrar')->name('registrar.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Registrar\RegistrarController::class, 'index'])->name('dashboard');
});
require __DIR__.'/auth.php';
