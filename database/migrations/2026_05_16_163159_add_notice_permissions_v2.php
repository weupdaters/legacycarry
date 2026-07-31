<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            ['permission_code' => 'notice_show',   'module' => 'notice', 'name' => 'show',   'label' => 'Show Notice'],
            ['permission_code' => 'notice_index',  'module' => 'notice', 'name' => 'index',  'label' => 'Notice List'],
            ['permission_code' => 'notice_create', 'module' => 'notice', 'name' => 'create', 'label' => 'Create Notice'],
            ['permission_code' => 'notice_edit',   'module' => 'notice', 'name' => 'edit',   'label' => 'Edit Notice'],
        ];

        foreach ($permissions as $p) {
            \App\Models\Permission::updateOrCreate(['permission_code' => $p['permission_code']], $p);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Permission::where('module', 'notice')->delete();
    }
};
