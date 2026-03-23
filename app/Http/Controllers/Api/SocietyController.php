<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Society;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SocietyController extends Controller
{
    public function index()
    {
        $societies = Society::all();
        return response()->json(['status' => true, 'data' => $societies], 200);
    }

    public function show($id)
    {
        $society = Society::find($id);
        if (!$society) {
            return response()->json(['status' => false, 'message' => 'Society not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $society], 200);
    }

    // Create new Society with validation
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'address' => 'nullable|string|max:1000',
            'pincode' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $now = Carbon::now();
        $city = City::find($request->city_id);

        $data = [
            'name' => $request->name,
            'address' => $request->address,
            'pincode' => $request->pincode,
            'city_id' => $request->city_id,
            'state_id' => $city ? $city->state_id : null,
            'country_id' => $city && $city->state ? $city->state->country_id : null,
            'updated_at' => $now,
            'created_at' => $now,
        ];

        // Insert or update using unique (city_id + name)
        Society::upsert(
            [$data],
            ['city_id', 'name'],
            ['address', 'pincode', 'state_id', 'country_id', 'updated_at']
        );

        return response()->json([
            'status' => true,
            'message' => 'Society inserted/updated successfully',
        ]);
    }



}
