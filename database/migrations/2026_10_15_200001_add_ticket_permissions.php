<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            ['permission_code' => 'ticket_index',         'module' => 'ticket', 'name' => 'index',         'label' => 'List Tickets'],
            ['permission_code' => 'ticket_create',        'module' => 'ticket', 'name' => 'create',        'label' => 'Create Ticket'],
            ['permission_code' => 'ticket_show',          'module' => 'ticket', 'name' => 'show',          'label' => 'View Ticket Details'],
            ['permission_code' => 'ticket_reply',         'module' => 'ticket', 'name' => 'reply',         'label' => 'Reply to Ticket'],
            ['permission_code' => 'ticket_status_update', 'module' => 'ticket', 'name' => 'status_update', 'label' => 'Update Ticket Status'],
            ['permission_code' => 'ticket_assign',        'module' => 'ticket', 'name' => 'assign',        'label' => 'Assign Ticket'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['permission_code' => $p['permission_code']], $p);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('module', 'ticket')->delete();
    }
};
