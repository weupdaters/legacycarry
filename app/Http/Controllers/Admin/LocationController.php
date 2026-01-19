<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;

class LocationController extends Controller
{
    // Get all countries with states and cities
    public function getAll()
    {
        $countries = Country::with('states.cities')->get();

        return response()->json([
            'status' => true,
            'countries' => $countries,
        ]);
    }

    // Get states by country ID
    public function getStates($countryId)
    {
        $states = State::where('country_id', $countryId)->with('cities')->get();

        return response()->json([
            'status' => true,
            'states' => $states,
        ]);
    }

    // Get cities by state ID
    public function getCities($stateId)
    {
        $cities = City::where('state_id', $stateId)->get();

        return response()->json([
            'status' => true,
            'cities' => $cities,
        ]);
    }
}
