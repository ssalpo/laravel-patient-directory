<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth:sanctum')->group(static function () {
    // Patients
    Route::get('/', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/all', [PatientController::class, 'all'])->name('patients.all');
    Route::get('/patients/{patient}/report', [PatientController::class, 'editReport'])->name('patients.edit.report');
    Route::put('/patients/{patient}/results', [PatientController::class, 'updateReport'])->name('patients.update.report');
    Route::get('/patients/{patient}/print', [PatientController::class, 'print'])->name('patients.print');
    Route::resource('patients', PatientController::class)->except('index');

    // Doctors
    Route::resource('doctors', DoctorController::class);

    // Users
    Route::resource('users', UserController::class);

    // Roles
    Route::resource('roles', RoleController::class);
});

// Auth
Route::controller(LoginController::class)->group(static function () {
    Route::get('login', 'create')->name('login')->middleware('guest');
    Route::post('login', 'store')->middleware('guest');
    Route::delete('logout', 'destroy')->name('logout');
});
