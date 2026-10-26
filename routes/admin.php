    <?php

    use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ExcelController;
use App\Http\Controllers\Admin\FlatController;
use App\Http\Controllers\Admin\GuardController;
use App\Http\Controllers\Admin\GuardShiftController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\JobPostController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResidentAssignController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SocietyController;
use App\Http\Controllers\Admin\SocietyNoticeController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserDetailController;
use App\Http\Controllers\Admin\UserPayoutMethodController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\WithdrawalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Logout route
Route::get('/admin-logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('admin.logout');




// Dashboard (only for super_admin)
Route::middleware(['isSuperAdmin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])
    ->name('admin.dashboard');
    Route::get('/tracking', [HomeController::class, 'tracking'])
    ->name('admin.tracking');
    Route::get('/live-activity-feed', [HomeController::class, 'liveFeed'])
    ->name('admin.live-feed');

    // User management routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');


    //resident management route
    Route::get('/residents', [UserController::class, 'Residents'])->name('residents.index');
    Route::patch('/residents/{id}/toggle-ban', [UserController::class, 'toggleBan'])->name('users.toggle-ban');
    Route::delete('/residents/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/residents/{id}/edit', [UserController::class, 'editResident'])->name('residents.edit');
    Route::patch('/residents/{id}', [UserController::class, 'updateResident'])->name('residents.update');
    Route::get('/residents/{id}/view', [UserController::class, 'viewResident'])->name('residents.view');
    Route::patch('/residents/documents/{id}/update-status', [UserController::class, 'ResiDocsupdateStatus'])->name('admin.resiDocs.update-status');
    Route::patch('/residents/vehicles/{id}/update-status', [UserController::class, 'ResiVehicleUpdateStatus'])->name('admin.resiVehicle.update-status');


    //employee management route
    Route::get('/employees', [UserController::class, 'Employees'])->name('employees.index');
    Route::get('/employees/{id}/edit', [UserController::class, 'editEmployees'])->name('employees.edit');
    Route::patch('/employees/{id}', [UserController::class, 'updateEmployees'])->name('employees.update');
    Route::get('/employees/{id}/view', [UserController::class, 'viewEmployees'])->name('employees.view');
    Route::delete('/employees/{id}', [SocietyController::class, 'destroyEmployees'])->name('employees.destroy');
    Route::patch('/employees/{id}/update-verification', [UserController::class, 'updateVerification'])->name('employees.update-verification');
    Route::patch('/employees/documents/{id}/update-status', [UserController::class, 'EmpDocsupdateStatus'])->name('admin.empDocs.update-status');



    // society management routes
    Route::get('/societies', [SocietyController::class, 'index'])->name('societies.index');
    Route::get('/societies/create', [SocietyController::class, 'create'])->name('societies.create');
    Route::post('/societies', [SocietyController::class, 'store'])->name('societies.store');
    Route::get('/societies/{society}/edit', [SocietyController::class, 'edit'])->name('societies.edit');
    Route::put('/societies/{society}', [SocietyController::class, 'update'])->name('societies.update');
    Route::delete('/societies/{society}', [SocietyController::class, 'destroy'])->name('societies.destroy');


    //payment method management routes
    Route::patch('/payments/status/{id}', [UserPayoutMethodController::class, 'updateStatus'])
    ->name('payments.updateStatus');



    // Multiple Entry (Bulk Insert)
    Route::get('societies/multiple/create', [SocietyController::class, 'createMultiple'])
        ->name('societies.multiple.create');

    Route::post('societies/multiple/store', [SocietyController::class, 'storeMultiple'])
        ->name('societies.storeMultiple');


    Route::get('/flats/create/{society_id}', [FlatController::class, 'create'])->name('flats.create');
    Route::post('/flats/store', [FlatController::class, 'store'])->name('flats.store');


    Route::get('/user-details', [UserDetailController::class, 'index']);
    Route::get('/user-details/{id}', [UserDetailController::class, 'show']);
    Route::post('/user-details', [UserDetailController::class, 'store']);
    Route::put('/user-details/{id}', [UserDetailController::class, 'update']);
    Route::delete('/user-details/{id}', [UserDetailController::class, 'destroy']);


    Route::get('/countries', [LocationController::class, 'getAll']);
    Route::get('/countries/{id}/states', [LocationController::class, 'getStates']);
    Route::get('/states/{id}/cities', [LocationController::class, 'getCities']);

    Route::get('/societies-ajax-list', [SocietyController::class, 'ajaxList'])
    ->name('societies.ajax');

    // Job Posts (Web Routes)
    Route::get('/jobs', [JobPostController::class, 'index'])->name('jobs.index');            // List all jobs
    Route::get('/jobs/create', [JobPostController::class, 'create'])->name('jobs.create');  // Show create form
    Route::post('/jobs', [JobPostController::class, 'store'])->name('jobs.store');          // Save job
    Route::get('/job-view/{id}', [JobPostController::class, 'view'])->name('jobs.view'); // Show job details (AJAX)

    Route::get('/jobs/{id}/applications', [JobPostController::class, 'applications'])
    ->name('jobs.applications');


    Route::get('/jobs/{id}', [JobPostController::class, 'show'])->name('jobs.show');        // Show single job
    Route::get('/jobs/{id}/edit', [JobPostController::class, 'edit'])->name('jobs.edit');   // Show edit form
    Route::put('/jobs/{id}', [JobPostController::class, 'update'])->name('jobs.update');    // Update job

    Route::delete('/jobs/{id}', [JobPostController::class, 'destroy'])->name('jobs.destroy'); // Delete job

    // Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    // Route::post('/settings/terms', [SettingsController::class, 'updateTerms'])->name('settings.updateTerms');
    // Route::post('/settings/faq', [SettingsController::class, 'addFaq'])->name('settings.addFaq');
    // Route::delete('/settings/faq/{id}', [SettingsController::class, 'deleteFaq'])->name('settings.deleteFaq');

    // // settings edit/update routes
    // Route::get('/settings/{key}', [SettingsController::class, 'edit'])->name('settings.edit');
    // Route::post('/settings/{key}', [SettingsController::class, 'update'])->name('settings.update');


    // RESIDENT ASSIGN CRUD
    Route::get('/resident-assign', [ResidentAssignController::class, 'index'])->name('resident.assign.index');
    Route::get('/resident-assign/create', [ResidentAssignController::class, 'create'])->name('resident.assign.create');
    Route::post('/resident-assign/store', [ResidentAssignController::class, 'store'])->name('resident.assign.store');
    Route::get('/resident-assign/{id}/edit', [ResidentAssignController::class, 'edit'])->name('resident.assign.edit');
    Route::put('/resident-assign/{id}', [ResidentAssignController::class, 'update'])->name('resident.assign.update');
    Route::delete('/resident-assign/{id}', [ResidentAssignController::class, 'destroy'])->name('resident.assign.delete');
    Route::put('/resident-assign/{id}/ban', [ResidentAssignController::class, 'ban'])->name('resident.assign.ban');
    Route::put('/resident-assign/{id}/unban', [ResidentAssignController::class, 'unban'])->name('resident.assign.unban');



    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('/settings/profile', [SettingsController::class, 'updateAdminProfile'])->name('admin.settings.profile');
    Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('admin.settings.clear-cache');


    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('admin.settings.profile');
    Route::post('/settings/avatar', [SettingsController::class, 'updateAvatar'])->name('admin.settings.update-avatar');
    Route::delete('/settings/avatar', [SettingsController::class, 'removeAvatar'])->name('admin.settings.remove-avatar');

    Route::post('/system/clear-cache', [SystemController::class, 'clearCache'])->name('admin.system.clear-cache');
    Route::post('/system/optimize-db', [SystemController::class, 'optimizeDatabase'])->name('admin.system.optimize-db');
    Route::post('/system/backup-db', [SystemController::class, 'backupDatabase'])->name('admin.system.backup-db');
    Route::get('/system/log/{file}', [SystemController::class, 'viewLog'])->name('admin.system.view-log');
    Route::post('/system/clear-logs', [SystemController::class, 'clearLogs'])->name('admin.system.clear-logs');
    Route::post('/system/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('admin.system.toggle-maintenance');

    // guard management route
    // Route::get('/guards', [UserController::class, 'Guards'])->name('guards.index');
    // Route::patch('/guards/{id}/toggle-ban', [UserController::class, 'toggleBanGuard'])->name('guards.toggle-ban');
    // Route::delete('/guards/{id}', [UserController::class, 'destroyGuard'])->name('guards.destroy');
    // Route::get('/guards/{id}/edit', [UserController::class, 'editGuard'])->name('guards.edit');
    // Route::patch('/guards/{id}', [UserController::class, 'updateGuard'])->name('guards.update');
    // Route::get('/guards/{id}/view', [UserController::class, 'viewGuard'])->name('guards.view');
    // Route::get('/guards/create', [GuardController::class, 'create'])->name('guards.create');
    // Route::post('/guards/store', [GuardController::class, 'store'])->name('guards.store');

    Route::get('guards', [GuardController::class, 'index'])->name('guards.index');
    Route::get('guards/create', [GuardController::class, 'create'])->name('guards.create');
    Route::post('guards/store', [GuardController::class, 'store'])->name('guards.store');
    Route::get('guards/{id}/view', [GuardController::class, 'view'])->name('guards.view');
    Route::get('guards/{id}/edit', [GuardController::class, 'edit'])->name('guards.edit');
    Route::patch('guards/{id}', [GuardController::class, 'update'])->name('guards.update');
    Route::delete('guards/{id}', [GuardController::class, 'destroy'])->name('guards.destroy');
    Route::post('guards/toggle-status', [GuardController::class, 'toggleStatus'])
        ->name('guards.toggleStatus');
    //Route::get('guards/{id}', [GuardController::class, 'view'])->name('guards.view');
    Route::get('/all-guard-history', [GuardController::class, 'allGuardHistory'])
    ->name('guards.all-pass-history');

    // Guard shift management routes
    Route::get('/shift/guards/assign', [GuardShiftController::class, 'create'])->name('shift.guards.assign');
    Route::post('/shift/guards/assign', [GuardShiftController::class, 'store'])->name('shift.guards.store');
    Route::get('/shift/guards/list', [GuardShiftController::class, 'index'])->name('shift.guards.index');
    Route::get('/shift/guards/{id}', [GuardShiftController::class, 'show'])->name('shift.guards.show');
    Route::post('/shift/guards/{id}/request-change', [GuardShiftController::class, 'requestChange'])
        ->name('shift.guards.request.change');
    Route::post('/shift-request/guards/{id}/decide', [GuardShiftController::class, 'decide'])
        ->name('shift.guards.request.decide');
    Route::get('/ajax-list-guards', [GuardShiftController::class, 'getGuardsBySociety'])->name('admin.guards.bySociety');


    // Service management routes
    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');


    // Export routes
    Route::get('/societies/export', [SocietyController::class, 'export'])->name('societies.export');
    Route::get('/residents/export', [ExcelController::class, 'exportResidentsFull'])->name('residents.export');
    Route::get('/guards/export', [ExcelController::class, 'exportGuards'])->name('guards.export');
    Route::get('/employees/export', [ExcelController::class, 'exportEmployeesFull'])->name('employees.export');
    Route::get('/job/export', [ExcelController::class, 'exportJobsFull'])
    ->name('jobs.export');
    Route::get('/resident-assign/export', [ExcelController::class, 'exportResidentAssign'])
    ->name('resident.assign.export');
    Route::get('/admin/jobs/{id}/applications/export', [ExcelController::class, 'exportJobApplications'])
    ->name('admin.jobs.applications.export');

    // Route::get('/chats', [AdminChatController::class, 'index']);
    // Route::get('/chats/{chatId}', [AdminChatController::class, 'view']);


    Route::get('/payments', [PaymentsController::class, 'adminIndex'])->name('admin.payments.index');
    Route::get('/payments/export', [PaymentsController::class, 'adminExport'])->name('admin.payments.export');
    Route::get('/payments/{payment}', [PaymentsController::class, 'adminShow'])->name('admin.payments.show');

    // Withdrawal / Payout Management routes
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('admin.withdrawals.index');
    Route::get('/withdrawals/export', [WithdrawalController::class, 'export'])->name('admin.withdrawals.export');
    Route::get('/withdrawals/{id}', [WithdrawalController::class, 'show'])->name('admin.withdrawals.show');
    Route::post('/withdrawals/batch-auto-process', [WithdrawalController::class, 'processBatchAuto'])->name('admin.withdrawals.processBatchAuto');
    Route::post('/withdrawals/{id}/process-payout', [WithdrawalController::class, 'processPayout'])->name('admin.withdrawals.processPayout');
    Route::post('/withdrawals/{id}/mark-manual-paid', [WithdrawalController::class, 'markManualPaid'])->name('admin.withdrawals.markManualPaid');
    Route::post('/withdrawals/{id}/hold', [WithdrawalController::class, 'hold'])->name('admin.withdrawals.hold');
    Route::post('/withdrawals/{id}/unhold', [WithdrawalController::class, 'unhold'])->name('admin.withdrawals.unhold');
    Route::post('/withdrawals/{id}/reject', [WithdrawalController::class, 'reject'])->name('admin.withdrawals.reject');
    Route::post('/withdrawals/test-connection', [WithdrawalController::class, 'testConnection'])->name('admin.withdrawals.testConnection');
    Route::post('/withdrawals/test-payout', [WithdrawalController::class, 'testPayoutTransfer'])->name('admin.withdrawals.testPayoutTransfer');


    Route::get('/chat/sync', [AdminChatController::class, 'syncFirestoreChats']);

    Route::get('/dashboard/chat', [AdminChatController::class, 'completeChat'])->name('dashboard.chat');

    Route::get('/admin/jobs/{job}/applications/{user}/chat', [AdminChatController::class, 'JobApplicationChat'])
        ->name('admin.job.application.chat');

    Route::get(
        '/chat/resident/{residentId}/employer/{userId}',
        [AdminChatController::class, 'chatBetweenResidentEmployer']
    )->name('admin.chat.resident.employer');


    // Route::get('/jobs/{job}/chat', [ChatController::class, 'openJobChat'])
    // ->name('job.chat');

    //get all notifications of user
    Route::get('/admin/notifications', [UserController::class, 'notifications'])
    ->name('admin.notifications.index');

    // Bug Reports & Support
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
    Route::get('/reports/{id}', [ReportController::class, 'show'])->name('admin.reports.show');
    Route::patch('/reports/{id}/status', [ReportController::class, 'updateStatus'])->name('admin.reports.update-status');
    Route::delete('/reports/{id}', [ReportController::class, 'destroy'])->name('admin.reports.destroy');




    // Edit permissions page
    Route::get('/users/{id}/permissions/edit', [UserPermissionController::class, 'edit'])
    ->name('admin.permissions.edit');

    // Store permissions
    Route::post('/users/{id}/permissions', [UserPermissionController::class, 'store'])
    ->name('admin.permissions.store');


    Route::prefix('notices')->group(function () {

        Route::get('/', [SocietyNoticeController::class, 'index'])->name('admin.notices.index'); // list all
        Route::get('/create', [SocietyNoticeController::class, 'create'])->name('admin.notices.create'); // create notice
        Route::post('/', [SocietyNoticeController::class, 'store'])->name('admin.notices.store'); // store notice
        Route::get('/{id}/edit', [SocietyNoticeController::class, 'edit'])->name('admin.notices.edit'); // edit notice
        Route::put('/{id}', [SocietyNoticeController::class, 'update'])->name('admin.notices.update'); // update notice
        Route::delete('/{id}', [SocietyNoticeController::class, 'destroy'])->name('admin.notices.destroy'); // delete notice

    });

    // Amenity management routes
    Route::prefix('amenities')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AmenityController::class, 'index'])->name('admin.amenities.index');
        Route::get('/create', [\App\Http\Controllers\Admin\AmenityController::class, 'create'])->name('admin.amenities.create');
        Route::post('/', [\App\Http\Controllers\Admin\AmenityController::class, 'store'])->name('admin.amenities.store');

        // Bookings
        Route::get('/bookings', [\App\Http\Controllers\Admin\AmenityController::class, 'bookings'])->name('admin.amenities.bookings');
        Route::get('/bookings/list', [\App\Http\Controllers\Admin\AmenityController::class, 'bookings']);
        Route::post('/quick-book', [\App\Http\Controllers\Admin\AmenityController::class, 'quickBook'])->name('admin.amenities.quick-book');
        Route::get('/{id}/slots-ajax', [\App\Http\Controllers\Admin\AmenityController::class, 'slotsAjax'])->name('admin.amenities.slots-ajax');
        Route::post('/bookings/{id}/approve', [\App\Http\Controllers\Admin\AmenityController::class, 'approveBooking'])->name('admin.amenities.bookings.approve');
        Route::post('/bookings/{id}/reject', [\App\Http\Controllers\Admin\AmenityController::class, 'rejectBooking'])->name('admin.amenities.bookings.reject');
        Route::post('/bookings/{id}/cancel', [\App\Http\Controllers\Admin\AmenityController::class, 'cancelBooking'])->name('admin.amenities.bookings.cancel');

        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AmenityController::class, 'edit'])->name('admin.amenities.edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\AmenityController::class, 'update'])->name('admin.amenities.update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AmenityController::class, 'destroy'])->name('admin.amenities.destroy');
    });

    // Poll management routes
    Route::prefix('polls')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PollController::class, 'index'])->name('admin.polls.index');
        Route::get('/create', [\App\Http\Controllers\Admin\PollController::class, 'create'])->name('admin.polls.create');
        Route::post('/', [\App\Http\Controllers\Admin\PollController::class, 'store'])->name('admin.polls.store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\PollController::class, 'edit'])->name('admin.polls.edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\PollController::class, 'update'])->name('admin.polls.update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\PollController::class, 'destroy'])->name('admin.polls.destroy');
        Route::post('/{id}/close', [\App\Http\Controllers\Admin\PollController::class, 'close'])->name('admin.polls.close');
        Route::get('/{id}/results', [\App\Http\Controllers\Admin\PollController::class, 'results'])->name('admin.polls.results');
    });

    // Ticket / Complaint Management routes
    Route::prefix('tickets')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('admin.tickets.index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('admin.tickets.show');
        Route::post('/{id}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('admin.tickets.reply');
        Route::patch('/{id}/status', [\App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('admin.tickets.status');
        Route::post('/{id}/assign', [\App\Http\Controllers\Admin\TicketController::class, 'assign'])->name('admin.tickets.assign');
    });

    // Route::get('/chat/{user?}', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');


});
