<?php

namespace App\Http\Middleware;

use App\Models\ApiActivityLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        try {
            // 1. Identify active user
            $user = auth('sanctum')->user() ?? auth()->user();

            if (!$user) {
                $possibleUserId = $request->route('user_id') 
                    ?? $request->route('id') 
                    ?? $request->input('user_id');

                if ($possibleUserId && is_numeric($possibleUserId)) {
                    $user = Cache::remember('user_lookup_' . $possibleUserId, 300, function () use ($possibleUserId) {
                        return User::find($possibleUserId);
                    });
                }
            }

            // 2. If user identified, mark online in Cache
            if ($user) {
                Cache::put('user_online_' . $user->id, true, now()->addMinutes(4));

                // Maintain a list of active online user IDs in cache
                $onlineIds = Cache::get('active_online_user_ids', []);
                if (!in_array($user->id, $onlineIds)) {
                    $onlineIds[] = $user->id;
                    Cache::put('active_online_user_ids', $onlineIds, now()->addMinutes(10));
                }

                // Throttle database write to once every 60 seconds to keep performance blazing fast
                $throttleKey = 'user_seen_throttled_' . $user->id;
                if (!Cache::has($throttleKey)) {
                    Cache::put($throttleKey, true, now()->addSeconds(60));
                    DB::table('users')->where('id', $user->id)->update([
                        'last_seen_at' => now(),
                        'last_active_endpoint' => $request->path(),
                    ]);
                }
            }

            // 3. Log API hit (only for /api/ routes or relevant web actions)
            if ($request->is('api/*') || $request->is('residents/*') || $request->is('admin/*')) {
                // Ignore very noisy or polling endpoints
                $ignoredPaths = ['slots-ajax', 'broadcasting/auth', 'live-feed-ajax'];
                $isIgnored = false;
                foreach ($ignoredPaths as $ignored) {
                    if (str_contains($request->path(), $ignored)) {
                        $isIgnored = true;
                        break;
                    }
                }

                if (!$isIgnored) {
                    $ua = strtolower($request->userAgent() ?? '');
                    $deviceType = 'Web Browser';
                    if (str_contains($ua, 'dart') || str_contains($ua, 'flutter') || str_contains($ua, 'okhttp') || str_contains($ua, 'cfnetwork')) {
                        $deviceType = 'Mobile App';
                    } elseif ($request->header('X-Device-Type')) {
                        $deviceType = $request->header('X-Device-Type');
                    } elseif (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
                        $deviceType = 'Mobile Web';
                    }

                    ApiActivityLog::create([
                        'user_id' => $user?->id,
                        'user_name' => $user?->name ?? 'Guest / App Visitor',
                        'endpoint' => substr($request->path(), 0, 255),
                        'method' => substr($request->method(), 0, 10),
                        'ip_address' => $request->ip(),
                        'device_type' => $deviceType,
                        'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200,
                        'response_time_ms' => $durationMs,
                        'created_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore logging failures so the user request is never broken
        }

        return $response;
    }
}
