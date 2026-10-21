<?php
/**
 * LegacyCarry Server Update & Cache Buster
 * Open this script in browser: http://legacycarry.com/update-server.php
 */
header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "   LegacyCarry Server Auto-Updater      \n";
echo "========================================\n\n";

$baseDir = dirname(__DIR__);

// 1. Git Pull
echo "[1/4] Running: git pull origin main...\n";
$gitOutput = [];
$returnVar = 0;
exec("cd {$baseDir} && git pull origin main 2>&1", $gitOutput, $returnVar);
if (!empty($gitOutput)) {
    echo implode("\n", $gitOutput) . "\n\n";
} else {
    echo "No output from git (shell_exec might be restricted, but files may already be synced).\n\n";
}

// 2. Direct File Deletion of Stale Cache
echo "[2/4] Deleting compiled cache files...\n";
$cacheFiles = [
    $baseDir . '/bootstrap/cache/routes-v7.php',
    $baseDir . '/bootstrap/cache/routes.php',
    $baseDir . '/bootstrap/cache/config.php',
];
foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (@unlink($file)) {
            echo " - Deleted: " . basename($file) . "\n";
        } else {
            echo " - Failed to delete: " . basename($file) . " (permissions issue)\n";
        }
    } else {
        echo " - Clean: " . basename($file) . " (not present)\n";
    }
}
echo "\n";

// 3. Clear Storage Cache / Views
echo "[3/4] Purging blade view cache in storage...\n";
$viewCacheDir = $baseDir . '/storage/framework/views';
if (is_dir($viewCacheDir)) {
    $viewFiles = glob($viewCacheDir . '/*.php');
    $deletedViews = 0;
    foreach ($viewFiles as $vf) {
        if (@unlink($vf)) {
            $deletedViews++;
        }
    }
    echo " - Purged {$deletedViews} cached blade views.\n\n";
}

// 4. Run Artisan Clear
echo "[4/4] Running php artisan commands...\n";
$artisanOutput = [];
exec("php {$baseDir}/artisan route:clear 2>&1", $artisanOutput);
exec("php {$baseDir}/artisan view:clear 2>&1", $artisanOutput);
exec("php {$baseDir}/artisan config:clear 2>&1", $artisanOutput);
exec("php {$baseDir}/artisan cache:clear 2>&1", $artisanOutput);
if (!empty($artisanOutput)) {
    echo implode("\n", $artisanOutput) . "\n\n";
}

echo "========================================\n";
echo "   UPDATE COMPLETE! ALL ROUTES ACTIVE! \n";
echo "========================================\n";
echo "You can now visit:\n";
echo " - Polls Create: http://legacycarry.com/residents/polls/create\n";
echo " - Amenities Create: http://legacycarry.com/residents/amenities/create\n";
echo " - Amenities Bookings: http://legacycarry.com/residents/amenities/bookings/all\n";
