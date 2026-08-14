<?php

namespace Database\Seeders;

use App\Models\Award;
use App\Models\ScheduleEvent;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class LandingContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAwards();
        $this->seedSchedule();
        $this->seedSponsors();
    }

    private function seedAwards(): void
    {
        $awards = [
            ['category' => 'Champion', 'prize' => 'TBD', 'position' => 1],
            ['category' => 'Runner-Up', 'prize' => 'TBD', 'position' => 2],
            ['category' => 'Third Place', 'prize' => 'TBD', 'position' => 3],
            ['category' => '4th - 10th Place', 'prize' => 'TBD', 'position' => 4],
            ['category' => 'Longest Drive', 'prize' => 'TBD', 'position' => 5],
            ['category' => 'Closest to Pin', 'prize' => 'TBD', 'position' => 6],
            ['category' => 'Best Amateur', 'prize' => 'TBD', 'position' => 7],
            ['category' => 'Leadership Award', 'prize' => 'TBD', 'position' => 8],
        ];

        foreach ($awards as $award) {
            Award::updateOrCreate(
                ['category' => $award['category']],
                $award
            );
        }
    }

    private function seedSchedule(): void
    {
        $events = [
            ['day' => 'Day 1', 'title' => 'Children', 'date' => 'Nov 8, 2026', 'time' => 'TBD', 'venue' => 'Python Intl. Golf Club, Port Harcourt', 'players' => 40, 'position' => 1],
            ['day' => 'Day 2', 'title' => 'Caddies', 'date' => 'Nov 9, 2026', 'time' => 'TBD', 'venue' => 'Python Intl. Golf Club, Port Harcourt', 'players' => 40, 'position' => 2],
            ['day' => 'Day 3', 'title' => 'Seniors (19-28)', 'date' => 'Nov 11, 2026', 'time' => 'TBD', 'venue' => 'Python Intl. Golf Club, Port Harcourt', 'players' => 28, 'position' => 3],
            ['day' => 'Day 4', 'title' => 'Pro Round 1', 'date' => 'Nov 12, 2026', 'time' => 'TBD', 'venue' => 'Python Intl. Golf Club, Port Harcourt', 'players' => 30, 'position' => 4],
            ['day' => 'Day 5', 'title' => 'Pro Round 2 + Ladies Amateur', 'date' => 'Nov 13, 2026', 'time' => 'TBD', 'venue' => 'Python Intl. Golf Club, Port Harcourt', 'players' => 30, 'position' => 5],
            ['day' => 'Day 5', 'title' => 'Cocktail', 'date' => 'Nov 13, 2026', 'time' => 'TBD', 'venue' => 'Python Intl. Golf Club, Port Harcourt', 'players' => 50, 'position' => 6],
            ['day' => 'Day 6', 'title' => 'Pro Round 3 + Amateur', 'date' => 'Nov 14, 2026', 'time' => 'TBD', 'venue' => 'Python Intl. Golf Club, Port Harcourt', 'players' => 50, 'position' => 7],
            ['day' => 'Day 7', 'title' => 'Pro Final & Amateur Awards', 'date' => 'Nov 15, 2026', 'time' => 'TBD', 'venue' => 'Python Intl. Golf Club, Port Harcourt', 'players' => 50, 'position' => 8],
        ];

        foreach ($events as $event) {
            ScheduleEvent::updateOrCreate(
                ['day' => $event['day'], 'title' => $event['title']],
                $event
            );
        }
    }

    private function seedSponsors(): void
    {
        $sponsors = [
            [
                'name' => 'Keves Global Leasing Limited',
                'type' => 'sponsor',
                'tier' => 'Title Sponsor',
                'logo' => '/keves-logo-cio-official-sponsor.png',
                'description' => "MD/CEO: Chief Sir Dr. Ikenna Okafor\n(IDE AKWAEZE)",
                'address' => "Plot 1, Keves Close, Off Uyo Street, Rumuomasi, Port Harcourt\nPhone: +234 (0) 8133502577\nEmail: info@kevesgloballeasing.com\nWeb: https://kevesgloballeasing.com",
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sophike Medical Centre',
                'type' => 'sponsor',
                'tier' => 'Associate Sponsor',
                'logo' => 'https://sophikemedicalcentre.com/wp-content/uploads/2021/02/sophike-logo.svg',
                'description' => 'Official Healthcare Partner',
                'address' => "Federal Housing Estate, Peter Odili, Trans Amadi\nMon - Sat | 8.00 - 18.00\nCall: 0802 5814 113\nEmail: info@sophikemedicalcentre.com",
                'featured' => false,
                'sort_order' => 2,
            ],
            ['name' => 'NTA Port Harcourt', 'type' => 'partner', 'tier' => 'Broadcast Partner', 'logo' => '/nta-portharcourt.png', 'sort_order' => 1],
            ['name' => 'Python Golf', 'type' => 'partner', 'tier' => 'Host Partner', 'logo' => '/python-golf.jpg', 'sort_order' => 2],
            ['name' => 'Rivers State', 'type' => 'partner', 'tier' => 'Host State', 'logo' => '/rivers-state-logo.jpg', 'sort_order' => 3],
            ['name' => 'MTN', 'type' => 'partner', 'tier' => 'Gold Partner', 'logo' => '/mtn-new-logo.svg', 'sort_order' => 4],
            ['name' => 'Nigerian Army', 'type' => 'partner', 'tier' => 'Strategic Partner', 'logo' => '/nigerian-army.png', 'sort_order' => 5],
            ['name' => 'OANDO', 'type' => 'partner', 'tier' => 'Platinum Partner', 'logo' => '/Oando_logo.png', 'sort_order' => 6],
            ['name' => 'TotalEnergies', 'type' => 'partner', 'tier' => 'Gold Partner', 'logo' => '/TotalEnergies_logo.svg', 'sort_order' => 7],
            ['name' => 'Renaissance', 'type' => 'partner', 'tier' => 'Silver Partner', 'logo' => '/renaissance-logo.png', 'sort_order' => 8],
            ['name' => 'Shell', 'type' => 'partner', 'tier' => 'Platinum Partner', 'logo' => '/Shell-logo.jpg', 'sort_order' => 9],
            ['name' => 'Nigerian Air Force', 'type' => 'partner', 'tier' => 'Strategic Partner', 'logo' => '/Nigerian_Air_Force_emblem-1.png', 'sort_order' => 10],
            ['name' => 'SuperSport', 'type' => 'partner', 'tier' => 'Media Partner', 'logo' => '/Supersport-logo.jpg', 'sort_order' => 11],
            ['name' => 'DSTV', 'type' => 'partner', 'tier' => 'Broadcast Partner', 'logo' => '/dstv-logo-vector.png', 'sort_order' => 12],
        ];

        foreach ($sponsors as $sponsor) {
            Sponsor::updateOrCreate(
                ['name' => $sponsor['name']],
                $sponsor
            );
        }
    }
}
