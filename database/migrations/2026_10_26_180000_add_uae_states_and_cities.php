<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure UAE country exists
        $country = Country::where('country_code', 'AE')
            ->orWhere('name', 'LIKE', '%United Arab Emirates%')
            ->orWhere('name', 'LIKE', '%UAE%')
            ->first();

        if (!$country) {
            $country = Country::create([
                'name' => 'United Arab Emirates (UAE)',
                'country_code' => 'AE',
            ]);
        } else {
            // Update name to include (UAE) for clear search & visibility
            if ($country->name === 'United Arab Emirates') {
                $country->name = 'United Arab Emirates (UAE)';
                $country->save();
            }
        }

        $countryId = $country->id;

        // 2. Emirates (States) of UAE with their Cities
        $emirates = [
            [
                'name' => 'Dubai',
                'state_code' => 'DU',
                'cities' => [
                    'Dubai',
                    'Downtown Dubai',
                    'Business Bay',
                    'Dubai Marina',
                    'Jumeirah',
                    'Palm Jumeirah',
                    'Deira',
                    'Bur Dubai',
                    'Al Barsha',
                    'Jebel Ali',
                    'Hatta',
                    'Al Quoz',
                    'Mirdif',
                    'Al Nahda',
                    'Dubai Silicon Oasis',
                    'International City',
                    'Arabian Ranches',
                    'JLT (Jumeirah Lake Towers)',
                    'Discovery Gardens',
                    'Motor City',
                    'Al Karama',
                    'Satwa',
                    'Al Qusais',
                    'Al Jaddaf',
                    'Al Sufouh',
                    'Dubai Hills Estate',
                    'Damac Hills',
                    'City Walk',
                    'Bluewaters Island',
                    'Jumeirah Village Circle (JVC)',
                ],
            ],
            [
                'name' => 'Abu Dhabi',
                'state_code' => 'AZ',
                'cities' => [
                    'Abu Dhabi',
                    'Al Ain',
                    'Al Dhafra',
                    'Madinat Zayed',
                    'Ruwais',
                    'Ghayathi',
                    'Liwa',
                    'Mussafah',
                    'Khalifa City',
                    'Yas Island',
                    'Saadiyat Island',
                    'Al Reem Island',
                    'Baniyas',
                    'Al Maryah Island',
                    'Shakhbout City',
                    'Mohammed Bin Zayed City',
                ],
            ],
            [
                'name' => 'Sharjah',
                'state_code' => 'SH',
                'cities' => [
                    'Sharjah',
                    'Khor Fakkan',
                    'Kalba',
                    'Al Dhaid',
                    'Dibba Al-Hisn',
                    'Al Madam',
                    'Al Qasimia',
                    'Al Majaz',
                    'Muwaileh',
                    'Al Nahda (Sharjah)',
                    'Al Taawun',
                    'Al Khan',
                ],
            ],
            [
                'name' => 'Ajman',
                'state_code' => 'AJ',
                'cities' => [
                    'Ajman',
                    'Manama',
                    'Masfout',
                    'Al Rashidiya',
                    'Al Jurf',
                    'Al Nuaimiya',
                    'Al Rawda',
                    'Al Bustan',
                ],
            ],
            [
                'name' => 'Ras Al Khaimah',
                'state_code' => 'RK',
                'cities' => [
                    'Ras Al Khaimah',
                    'Al Jazirah Al Hamra',
                    'Rams',
                    'Digdaga',
                    'Khatt',
                    'Al Nakheel',
                    'Mina Al Arab',
                    'Al Marjan Island',
                ],
            ],
            [
                'name' => 'Fujairah',
                'state_code' => 'FU',
                'cities' => [
                    'Fujairah',
                    'Dibba Al-Fujairah',
                    'Al Bidya',
                    'Masafi',
                    'Qidfa',
                    'Mirbah',
                    'Al Aqah',
                ],
            ],
            [
                'name' => 'Umm Al Quwain',
                'state_code' => 'UQ',
                'cities' => [
                    'Umm Al Quwain',
                    'Falaj Al Mualla',
                    'Al Salamah',
                    'Al Raudah',
                    'Al Ramlah',
                ],
            ],
        ];

        foreach ($emirates as $em) {
            $state = State::firstOrCreate(
                [
                    'country_id' => $countryId,
                    'name' => $em['name'],
                ],
                [
                    'state_code' => $em['state_code'],
                ]
            );

            // If state exists but code missing/different, update it
            if ($state->state_code !== $em['state_code']) {
                $state->state_code = $em['state_code'];
                $state->save();
            }

            foreach ($em['cities'] as $cityName) {
                City::firstOrCreate([
                    'state_id' => $state->id,
                    'name' => $cityName,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $country = Country::where('country_code', 'AE')
            ->orWhere('name', 'LIKE', '%United Arab Emirates%')
            ->first();

        if ($country) {
            $stateIds = State::where('country_id', $country->id)->pluck('id');
            City::whereIn('state_id', $stateIds)->delete();
            State::where('country_id', $country->id)->delete();
        }
    }
};
