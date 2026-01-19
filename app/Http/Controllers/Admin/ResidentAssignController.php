<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResidentAssignment;
use App\Models\Society;
use App\Models\User;
use Illuminate\Http\Request;

class ResidentAssignController extends Controller
{
    /**
     * Display all assigned residents
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $society = $request->query('society_id');
        $status = $request->query('status');

        $assignments = ResidentAssignment::with(['user', 'society'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->when($society, function ($q) use ($society) {
                $q->where('society_id', $society);
            })
            ->when($status, function ($q) use ($status) {

                // Auto convert text to 1/0
                $activeValue = ($status === 'Active') ? 1 : 0;

                $q->whereHas('user', function ($u) use ($activeValue) {
                    $u->where('is_active', $activeValue);
                });
            })
            ->orderByDesc('id')
            ->paginate(15);

        $societies = Society::orderBy('name')->get();

        return view('admin.resident_assign.index', compact('assignments', 'societies'));
    }


    /**
     * Create Page
     */
    public function create()
    {
        $societies = Society::orderBy('name')->get();
        $customers = User::where('role', 'customer')
                        ->orderBy('name')
                        ->get();

        return view('admin.resident_assign.create', compact('societies', 'customers'));
    }

    /**
     * Store new assignment
     */
    public function store(Request $request)
    {
        $request->validate([
            'society_id' => 'required|integer|exists:societies,id',
        ]);

        /** ----- CASE 1: Existing user selected ----- */
        if ($request->filled('existing_user_id')) {

            $user = User::findOrFail($request->existing_user_id);

        } else {
            /** ----- CASE 2: Create new customer ----- */
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:20|unique:users,phone',
            ]);

            // Handle avatar upload safely
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('uploads/avatars', 'public');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password ? bcrypt($request->password) : bcrypt('123456'),
                'role' => 'resident',
                'language' => 'English',
                'region' => 'India',
                'is_active' => 1,
                'avatar' => $avatarPath,
                'verification_status' => 'pending',
            ]);

            $user->details()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'society_id' => $request->society_id,
                    'full_name' => $request->full_name ?? $user->name,
                    'email' => $user->email,
                ]
            );

        }

        // Prevent duplicate assignment
        ResidentAssignment::updateOrCreate(
            ['user_id' => $user->id],
            [
                'society_id' => $request->society_id,
                'assigned_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('resident.assign.index')
            ->with('success', 'Resident assigned successfully.');
    }

    /**
     * Edit assignment
     */
    public function edit($id)
    {
        $assignment = ResidentAssignment::with(['user'])->findOrFail($id);
        $societies = Society::orderBy('name')->get();

        return view('admin.resident_assign.edit', compact('assignment', 'societies'));
    }

    /**
     * Update assignment
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'society_id' => 'required|integer|exists:societies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
        ]);

        $assign = ResidentAssignment::findOrFail($id);
        $user = $assign->user;

        // Update user profile
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        // Update society assignment
        $assign->update([
            'society_id' => $request->society_id,
        ]);

        return redirect()
            ->route('resident.assign.index')
            ->with('success', 'Resident assignment and user details updated successfully.');
    }


    /**
     * Delete assignment
     */
    public function destroy($id)
    {
        $assign = ResidentAssignment::findOrFail($id);

        $assign->delete();

        return redirect()
            ->route('resident.assign.index')
            ->with('success', 'Resident assignment deleted successfully.');
    }

    /**
     * Ban user (set is_active = 0)
     */
    public function ban($id)
    {
        $assign = ResidentAssignment::findOrFail($id);
        $user = $assign->user;

        if ($user->id == auth()->id()) {
            return back()->with('error', 'You cannot ban yourself.');
        }

        $user->update(['is_active' => 0]);

        return back()->with('success', 'User banned successfully.');
    }

    /**
     * Unban user (set is_active = 1)
     */
    public function unban($id)
    {
        $assign = ResidentAssignment::findOrFail($id);
        $user = $assign->user;

        $user->update(['is_active' => 1]);

        return back()->with('success', 'User unbanned successfully.');
    }

}
