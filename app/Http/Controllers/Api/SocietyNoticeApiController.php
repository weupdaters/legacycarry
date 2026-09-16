<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocietyNotice;
use Illuminate\Http\Request;

class SocietyNoticeApiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $societyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;

        $notices = SocietyNotice::where('society_id', $societyId)
            ->where('is_published', true)
            ->where(function ($q) use ($user) {
                $q->whereIn('notice_type', ['all', 'society'])
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('notice_type', 'single')
                         ->where('target_user_id', $user->id);
                  });
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Notice list fetched successfully',
            'data' => $notices->getCollection()->map(function ($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'slug' => $notice->slug,
                    'description' => $notice->description,
                    'image' => $notice->image_url,
                    'notice_type' => $notice->notice_type,
                    'published_at' => optional($notice->published_at)->format('Y-m-d H:i:s'),
                ];
            }),
            'pagination' => [
                'current_page' => $notices->currentPage(),
                'last_page' => $notices->lastPage(),
                'per_page' => $notices->perPage(),
                'total' => $notices->total(),
            ],
        ]);
    }

    public function show(SocietyNotice $notice)
    {
        $user = auth()->user();
        $societyId = $user->user_society_id ?? $user->details?->society_id ?? $user->residentAssignment?->society_id ?? $user->society_id;

        abort_unless((int) $notice->society_id === (int) $societyId, 403);

        if (
            $notice->notice_type === 'single' &&
            (int) $notice->target_user_id !== (int) $user->id
        ) {
            abort(403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Notice detail fetched successfully',
            'data' => [
                'id' => $notice->id,
                'title' => $notice->title,
                'slug' => $notice->slug,
                'description' => $notice->description,
                'image' => $notice->image_url,
                'notice_type' => $notice->notice_type,
                'published_at' => optional($notice->published_at)->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
