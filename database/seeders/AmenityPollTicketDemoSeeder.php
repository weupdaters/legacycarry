<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Society;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\AmenityBookingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmenityPollTicketDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Find existing societies or create one if none exist
        $societies = Society::take(3)->get();

        if ($societies->isEmpty()) {
            $society = Society::create([
                'name' => 'Green Valley Heights',
                'address' => 'Plot 101, Golf Course Road, Sector 54',
                'city' => 'Gurugram',
                'state' => 'Haryana',
                'pincode' => '122002',
                'status' => 'active',
            ]);
            $societies = collect([$society]);
        }

        $adminUser = User::where('role', 'super_admin')->first() ?? User::first();
        $residentUsers = User::take(6)->get();

        foreach ($societies as $society) {
            $this->command?->info("Seeding demo data for Society: {$society->name} (ID: {$society->id})");

            /*
            |--------------------------------------------------------------------------
            | 1. AMENITIES
            |--------------------------------------------------------------------------
            */
            $clubhouse = Amenity::updateOrCreate(
                ['society_id' => $society->id, 'name' => 'Grand Clubhouse & Banquet'],
                [
                    'description' => 'Air-conditioned luxury community hall for private family functions, birthday parties, and society gatherings. Capacity up to 150 guests.',
                    'booking_mode' => 'admin_approval',
                    'opening_time' => '09:00:00',
                    'closing_time' => '23:00:00',
                    'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    'slot_duration_minutes' => 180,
                    'max_booking_duration_minutes' => 360,
                    'advance_booking_days' => 30,
                    'fee' => 2500.00,
                    'status' => 'active',
                ]
            );

            $swimmingPool = Amenity::updateOrCreate(
                ['society_id' => $society->id, 'name' => 'Olympic Swimming Pool'],
                [
                    'description' => 'Half-Olympic size temperature-controlled pool with dedicated kids pool, trained lifeguard on duty, and changing rooms.',
                    'booking_mode' => 'automatic',
                    'opening_time' => '06:00:00',
                    'closing_time' => '21:00:00',
                    'available_days' => ['Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    'slot_duration_minutes' => 60,
                    'max_booking_duration_minutes' => 60,
                    'advance_booking_days' => 3,
                    'fee' => 0.00,
                    'status' => 'active',
                ]
            );

            $gym = Amenity::updateOrCreate(
                ['society_id' => $society->id, 'name' => 'Fitness Gym & Yoga Deck'],
                [
                    'description' => 'Modern fitness center with cardio treadmills, cross-trainers, free weights, and a separate open yoga deck on the terrace.',
                    'booking_mode' => 'automatic',
                    'opening_time' => '05:30:00',
                    'closing_time' => '22:00:00',
                    'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    'slot_duration_minutes' => 60,
                    'max_booking_duration_minutes' => 120,
                    'advance_booking_days' => 7,
                    'fee' => 0.00,
                    'status' => 'active',
                ]
            );

            $tennisCourt = Amenity::updateOrCreate(
                ['society_id' => $society->id, 'name' => 'Synthetic Tennis & Pickleball Court'],
                [
                    'description' => 'Floodlit all-weather synthetic turf tennis court. Rackets and balls available at security gate.',
                    'booking_mode' => 'automatic',
                    'opening_time' => '06:00:00',
                    'closing_time' => '21:00:00',
                    'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    'slot_duration_minutes' => 60,
                    'max_booking_duration_minutes' => 60,
                    'advance_booking_days' => 5,
                    'fee' => 150.00,
                    'status' => 'active',
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 2. AMENITY BOOKINGS
            |--------------------------------------------------------------------------
            */
            if ($residentUsers->isNotEmpty()) {
                $user1 = $residentUsers->first();
                $user2 = $residentUsers->skip(1)->first() ?? $user1;

                // Tomorrow confirmed slot
                AmenityBooking::updateOrCreate(
                    [
                        'society_id' => $society->id,
                        'amenity_id' => $swimmingPool->id,
                        'booking_date' => Carbon::tomorrow()->toDateString(),
                        'start_time' => '07:00:00',
                    ],
                    [
                        'user_id' => $user1->id,
                        'end_time' => '08:00:00',
                        'status' => 'confirmed',
                        'approved_by' => $adminUser?->id,
                        'approved_at' => now(),
                    ]
                );

                // Pending request for Clubhouse
                AmenityBooking::updateOrCreate(
                    [
                        'society_id' => $society->id,
                        'amenity_id' => $clubhouse->id,
                        'booking_date' => Carbon::now()->addDays(5)->toDateString(),
                        'start_time' => '17:00:00',
                    ],
                    [
                        'user_id' => $user2->id,
                        'end_time' => '20:00:00',
                        'status' => 'pending',
                        'admin_notes' => 'Resident requested for kid 5th birthday celebration.',
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 3. POLLS & VOTES
            |--------------------------------------------------------------------------
            */
            // Poll 1: Active EV Charging
            $poll1 = Poll::updateOrCreate(
                ['society_id' => $society->id, 'title' => 'Should we install EV Fast-Charging Stations in Basement Parking?'],
                [
                    'created_by' => $adminUser?->id ?? 1,
                    'description' => 'Proposal by RWA committee to install 6 shared 22kW EV car chargers in Basement 1 with per-unit metered billing.',
                    'start_date' => now()->subDays(2),
                    'end_date' => now()->addDays(10),
                    'status' => 'active',
                    'results_visibility' => 'always',
                    'target_audience' => 'all',
                ]
            );

            $p1Opt1 = PollOption::firstOrCreate(['poll_id' => $poll1->id, 'option_text' => 'Yes, install immediately (Essential need)'], ['sort_order' => 1]);
            $p1Opt2 = PollOption::firstOrCreate(['poll_id' => $poll1->id, 'option_text' => 'Yes, but only if users pay installation cost'], ['sort_order' => 2]);
            $p1Opt3 = PollOption::firstOrCreate(['poll_id' => $poll1->id, 'option_text' => 'No, not required at this moment'], ['sort_order' => 3]);

            // Poll 2: Active Festival Theme
            $poll2 = Poll::updateOrCreate(
                ['society_id' => $society->id, 'title' => 'Annual Diwali & Cultural Night Celebration Theme'],
                [
                    'created_by' => $adminUser?->id ?? 1,
                    'description' => 'Vote for your preferred entertainment and dinner theme for our upcoming grand Diwali community gathering.',
                    'start_date' => now()->subDay(),
                    'end_date' => now()->addDays(15),
                    'status' => 'active',
                    'results_visibility' => 'after_vote',
                    'target_audience' => 'all',
                ]
            );

            $p2Opt1 = PollOption::firstOrCreate(['poll_id' => $poll2->id, 'option_text' => 'Traditional Folk Music & Rajasthani Cuisine'], ['sort_order' => 1]);
            $p2Opt2 = PollOption::firstOrCreate(['poll_id' => $poll2->id, 'option_text' => 'Bollywood Live Band & Multi-Cuisine Buffet'], ['sort_order' => 2]);
            $p2Opt3 = PollOption::firstOrCreate(['poll_id' => $poll2->id, 'option_text' => 'Acoustic Musical Evening & Family Games'], ['sort_order' => 3]);

            // Poll 3: Closed Poll (Completed)
            $poll3 = Poll::updateOrCreate(
                ['society_id' => $society->id, 'title' => 'Upgrade Main Security Gate to Automatic RFID Boom Barriers?'],
                [
                    'created_by' => $adminUser?->id ?? 1,
                    'description' => 'Voting concluded on upgrading society entry gates with automatic number plate & RFID tag recognition.',
                    'start_date' => now()->subDays(20),
                    'end_date' => now()->subDays(3),
                    'status' => 'closed',
                    'results_visibility' => 'always',
                    'target_audience' => 'all',
                ]
            );

            $p3Opt1 = PollOption::firstOrCreate(['poll_id' => $poll3->id, 'option_text' => 'Yes, approve RFID gate system'], ['sort_order' => 1]);
            $p3Opt2 = PollOption::firstOrCreate(['poll_id' => $poll3->id, 'option_text' => 'No, keep security guard manual entry'], ['sort_order' => 2]);

            // Add demo votes
            foreach ($residentUsers as $index => $resUser) {
                // Vote on Poll 1
                PollVote::updateOrCreate(
                    ['poll_id' => $poll1->id, 'user_id' => $resUser->id],
                    [
                        'poll_option_id' => ($index % 2 == 0) ? $p1Opt1->id : $p1Opt2->id,
                        'society_id' => $society->id,
                        'ip_address' => '127.0.0.1',
                        'voted_at' => now()->subHours($index * 3),
                    ]
                );

                // Vote on Closed Poll 3
                PollVote::updateOrCreate(
                    ['poll_id' => $poll3->id, 'user_id' => $resUser->id],
                    [
                        'poll_option_id' => ($index % 3 == 0) ? $p3Opt2->id : $p3Opt1->id,
                        'society_id' => $society->id,
                        'ip_address' => '127.0.0.1',
                        'voted_at' => now()->subDays(5),
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 4. COMPLAINT / TICKETS & REPLIES
            |--------------------------------------------------------------------------
            */
            if ($residentUsers->isNotEmpty()) {
                $user1 = $residentUsers->first();
                $user2 = $residentUsers->skip(1)->first() ?? $user1;
                $user3 = $residentUsers->skip(2)->first() ?? $user1;

                // Ticket 1: Urgent Plumbing
                $t1 = Ticket::updateOrCreate(
                    ['ticket_number' => 'TKT-20261015-PL01'],
                    [
                        'society_id' => $society->id,
                        'user_id' => $user1->id,
                        'assigned_to' => $adminUser?->id,
                        'category' => 'Plumbing',
                        'title' => 'Severe water leakage in kitchen pipeline duct',
                        'description' => 'Water is continuously leaking inside the common pipeline shaft, causing seepage in the kitchen wall. Needs urgent inspection by society plumber.',
                        'priority' => 'urgent',
                        'status' => 'in_progress',
                        'created_at' => now()->subDay(),
                    ]
                );

                TicketReply::firstOrCreate(
                    ['ticket_id' => $t1->id, 'message' => 'Plumbing technician has been assigned and will visit between 11:00 AM - 12:00 PM today.'],
                    [
                        'user_id' => $adminUser?->id ?? $user1->id,
                        'is_internal' => false,
                        'created_at' => now()->subHours(18),
                    ]
                );

                TicketReply::firstOrCreate(
                    ['ticket_id' => $t1->id, 'message' => 'Thanks, I am at home and waiting for the technician.'],
                    [
                        'user_id' => $user1->id,
                        'is_internal' => false,
                        'created_at' => now()->subHours(16),
                    ]
                );

                // Ticket 2: Resolved Electrical
                Ticket::updateOrCreate(
                    ['ticket_number' => 'TKT-20261015-EL02'],
                    [
                        'society_id' => $society->id,
                        'user_id' => $user2->id,
                        'assigned_to' => $adminUser?->id,
                        'category' => 'Electrical',
                        'title' => 'Corridor ceiling lights flickering near flat entrance',
                        'description' => 'Two tube lights outside Flat 304 are flickering since last 2 days.',
                        'priority' => 'medium',
                        'status' => 'resolved',
                        'resolution_notes' => 'Electrician replaced both LED drivers and bulbs. Working properly now.',
                        'resolved_at' => now()->subHours(4),
                        'created_at' => now()->subDays(2),
                    ]
                );

                // Ticket 3: Open Security / Parking
                Ticket::updateOrCreate(
                    ['ticket_number' => 'TKT-20261015-PK03'],
                    [
                        'society_id' => $society->id,
                        'user_id' => $user3->id,
                        'category' => 'Parking',
                        'title' => 'Unknown visitor vehicle parked in reserved slot #45',
                        'description' => 'A white Hyundai Creta (DL-3C-XX-1234) is wrongly parked in my reserved basement slot since morning.',
                        'priority' => 'high',
                        'status' => 'open',
                        'created_at' => now()->subHours(2),
                    ]
                );

                // Ticket 4: Cleanliness
                Ticket::updateOrCreate(
                    ['ticket_number' => 'TKT-20261015-CL04'],
                    [
                        'society_id' => $society->id,
                        'user_id' => $user1->id,
                        'category' => 'Cleanliness',
                        'title' => 'Housekeeping garbage collection missed on 5th floor',
                        'description' => 'Door-to-door morning garbage collection was missed today for entire 5th floor corridor.',
                        'priority' => 'low',
                        'status' => 'open',
                        'created_at' => now()->subHours(1),
                    ]
                );
            }
        }
    }
}
