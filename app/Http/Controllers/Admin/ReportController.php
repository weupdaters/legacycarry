<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\SocietyNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Build base query for reports with filters applied
     */
    private function buildFilterQuery(Request $request)
    {
        $query = Report::with('user');

        // Search in reference, description, email, or user name/phone
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%")
                         ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by report type (Bug, Payment, Feedback, Account, Other)
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        // Filter by status (open, in_progress, resolved, closed)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user role (customer, employee, super_admin)
        if ($request->filled('user_role')) {
            $role = $request->user_role;
            $query->whereHas('user', function ($uq) use ($role) {
                $uq->where('role', $role);
            });
        }

        // Filter by date range (from / to)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'type_asc':
                $query->orderBy('report_type', 'asc');
                break;
            case 'type_desc':
                $query->orderBy('report_type', 'desc');
                break;
            case 'status_asc':
                $query->orderBy('status', 'asc');
                break;
            case 'status_desc':
                $query->orderBy('status', 'desc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    /**
     * Display a listing of all reports & bug reports
     */
    public function index(Request $request)
    {
        $query = $this->buildFilterQuery($request);

        $perPage = (int) $request->get('per_page', 15);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 15;
        }

        $reports = $query->paginate($perPage)->withQueryString();

        // Summary Counts
        $totalReports = Report::count();
        $totalBugs = Report::where('report_type', 'Bug')->count();
        $openReports = Report::whereIn('status', ['open', 'pending'])->count();
        $inProgressReports = Report::where('status', 'in_progress')->count();
        $resolvedReports = Report::whereIn('status', ['resolved', 'closed'])->count();

        if ($request->ajax()) {
            return view('admin.reports.partials.table', compact('reports'))->render();
        }

        return view('admin.reports.index', compact(
            'reports',
            'totalReports',
            'totalBugs',
            'openReports',
            'inProgressReports',
            'resolvedReports'
        ));
    }

    /**
     * Show detailed view of a report
     */
    public function show($id)
    {
        $report = Report::with('user')->findOrFail($id);
        return view('admin.reports.show', compact('report'));
    }

    /**
     * Update report status and admin notes
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $report = Report::with('user')->findOrFail($id);
        $report->status = $request->status;
        if ($request->has('admin_notes')) {
            $report->admin_notes = $request->admin_notes;
        }
        $report->save();

        // Notify resident user about report status update
        if ($report->user) {
            $statusLabel = ucfirst(str_replace('_', ' ', $report->status));
            $title = "Report Status Updated: {$statusLabel}" . ($report->status === 'resolved' ? ' ✅' : '');
            $typeDesc = !empty($report->report_type) ? " ({$report->report_type})" : '';
            $body = "Your report #{$report->reference}{$typeDesc} has been marked as {$statusLabel}." . ($request->filled('admin_notes') ? " Note: " . $request->admin_notes : "");

            SocietyNotificationService::notifyUser(
                $report->user,
                $title,
                $body,
                'resident_report_status',
                [
                    'screen'       => 'report_details',
                    'type'         => 'resident_report_status',
                    'report_id'    => (string) $report->id,
                    'reference'    => (string) $report->reference,
                    'status'       => (string) $report->status,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully.',
                'status' => $report->status,
            ]);
        }

        return redirect()->back()->with('success', 'Report status updated successfully.');
    }

    /**
     * Remove a report
     */
    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return redirect()->route('admin.reports.index')->with('success', 'Report deleted successfully.');
    }

    /**
     * Export filtered reports to CSV
     */
    public function export(Request $request)
    {
        $reports = $this->buildFilterQuery($request)->get();

        $filename = 'reports_export_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($reports) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'ID',
                'Reference',
                'Report Type',
                'Status',
                'Reported By Name',
                'Reported By Email',
                'Reported By Phone',
                'Contact Email',
                'Description',
                'Admin Notes',
                'Created At',
                'Updated At'
            ]);

            foreach ($reports as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->reference,
                    $r->report_type,
                    $r->status,
                    $r->user ? $r->user->name : 'N/A',
                    $r->user ? $r->user->email : 'N/A',
                    $r->user ? $r->user->phone : 'N/A',
                    $r->email,
                    $r->description,
                    $r->admin_notes,
                    $r->created_at ? $r->created_at->format('Y-m-d H:i:s') : '',
                    $r->updated_at ? $r->updated_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
