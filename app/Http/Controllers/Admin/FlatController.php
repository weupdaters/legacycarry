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
        $submitted_ids = collect($request->flats)->pluck('id')->filter()->toArray();

        // 1. Delete flats that were removed in the UI (not in submitted_ids)
        Flat::where('society_id', $society_id)
            ->whereNotIn('id', $submitted_ids)
            ->delete();

        // 2. Update or Create
        foreach ($request->flats as $flatData) {
            if (isset($flatData['id']) && !empty($flatData['id'])) {
                // Update existing
                Flat::where('id', $flatData['id'])->update([
                    'building_no' => $flatData['building_no'],
                    'owner_name'  => $flatData['owner_name'] ?? null,
                    'status'      => $flatData['status'],
                ]);
            } else {
                // Create new
                Flat::create([
                    'society_id'  => $society_id,
                    'building_no' => $flatData['building_no'],
                    'owner_name'  => $flatData['owner_name'] ?? null,
                    'status'      => $flatData['status'],
                ]);
            }
        }

        return redirect()->route('societies.index')
                         ->with('success', 'Buildings updated successfully!');
    }
}
