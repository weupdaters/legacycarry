<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditController extends Controller
{
    // get logs for a pass
    public function passLogs($pass_id)
    {
        $logs = AuditLog::where('table_name', 'passes')->where('record_id', $pass_id)->orderBy('id','desc')->get();
        return response()->json(['status' => true, 'logs' => $logs]);
    }

    // generic search (q)
    public function search(Request $request)
    {
        $q = $request->query('q', null);
        $query = AuditLog::query();

        if ($q) {
            $query->where(function($s) use ($q) {
                $s->where('action', 'like', "%{$q}%")
                  ->orWhere('table_name', 'like', "%{$q}%")
                  ->orWhere('old_values', 'like', "%{$q}%")
                  ->orWhere('new_values', 'like', "%{$q}%");
            });
        }

        $results = $query->latest()->limit(500)->get();
        return response()->json(['status' => true, 'total' => $results->count(), 'results' => $results]);
    }
}
