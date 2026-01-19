<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPayoutMethodController extends Controller
{
    // GET /api/users/{userId}/payment-methods
    public function index($userId)
    {
        $methods = UserPaymentMethod::where('user_id', $userId)
                    ->orderByDesc('is_primary')
                    ->get();

        return response()->json(['data' => $methods], 200);
    }

    // POST /api/users/{userId}/payment-methods
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,verified,unverified,failed',
        ]);

        $payment = UserPaymentMethod::findOrFail($id);
        $payment->verification_status = $request->status;

        // Set verified_at timestamp only when approved
        if ($request->status === 'verified') {
            $payment->verified_at = now();
        } else {
            $payment->verified_at = null;
        }

        $payment->save();

        return back()->with('success', 'Payment status updated');
    }

}
