<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use Illuminate\Http\Request;

class UserDetailController extends Controller
{
    // List all user details
    public function index()
    {
        $userDetails = UserDetail::with(['user', 'society'])->get();
        return response()->json($userDetails);
    }

    // Show a single user detail
    public function show($id)
    {
        $userDetail = UserDetail::with(['user', 'society'])->findOrFail($id);
        return response()->json($userDetail);
    }

    // Store new user detail
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'society_id' => 'required|exists:societies,id',
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'occupation' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        $userDetail = UserDetail::create($validated);

        return response()->json($userDetail, 201);
    }

    // Update existing user detail
    public function update(Request $request, $id)
    {
        $userDetail = UserDetail::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'society_id' => 'sometimes|exists:societies,id',
            'full_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'occupation' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        $userDetail->update($validated);

        return response()->json($userDetail);
    }

    // Delete a user detail
    public function destroy($id)
    {
        $userDetail = UserDetail::findOrFail($id);
        $userDetail->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
