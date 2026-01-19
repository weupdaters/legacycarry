<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use App\Models\Society; 
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Flat;



class LocationController extends Controller
{
    // Get all countries with states and cities
    public function getAll()
    {
        $countries = Country::with('states.cities')->get();

        return response()->json([
            'status' => true,
            'countries' => $countries
        ]);
    }

    // Get states by country ID
    public function getStates($countryId)
    {
        $states = State::where('country_id', $countryId)->with('cities')->get();

        return response()->json([
            'status' => true,
            'states' => $states
        ]);
    }

    // Get cities by state ID
    public function getCities($stateId)
    {
        $cities = City::where('state_id', $stateId)->get();

        return response()->json([
            'status' => true,
            'cities' => $cities
        ]);
    }

    // Get societies by city id
    public function getSocietiesByCity($cityId)
    {
        $societies = Society::where('city_id', $cityId)->get();

        return response()->json([
            'status' => true,
            'societies' => $societies
        ]);
    }

    public function getflatsBySociety($societyId)
    {
        $society = Society::with('flats')->find($societyId);

        if (!$society) {
            return response()->json([
                'status' => false,
                'message' => 'Society not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'flats' => $society->flats
        ]);
    }


    // Bulk create or upsert multiple societies in one request
    public function upsertSocieties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'societies' => 'required|array|min:1',
            'societies.*.name' => 'required|string|max:255',
            'societies.*.city_id' => 'required|exists:cities,id',
            'societies.*.address' => 'nullable|string|max:1000',
            'societies.*.pincode' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $now = Carbon::now();
        $rows = [];

        foreach ($request->input('societies') as $s) {
            // optionally, fill state_id and country_id if you want consistency
            $city = City::find($s['city_id']);
            $rows[] = [
                'name' => $s['name'],
                'address' => $s['address'] ?? null,
                'pincode' => $s['pincode'] ?? null,
                'city_id' => $s['city_id'],
                'state_id' => $city ? $city->state_id : null,
                'country_id' => $city && $city->state ? $city->state->country_id : null,
                'updated_at' => $now,
                'created_at' => $now, // upsert will ignore for exists, but safe to include
            ];
        }

        // Upsert by unique key city_id + name, and update address/pincode/updated_at on conflict
        Society::upsert($rows, ['city_id', 'name'], ['address', 'pincode', 'state_id', 'country_id', 'updated_at']);

        return response()->json([
            'status' => true,
            'message' => 'Societies inserted/updated successfully'
        ]);
    }
}
