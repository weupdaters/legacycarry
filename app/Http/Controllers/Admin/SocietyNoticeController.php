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
        abort_unless(has_permission('notice_index'), 403);

        $user = auth()->user();
        $query = SocietyNotice::with('user', 'targetUser', 'society');

        if (!$user->isSuperAdmin()) {
            $query->where('society_id', $user->society_id);
        }

        $notices = $query->latest()->paginate(15);

        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        abort_unless(has_permission('notice_create'), 403);
        
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $societies = Society::orderBy('name')->get();
        } else {
            $societies = Society::where('id', $user->society_id)->get();
        }

        $users = User::where('role', 'customer')
            ->with('details')
            ->orderBy('name')
            ->get();

        return view('admin.notices.create', compact('societies', 'users'));
    }

    public function store(Request $request)
    {
        abort_unless(has_permission('notice_create'), 403);

        $request->validate([
            'society_id' => 'required|exists:societies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_published' => 'nullable|boolean',
            'notice_type' => 'required|in:all,single,society',
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

        if ($request->notice_type === 'society' || $request->notice_type === 'all') {
            $data['notice_type'] = 'all'; // Standardize to 'all' for database
            $data['target_user_id'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('notices', 'public');
        }

        $notice = SocietyNotice::create($data);

        // SEND PUSH NOTIFICATION
        if ($notice->is_published) {
            $this->sendNoticeNotification($notice);
        }

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    public function edit(SocietyNotice $notice)
    {
        abort_unless(has_permission('notice_edit'), 403);
        $this->authorizeNotice($notice);

        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $societies = Society::orderBy('name')->get();
        } else {
            $societies = Society::where('id', $user->society_id)->get();
        }

        $users = User::where('role', 'customer')
            ->with('details')
            ->orderBy('name')
            ->get();

        return view('admin.notices.edit', compact('notice', 'societies', 'users'));
    }

    public function update(Request $request, SocietyNotice $notice)
    {
        abort_unless(has_permission('notice_edit'), 403);
        $this->authorizeNotice($notice);

        $request->validate([
            'society_id' => 'required|exists:societies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_published' => 'nullable|boolean',
            'notice_type' => 'required|in:all,single,society',
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

        if ($request->notice_type === 'society' || $request->notice_type === 'all') {
            $data['notice_type'] = 'all';
            $data['target_user_id'] = null;
        }

        if ($request->hasFile('image')) {
            if ($notice->image) {
                Storage::disk('public')->delete($notice->image);
            }
            $data['image'] = $request->file('image')->store('notices', 'public');
        }

        $wasPublished = $notice->is_published;

        if ($data['is_published'] && !$notice->published_at) {
            $data['published_at'] = now();
        }

        $notice->update($data);

        // SEND PUSH NOTIFICATION (only if just published)
        if ($notice->is_published && !$wasPublished) {
            $this->sendNoticeNotification($notice);
        }

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    /**
     * Send push notification to residents
     */
    private function sendNoticeNotification(SocietyNotice $notice)
    {
        $tokens = [];
        if ($notice->notice_type === 'all') {
            // Get all residents of the society
            $tokens = User::whereHas('details', function ($q) use ($notice) {
                $q->where('society_id', $notice->society_id);
            })
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();
        } elseif ($notice->target_user_id) {
            // Single user
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
                [
                    'notice_id' => (string) $notice->id,
                    'type' => 'society_notice'
                ]
            );
        }
    }

    public function destroy(SocietyNotice $notice)
    {
        abort_unless(has_permission('notice_delete'), 403);
        $this->authorizeNotice($notice);

        if ($notice->image) {
            Storage::disk('public')->delete($notice->image);
        }

        $notice->delete();

        return back()->with('success', 'Notice deleted successfully.');
    }

    private function authorizeNotice(SocietyNotice $notice): void
    {
        if (auth()->user()->isSuperAdmin()) {
            return;
        }
        abort_unless($notice->society_id === auth()->user()->society_id, 403);
    }
}
