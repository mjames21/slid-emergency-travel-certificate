<?php
// FILE: database/seeders/SystemSettingSeeder.php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'permit_signing_officer_name' => 'Dr. Moses Tiffa Baio, Esq',
            'permit_signing_officer_title' => 'IMMIGRATION OFFICER',
            'permit_chief_officer_name' => 'MOHAMED H. SESAY',
            'permit_chief_officer_title' => 'CHIEF IMMIGRATION OFFICER',
            'permit_attention_line' => 'ATTN: IMMIGRATION - LUNGI',
            'permit_attention_line_2' => 'ONS - STATE HOUSE',
            'permit_official_address' => '14 GLOUSCESTER STREET, FREETOWN, SIERRA LEONE',
            'permit_official_phone' => 'TEL: (+232) 22 224446 / 22 224447',
        ];

        foreach ($settings as $key => $value) {
            SystemSetting::setValue($key, $value);
        }
    }
}
