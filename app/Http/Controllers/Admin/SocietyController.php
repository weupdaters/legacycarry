<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Society;
use App\Models\City;
use App\Exports\GlobalExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Traits\HasGlobalExport;



class SocietyController extends Controller
{

    use HasGlobalExport;
    /*----------------------------------------
     | LIST PAGE
     ----------------------------------------*/
     public function index(Request $request)
     {
         $societies = Society::with(['city', 'state', 'country', 'flats'])
             ->when($request->search, function ($q) use ($request) {
                 $q->where(function ($sub) use ($request) {
                     $sub->where('name', 'like', "%{$request->search}%")
                         ->orWhere('address', 'like', "%{$request->search}%");
                 });
             })
             ->when($request->city, function ($q) use ($request) {
                 $q->whereHas('city', function ($sub) use ($request) {
                     $sub->where('name', 'like', "%{$request->city}%");
                 });
             })
             ->when($request->state, function ($q) use ($request) {
                 $q->whereHas('state', function ($sub) use ($request) {
                     $sub->where('name', 'like', "%{$request->state}%");
                 });
             })
             ->when($request->pincode, function ($q) use ($request) {
                 $q->where('pincode', 'like', "%{$request->pincode}%");
             })
             ->orderBy('id', 'DESC')
             ->paginate(10);
     
         // For AJAX table reload
        if ($request->ajax()) {
            return view('admin.societies.index', compact('societies'))->renderSections()['table'];
        }
     
         return view('admin.societies.index', compact('societies'));
     }
     

    /*----------------------------------------
     | SINGLE CREATE PAGE
     ----------------------------------------*/
    public function create()
    {
        $cities = City::with('state.country')->get();
        return view('admin.societies.create', compact('cities'));
    }

    /*----------------------------------------
     | SINGLE STORE
     ----------------------------------------*/
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'address'  => 'required|string|max:1000',
            'city_id'  => 'required|exists:cities,id',
            'pincode'  => 'nullable|string|max:20',
        ]);

        // auto fill state_id, country_id
        $city = City::with(['state.country'])->find($request->city_id);

        Society::create([
            'name'       => $request->name,
            'address'    => $request->address,
            'city_id'    => $city->id,
            'state_id'   => $city->state->id,
            'country_id' => $city->state->country->id,
            'pincode'    => $request->pincode,
        ]);

        return redirect()->route('societies.index')
            ->with('success', 'Society added successfully.');
    }

    /*----------------------------------------
     | SINGLE EDIT
     ----------------------------------------*/
    public function edit(Society $society)
    {
        $cities = City::with('state.country')->get();
        return view('admin.societies.edit', compact('society', 'cities'));
    }

    /*----------------------------------------
     | UPDATE
     ----------------------------------------*/
    public function update(Request $request, Society $society)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'address'  => 'required|string|max:1000',
            'city_id'  => 'required|exists:cities,id',
            'pincode'  => 'nullable|string|max:20',
        ]);

        $city = City::with(['state.country'])->find($request->city_id);

        $society->update([
            'name'       => $request->name,
            'address'    => $request->address,
            'city_id'    => $city->id,
            'state_id'   => $city->state->id,
            'country_id' => $city->state->country->id,
            'pincode'    => $request->pincode,
        ]);

        return redirect()->route('societies.index')
            ->with('success', 'Society updated successfully.');
    }

    /*----------------------------------------
     | DELETE
     ----------------------------------------*/
    public function destroy(Society $society)
    {
        $society->delete();

        return redirect()->route('societies.index')
            ->with('success', 'Society deleted successfully.');
    }

    /*----------------------------------------
     | MULTIPLE CREATE PAGE
     ----------------------------------------*/
    public function createMultiple()
    {
        $cities = City::with('state.country')->get();
        return view('admin.societies.multiple-create', compact('cities'));
    }

    /*----------------------------------------
     | MULTIPLE STORE
     ----------------------------------------*/
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'societies' => 'required|array|min:1',

            'societies.*.name'    => 'required|string|max:255',
            'societies.*.address' => 'required|string|max:1000',
            'societies.*.city_id' => 'required|exists:cities,id',
            'societies.*.pincode' => 'nullable|string|max:20',
        ]);

        foreach ($request->societies as $row) {

            $city = City::with(['state.country'])->find($row['city_id']);

            Society::create([
                'name'       => $row['name'],
                'address'    => $row['address'],
                'city_id'    => $city->id,
                'state_id'   => $city->state->id,
                'country_id' => $city->state->country->id,
                'pincode'    => $row['pincode'] ?? null
            ]);
        }

        return redirect()->route('societies.index')
            ->with('success', 'Multiple societies added successfully.');
    }

    public function ajaxList(Request $request)
    {
        $query = Society::with(['city','state','country']);

        // SEARCH
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('address', 'like', "%{$request->search}%")
                ->orWhere('pincode', 'like', "%{$request->search}%");
            });
        }

        // FILTER BY CITY
        if ($request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        $societies = $query->orderBy('id','DESC')->paginate(10);

        return view('admin.societies.partials.table', compact('societies'))->render();
    }

    public function export()
    {
        $data = Society::with(['city', 'state'])->get();

        return $this->exportGlobal(
            $data,
            ['ID', 'Name', 'Address', 'City', 'State', 'Pincode'],
            ['id', 'name', 'address', 'city.name', 'state.name', 'pincode'],
            'societies.xlsx'
        );
    }

}
