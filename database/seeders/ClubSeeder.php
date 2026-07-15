<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $systemUser = User::updateOrCreate(
            ['email' => 'system@ciogolf.com'],
            [
                'name' => 'CIO Golf System',
                'password' => Hash::make(uniqid('sys-', true)),
                'role' => 'admin',
                'user_type' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $clubs = [
            ['state' => 'LAGOS', 'club_name' => 'IKOYI CLUB 1938', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'LAGOS', 'club_name' => 'LAKOWE GOLF LAKES', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'LAGOS', 'club_name' => 'IKEJA GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'LAGOS', 'club_name' => 'DOLPHIN GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'OGUN', 'club_name' => 'ABEOKUTA GOLF CLUB, ABEOKUTA', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'OGUN', 'club_name' => 'SAGAMU GOLF CLUB, SAGAMU', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'OYO', 'club_name' => 'IBADAN GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'OYO', 'club_name' => 'TYGER GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'OSUN', 'club_name' => 'OBAFEMI AWOLOWO UNIVERSITY GOLF COURSE, IFE', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'ONDO', 'club_name' => 'SMOKIN HILLS GOLF CLUB, ILARA MOKIN', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'EKITI', 'club_name' => 'EKITI GOLF CLUB, EKITI', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'KWARA', 'club_name' => 'ILORIN GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'NIGER', 'club_name' => 'MINNA CANTONMENT GOLF CLUB, MINNA', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'NIGER', 'club_name' => 'SHIRORO GOLF CLUB, SHIRORO', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'ABUJA', 'club_name' => 'IBB INTL GOLF & COUNTRY CLUB, ABUJA', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'ABUJA', 'club_name' => "TYB GOLF CLUB, MUSA YAR'ADUA BARRACKS, ABUJA", 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'BENUE', 'club_name' => 'OTUKPO GOLF & COUNTRY CLUB, OTUKPO', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'BENUE', 'club_name' => 'AIR FORCE GOLF CLUB, MAKURDI', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'EDO', 'club_name' => 'BENIN CLUB, BENIN', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'EDO', 'club_name' => 'UBTH GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'DELTA', 'club_name' => 'IBORI GOLF & COUNTRY CLUB, ASABA', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'DELTA', 'club_name' => 'SHELL OGUNU GOLF CLUB, WARRI', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'KANO', 'club_name' => 'KANO GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'KADUNA', 'club_name' => 'KADUNA GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'KADUNA', 'club_name' => 'NDA GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'PLATEAU', 'club_name' => 'LAMINGO GOLF CLUB, JOS', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'PLATEAU', 'club_name' => 'RAYFIELD GOLF CLUB, RAYFIELD, JOS', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'ABIA', 'club_name' => 'ABA SPORTS CLUB, ABA', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'ENUGU', 'club_name' => 'ENUGU SPORTS CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'IMO', 'club_name' => 'ARSENAL GOLF CLUB, OBINZE, OWERRI', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'RIVERS', 'club_name' => 'PORT HARCOURT CLUB, GRA, PORT HARCOURT', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'RIVERS', 'club_name' => 'PYTHON GOLF CLUB', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'RIVERS', 'club_name' => 'SHELL GOLF CLUB, RUMUKOROSHIE, PH', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'RIVERS', 'club_name' => 'SBA GOLF CLUB, AIR FORCE BASE, PH', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'RIVERS', 'club_name' => 'NLNG GOLF CLUB, BONNY ISLAND', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'RIVERS', 'club_name' => 'SEA LORDS GOLF CLUB, PH', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'AKWA IBOM', 'club_name' => 'IBOM GOLF CLUB, IBOM', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'C/RIVERS', 'club_name' => 'CALABAR GOLF CLUB, CALABAR', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'EBONYI', 'club_name' => 'ABAKALIKI GOLF CLUB, ABAKALIKI', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
            ['state' => 'BAYELSA', 'club_name' => 'HSD GOLF & COUNTRY CLUB, YENAGOA', 'm1_18' => 4, 'm20_28' => 4, 'm_snr' => 1, 'l1_20' => 0, 'l21_28' => 0, 'l_snr' => 1],
        ];

        $count = 0;
        foreach ($clubs as $data) {
            Club::updateOrCreate(
                ['club_name' => $data['club_name']],
                [
                    'user_id' => $systemUser->id,
                    'state' => $data['state'],
                    'country' => 'Nigeria',
                    'contact_person' => null,
                    'contact_email' => null,
                    'contact_phone' => null,
                    'status' => 'active',
                    'm1_18' => $data['m1_18'],
                    'm20_28' => $data['m20_28'],
                    'm_snr' => $data['m_snr'],
                    'l1_20' => $data['l1_20'],
                    'l21_28' => $data['l21_28'],
                    'l_snr' => $data['l_snr'],
                    'edition' => '7th',
                ]
            );
            $count++;
        }

        $this->command->info("{$count} clubs seeded from CIO 7th Edition Selected Clubsides.");
    }
}
