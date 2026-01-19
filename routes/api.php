<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GuardController;
use App\Http\Controllers\Api\GuardShiftController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobPaymentController;
use App\Http\Controllers\Api\JobPostController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\PassController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserDocumentController;
use App\Http\Controllers\Api\UserPayoutMethodController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VisitorPassController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/users', [UserController::class, 'registerAndVerify']);
Route::get('/user/{id}', [UserController::class, 'show']);
Route::post('/user/{id}/update', [UserController::class, 'updateProfile']);
Route::post('/user/{id}/language-region', [UserController::class, 'updateLanguageRegion']);

Route::post('/register-and-verify', [UserController::class, 'registerAndVerify']);
Route::post('/send-user-otp', [UserController::class, 'sendPhoneVerification']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/societies', [App\Http\Controllers\Api\SocietyController::class, 'index']);
Route::get('/societies/{id}', [App\Http\Controllers\Api\SocietyController::class, 'show']);
Route::post('/societies-create', [App\Http\Controllers\Api\SocietyController::class, 'store']);


Route::get('/countries', [LocationController::class, 'getAll']);
Route::get('/countries/{id}/states', [LocationController::class, 'getStates']);
Route::get('/states/{id}/cities', [LocationController::class, 'getCities']);
Route::get('/societies-by-cities/{cityId}', [LocationController::class, 'getSocietiesByCity']);
Route::get('/flats-by-society/{societyId}', [LocationController::class, 'getFlatsBySociety']);

// Upsert / Create multiple societies
Route::post('/societies/upsert', [LocationController::class, 'upsertSocieties']);

//add resident detail
Route::post('/resident-users-create', [UserController::class, 'residentUsersCreate']);

// Login with phone OTP
Route::post('/login-otp', [UserController::class, 'loginWithOtp']);


// Other API routes...

Route::middleware('auth:sanctum')->group(function () {
    // CRUD API for Job Posts
    Route::get('/jobs/{user_id?}', [JobPostController::class, 'index']);        // Get all jobs
    Route::get('/job-detials/{id}', [JobPostController::class, 'show']);    // Get single job
    Route::post('/jobs/create', [JobPostController::class, 'store']);       // Create job

    Route::post('/jobs/{id}/edit', [JobPostController::class, 'update']);  // Update job
    Route::post('/jobs/{id}/delete', [JobPostController::class, 'destroy']); // Delete job
    Route::post('/jobs/{id}/status', [JobPostController::class, 'changeStatus']);
    // filterJobsByStatus
    Route::get('/filter-jobs/{employer_id}/{status}', [JobApplicationController::class, 'filterJobsByStatus']);
    //Route::post('job/status', [JobPostController::class, 'updateEmployerJobStatus']);
    Route::get('/resident-jobs/{id}/{status?}/{pay_type?}', [JobPostController::class, 'residentJobByID']);

    // search jobs
    Route::post('/search-jobs', [JobPostController::class, 'searchJobs']);
    //fiter by jobs
    Route::get('/search-jobs/filter', [JobPostController::class, 'filterJobs']);

    // search job only user id based
    Route::post('/resident-search-jobs/{user_id}', [JobPostController::class, 'jobsByUserId']);

    //residents can view jobs posted by employers with status
    Route::post('/view-jobs/{user_id}', [JobPostController::class, 'viewJobsByUserId']);


    Route::post('/submit-job-application', [JobApplicationController::class, 'submitApplication']);
    Route::post('/check-applied-job', [JobApplicationController::class, 'checkAppliedJob']);
    Route::get('/get-jobs-by-users/{user_id}', [JobApplicationController::class, 'getApplicationsByUserId']);



    // resident & employer can shortlist & hire applicants
    Route::get('/get-applications-resident/{user_id}', [JobApplicationController::class, 'getApplicationsResident']);
    Route::post('/resident-application-status', [JobApplicationController::class, 'resJobStatus']);
    Route::post('/applications/{id}/reschedule', [JobApplicationController::class, 'reschedule']);



    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('/{identifier}', [SettingController::class, 'show']);
        Route::post('/', [SettingController::class, 'store']);
        Route::put('/{identifier}', [SettingController::class, 'update']);
        Route::delete('/{identifier}', [SettingController::class, 'destroy']);
        Route::post('/batch-get', [SettingController::class, 'batchGet']);
    });


    Route::get('/users/{userId}/payment-methods', [UserPayoutMethodController::class, 'index']);
    Route::post('/users/{userId}/payment-methods', [UserPayoutMethodController::class, 'store']);
    Route::put('/users/{userId}/payment-methods/{id}', [UserPayoutMethodController::class, 'update']);

    Route::prefix('reports')->group(function () {
        Route::post('/', [ReportController::class, 'store']);      // Create new report
        Route::get('/user/{user_id}', [ReportController::class, 'index']); // List user's reports
        Route::get('/{id}', [ReportController::class, 'show']);    // Get single report
    });


    Route::post('/user-documents/upload', [UserDocumentController::class, 'uploadOrUpdate']);
    Route::get('/user-documents/{user_id}', [UserDocumentController::class, 'getByUser']);

    Route::prefix('vehicles')->group(function () {
        Route::post('/register', [VehicleController::class, 'register']);        // Register new car
        Route::get('/user/{user_id}', [VehicleController::class, 'userVehicles']); // List user's vehicles
        Route::post('/scan', [VehicleController::class, 'scan']);                // Gate scan (IN/OUT via QR)
    });

    //resident pays for job application
    Route::post('/jobs/{job}/applications/{application}/pay', [JobPaymentController::class, 'pay']); // ensure authenticated
    Route::get('/payments/{paymentId}', [JobPaymentController::class, 'getPaymentDetails']);
    Route::get('/jobs/{job}/payments', [JobPaymentController::class, 'getJobPayments']);
    Route::get('/my-payments', [JobPaymentController::class, 'getMyPayments']);


    // List all visitor passes
    Route::get('visitor-passes', [VisitorPassController::class, 'index'])->name('visitor-passes.index');

    // Create a visitor pass
    Route::post('visitor-passes', [VisitorPassController::class, 'store'])->name('visitor-passes.store');

    // Get a single visitor pass
    Route::get('visitor-passes/{visitorPass}', [VisitorPassController::class, 'show'])->name('visitor-passes.show');

    // Update a visitor pass
    Route::put('visitor-passes/{visitorPass}', [VisitorPassController::class, 'update'])->name('visitor-passes.update');

    // Delete a visitor pass
    Route::delete('visitor-passes/{visitorPass}', [VisitorPassController::class, 'destroy'])->name('visitor-passes.destroy');

    Route::post('visitor-passes/{visitorPass}/complete', [VisitorPassController::class, 'complete'])->name('visitor-passes.complete');


    Route::prefix('appointments')->group(function () {

        Route::post('{appointment}/approve', [AppointmentController::class,'approve']);

        Route::post('{appointment}/start', [AppointmentController::class,'start']);
        Route::post('{appointment}/end', [AppointmentController::class,'end']);
        Route::post('{appointment}/confirm', [AppointmentController::class,'confirm']);

        Route::post('{appointment}/pay/advance', [PaymentController::class,'payAdvance']);
        Route::post('{appointment}/pay/final', [PaymentController::class,'payFinal']);
    });

    // Route::post('/guard/entry', [AppointmentController::class,'guardEntry']);
    // Route::post('/guard/exit', [AppointmentController::class,'scanExit']);

    Route::post('/save-device-token', [NotificationController::class, 'saveToken']);
    Route::post('/save-device-token', [NotificationController::class, 'saveToken']);


    /*
    |--------------------------------------------------------------------------
    | GUARD CRUD
    |--------------------------------------------------------------------------
    */
    Route::post('/guard/create', [GuardController::class, 'guardCreate']);
    Route::get('/guard/{id}/edit', [GuardController::class, 'guardEdit']);
    Route::post('/guard/{id}/update', [GuardController::class, 'guardUpdate']);

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/guard/dashboard/{society_id}', [GuardController::class, 'dashboardSummary']);
    Route::get('/guard/passes/today/{society_id}', [GuardController::class, 'todayPasses']);
    Route::get('/guard/logs/{society_id}', [GuardController::class, 'logs']);

    /*
    |--------------------------------------------------------------------------
    | PASS SCANNING – VISITOR / DELIVERY / VEHICLE
    |--------------------------------------------------------------------------
    */
    Route::post('/guard/scan-entry', [GuardController::class, 'scanEntry']);
    Route::post('/guard/scan-exit', [GuardController::class, 'scanExit']);
    Route::get('/guard/active-visitors/{society_id}', [GuardController::class, 'activeVisitors']);

    /*
    |--------------------------------------------------------------------------
    | JOB APPOINTMENT ENTRY/EXIT
    |--------------------------------------------------------------------------
    */
    Route::post('/guard/job-entry', [GuardController::class, 'jobEntry']);
    Route::post('/guard/job-exit', [GuardController::class, 'jobExit']);
    Route::get('/guard/appointment/{id}', [GuardController::class, 'appointmentDetails']);
    Route::get('/guard/active-appointments/{society_id}', [GuardController::class, 'activeAppointments']);

    /*
    |--------------------------------------------------------------------------
    | PARKING
    |--------------------------------------------------------------------------
    */
    Route::get('/guard/parking/slots/{society_id}', [GuardController::class, 'parkingSlots']);
    Route::post('/guard/parking/assign', [GuardController::class, 'assignParkingSlot']);
    Route::get('/guard/parking/inside/{society_id}', [GuardController::class, 'vehiclesInside']);
    Route::get('/guard/parking/history/{society_id}', [GuardController::class, 'parkingHistory']);


    //test notification
    Route::post('/test-notification', [NotificationController::class, 'testPush']);



    /*
    |--------------------------------------------------------------------------
    | PASS ROUTES (Resident + Guard)
    |--------------------------------------------------------------------------
    */

    // Create Pass (Resident)
    Route::post('/passes/create', [PassController::class, 'create']);

    // Pass Details
    Route::get('/passes/{id}', [PassController::class, 'show']);

    // Resident Pass List
    Route::get('/passes/user/{user_id}', [PassController::class, 'myPasses']);


    /*
    |--------------------------------------------------------------------------
    | GUARD – VISITOR / VEHICLE / DELIVERY
    |--------------------------------------------------------------------------
    */

    // Scan Entry
    Route::post('/guard/scan-entry', [GuardController::class, 'scanEntry']);

    // Scan Exit
    Route::post('/guard/scan-exit', [GuardController::class, 'scanExit']);

    // Active Inside
    Route::get('/guard/active-visitors/{society_id}', [GuardController::class, 'activeVisitors']);


    /*
    |--------------------------------------------------------------------------
    | GUARD – JOB APPOINTMENT
    |--------------------------------------------------------------------------
    */

    // Scan Employee Entry
    // Route::post('/guard/entry', [AppointmentController::class, 'secureAppointmentEntry']);

    // // Scan Employee Exit
    // Route::post('/guard/exit', [AppointmentController::class, 'secureAppointmentExit']);

    // Appointment detail
    Route::get('/appointments/{id}', [GuardController::class, 'appointmentDetails']);

    // Active Jobs Inside Society
    // Route::get('/appointments/active/{society_id}', [GuardController::class, 'activeAppointments']);


    /*
    |--------------------------------------------------------------------------
    | PARKING
    |--------------------------------------------------------------------------
    */

    Route::get('/parking/slots/{society_id}', [ParkingController::class, 'listSlots']);
    Route::post('/parking/assign', [ParkingController::class, 'assignSlot']);
    Route::get('/parking/inside/{society_id}', [ParkingController::class, 'vehiclesInside']);
    Route::get('/parking/history/{society_id}', [ParkingController::class, 'history']);


    /*
    |--------------------------------------------------------------------------
    | GUARD DASHBOARD
    |--------------------------------------------------------------------------
    */

    // Route::get('/guard/dashboard/{society_id}', [DashboardController::class, 'summary']);
    // Route::get('/guard/passes/today/{society_id}', [DashboardController::class, 'todayPasses']);
    // Route::get('/guard/logs/{society_id}', [DashboardController::class, 'logs']);


    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    Route::post('/update-fcm-token', [NotificationController::class, 'updateToken']);
    Route::post('/testing/firebase', [NotificationController::class, 'testPush']);
    Route::get('/notifications/{user_id}', [NotificationController::class, 'get']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);


    /*
    |--------------------------------------------------------------------------
    | AUDIT LOGS
    |--------------------------------------------------------------------------
    */

    Route::get('/audit/passes/{pass_id}', [AuditController::class, 'passLogs']);
    Route::get('/audit/search', [AuditController::class, 'search']);

    /*|--------------------------------------------------------------------------
    | SERVICES AND CATEGORIES
    |--------------------------------------------------------------------------
    */

    Route::get('/service-categories', [ServiceController::class, 'categories']);
    Route::get('/services/{id?}', [ServiceController::class, 'servicesByCategory']);


    Route::post('/labor/earnings', [PaymentController::class, 'laborEarnings']);
    Route::get('/labor/appointment/payments', [PaymentController::class, 'laborAppointmentPayments']);


    Route::get('/guard/filter-passes/{society_id}', [GuardController::class, 'filterPasses']);
    Route::get('/get-societies/{society_id}/residents-inside', [GuardController::class, 'residentsInside']);


    Route::prefix('guard')->group(function () {
        Route::post('identify', [GuardController::class, 'identify']);
        Route::post('approve-entry', [GuardController::class, 'approveEntry']);
        Route::post('approve-exit', [GuardController::class, 'approveExit']);
        Route::get('report/inside/{societyId}', [GuardController::class, 'reportActiveInside']);
        Route::get('history/{societyId}', [GuardController::class, 'scanHistory']);
        Route::get('active-passes/{societyId}', [GuardController::class, 'activePassesInside']);
        Route::get('pass-logs/{societyId}', [GuardController::class, 'passLogs']);
        Route::get('pass-detail/{passId}', [GuardController::class, 'passDetail']);
        Route::post('add-vehicle', [GuardController::class, 'guardAddVehicle']);
        Route::get('inside-detail/{type}/{id}', [GuardController::class, 'insideDetail']);
        Route::get('log-history/{societyId}', [GuardController::class, 'allLogHistory']);

        Route::post('shift/assign', [ShiftController::class,'assignShift']);
        Route::get('shift/list/{societyId}', [ShiftController::class,'listShifts']);
        Route::get('shift/detail/{id}', [ShiftController::class,'shiftDetail']);
        Route::post('shift/{id}/request-change', [ShiftController::class,'requestShiftChange']);
        Route::post('shift-request/{id}/decide', [ShiftController::class,'decideShiftRequest']);
        Route::post('shift/{id}/complete', [ShiftController::class,'markShiftComplete']);
        Route::post('shift/{id}/cancel', [ShiftController::class,'cancelShift']);

        // ✅ SOCIETY → SHIFTS (FIXED ROUTE)
        Route::get(
            '/societies/{societyId}/shifts',
            [GuardShiftController::class, 'societyShifts']
        );

        // Shift detail
        Route::get(
            '/shifts/{id}',
            [GuardShiftController::class, 'show']
        );

        // Guard request change
        Route::post(
            '/shifts/{id}/request-change',
            [GuardShiftController::class, 'requestChange']
        );

        // Admin approve / reject
        Route::post(
            '/shift-requests/{id}/decide',
            [GuardShiftController::class, 'decide']
        );
    });


});
