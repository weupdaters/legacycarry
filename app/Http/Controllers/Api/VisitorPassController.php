<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pass;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VisitorPassController extends Controller
{
    public function index()
    {
        $user_id = Auth()->user()->id;


        $passes = Pass::query()->where('created_by', $user_id)->get();

        if ($passes->count() == 0) {
            return response()->json([
                'message' => 'No visitor pass found',
            ], 404);
        }

        return $passes;

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employer_id' => 'nullable|exists:employers,id',
            'purpose_id' => 'nullable|exists:purposes,id',
            'visitor_name' => 'required|string',
            'contact_no' => 'nullable|string',
            'society' => 'nullable|string',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'notes' => 'nullable|string',
            'vehicle' => 'nullable|array',
            'vehicle.vehicle_number' => 'nullable|string',
            'vehicle.vehicle_type' => 'nullable|string',
            'photo' => 'nullable|file|image|max:2048',
        ]);

        // handle photo if uploaded
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $data['photo_path'] = $path;
        }

        $data['user_id'] = $request->user()->id ?? null;

        $pass = VisitorPass::create($data);

        // optionally create vehicle record
        if (!empty($data['vehicle']['vehicle_number'])) {
            $pass->vehicles()->create([
                'vehicle_number' => $data['vehicle']['vehicle_number'],
                'vehicle_type' => $data['vehicle']['vehicle_type'] ?? null,
            ]);
        }

        return response()->json($pass->load(['employer','purpose','vehicles']), 201);
    }

    public function show(VisitorPass $visitorPass)
    {
        return $visitorPass->load(['employer','purpose','vehicles','creator']);
    }

    public function complete(VisitorPass $visitorPass)
    {
        $visitorPass->status = 'completed';
        $visitorPass->completed_at = now();
        $visitorPass->save();
        return response()->json($visitorPass);
    }

    public function update(Request $request, VisitorPass $visitorPass)
    {
        $data = $request->validate([
            'visitor_name' => 'sometimes|required|string',
            'contact_no' => 'nullable|string',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $visitorPass->update($data);
        return response()->json($visitorPass);
    }
}
