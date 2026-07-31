<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ResidentAssignment;
use App\Models\Society;
use App\Models\SocietyNotice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoticeController extends Controller
{
    public function index()
    {
        abort_unless(has_permission('notice_index'), 403);

        $user = auth()->user();
        
        // Get user's society_id
        $assignment = ResidentAssignment::where('user_id', $user->id)->first();
        $societyId = $assignment ? $assignment->society_id : null;

        if (!$societyId) {
            return view('member.notices.index', ['notices' => collect()]);
        }

        // Fetch notices for this society or specifically for this user
        $notices = SocietyNotice::where('society_id', $societyId)
            ->where(function ($query) use ($user) {
                $query->where('notice_type', 'all')
                      ->orWhere(function ($q) use ($user) {
                          $q->where('notice_type', 'single')
                            ->where('target_user_id', $user->id);
                      });
            })
            ->where('is_published', true)
            ->latest()
            ->paginate(10);

        return view('member.notices.index', compact('notices', 'societyId'));
    }

    public function show($id)
    {
        abort_unless(has_permission('notice_show'), 403);

        $user = auth()->user();
        $assignment = ResidentAssignment::where('user_id', $user->id)->first();
        $societyId = $assignment ? $assignment->society_id : null;

        $notice = SocietyNotice::where('id', $id)->firstOrFail();

        // Security check
        if ($notice->society_id != $societyId) {
            abort(403);
        }

        if ($notice->notice_type == 'single' && $notice->target_user_id != $user->id && !has_permission('notice_index')) {
            abort(403);
        }

        return view('member.notices.show', compact('notice'));
    }

    public function create()
    {
        abort_unless(has_permission('notice_create'), 403);

        $user = auth()->user();
        $assignment = ResidentAssignment::where('user_id', $user->id)->first();
        $societyId = $assignment ? $assignment->society_id : null;

        if (!$societyId) {
            return back()->with('error', 'You are not assigned to any society.');
        }

        $society = Society::findOrFail($societyId);
        
        // Only residents of the same society
        $users = User::whereHas('details', function($q) use ($societyId) {
            $q->where('society_id', $societyId);
        })->where('role', 'customer')->orderBy('name')->get();

        return view('member.notices.form', compact('society', 'users'));
    }

    public function store(Request $request)
    {
        abort_unless(has_permission('notice_create'), 403);

        $user = auth()->user();
        $assignment = ResidentAssignment::where('user_id', $user->id)->first();
        $societyId = $assignment ? $assignment->society_id : null;

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'notice_type' => 'required|in:all,single',
            'target_user_id' => 'nullable|required_if:notice_type,single|exists:users,id',
        ]);

        $data = $request->only(['title', 'description', 'notice_type', 'target_user_id']);
        $data['society_id'] = $societyId;
        $data['created_by'] = $user->id;
        $data['is_published'] = true;
        $data['published_at'] = now();
        $data['slug'] = Str::slug($request->title) . '-' . Str::random(6);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('notices', 'public');
        }

        $notice = SocietyNotice::create($data);

        // Firebase Notification
        $this->sendNoticeNotification($notice);

        return redirect()->route('member.notices.index')->with('success', 'Notice published successfully.');
    }

    public function edit($id)
    {
        abort_unless(has_permission('notice_edit'), 403);

        $user = auth()->user();
        $notice = SocietyNotice::findOrFail($id);

        // Security: Can only edit their own society's notices
        $assignment = ResidentAssignment::where('user_id', $user->id)->first();
        if ($notice->society_id != ($assignment->society_id ?? null)) {
            abort(403);
        }

        $society = Society::findOrFail($notice->society_id);
        $users = User::whereHas('details', function($q) use ($notice) {
            $q->where('society_id', $notice->society_id);
        })->where('role', 'customer')->orderBy('name')->get();

        return view('member.notices.form', compact('notice', 'society', 'users'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(has_permission('notice_edit'), 403);

        $user = auth()->user();
        $notice = SocietyNotice::findOrFail($id);
        
        $assignment = ResidentAssignment::where('user_id', $user->id)->first();
        if ($notice->society_id != ($assignment->society_id ?? null)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'notice_type' => 'required|in:all,single',
            'target_user_id' => 'nullable|required_if:notice_type,single|exists:users,id',
        ]);

        $data = $request->only(['title', 'description', 'notice_type', 'target_user_id']);

        if ($request->hasFile('image')) {
            if ($notice->image) {
                Storage::disk('public')->delete($notice->image);
            }
            $data['image'] = $request->file('image')->store('notices', 'public');
        }

        $notice->update($data);

        return redirect()->route('member.notices.index')->with('success', 'Notice updated successfully.');
    }

    public function destroy($id)
    {
        abort_unless(has_permission('notice_delete'), 403);

        $user = auth()->user();
        $notice = SocietyNotice::findOrFail($id);

        $assignment = ResidentAssignment::where('user_id', $user->id)->first();
        if ($notice->society_id != ($assignment->society_id ?? null)) {
            abort(403);
        }

        if ($notice->image) {
            Storage::disk('public')->delete($notice->image);
        }

        $notice->delete();

        return back()->with('success', 'Notice deleted successfully.');
    }

    private function sendNoticeNotification(SocietyNotice $notice)
    {
        $tokens = [];
        if ($notice->notice_type === 'all') {
            $tokens = User::whereHas('details', function ($q) use ($notice) {
                $q->where('society_id', $notice->society_id);
            })
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();
        } elseif ($notice->target_user_id) {
            $user = User::find($notice->target_user_id);
            if ($user && $user->fcm_token) {
                $tokens = [$user->fcm_token];
            }
        }

        if (!empty($tokens)) {
            send_push_notification(
                $tokens,
                "New Society Notice: " . $notice->title,
                Str::limit(strip_tags($notice->description), 100),
                ['notice_id' => (string) $notice->id, 'type' => 'society_notice']
            );
        }
    }
}
