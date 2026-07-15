<?php

namespace Database\Seeders;

use App\Models\Tournament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $tournaments = [
            [
                'name' => 'CIO International Golf Classic - 1st Edition',
                'description' => 'The inaugural CIO International Golf Classic, bringing together top executives and professional golfers for a prestigious championship tournament.',
                'venue' => 'Ikoyi Club 1938',
                'host_country' => 'Nigeria',
                'start_date' => '2020-11-14',
                'end_date' => '2020-11-16',
                'registration_deadline' => '2020-10-31',
                'total_slots' => 120,
                'available_slots' => 0,
                'prize_pool' => 5000000,
                'registration_fee' => 25000,
                'status' => 'completed',
            ],
            [
                'name' => 'CIO International Golf Classic - 2nd Edition',
                'description' => 'Building on the success of the inaugural event, the 2nd edition expanded participation across more golf clubs nationwide.',
                'venue' => 'Lakowe Lakes Golf Estate',
                'host_country' => 'Nigeria',
                'start_date' => '2021-11-13',
                'end_date' => '2021-11-15',
                'registration_deadline' => '2021-10-30',
                'total_slots' => 150,
                'available_slots' => 0,
                'prize_pool' => 10000000,
                'registration_fee' => 30000,
                'status' => 'completed',
            ],
            [
                'name' => 'CIO International Golf Classic - 3rd Edition',
                'description' => 'The 3rd edition saw increased international participation and a larger prize pool, solidifying the tournament as a premier executive golf event.',
                'venue' => 'Ikeja Golf Club',
                'host_country' => 'Nigeria',
                'start_date' => '2022-11-12',
                'end_date' => '2022-11-14',
                'registration_deadline' => '2022-10-29',
                'total_slots' => 180,
                'available_slots' => 0,
                'prize_pool' => 15000000,
                'registration_fee' => 35000,
                'status' => 'completed',
            ],
            [
                'name' => 'CIO International Golf Classic - 4th Edition',
                'description' => 'The 4th edition continued the legacy with record-breaking registrations and a showcase of executive golf talent from across Africa.',
                'venue' => 'Ikoyi Club 1938',
                'host_country' => 'Nigeria',
                'start_date' => '2023-11-11',
                'end_date' => '2023-11-13',
                'registration_deadline' => '2023-10-28',
                'total_slots' => 200,
                'available_slots' => 0,
                'prize_pool' => 25000000,
                'registration_fee' => 40000,
                'status' => 'completed',
            ],
            [
                'name' => 'CIO International Golf Classic - 5th Edition',
                'description' => 'The milestone 5th edition attracted sponsors and participants from over 15 countries, establishing the tournament as Africa\'s premier executive golf championship.',
                'venue' => 'Dolphin Golf Club',
                'host_country' => 'Nigeria',
                'start_date' => '2024-11-09',
                'end_date' => '2024-11-11',
                'registration_deadline' => '2024-10-26',
                'total_slots' => 250,
                'available_slots' => 0,
                'prize_pool' => 50000000,
                'registration_fee' => 45000,
                'status' => 'completed',
            ],
            [
                'name' => 'CIO International Golf Classic - 6th Edition',
                'description' => 'The 6th edition delivered world-class competition with enhanced media coverage and a star-studded field of executive golfers.',
                'venue' => 'Lakowe Lakes Golf Estate',
                'host_country' => 'Nigeria',
                'start_date' => '2025-11-08',
                'end_date' => '2025-11-10',
                'registration_deadline' => '2025-10-25',
                'total_slots' => 300,
                'available_slots' => 0,
                'prize_pool' => 75000000,
                'registration_fee' => 50000,
                'status' => 'completed',
            ],
            [
                'name' => 'CIO International Golf Classic - 7th Edition',
                'description' => 'The prestigious 7th edition of the CIO International Golf Classic, where leadership, excellence and championship golf converge. Featuring 40 selected clubs from across Nigeria competing for the ultimate executive golf championship title.',
                'venue' => 'Ikoyi Club 1938',
                'host_country' => 'Nigeria',
                'start_date' => '2026-11-08',
                'end_date' => '2026-11-15',
                'registration_deadline' => '2026-10-15',
                'total_slots' => 400,
                'available_slots' => 400,
                'prize_pool' => 100000000,
                'registration_fee' => 50000,
                'status' => 'published',
            ],
        ];

        $count = 0;
        foreach ($tournaments as $data) {
            Tournament::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                $data
            );
            $count++;
        }

        $this->command->info("{$count} tournaments seeded (1st through 7th editions).");
    }
}
