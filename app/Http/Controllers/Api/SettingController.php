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

        if ($request->has('group')) {
            $query->where('group_name', $request->query('group'));
        }

        $settings = $query->get()->map(function ($s) {
            return [
                'id'          => $s->id,
                'key'         => $s->key,
                'value'       => $s->value,
                'type'        => $s->type,
                'group_name'  => $s->group_name,
                'created_at'  => $s->created_at,
                'updated_at'  => $s->updated_at,
            ];
        });

        return response()->json(['data' => $settings]);
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
            return response()->json(['message' => 'Setting not found'], 404);
        }

        // Decode JSON if it's stored as JSON in DB
        $value = $setting->type === 'json' && is_string($setting->value)
            ? json_decode($setting->value, true)
            : $setting->value;

        // ✅ Return only the value field
        return response()->json($value);
    }


    /**
     * Create a new setting.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'key'        => 'required|string|max:191|unique:settings,key',
            'value'      => 'required',
            'type'       => 'nullable|in:string,text,json,boolean,integer',
            'group_name' => 'nullable|string|max:100',
        ]);

        $data['type'] = $data['type'] ?? 'text';

        $setting = Setting::create([
            'key'        => $data['key'],
            'value'      => $data['value'],
            'type'       => $data['type'],
            'group_name' => $data['group_name'] ?? null,
        ]);

        return response()->json(['message' => 'Created', 'data' => $setting], 201);
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
            return response()->json(['message' => 'Setting not found'], 404);
        }

        $data = $request->validate([
            'value'      => 'required',
            'type'       => 'nullable|in:string,text,json,boolean,integer',
            'group_name' => 'nullable|string|max:100',
        ]);

        $setting->update([
            'value'      => $data['value'],
            'type'       => $data['type'] ?? $setting->type,
            'group_name' => $data['group_name'] ?? $setting->group_name,
        ]);

        return response()->json(['message' => 'Updated', 'data' => $setting]);
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
            return response()->json(['message' => 'Setting not found'], 404);
        }

        $setting->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Retrieve multiple settings by keys in one request.
     * POST /api/settings/batch-get  with body: { "keys": ["terms", "faqs"] }
     */
    public function batchGet(Request $request)
    {
        $data = $request->validate([
            'keys' => 'required|array|min:1',
        ]);

        $keys = $data['keys'];

        $settings = Setting::whereIn('key', $keys)
            ->get()
            ->keyBy('key')
            ->map(fn($s) => $s->value);

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $settings[$key] ?? null;
        }

        return response()->json(['data' => $result]);
    }
}
