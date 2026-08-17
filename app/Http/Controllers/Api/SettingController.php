<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Display all settings, optionally filtered by group.
     */
    public function index(Request $request)
    {
        $query = Setting::query();

        $group = $request->query('group') ?? $request->query('group_name');
        if ($group) {
            $query->where('group', $group);
        }

        $settings = $query->get()->map(function ($s) {
            // Decode json values if needed
            $val = $s->value;
            if ($s->type === 'json' && is_string($val)) {
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $val = $decoded;
                }
            }

            return [
                'id'          => $s->id,
                'key'         => $s->key,
                'value'       => $val,
                'type'        => $s->type,
                'group'       => $s->group,
                'group_name'  => $s->group,
                'label'       => $s->label,
                'description' => $s->description,
                'options'     => $s->options,
                'created_at'  => $s->created_at,
                'updated_at'  => $s->updated_at,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $settings
        ]);
    }

    /**
     * Show a single setting by ID or key.
     */
    public function show($identifier)
    {
        $setting = is_numeric($identifier)
            ? Setting::find($identifier)
            : Setting::where('key', $identifier)->first();

        if (! $setting) {
            return response()->json(['status' => false, 'message' => 'Setting not found'], 404);
        }

        // Decode JSON if it's stored as JSON in DB
        $value = $setting->value;
        if ($setting->type === 'json' && is_string($setting->value)) {
            $decoded = json_decode($setting->value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        return response()->json([
            'status' => true,
            'key'    => $setting->key,
            'value'  => $value,
            'group'  => $setting->group,
            'label'  => $setting->label,
            'type'   => $setting->type,
        ]);
    }

    /**
     * Retrieve payment gateway configuration for mobile / client apps.
     * GET /api/settings/payment-config or GET /api/payment-config
     */
    public function paymentConfig()
    {
        $gateway = Setting::getValue('payment_gateway', 'cashfree');
        $cfConfig = Setting::getCashfreeConfig();
        $rzKey = Setting::getValue('razorpay_key', config('services.razorpay.key', ''));
        $currency = Setting::getValue('currency', 'INR');
        $minAmount = (float) Setting::getValue('minimum_payment_amount', 10);

        return response()->json([
            'status'                 => true,
            'payment_gateway'        => $gateway,
            'currency'               => $currency,
            'minimum_payment_amount' => $minAmount,
            'cashfree'               => [
                'app_id'      => $cfConfig['app_id'],
                'environment' => $cfConfig['is_live'] ? 'PRODUCTION' : 'SANDBOX',
                'is_live'     => $cfConfig['is_live'],
                'mode'        => $cfConfig['environment'],
            ],
            'razorpay'               => [
                'key_id'      => $rzKey,
            ],
        ]);
    }

    /**
     * Retrieve Security Guard configuration and options.
     * GET /api/settings/guard-config or GET /api/guard/settings
     */
    public function guardConfig()
    {
        $guardSettings = Setting::where('group', 'guard')->get()->pluck('value', 'key');

        return response()->json([
            'status' => true,
            'data'   => [
                'guard_allow_manual_entry'         => (bool) ($guardSettings['guard_allow_manual_entry'] ?? 1),
                'guard_require_vehicle_number'     => (bool) ($guardSettings['guard_require_vehicle_number'] ?? 1),
                'guard_visitor_auto_checkout_hours'=> (int) ($guardSettings['guard_visitor_auto_checkout_hours'] ?? 12),
                'guard_emergency_number'           => $guardSettings['guard_emergency_number'] ?? '+919999999999',
                'guard_require_resident_approval'  => (bool) ($guardSettings['guard_require_resident_approval'] ?? 0),
                'settings'                         => $guardSettings,
            ]
        ]);
    }

    /**
     * Create a new setting.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'key'        => 'required|string|max:191|unique:settings,key',
            'value'      => 'required',
            'type'       => 'nullable|in:string,text,json,boolean,integer,number,select,amount,file,password',
            'group'      => 'nullable|string|max:100',
            'group_name' => 'nullable|string|max:100',
            'label'      => 'nullable|string|max:255',
            'description'=> 'nullable|string|max:500',
            'options'    => 'nullable|array',
        ]);

        $group = $data['group'] ?? $data['group_name'] ?? 'general';
        $type = $data['type'] ?? 'text';

        $val = is_array($data['value']) ? json_encode($data['value']) : $data['value'];

        $setting = Setting::create([
            'key'         => $data['key'],
            'value'       => $val,
            'type'        => $type,
            'group'       => $group,
            'label'       => $data['label'] ?? Str::headline($data['key']),
            'description' => $data['description'] ?? null,
            'options'     => $data['options'] ?? null,
        ]);

        return response()->json(['status' => true, 'message' => 'Setting created successfully', 'data' => $setting], 201);
    }

    /**
     * Update an existing setting (by ID or key).
     */
    public function update(Request $request, $identifier)
    {
        $setting = is_numeric($identifier)
            ? Setting::find($identifier)
            : Setting::where('key', $identifier)->first();

        if (! $setting) {
            return response()->json(['status' => false, 'message' => 'Setting not found'], 404);
        }

        $data = $request->validate([
            'value'       => 'required',
            'type'        => 'nullable|in:string,text,json,boolean,integer,number,select,amount,file,password',
            'group'       => 'nullable|string|max:100',
            'group_name'  => 'nullable|string|max:100',
            'label'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'options'     => 'nullable|array',
        ]);

        $group = $data['group'] ?? $data['group_name'] ?? $setting->group;
        $val = is_array($data['value']) ? json_encode($data['value']) : $data['value'];

        $setting->update([
            'value'       => $val,
            'type'        => $data['type'] ?? $setting->type,
            'group'       => $group,
            'label'       => $data['label'] ?? $setting->label,
            'description' => $data['description'] ?? $setting->description,
            'options'     => $data['options'] ?? $setting->options,
        ]);

        return response()->json(['status' => true, 'message' => 'Setting updated successfully', 'data' => $setting]);
    }

    /**
     * Delete a setting by ID or key.
     */
    public function destroy($identifier)
    {
        $setting = is_numeric($identifier)
            ? Setting::find($identifier)
            : Setting::where('key', $identifier)->first();

        if (! $setting) {
            return response()->json(['status' => false, 'message' => 'Setting not found'], 404);
        }

        $setting->delete();

        return response()->json(['status' => true, 'message' => 'Setting deleted successfully']);
    }

    /**
     * Retrieve multiple settings by keys in one request.
     * POST /api/settings/batch-get  with body: { "keys": ["terms_and_conditions", "faqs", "payment_gateway"] }
     */
    public function batchGet(Request $request)
    {
        $data = $request->validate([
            'keys' => 'required|array|min:1',
        ]);

        $keys = $data['keys'];

        $settings = Setting::whereIn('key', $keys)
            ->get()
            ->keyBy('key');

        $result = [];
        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                $s = $settings[$key];
                $val = $s->value;
                if ($s->type === 'json' && is_string($val)) {
                    $decoded = json_decode($val, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $val = $decoded;
                    }
                }
                $result[$key] = $val;
            } else {
                $result[$key] = null;
            }
        }

        return response()->json(['status' => true, 'data' => $result]);
    }
}
