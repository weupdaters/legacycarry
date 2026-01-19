<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{


    // LIST + SEARCH
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('category', 'like', "%{$request->search}%");
            });
        }

        $services = $query->orderBy('id', 'DESC')->paginate(10);

        return view('admin.services.index', compact('services'));
    }


    // ADD PAGE
    public function create()
    {
        $categories = Service::select('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category');


        return view('admin.services.create', compact('categories'));
    }


    // SAVE NEW SERVICE
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required',
            'category_option'=> 'required',
            'category_new'   => 'nullable|string',
            'description'    => 'nullable|string',
        ]);

        // Determine category
        $category = $request->category_option === 'new'
                    ? $request->category_new
                    : $request->category_option;

        Service::create([
            'name'         => $request->name,
            'category'     => $category,
            'description'  => $request->description,
            'is_active'    => $request->has('is_active'),
        ]);

        return redirect()->route('services.index')->with('success', 'Service added successfully');
    }


    // EDIT PAGE
    public function edit(Service $service)
    {
        $categories = Service::select('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category');

        return view('admin.services.edit', compact('service', 'categories'));
    }


    // UPDATE SERVICE
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name'           => 'required',
            'category_option'=> 'required',
            'category_new'   => 'nullable|string',
            'description'    => 'nullable|string',
        ]);

        // Determine category
        $category = $request->category_option === 'new'
                    ? $request->category_new
                    : $request->category_option;

        $service->update([
            // WE DO NOT GENERATE NEW CODE HERE
            
            'name'         => $request->name,
            'category'     => $category,
            'description'  => $request->description,
            'is_active'    => $request->has('is_active'),
        ]);

        return redirect()->route('services.index')->with('success', 'Service updated successfully');
    }


    // DELETE SERVICE
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service deleted successfully');
    }
}
