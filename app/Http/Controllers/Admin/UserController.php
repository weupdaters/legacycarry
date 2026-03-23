<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\JobPost;
use App\Models\Society;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserDocument;
use App\Models\UserPaymentMethod;
use App\Models\Vehicle;
use App\Traits\SafeFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use SafeFilter;

    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function Residents(Request $request)
    {
        $residents = User::where('role', 'customer')
            ->with('details')

            // Global search
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })

            // Verification status filter
            ->when($request->status, function ($q) use ($request) {
                $q->where('verification_status', $request->status);
            })

            ->paginate(10);

        if ($request->ajax()) {
            return view('admin.residents.index', compact('residents'))->render();
        }



        return view('admin.residents.index', compact('residents'));
    }


    public function editResident($id)
    {
        $user = User::with('details')->findOrFail($id);
        return view('admin.residents.edit', compact('user'));
    }

    public function updateResident(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // ---------------------
        // 1) VALIDATION
        // ---------------------
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',

            // Avatar upload
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Details
            'details.full_name' => 'nullable|string|max:255',
            'details.gender' => 'nullable|string',
            'details.date_of_birth' => 'nullable|date',
            'details.address' => 'nullable|string',
            'details.occupation' => 'nullable|string',
            'details.marital_status' => 'nullable|string',

            // Society + Flat
            'details.society_id' => 'nullable|exists:societies,id',
            'details.flat.building_no' => 'nullable|string|max:50',
            'details.flat.owner_name' => 'nullable|string|max:255',
            'details.flat.status' => 'nullable|string|max:255',

            // Emergency
            'details.emergency_contact_name' => 'nullable|string|max:255',
            'details.emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        // ---------------------
        // 2) UPDATE USER FIELDS
        // ---------------------
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->is_active = $request->is_active;

        // ---------------------
        // 3) HANDLE AVATAR UPLOAD
        // ---------------------
        if ($request->hasFile('avatar')) {
            // delete old avatar
            if ($user->avatar && file_exists(public_path('storage/' . $user->avatar))) {
                unlink(public_path('storage/' . $user->avatar));
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        // ---------------------
        // 4) UPDATE USER DETAILS (JSON or separate table)
        // ---------------------

        // If you store details inside "details" JSON column on users table:
        if (property_exists($user, 'details')) {
            $details = $user->details ?? [];
            $newDetails = $request->input('details', []);

            // Merge old + new details
            $details = array_replace_recursive($details, $newDetails);

            $user->details = $details;
            $user->save();
        }

        // If details, flat, society are separate models (optional)
        if ($user->details) {
            $user->details->update($request->input('details', []));
        }

        return redirect()
            ->route('residents.index', $user->id)
            ->with('success', 'Resident updated successfully!');
    }


    public function Employees(Request $request)
    {
        $query = $request->input('query');

        $query = User::where('role', 'labor')
        ->with('employee')
        ->when($request->search, function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        })
        ->when(
            $request->status,
            fn ($q) =>
            $q->where('verification_status', $request->status)
        )
        ->when($request->availability, function ($q) use ($request) {
            $q->whereHas(
                'employee',
                fn ($e) =>
                $e->where('availability', $request->availability)
            );
        })
        ->when($request->experience, function ($q) use ($request) {
            $q->whereHas(
                'employee',
                fn ($e) =>
                $e->where('experience_years', '>=', $request->experience)
            );
        })
        ->when($request->min_rate, function ($q) use ($request) {
            $q->whereHas(
                'employee',
                fn ($e) =>
                $e->where('hourly_rate', '>=', $request->min_rate)
            );
        })
        ->when($request->max_rate, function ($q) use ($request) {
            $q->whereHas(
                'employee',
                fn ($e) =>
                $e->where('hourly_rate', '<=', $request->max_rate)
            );
        });

        $employers = $query->withCount([
            'jobs as pending_jobs_count' => fn ($q) => $q->where('status', 'pending'),
            'jobs as active_jobs_count' => fn ($q) => $q->where('status', 'active'),
            'jobs as completed_jobs_count' => fn ($q) => $q->where('status', 'completed'),
        ])
        ->paginate(10);

        // ✅ If AJAX → return ONLY table HTML, not whole page

        if ($request->ajax()) {
            return view('admin.employers.index', compact('employers'))
                    ->renderSections()['table'];
        }


        return view('admin.employers.index', compact('employers'));
    }


    public function viewEmployees($id)
    {
        $user = User::with('employee')->findOrFail($id);

        // Jobs created by user
        $jobs = JobPost::where('user_id', $id)->latest()->get();

        $payments = UserPaymentMethod::where('user_id', $user->id)->get();

        $documents = UserDocument::where('user_id', $user->id)->get();

        return view('admin.employers.view', compact('user', 'jobs', 'payments', 'documents'));
    }

    public function editEmployees($id)
    {
        $user = User::with('employee')->findOrFail($id);
        return view('admin.employers.edit', compact('user'));
    }

    public function updateEmployees(Request $request, $id)
    {
        // Similar to updateResident but for employees
    }




    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:25',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:labor,customer,guard,super_admin',
            'verification_status' => 'required|in:unverified,pending,verified,rejected',
            'aadhaar_number' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'verification_status' => $request->verification_status,
            'aadhaar_number' => $request->aadhaar_number,
            'avatar' => $avatarPath,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'User added successfully!');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:25',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:labor,customer,guard,super_admin',
            'verification_status' => 'required|in:unverified,pending,verified,rejected',
            'aadhaar_number' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $avatarPath = $user->avatar;
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => $request->role,
            'verification_status' => $request->verification_status,
            'aadhaar_number' => $request->aadhaar_number,
            'avatar' => $avatarPath,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }


    public function toggleBan($id)
    {
        $user = User::findOrFail($id);

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'User status updated!');
    }

    public function viewResident($id)
    {
        // Load user with related details
        $user = User::with([
            'details',
            'details.society',
            'details.flat',
        ])->findOrFail($id);

        // Jobs created by user
        $jobs = JobPost::where('user_id', $id)
            ->latest()
            ->get();

        // Payment methods of user
        $payments = UserPaymentMethod::where('user_id', $user->id)
            ->latest()
            ->get();

        // Uploaded documents
        $documents = UserDocument::where('user_id', $user->id)
            ->latest()
            ->get();

        // ✅ ADD: Registered vehicles of user
        $vehicles = Vehicle::where('user_id', $user->id)
            ->latest()
            ->get();

        return view(
            'admin.residents.view',
            compact('user', 'jobs', 'payments', 'documents', 'vehicles')
        );
    }



    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'User deleted successfully!');
    }

    public function updateVerification(Request $request, $id)
    {
        // Validate allowed values
        $request->validate([
            'verification_status' => 'required|in:unverified,pending,verified,rejected',
        ]);

        // Find user/employee
        $user = User::findOrFail($id);

        // Update field
        $user->verification_status = $request->verification_status;
        $user->save();

        // Return back with success message
        return back()->with('success', 'Verification status updated successfully.');
    }

    public function ResiDocsupdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'remarks' => 'nullable|string|max:255',
        ]);

        $doc = UserDocument::findOrFail($id);

        $doc->status = $request->status;
        $doc->remarks = $request->remarks;
        $doc->save();

        return back()->with('success', 'Document status updated successfully.');
    }

    public function EmpDocsupdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'remarks' => 'nullable|string|max:255',
        ]);

        $doc = UserDocument::findOrFail($id);

        $doc->status = $request->status;
        $doc->remarks = $request->remarks;
        $doc->save();

        return back()->with('success', 'Document status updated successfully.');
    }

    public function ResiVehicleUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $vehicle = Vehicle::findOrFail($id);
        $vehicle->status = $request->status;
        $vehicle->save();

        return back()->with('success', 'Vehicle status updated');
    }


    public function Guards(Request $request)
    {
        $guards = User::where('role', 'guard')
            ->with('details')

            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })

            ->when($request->status, function ($q) use ($request) {
                $activeValue = $request->status === 'Active' ? 1 : 0;
                $q->where('is_active', $activeValue);
            })

            ->paginate(10);

        if ($request->ajax()) {
            return view('admin.guards.index', compact('guards'))->renderSections()['table'];
        }

        return view('admin.guards.index', compact('guards'));
    }



    public function editGuard($id)
    {
        $user = User::with('details')->findOrFail($id);
        return view('admin.guards.edit', compact('user'));
    }

    public function destroyGuard($id)
    {
        return $this->destroy($id);
    }


    public function toggleBanGuard($id)
    {
        return $this->toggleBan($id);
    }


    //get all notifications of user
    public function notifications(Request $request)
    {
        $query = AppNotification::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('sent_to', 'like', '%' . $request->search . '%');
        }

        $notifications = $query->orderByDesc('id')->paginate(10);

        if ($request->ajax()) {
            return view('admin.notifications.table', compact('notifications'))->render();
        }

        return view('admin.notifications.index', compact('notifications'));
    }

}
