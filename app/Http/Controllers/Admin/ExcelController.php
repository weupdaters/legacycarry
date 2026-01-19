<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\ResidentAssignment;
use App\Models\User;
use App\Traits\HasGlobalExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    use HasGlobalExport;


    public function exportEmployeesFull()
    {
        // Get all employees with all relations needed for export
        $data = User::where('role', 'labor')
            ->with([
                'employee',
            ])
            ->get();

        // Define headings for Excel columns
        $columns = [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Verification Status',
            'Experience (Years)',
            'Hourly Rate',
            'Availability',
            'Work Radius (KM)',
            'Services',
            'About',
            'ID Number',
        ];

        $rows = $data->map(function ($user) {

            // Employee services (JSON → string)
            $services = $user->employee->services ?? [];
            if (is_string($services)) {
                $services = json_decode($services, true);
            }
            $services = is_array($services) ? implode(', ', $services) : '';



            return [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                ucfirst($user->verification_status),
                $user->employee->experience_years ?? '—',
                $user->employee->hourly_rate ?? '—',
                $user->employee->availability ?? '—',
                $user->employee->work_radius_km ?? '—',
                $services,
                $user->employee->about ?? '—',
                $user->employee->id_number ?? '—',
            ];
        });

        return Excel::download(new \App\Exports\ArrayExport($columns, $rows), 'employees_full.xlsx');
    }


    public function exportResidentsFull()
    {
        $data = User::where('role', 'customer')
            ->with([
                'details',
                'details.society',
                'details.flat',
            ])
            ->get();

        $columns = [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Verification Status',

            // Details
            'Full Name',
            'Gender',
            'Date of Birth',
            'Address',
            'Occupation',
            'Marital Status',

            // Society / Flat
            'Society Name',
            'Building No',
            'Owner Name',
            'Flat Status',

            // Emergency
            'Emergency Contact Name',
            'Emergency Contact Phone',

            // System
            'Joined Date',
        ];

        $rows = $data->map(function ($user) {

            $d = $user->details;     // may be null
            $soc = optional($d)->society;
            $flat = optional($d)->flat;

            return [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                ucfirst($user->verification_status),

                // DETAILS
                optional($d)->full_name ?? '-',
                optional($d)->gender ?? '-',
                optional($d)->date_of_birth ?? '-',
                optional($d)->address ?? '-',
                optional($d)->occupation ?? '-',
                optional($d)->marital_status ?? '-',

                // SOCIETY + FLAT
                optional($soc)->name ?? '-',
                optional($flat)->building_no ?? '-',
                optional($flat)->owner_name ?? '-',
                optional($flat)->status ?? '-',

                // EMERGENCY
                optional($d)->emergency_contact_name ?? '-',
                optional($d)->emergency_contact_phone ?? '-',

                // SYSTEM FIELD
                $user->created_at ? $user->created_at->format('d M Y') : '-',
            ];
        });


        return Excel::download(
            new \App\Exports\ArrayExport($columns, $rows),
            'residents_full.xlsx'
        );
    }


    public function exportGuards()
    {
        $guards = User::where('role', 'guard')
            ->with(['details', 'details.society', 'details.flat'])
            ->get();

        dd($guards);

        // Column titles
        $columns = [
            'ID', 'Name', 'Email', 'Phone', 'Status',
            'Full Name', 'Gender', 'DOB', 'Address',
            'Occupation', 'Marital Status',
            'Society', 'Building No', 'Owner', 'Flat Status',
            'Emergency Name', 'Emergency Phone',
            'Joined Date',
        ];

        // Convert DB rows → Excel rows
        $rows = $guards->map(function ($user) {

            $d = $user->details;
            $soc = optional($d)->society;
            $flat = optional($d)->flat;

            return [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                ucfirst($user->verification_status),

                // DETAILS
                optional($d)->full_name ?? '-',
                optional($d)->gender ?? '-',
                optional($d)->date_of_birth ?? '-',
                optional($d)->address ?? '-',
                optional($d)->occupation ?? '-',
                optional($d)->marital_status ?? '-',

                // SOCIETY + FLAT
                optional($soc)->name ?? '-',
                optional($flat)->building_no ?? '-',
                optional($flat)->owner_name ?? '-',
                optional($flat)->status ?? '-',

                // EMERGENCY
                optional($d)->emergency_contact_name ?? '-',
                optional($d)->emergency_contact_phone ?? '-',

                // JOINED
                $user->created_at ? $user->created_at->format('d M Y') : '-',
            ];
        });



        // Export using GlobalExport trait
        return Excel::download(
            new \App\Exports\ArrayExport($columns, $rows),
            'guards.xlsx'
        );
    }


    public function exportJobsFull()
    {
        $jobs = JobPost::with(['user', 'user.details'])->get();

        $columns = [
            'Job ID', 'Job Title', 'Job Type', 'Workers Required',
            'Pay Type', 'Pay Amount', 'Advance Payment', 'Advance Amount',
            'Skills Required', 'Tools Provided', 'Documents Required',
            'Safety Instructions', 'Start Date', 'End Date', 'Status',
            'User ID', 'User Name', 'User Phone', 'User Email',
            'User Society', 'User Flat', 'User Address',
            'Location', 'Address', 'Created Date',
        ];

        $rows = [];

        foreach ($jobs as $job) {

            $skills = $job->skills_required;
            if (is_string($skills)) {
                $skills = json_decode($skills, true);
            }
            $skills = is_array($skills) ? implode(', ', $skills) : '-';

            $user = $job->user;
            $details = $user->details ?? null;

            // IMPORTANT: associative row with matching keys
            $rows[] = [
                'Job ID' => $job->id,
                'Job Title' => $job->job_title,
                'Job Type' => $job->job_type,
                'Workers Required' => $job->workers_required,
                'Pay Type' => $job->pay_type,
                'Pay Amount' => $job->pay_amount,
                'Advance Payment' => $job->advance_payment ? 'Yes' : 'No',
                'Advance Amount' => $job->advance_payment ? $job->advance_amount : '-',
                'Skills Required' => $skills,
                'Tools Provided' => $job->tools_provided ?? '-',
                'Documents Required' => $job->documents_required ?? '-',
                'Safety Instructions' => $job->safety_instructions ?? '-',
                'Start Date' => optional($job->start_date)->format('d-m-Y') ?? '-',
                'End Date' => optional($job->end_date)->format('d-m-Y') ?? '-',
                'Status' => ucfirst($job->status),

                'User ID' => $user->id ?? '-',
                'User Name' => $user->name ?? '-',
                'User Phone' => $user->phone ?? '-',
                'User Email' => $user->email ?? '-',

                'User Society' => $details->society->name ?? '-',
                'User Flat' => $details->flat->building_no ?? '-',
                'User Address' => $details->address ?? '-',

                'Location' => $job->location ?? '-',
                'Address' => $job->address ?? '-',
                'Created Date' => $job->created_at?->format('d M Y') ?? '-',
            ];
        }

        // EXPORT
        return Excel::download(
            new \App\Exports\ArraySingle($columns, $rows),
            'jobs_full.csv'
        );

    }


    public function exportResidentAssign()
    {
        // Fetch assigned residents with related data
        $data = ResidentAssignment::with(['user', 'society','flat'])->get();

        // Define Excel column headings
        $columns = [
            'ID',
            'User Name',
            'User Phone',
            'Society',
            'Assigned At',
            'status',
        ];

        // Map data to rows
        $rows = $data->map(function ($assign) {
            return [
                $assign->id,
                $assign->user->name ?? '-',
                $assign->user->phone ?? '-',
                $assign->society->name ?? '-',
                $assign->created_at ? $assign->created_at->format('d M Y') : '-',
                ucfirst($assign->status),
            ];
        });

        // Export using GlobalExport trait
        return Excel::download(
            new \App\Exports\ArrayExport($columns, $rows),
            'resident_assignments.xlsx'
        );
    }


}
