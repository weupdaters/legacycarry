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
            // Amenities Module
            ['permission_code' => 'amenity_show',            'module' => 'amenity', 'name' => 'show',            'label' => 'View Amenity'],
            ['permission_code' => 'amenity_index',           'module' => 'amenity', 'name' => 'index',           'label' => 'List Amenities'],
            ['permission_code' => 'amenity_create',          'module' => 'amenity', 'name' => 'create',          'label' => 'Create Amenity'],
            ['permission_code' => 'amenity_edit',            'module' => 'amenity', 'name' => 'edit',            'label' => 'Edit Amenity'],
            ['permission_code' => 'amenity_delete',          'module' => 'amenity', 'name' => 'delete',          'label' => 'Delete Amenity'],
            ['permission_code' => 'amenity_booking_index',   'module' => 'amenity', 'name' => 'booking_index',   'label' => 'View Bookings'],
            ['permission_code' => 'amenity_booking_approve', 'module' => 'amenity', 'name' => 'booking_approve', 'label' => 'Approve/Reject Bookings'],

            // Polls Module
            ['permission_code' => 'poll_show',               'module' => 'poll',    'name' => 'show',            'label' => 'View Poll'],
            ['permission_code' => 'poll_index',              'module' => 'poll',    'name' => 'index',           'label' => 'List Polls'],
            ['permission_code' => 'poll_create',             'module' => 'poll',    'name' => 'create',          'label' => 'Create Poll'],
            ['permission_code' => 'poll_edit',               'module' => 'poll',    'name' => 'edit',            'label' => 'Edit Poll'],
            ['permission_code' => 'poll_delete',             'module' => 'poll',    'name' => 'delete',          'label' => 'Delete Poll'],
            ['permission_code' => 'poll_close',              'module' => 'poll',    'name' => 'close',           'label' => 'Close Poll'],
            ['permission_code' => 'poll_vote',               'module' => 'poll',    'name' => 'vote',            'label' => 'Vote in Poll'],
            ['permission_code' => 'poll_results',            'module' => 'poll',    'name' => 'results',         'label' => 'View Poll Results'],
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
        Permission::whereIn('module', ['amenity', 'poll'])->delete();
    }
};
