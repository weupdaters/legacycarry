<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use App\Services\AmenityBookingService;
use App\Services\SocietyNotificationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Store a new report (API).
     *
     * - If the request is authenticated, the authenticated user's id is used.
     * - Otherwise request must include user_id that exists in users table.
     */
    public function store(Request $request)
    {
        $rules = [
            'report_type' => 'required|in:Bug,Payment,Feedback,Account,Other',
            'description' => 'required|string|min:10',
            'email' => 'nullable|email|max:255',
            'user_id' => 'nullable|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        // Determine user id: prefer authenticated user
        $userId = $request->user() ? $request->user()->id : $request->input('user_id');

        if (! $userId) {
            return response()->json([
                'errors' => [
                    'user_id' => ['The user_id is required when not authenticated.']
                ]
            ], 422);
        }

        // Generate a unique reference, ensure no collision
        do {
            $reference = 'RPT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Report::where('reference', $reference)->exists());

        $report = Report::create([
            'user_id' => $userId,
            'report_type' => $request->report_type,
            'description' => $request->description,
            'email' => $request->email,
            'reference' => $reference,
            'status' => 'open',
        ]);

        // Send confirmation notification to resident & notify admins
        $user = $request->user() ?: User::find($userId);
        if ($user) {
            SocietyNotificationService::notifyUser(
                $user,
                "Report Submitted: #{$report->reference}",
                "Your report ({$report->report_type}) has been submitted successfully.",
                'resident_report_status',
                [
                    'screen'       => 'report_details',
                    'type'         => 'resident_report_status',
                    'report_id'    => (string) $report->id,
                    'reference'    => (string) $report->reference,
                    'status'       => 'open',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]
            );

            $societyId = AmenityBookingService::getUserSocietyId($user);
            if ($societyId) {
                SocietyNotificationService::notifyAdmins(
                    $societyId,
                    "New Report #{$report->reference}",
                    "{$user->name} submitted a {$report->report_type} report.",
                    'resident_report_status',
                    [
                        'screen'       => 'report_details',
                        'type'         => 'resident_report_status',
                        'report_id'    => (string) $report->id,
                        'reference'    => (string) $report->reference,
                        'status'       => 'open',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ]
                );
            }
        }

        return response()->json([
            'message' => 'Report submitted',
            'data' => [
                'reference' => $report->reference,
                'submitted_at' => $report->created_at->toDateTimeString(),
                'report_id' => $report->id,
            ],
        ], 200);
    }

    /**
     * List reports for a user (by user_id).
     */
    public function index($user_id)
    {
        $reports = Report::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * Get single report details by report id.
     */
    public function show($id)
    {
        $report = Report::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }
}
