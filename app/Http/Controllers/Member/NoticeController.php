<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\ResidentAssignment;
use App\Models\Society;
use App\Models\SocietyNotice;
use App\Models\User;
use App\Models\UserNotification;
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
        $societyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;

        if (!$societyId) {
            $assignment = ResidentAssignment::where('user_id', $user->id)->first();
            $societyId = $assignment ? $assignment->society_id : null;
        }

        if (!$societyId) {
            return view('member.notices.index', ['notices' => collect()]);
        }

        // Fetch notices for this society or specifically for this user
        $notices = SocietyNotice::where('society_id', $societyId)
            ->where(function ($query) use ($user) {
                $query->whereIn('notice_type', ['all', 'society'])
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
        $societyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;
        if (!$societyId) {
            $assignment = ResidentAssignment::where('user_id', $user->id)->first();
            $societyId = $assignment ? $assignment->society_id : null;
        }

        $notice = SocietyNotice::where('id', $id)->firstOrFail();

        // Security check
        if ((int) $notice->society_id !== (int) $societyId) {
            abort(403);
        }

        if ($notice->notice_type == 'single' && (int) $notice->target_user_id !== (int) $user->id && !has_permission('notice_index')) {
            abort(403);
        }

        return view('member.notices.show', compact('notice'));
    }

    public function create()
    {
        abort_unless(has_permission('notice_create'), 403);

        $user = auth()->user();
        $societyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;
        if (!$societyId) {
            $assignment = ResidentAssignment::where('user_id', $user->id)->first();
            $societyId = $assignment ? $assignment->society_id : null;
        }

        if (!$societyId) {
            return back()->with('error', 'You are not assigned to any society.');
        }

        $society = Society::findOrFail($societyId);
        
        // Only residents of the same society
        $users = User::where(function($q) use ($societyId) {
            $q->where('user_society_id', $societyId)
              ->orWhereHas('details', function ($dq) use ($societyId) {
                  $dq->where('society_id', $societyId);
              })
              ->orWhereHas('residentAssignment', function ($rq) use ($societyId) {
                  $rq->where('society_id', $societyId);
              });
        })->where('role', 'customer')->orderBy('name')->get();

        return view('member.notices.form', compact('society', 'users'));
    }

    public function store(Request $request)
    {
        abort_unless(has_permission('notice_create'), 403);

        $user = auth()->user();
        $societyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;
        if (!$societyId) {
            $assignment = ResidentAssignment::where('user_id', $user->id)->first();
            $societyId = $assignment ? $assignment->society_id : null;
        }

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

        // Firebase Notification & DB Notification
        $this->sendNoticeNotification($notice);

        return redirect()->route('member.notices.index')->with('success', 'Notice published successfully.');
    }

    public function edit($id)
    {
        abort_unless(has_permission('notice_edit'), 403);

        $user = auth()->user();
        $notice = SocietyNotice::findOrFail($id);

        $userSocietyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;

        // Security: Can only edit their own society's notices
        if ((int) $notice->society_id !== (int) $userSocietyId) {
            abort(403);
        }

        $society = Society::findOrFail($notice->society_id);
        $users = User::where(function($q) use ($notice) {
            $q->where('user_society_id', $notice->society_id)
              ->orWhereHas('details', function ($dq) use ($notice) {
                  $dq->where('society_id', $notice->society_id);
              })
              ->orWhereHas('residentAssignment', function ($rq) use ($notice) {
                  $rq->where('society_id', $notice->society_id);
              });
        })->where('role', 'customer')->orderBy('name')->get();

        return view('member.notices.form', compact('notice', 'society', 'users'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(has_permission('notice_edit'), 403);

        $user = auth()->user();
        $notice = SocietyNotice::findOrFail($id);
        
        $userSocietyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;

        if ((int) $notice->society_id !== (int) $userSocietyId) {
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

        $userSocietyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;

        if ((int) $notice->society_id !== (int) $userSocietyId) {
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
        $targetUsers = collect();

        if ($notice->notice_type === 'single' && $notice->target_user_id) {
            $singleUser = User::find($notice->target_user_id);
            if ($singleUser) {
                $targetUsers = collect([$singleUser]);
            }
        } else {
            $targetUsers = User::where(function ($q) use ($notice) {
                $q->where('user_society_id', $notice->society_id)
                  ->orWhereHas('details', function ($dq) use ($notice) {
                      $dq->where('society_id', $notice->society_id);
                  })
                  ->orWhereHas('residentAssignment', function ($rq) use ($notice) {
                      $rq->where('society_id', $notice->society_id);
                  });
            })->get();
        }

        if ($targetUsers->isEmpty()) {
            return;
        }

        $imageUrl = $notice->image ? asset('storage/' . $notice->image) : null;
        $title = "New Society Notice: " . $notice->title;
        $description = Str::limit(strip_tags($notice->description), 200);

        // 1. SAVE TO DATABASE (AppNotification & UserNotification)
        foreach ($targetUsers as $targetUser) {
            try {
                AppNotification::create([
                    'title' => $title,
                    'description' => $description,
                    'image' => $imageUrl,
                    'sent_to' => $targetUser->id,
                    'meta' => [
                        'sent_by' => $notice->created_by ?? auth()->id(),
                        'notice_id' => (string) $notice->id,
                        'type' => 'society_notice',
                        'image_url' => $imageUrl,
                        'data' => [
                            'notice_id' => (string) $notice->id,
                            'type' => 'society_notice',
                        ],
                    ],
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create AppNotification: ' . $e->getMessage());
            }

            try {
                UserNotification::create([
                    'user_id' => $targetUser->id,
                    'title' => $title,
                    'message' => $description,
                    'image' => $imageUrl,
                    'type' => 'society_notice',
                    'pass_id' => null,
                ]);
            } catch (\Throwable $e) {
                // Ignore optional UserNotification table error
            }
        }

        // 2. SEND PUSH NOTIFICATION VIA FCM
        $tokens = $targetUsers->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->map(fn($t) => trim($t))
            ->filter(fn($t) => !empty($t))
            ->unique()
            ->values()
            ->toArray();

        if (!empty($tokens)) {
            send_push_notification(
                $tokens,
                $title,
                Str::limit(strip_tags($notice->description), 100),
                [
                    'notice_id' => (string) $notice->id,
                    'type' => 'society_notice',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                $imageUrl
            );
        }
    }
}
