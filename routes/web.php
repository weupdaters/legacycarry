<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\GuardShiftController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Index
Route::get('/', function () {
    return view('frontend.index');
})->name('index');

//about
Route::get('/about', function () {
    return view('frontend.about');
})->name('about');

//contact
Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');

//privacy
Route::get('/privacy', function () {
    return view('frontend.privacy');
})->name('privacy');

//refund policy
Route::get('/refund', function () {
    return view('frontend.refund');
})->name('refund');


//privacy
Route::get('/terms', function () {
    return view('frontend.terms');
})->name('terms');

Route::get('/resident-terms', function () {
    return view('frontend.resident_terms');
})->name('resident-terms');

Route::get('/service-provider-terms', function () {
    return view('frontend.service_provider_terms');
})->name('service-provider-terms');

// Show login page
Route::get('/login', function () {
    return view('auth.login');
})->name('login');


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


    // Manage Buildings (Flats)
    Route::get('/manage-buildings', [IndexController::class, 'manageFlats'])->name('flats.manage');
    Route::post('/manage-buildings', [IndexController::class, 'storeFlats'])->name('flats.store');

    // Notices
    Route::get('/notices', [\App\Http\Controllers\Member\NoticeController::class, 'index'])->name('notices.index');
    Route::get('/notices/create', [\App\Http\Controllers\Member\NoticeController::class, 'create'])->name('notices.create');
    Route::post('/notices', [\App\Http\Controllers\Member\NoticeController::class, 'store'])->name('notices.store');
    Route::get('/notices/{id}', [\App\Http\Controllers\Member\NoticeController::class, 'show'])->name('notices.show');
    Route::get('/notices/{id}/edit', [\App\Http\Controllers\Member\NoticeController::class, 'edit'])->name('notices.edit');
    Route::patch('/notices/{id}', [\App\Http\Controllers\Member\NoticeController::class, 'update'])->name('notices.update');
    Route::delete('/notices/{id}', [\App\Http\Controllers\Member\NoticeController::class, 'destroy'])->name('notices.destroy');

    // Amenity Booking & Management
    Route::get('/amenities', [\App\Http\Controllers\Member\AmenityBookingController::class, 'index'])->name('amenities.index');
    Route::get('/amenities/create', [\App\Http\Controllers\Member\AmenityBookingController::class, 'create'])->name('amenities.create');
    Route::post('/amenities', [\App\Http\Controllers\Member\AmenityBookingController::class, 'store'])->name('amenities.store');
    Route::get('/amenities/my-bookings', [\App\Http\Controllers\Member\AmenityBookingController::class, 'myBookings'])->name('amenities.my-bookings');
    Route::get('/amenities/bookings/all', [\App\Http\Controllers\Member\AmenityBookingController::class, 'allBookings'])->name('amenities.bookings.all');
    Route::get('/amenities/all-bookings', [\App\Http\Controllers\Member\AmenityBookingController::class, 'allBookings'])->name('amenities.bookings.all.alias');
    Route::get('/amenities/manage-bookings', [\App\Http\Controllers\Member\AmenityBookingController::class, 'allBookings'])->name('amenities.bookings.manage');
    Route::post('/amenities/bookings/{id}/approve', [\App\Http\Controllers\Member\AmenityBookingController::class, 'approveBooking'])->name('amenities.bookings.approve')->whereNumber('id');
    Route::post('/amenities/bookings/{id}/reject', [\App\Http\Controllers\Member\AmenityBookingController::class, 'rejectBooking'])->name('amenities.bookings.reject')->whereNumber('id');
    Route::get('/amenities/{id}/book', [\App\Http\Controllers\Member\AmenityBookingController::class, 'bookView'])->name('amenities.book')->whereNumber('id');
    Route::post('/amenities/{id}/book', [\App\Http\Controllers\Member\AmenityBookingController::class, 'bookSubmit'])->name('amenities.book.submit')->whereNumber('id');
    Route::get('/amenities/{id}/slots-ajax', [\App\Http\Controllers\Member\AmenityBookingController::class, 'getSlotsAjax'])->name('amenities.slots-ajax')->whereNumber('id');
    Route::get('/amenities/{id}/edit', [\App\Http\Controllers\Member\AmenityBookingController::class, 'edit'])->name('amenities.edit')->whereNumber('id');
    Route::put('/amenities/{id}', [\App\Http\Controllers\Member\AmenityBookingController::class, 'update'])->name('amenities.update')->whereNumber('id');
    Route::delete('/amenities/{id}', [\App\Http\Controllers\Member\AmenityBookingController::class, 'destroy'])->name('amenities.destroy')->whereNumber('id');
    Route::post('/amenities/bookings/{id}/cancel', [\App\Http\Controllers\Member\AmenityBookingController::class, 'cancel'])->name('amenities.cancel')->whereNumber('id');

    // Polls & Voting & Management
    Route::get('/polls', [\App\Http\Controllers\Member\PollController::class, 'index'])->name('polls.index');
    Route::get('/polls/create', [\App\Http\Controllers\Member\PollController::class, 'create'])->name('polls.create');
    Route::post('/polls', [\App\Http\Controllers\Member\PollController::class, 'store'])->name('polls.store');
    Route::get('/polls/{id}', [\App\Http\Controllers\Member\PollController::class, 'show'])->name('polls.show')->whereNumber('id');
    Route::get('/polls/{id}/edit', [\App\Http\Controllers\Member\PollController::class, 'edit'])->name('polls.edit')->whereNumber('id');
    Route::put('/polls/{id}', [\App\Http\Controllers\Member\PollController::class, 'update'])->name('polls.update')->whereNumber('id');
    Route::delete('/polls/{id}', [\App\Http\Controllers\Member\PollController::class, 'destroy'])->name('polls.destroy')->whereNumber('id');
    Route::post('/polls/{id}/close', [\App\Http\Controllers\Member\PollController::class, 'close'])->name('polls.close')->whereNumber('id');
    Route::get('/polls/{id}/results', [\App\Http\Controllers\Member\PollController::class, 'results'])->name('polls.results')->whereNumber('id');
    Route::post('/polls/{id}/vote', [\App\Http\Controllers\Member\PollController::class, 'vote'])->name('polls.vote')->whereNumber('id');

    // Tickets & Complaints
    Route::get('/tickets', [\App\Http\Controllers\Member\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [\App\Http\Controllers\Member\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [\App\Http\Controllers\Member\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [\App\Http\Controllers\Member\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{id}/reply', [\App\Http\Controllers\Member\TicketController::class, 'reply'])->name('tickets.reply');
    Route::post('/tickets/{id}/close', [\App\Http\Controllers\Member\TicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{id}/status', [\App\Http\Controllers\Member\TicketController::class, 'updateStatus'])->name('tickets.status');

});


Route::get('/page/{slug}', [IndexController::class, 'singlePage'])
    ->where('slug', 'privacy_policy|terms_and_conditions|return_refund_policy');

// Cashfree Hosted Web Checkout & Callback
Route::get('/cashfree-checkout/{paymentSessionId}', [\App\Http\Controllers\CashfreeCheckoutController::class, 'checkout'])->name('cashfree.checkout');
Route::get('/cashfree-return', [\App\Http\Controllers\CashfreeCheckoutController::class, 'callback'])->name('cashfree.return');
Route::get('/cashfree-tester', [\App\Http\Controllers\CashfreeCheckoutController::class, 'tester'])->name('cashfree.tester');
Route::post('/cashfree-tester/create-order', [\App\Http\Controllers\CashfreeCheckoutController::class, 'createTestOrder'])->name('cashfree.tester.create');

// Direct storage fallback when public/storage shortcut is removed
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*');
