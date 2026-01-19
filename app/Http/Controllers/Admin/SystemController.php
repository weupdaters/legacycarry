<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class SystemController extends Controller
{
    /**
     * Clear cache with specific type
     */
    public function clearCache(Request $request)
    {
        $cacheType = $request->input('cache_type', 'all');
        
        try {
            switch ($cacheType) {
                case 'all':
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('event:clear');
                    $message = 'All caches cleared successfully!';
                    break;
                    
                case 'application':
                    Artisan::call('cache:clear');
                    $message = 'Application cache cleared!';
                    break;
                    
                case 'config':
                    Artisan::call('config:clear');
                    $message = 'Config cache cleared!';
                    break;
                    
                case 'route':
                    Artisan::call('route:clear');
                    $message = 'Route cache cleared!';
                    break;
                    
                case 'view':
                    Artisan::call('view:clear');
                    $message = 'View cache cleared!';
                    break;
                    
                case 'event':
                    Artisan::call('event:clear');
                    $message = 'Event cache cleared!';
                    break;
                    
                default:
                    $message = 'Cache type not recognized.';
            }
            
            // Log the action
            \App\Models\SystemLog::create([
                'action' => 'clear_cache',
                'details' => json_encode(['type' => $cacheType]),
                'user_id' => auth()->id(),
            ]);
            
            return back()->with('system_message', $message);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error clearing cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Optimize database
     */
    public function optimizeDatabase()
    {
        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            
            $tableName = 'Tables_in_' . $databaseName;
            
            foreach ($tables as $table) {
                DB::statement('OPTIMIZE TABLE ' . $table->$tableName);
            }
            
            \App\Models\SystemLog::create([
                'action' => 'optimize_database',
                'details' => json_encode(['tables_count' => count($tables)]),
                'user_id' => auth()->id(),
            ]);
            
            return back()->with('system_message', 'Database optimized successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error optimizing database: ' . $e->getMessage());
        }
    }
    
    /**
     * Create database backup
     */
    public function backupDatabase(Request $request)
    {
        $request->validate([
            'backup_type' => 'required|in:full,full_with_files,structure,data',
            'compression' => 'required|in:gzip,zip,none',
            'download' => 'nullable|boolean',
        ]);
        
        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            
            $backupName = 'backup_' . date('Y_m_d_His') . '.sql';
            $backupPath = storage_path('app/backups/' . $backupName);
            
            // Ensure backup directory exists
            if (!File::isDirectory(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }
            
            // Build mysqldump command
            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database}";
            
            if ($request->backup_type === 'structure') {
                $command .= ' --no-data';
            } elseif ($request->backup_type === 'data') {
                $command .= ' --no-create-info';
            }
            
            $command .= " > {$backupPath}";
            
            // Execute command
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('Backup failed with error code: ' . $returnVar);
            }
            
            // Compress if requested
            $finalPath = $backupPath;
            if ($request->compression === 'gzip') {
                exec("gzip {$backupPath}");
                $finalPath = $backupPath . '.gz';
            } elseif ($request->compression === 'zip') {
                exec("zip -j {$backupPath}.zip {$backupPath}");
                $finalPath = $backupPath . '.zip';
            }
            
            \App\Models\SystemLog::create([
                'action' => 'database_backup',
                'details' => json_encode([
                    'type' => $request->backup_type,
                    'compression' => $request->compression,
                    'file' => basename($finalPath),
                ]),
                'user_id' => auth()->id(),
            ]);
            
            // Download if requested
            if ($request->boolean('download')) {
                return response()->download($finalPath)->deleteFileAfterSend(true);
            }
            
            return back()->with('system_message', 'Backup created successfully! File: ' . basename($finalPath));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
    
    /**
     * View log file
     */
    public function viewLog($file)
    {
        $logPath = storage_path('logs/' . $file);
        
        if (!File::exists($logPath)) {
            abort(404, 'Log file not found.');
        }
        
        $content = File::get($logPath);
        
        return view('admin.system.log-viewer', [
            'filename' => $file,
            'content' => $content,
        ]);
    }
    
    /**
     * Clear all logs
     */
    public function clearLogs()
    {
        try {
            $logFiles = File::files(storage_path('logs'));
            
            foreach ($logFiles as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                    File::put($file, '');
                }
            }
            
            \App\Models\SystemLog::create([
                'action' => 'clear_logs',
                'details' => json_encode(['files_cleared' => count($logFiles)]),
                'user_id' => auth()->id(),
            ]);
            
            return back()->with('system_message', 'All log files cleared successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error clearing logs: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenance(Request $request)
    {
        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                $message = 'Maintenance mode disabled!';
            } else {
                $message = $request->input('maintenance_message', 'We are performing maintenance. Please check back soon.');
                Artisan::call('down', [
                    '--message' => $message,
                    '--retry' => 60,
                ]);
                $message = 'Maintenance mode enabled!';
            }
            
            \App\Models\SystemLog::create([
                'action' => 'toggle_maintenance',
                'details' => json_encode(['status' => !app()->isDownForMaintenance()]),
                'user_id' => auth()->id(),
            ]);
            
            return back()->with('system_message', $message);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error toggling maintenance mode: ' . $e->getMessage());
        }
    }
    
    /**
     * Get system statistics for dashboard
     */
    private function getSystemStats()
    {
        $stats = [];
        
        try {
            // Database size
            $dbSize = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = ?", [DB::getDatabaseName()]);
            
            $stats['dbSize'] = $dbSize[0]->size_mb . ' MB';
            
            // Table count
            $stats['tableCount'] = count(DB::select('SHOW TABLES'));
            
            // Cache size
            $cachePath = storage_path('framework/cache');
            $stats['cacheSize'] = $this->formatBytes($this->folderSize($cachePath));
            
            // Uptime
            if (function_exists('shell_exec')) {
                $uptime = @shell_exec('uptime -p');
                $stats['uptime'] = $uptime ? trim(str_replace('up ', '', $uptime)) : 'N/A';
            }
            
        } catch (\Exception $e) {
            // Silently fail - these are just stats
        }
        
        return $stats;
    }
    
    /**
     * Calculate folder size
     */
    private function folderSize($path)
    {
        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Show system page
     */
    public function index()
    {
        $stats = $this->getSystemStats();
        $maintenanceMessage = app()->isDownForMaintenance() ? 
            File::get(storage_path('framework/down')) : '';
            
        return view('admin.settings.pro', array_merge($stats, [
            'maintenanceMessage' => $maintenanceMessage,
        ]));
    }
}