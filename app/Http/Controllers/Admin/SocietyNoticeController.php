<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Society;
use App\Models\SocietyNotice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SocietyNoticeController extends Controller
{
    public function index()
    {
        $notices = SocietyNotice::with('user', 'targetUser', 'society')
            ->latest()
            ->paginate(15);

        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        $societies = Society::orderBy('name')->get();

        $users = User::where('role', 'customer')
            ->orderBy('name')
            ->get();

        return view('admin.notices.create', compact('societies', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'society_id' => 'required|exists:societies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_published' => 'nullable|boolean',
            'notice_type' => 'required|in:all,single',
            'target_user_id' => 'nullable|required_if:notice_type,single|exists:users,id',
        ]);

        $data = $request->only([
            'society_id',
            'title',
            'description',
            'notice_type',
            'target_user_id',
        ]);

        $data['created_by'] = auth()->id();
        $data['is_published'] = $request->boolean('is_published', true);
        $data['slug'] = Str::slug($request->title) . '-' . Str::random(6);
        $data['published_at'] = $data['is_published'] ? now() : null;

        if ($data['notice_type'] === 'all') {
            $data['target_user_id'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('notices', 'public');
        }

        SocietyNotice::create($data);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    public function edit(SocietyNotice $notice)
    {
        $this->authorizeNotice($notice);

        $societies = Society::orderBy('name')->get();

        $users = User::where('role', 'customer')
            ->orderBy('name')
            ->get();

        return view('admin.notices.edit', compact('notice', 'societies', 'users'));
    }

    public function update(Request $request, SocietyNotice $notice)
    {
        $this->authorizeNotice($notice);

        $request->validate([
            'society_id' => 'required|exists:societies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_published' => 'nullable|boolean',
            'notice_type' => 'required|in:all,single',
            'target_user_id' => 'nullable|required_if:notice_type,single|exists:users,id',
        ]);

        $data = $request->only([
            'society_id',
            'title',
            'description',
            'notice_type',
            'target_user_id',
        ]);

        $data['is_published'] = $request->boolean('is_published', true);

        if ($data['notice_type'] === 'all') {
            $data['target_user_id'] = null;
        }

        if ($request->hasFile('image')) {
            if ($notice->image) {
                Storage::disk('public')->delete($notice->image);
            }
            $data['image'] = $request->file('image')->store('notices', 'public');
        }

        if ($data['is_published'] && !$notice->published_at) {
            $data['published_at'] = now();
        }

        $notice->update($data);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    public function destroy(SocietyNotice $notice)
    {
        $this->authorizeNotice($notice);

        if ($notice->image) {
            Storage::disk('public')->delete($notice->image);
        }

        $notice->delete();

        return back()->with('success', 'Notice deleted successfully.');
    }

    private function authorizeNotice(SocietyNotice $notice): void
    {
        abort_unless($notice->society_id === auth()->user()->society_id, 403);
    }
}
