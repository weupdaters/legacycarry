<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flat;
use App\Models\Society;
use Illuminate\Http\Request;

class FlatController extends Controller
{
    /*--------------------------------------------------------
     | SHOW ADD + UPDATE BUILDINGS PAGE
     --------------------------------------------------------*/
    public function create($society_id)
    {
        $society = Society::findOrFail($society_id);

        // Load existing flats to edit
        $flats = Flat::where('society_id', $society_id)->get();

        return view('admin.societies.manageFlat', compact('society', 'flats'));
    }


    /*--------------------------------------------------------
     | STORE OR UPDATE MULTIPLE BUILDINGS
     --------------------------------------------------------*/
    public function store(Request $request)
    {
        $request->validate([
            'society_id' => 'required|exists:societies,id',
            'flats'      => 'required|array|min:1',

            'flats.*.building_no' => 'required|string|max:100',
            'flats.*.owner_name'  => 'nullable|string|max:255',
            'flats.*.status'      => 'required|in:occupied,vacant',
        ]);

        $society_id = $request->society_id;

        // Delete all old flats FIRST
        Flat::where('society_id', $society_id)->delete();

        // Recreate all submitted flats
        foreach ($request->flats as $flat) {
            Flat::create([
                'society_id'  => $society_id,
                'building_no' => $flat['building_no'],
                'owner_name'  => $flat['owner_name'] ?? null,
                'status'      => $flat['status'],
            ]);
        }

        return redirect()->route('societies.index')
                         ->with('success', 'Buildings updated successfully!');
    }
}
