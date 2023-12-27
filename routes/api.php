<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('patients')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('patients.index');
        Route::get('/search', [PatientController::class, 'searchPatients'])->name('patients.search');
        Route::post('/', [PatientController::class, 'store'])->name('patients.store');
        Route::put('/{id}', [PatientController::class, 'update'])->name('patients.update');
        Route::delete('/{id}', [PatientController::class, 'destroy'])->name('patients.destroy');
    });

    Route::prefix('diagnoses')->group(function () {
        Route::post('/', [DiagnosisController::class, 'store'])->name('diagnoses.store');
        Route::post('/{patient_id}/assign-diagnosis', [DiagnosisController::class, 'assignDiagnosis'])->name('diagnoses.assign');
        Route::get('/top-diagnoses-last-six-months', [DiagnosisController::class, 'topDiagnosesLastSixMonths'])->name('diagnoses.top-five');
    });
});
