<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\GuardShiftController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->name('login');

// Show login page
Route::get('/login', function () {
    return view('auth.login');
})->name('users.login');


// Handle login (Admin + Resident - single function)
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Logout route
Route::get('/user-logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');


Route::middleware(['auth','isUser'])->prefix('residents')->name('member.')->group(function () {
    // Resident Dashboard
    Route::get('/dashboard', [IndexController::class, 'index'])
     ->name('dashboard');


    //resident management route
    Route::get('/residents', [IndexController::class, 'Residents'])->name('residents.index');
    Route::get('/residents/{id}/view', [IndexController::class, 'viewResident'])->name('residents.view');
    Route::patch('/residents/documents/{id}/update-status', [IndexController::class, 'updateStatus'])->name('residents.documents.update-status');
    Route::patch('/residents/{id}/update-verification', [IndexController::class, 'updateVerification'])->name('residents.update-verification');
    Route::patch('/residents/{id}/toggle-ban', [IndexController::class, 'toggleBan'])->name('toggle-ban');
    Route::patch('/residents/vehicles/{id}/update-status', [IndexController::class, 'ResiVehicleUpdateStatus'])->name('resiVehicle.update-status');


    //employee management route
    Route::get('/employees', [UserController::class, 'Employees'])->name('employees.index');
    Route::get('/employees/{id}/edit', [UserController::class, 'editEmployees'])->name('employees.edit');
    Route::patch('/employees/{id}', [UserController::class, 'updateEmployees'])->name('employees.update');
    Route::get('/employees/{id}/view', [UserController::class, 'viewEmployees'])->name('employees.view');
    Route::delete('/employees/{id}', [UserController::class, 'destroyEmployees'])->name('employees.destroy');
    Route::patch('/employees/{id}/update-verification', [UserController::class, 'updateVerification'])->name('employees.update-verification');
    Route::patch('/employees/documents/{id}/update-status', [UserController::class, 'updateStatus'])->name('employees.documents.update-status');


    // Job Posts (Web Routes)
    // Route::get('/jobs', [JobPostController::class, 'index'])->name('jobs.index');            // List all jobs
    // Route::get('/jobs/create', [JobPostController::class, 'create'])->name('jobs.create');  // Show create form
    // Route::post('/jobs', [JobPostController::class, 'store'])->name('jobs.store');          // Save job
    // Route::get('/job-view/{id}', [JobPostController::class, 'view'])->name('jobs.view'); // Show job details (AJAX)

    // Route::get('/jobs/{id}/applications', [JobPostController::class, 'applications'])
    //  ->name('jobs.applications');


    // Route::get('/jobs/{id}', [JobPostController::class, 'show'])->name('jobs.show');        // Show single job
    // Route::get('/jobs/{id}/edit', [JobPostController::class, 'edit'])->name('jobs.edit');   // Show edit form
    // Route::put('/jobs/{id}', [JobPostController::class, 'update'])->name('jobs.update');    // Update job

    // Route::delete('/jobs/{id}', [JobPostController::class, 'destroy'])->name('jobs.destroy'); // Delete job


    Route::get('guards', [GuardController::class, 'index'])->name('guards.index');
    Route::get('guards/create', [GuardController::class, 'create'])->name('guards.create');
    Route::post('guards/store', [GuardController::class, 'store'])->name('guards.store');
    Route::get('guards/{id}/view', [GuardController::class, 'view'])->name('guards.view');
    Route::get('guards/{id}/edit', [GuardController::class, 'edit'])->name('guards.edit');
    Route::patch('guards/{id}', [GuardController::class, 'update'])->name('guards.update');
    Route::delete('guards/{id}', [GuardController::class, 'destroy'])->name('guards.destroy');
    Route::post('guards/toggle-status', [GuardController::class, 'toggleStatus'])
        ->name('guards.toggleStatus');
    // Route::get('guards/{id}', [GuardController::class, 'view'])->name('guards.view');
    Route::get('/all-guard-history', [GuardController::class, 'allGuardHistory'])
    ->name('guards.all-pass-history');
    Route::patch('guards/{id}/update-verification', [GuardController::class, 'updateVerification'])
        ->name('guards.update-verification');



    // Guard shift management routes
    Route::get('/shift/guards/assign', [GuardShiftController::class, 'create'])->name('shift.guards.assign');
    Route::post('/shift/guards/assign', [GuardShiftController::class, 'store'])->name('shift.guards.store');
    Route::get('/shift/guards/list', [GuardShiftController::class, 'index'])->name('shift.guards.index');
    Route::get('/shift/guards/{id}', [GuardShiftController::class, 'show'])->name('shift.guards.show');
    Route::post('/shift/guards/{id}/request-change', [GuardShiftController::class, 'requestChange'])
        ->name('shift.guards.request.change');
    Route::post('/shift-request/guards/{id}/decide', [GuardShiftController::class, 'decide'])
        ->name('shift.guards.request.decide');


    // Export routes
    // Route::get('/societies/export', [ExcelController::class, 'export'])->name('societies.export');
    // Route::get('/residents/export', [ExcelController::class, 'exportResidentsFull'])->name('residents.export');
    // Route::get('/guards/export', [ExcelController::class, 'exportGuards'])->name('guards.export');
    // Route::get('/employees/export', [ExcelController::class, 'exportEmployeesFull'])->name('employees.export');
    // Route::get('/job/export', [ExcelController::class, 'exportJobsFull'])
    // ->name('jobs.export');
    // Route::get('/resident-assign/export', [ExcelController::class, 'exportResidentAssign'])
    // ->name('resident.assign.export');
    // Route::get('/admin/jobs/{id}/applications/export', [ExcelController::class, 'exportJobApplications'])
    // ->name('admin.jobs.applications.export');



    Route::get('/shift/assign', [GuardShiftController::class, 'create'])->name('shift.assign');
    Route::post('/shift/assign', [GuardShiftController::class, 'store']);

    Route::get('/shift/list', [GuardShiftController::class, 'index'])->name('shift.list');

    Route::get('/shift/{id}', [GuardShiftController::class, 'show'])->name('shift.show');

    Route::post('/shift/{id}/request-change', [GuardShiftController::class, 'requestChange'])
        ->name('shift.request.change');

    Route::post('/shift-request/{id}/decide', [GuardShiftController::class, 'decide'])
        ->name('shift.request.decide');


});


Route::get('/page/{slug}', [IndexController::class, 'singlePage'])
    ->where('slug', 'privacy_policy|terms_and_conditions|return_refund_policy');
