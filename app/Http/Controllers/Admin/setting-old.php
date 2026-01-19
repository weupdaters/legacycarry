<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $terms = Setting::where('type', 'terms')->first();
        $faqs = Setting::where('type', 'faq')->get();
        return view('admin.settings', compact('terms', 'faqs'));
    }

    public function edit($key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request, $key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        $setting->value = $request->value;
        $setting->save();

        return back()->with('success', 'Setting updated successfully.');
    }


    public function show($identifier)
    {
        // Case 1: if identifier = "faq" or "terms"
        if (in_array($identifier, ['faq', 'terms'])) {
            $data = Setting::where('type', $identifier)->get();
        }
        // Case 2: if identifier is numeric (ID)
        elseif (is_numeric($identifier)) {
            $data = Setting::where('id', $identifier)->get();
        }
        // Case 3: fallback (not found)
        else {
            return response()->json(['error' => 'Invalid identifier'], 404);
        }

        return response()->json($data);
    }


    public function updateTerms(Request $request)
    {
        $request->validate(['content' => 'required|string']);
        $term = Setting::where('type', 'terms')->first();
        $term->update(['content' => $request->content]);

        return redirect()->back()->with('success', 'Terms updated successfully!');
    }

    public function addFaq(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        Setting::create([
            'type' => 'faq',
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'FAQ added successfully!');
    }

    public function deleteFaq($id)
    {
        Setting::where('id', $id)->delete();
        return redirect()->back()->with('success', 'FAQ deleted successfully!');
    }
}
