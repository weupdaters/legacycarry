<?php

namespace App\Http\Controllers\Api;

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
    public function store(Request $request, $userId)
    {
        // base rules
        $rules = [
            'type' => 'required|in:upi,bank,wallet,card',
            'details' => 'required|array',
            'is_primary' => 'nullable|boolean',
            'provider' => 'nullable|string|max:100'
        ];

        $validator = Validator::make($request->all(), $rules);

        // additional per-type validation
        $validator->sometimes('details.upi_id', 'required|string|max:100', function ($input) {
            return isset($input['type']) && $input['type'] === 'upi';
        });

        $validator->sometimes(['details.bank_name','details.account_number','details.ifsc'], 'required|string|max:191', function ($input) {
            return isset($input['type']) && $input['type'] === 'bank';
        });

        $validator->sometimes(['details.card_number','details.last4','details.expiry'], 'required_without:details.token', function ($input) {
            return isset($input['type']) && $input['type'] === 'card';
        });

        $validator->sometimes(['details.wallet_name','details.wallet_number'], 'required|string|max:191', function ($input) {
            return isset($input['type']) && $input['type'] === 'wallet';
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // If new primary, unset others
        if (!empty($data['is_primary'])) {
            UserPaymentMethod::where('user_id', $userId)->update(['is_primary' => false]);
        }

        // generate masked string
        $masked = $this->generateMasked($data['type'], $data['details']);

        $method = UserPaymentMethod::create([
            'user_id' => $userId,
            'type' => $data['type'],
            'details' => $data['details'],
            'masked' => $masked,
            'is_primary' => $data['is_primary'] ?? false,
            'provider' => $data['provider'] ?? null,
        ]);

        return response()->json(['message' => 'Added', 'data' => $method], 201);
    }

    // PUT /api/users/{userId}/payment-methods/{id}
    public function update(Request $request, $userId, $id)
    {
        $method = UserPaymentMethod::where('user_id', $userId)->where('id', $id)->first();
        if (! $method) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $rules = [
            'details' => 'nullable|array',
            'is_primary' => 'nullable|boolean',
            'provider' => 'nullable|string|max:100'
        ];

        $validator = Validator::make($request->all(), $rules);

        // If details provided, apply per-type checks (based on existing or new type)
        $type = $method->type;
        $input = array_merge($method->toArray(), $request->all()); // allow using request->type in future if you allow changing type

        $validator->sometimes('details.upi_id', 'required|string|max:100', function () use ($type, $input) {
            return $type === 'upi' && array_key_exists('details', $input);
        });

        $validator->sometimes(['details.bank_name','details.account_number','details.ifsc'], 'required|string|max:191', function () use ($type, $input) {
            return $type === 'bank' && array_key_exists('details', $input);
        });

        $validator->sometimes(['details.card_number','details.last4','details.expiry'], 'required_without:details.token', function () use ($type, $input) {
            return $type === 'card' && array_key_exists('details', $input);
        });

        $validator->sometimes(['details.wallet_name','details.wallet_number'], 'required|string|max:191', function () use ($type, $input) {
            return $type === 'wallet' && array_key_exists('details', $input);
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (isset($data['is_primary']) && $data['is_primary']) {
            UserPaymentMethod::where('user_id', $userId)->update(['is_primary' => false]);
            $method->is_primary = true;
        } elseif (isset($data['is_primary'])) {
            $method->is_primary = false;
        }

        if (isset($data['details'])) {
            $method->details = $data['details'];
            $method->masked = $this->generateMasked($method->type, $data['details']);
        }

        if (isset($data['provider'])) {
            $method->provider = $data['provider'];
        }

        $method->save();

        return response()->json(['message' => 'Updated', 'data' => $method], 200);
    }

    // helper: create masked display text
    private function generateMasked($type, $details)
    {
        if (!is_array($details)) return null;

        switch ($type) {
            case 'upi':
                return $details['upi_id'] ?? null;
            case 'bank':
                $bank = $details['bank_name'] ?? 'Bank';
                $acc = isset($details['account_number']) ? substr($details['account_number'], -4) : null;
                return $acc ? sprintf("%s ****%s", $bank, $acc) : $bank;
            case 'wallet':
                $wallet = $details['wallet_name'] ?? 'Wallet';
                $num = isset($details['wallet_number']) ? substr($details['wallet_number'], -4) : null;
                return $num ? sprintf("%s ****%s", $wallet, $num) : $wallet;
            case 'card':
                $brand = $details['brand'] ?? 'Card';
                $last4 = $details['last4'] ?? (isset($details['card_number']) ? substr($details['card_number'],-4) : null);
                return $last4 ? sprintf("%s ****%s", $brand, $last4) : $brand;
            default:
                return null;
        }
    }
}
