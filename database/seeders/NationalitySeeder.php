<?php
// FILE: database/seeders/NationalitySeeder.php

namespace Database\Seeders;

use App\Models\Nationality;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['name' => 'Sierra Leone', 'code' => 'SLE', 'alpha2' => 'SL', 'demonym' => 'Sierra Leonean'],
            ['name' => 'Liberia', 'code' => 'LBR', 'alpha2' => 'LR', 'demonym' => 'Liberian'],
            ['name' => 'Guinea', 'code' => 'GIN', 'alpha2' => 'GN', 'demonym' => 'Guinean'],
            ['name' => 'Guinea-Bissau', 'code' => 'GNB', 'alpha2' => 'GW', 'demonym' => 'Bissau-Guinean'],
            ['name' => 'Gambia', 'code' => 'GMB', 'alpha2' => 'GM', 'demonym' => 'Gambian'],
            ['name' => 'Senegal', 'code' => 'SEN', 'alpha2' => 'SN', 'demonym' => 'Senegalese'],
            ['name' => 'Mali', 'code' => 'MLI', 'alpha2' => 'ML', 'demonym' => 'Malian'],
            ['name' => 'Burkina Faso', 'code' => 'BFA', 'alpha2' => 'BF', 'demonym' => 'Burkinabè'],
            ['name' => 'Niger', 'code' => 'NER', 'alpha2' => 'NE', 'demonym' => 'Nigerien'],
            ['name' => 'Nigeria', 'code' => 'NGA', 'alpha2' => 'NG', 'demonym' => 'Nigerian'],
            ['name' => 'Ghana', 'code' => 'GHA', 'alpha2' => 'GH', 'demonym' => 'Ghanaian'],
            ['name' => 'Togo', 'code' => 'TGO', 'alpha2' => 'TG', 'demonym' => 'Togolese'],
            ['name' => 'Benin', 'code' => 'BEN', 'alpha2' => 'BJ', 'demonym' => 'Beninese'],
            ['name' => 'Côte d’Ivoire', 'code' => 'CIV', 'alpha2' => 'CI', 'demonym' => 'Ivorian'],
            ['name' => 'Cape Verde', 'code' => 'CPV', 'alpha2' => 'CV', 'demonym' => 'Cape Verdean'],
            ['name' => 'Mauritania', 'code' => 'MRT', 'alpha2' => 'MR', 'demonym' => 'Mauritanian'],
            ['name' => 'Cameroon', 'code' => 'CMR', 'alpha2' => 'CM', 'demonym' => 'Cameroonian'],
            ['name' => 'South Africa', 'code' => 'ZAF', 'alpha2' => 'ZA', 'demonym' => 'South African'],
            ['name' => 'Kenya', 'code' => 'KEN', 'alpha2' => 'KE', 'demonym' => 'Kenyan'],
            ['name' => 'Uganda', 'code' => 'UGA', 'alpha2' => 'UG', 'demonym' => 'Ugandan'],
            ['name' => 'Tanzania', 'code' => 'TZA', 'alpha2' => 'TZ', 'demonym' => 'Tanzanian'],
            ['name' => 'Rwanda', 'code' => 'RWA', 'alpha2' => 'RW', 'demonym' => 'Rwandan'],
            ['name' => 'Ethiopia', 'code' => 'ETH', 'alpha2' => 'ET', 'demonym' => 'Ethiopian'],
            ['name' => 'Egypt', 'code' => 'EGY', 'alpha2' => 'EG', 'demonym' => 'Egyptian'],
            ['name' => 'Morocco', 'code' => 'MAR', 'alpha2' => 'MA', 'demonym' => 'Moroccan'],
            ['name' => 'Algeria', 'code' => 'DZA', 'alpha2' => 'DZ', 'demonym' => 'Algerian'],
            ['name' => 'Tunisia', 'code' => 'TUN', 'alpha2' => 'TN', 'demonym' => 'Tunisian'],
            ['name' => 'United Kingdom', 'code' => 'GBR', 'alpha2' => 'GB', 'demonym' => 'British'],
            ['name' => 'Ireland', 'code' => 'IRL', 'alpha2' => 'IE', 'demonym' => 'Irish'],
            ['name' => 'France', 'code' => 'FRA', 'alpha2' => 'FR', 'demonym' => 'French'],
            ['name' => 'Germany', 'code' => 'DEU', 'alpha2' => 'DE', 'demonym' => 'German'],
            ['name' => 'Netherlands', 'code' => 'NLD', 'alpha2' => 'NL', 'demonym' => 'Dutch'],
            ['name' => 'Belgium', 'code' => 'BEL', 'alpha2' => 'BE', 'demonym' => 'Belgian'],
            ['name' => 'Spain', 'code' => 'ESP', 'alpha2' => 'ES', 'demonym' => 'Spanish'],
            ['name' => 'Portugal', 'code' => 'PRT', 'alpha2' => 'PT', 'demonym' => 'Portuguese'],
            ['name' => 'Italy', 'code' => 'ITA', 'alpha2' => 'IT', 'demonym' => 'Italian'],
            ['name' => 'Switzerland', 'code' => 'CHE', 'alpha2' => 'CH', 'demonym' => 'Swiss'],
            ['name' => 'Norway', 'code' => 'NOR', 'alpha2' => 'NO', 'demonym' => 'Norwegian'],
            ['name' => 'Sweden', 'code' => 'SWE', 'alpha2' => 'SE', 'demonym' => 'Swedish'],
            ['name' => 'Denmark', 'code' => 'DNK', 'alpha2' => 'DK', 'demonym' => 'Danish'],
            ['name' => 'United States', 'code' => 'USA', 'alpha2' => 'US', 'demonym' => 'American'],
            ['name' => 'Canada', 'code' => 'CAN', 'alpha2' => 'CA', 'demonym' => 'Canadian'],
            ['name' => 'Brazil', 'code' => 'BRA', 'alpha2' => 'BR', 'demonym' => 'Brazilian'],
            ['name' => 'Argentina', 'code' => 'ARG', 'alpha2' => 'AR', 'demonym' => 'Argentine'],
            ['name' => 'India', 'code' => 'IND', 'alpha2' => 'IN', 'demonym' => 'Indian'],
            ['name' => 'Pakistan', 'code' => 'PAK', 'alpha2' => 'PK', 'demonym' => 'Pakistani'],
            ['name' => 'Bangladesh', 'code' => 'BGD', 'alpha2' => 'BD', 'demonym' => 'Bangladeshi'],
            ['name' => 'China', 'code' => 'CHN', 'alpha2' => 'CN', 'demonym' => 'Chinese'],
            ['name' => 'Japan', 'code' => 'JPN', 'alpha2' => 'JP', 'demonym' => 'Japanese'],
            ['name' => 'South Korea', 'code' => 'KOR', 'alpha2' => 'KR', 'demonym' => 'South Korean'],
            ['name' => 'United Arab Emirates', 'code' => 'ARE', 'alpha2' => 'AE', 'demonym' => 'Emirati'],
            ['name' => 'Saudi Arabia', 'code' => 'SAU', 'alpha2' => 'SA', 'demonym' => 'Saudi'],
            ['name' => 'Qatar', 'code' => 'QAT', 'alpha2' => 'QA', 'demonym' => 'Qatari'],
            ['name' => 'Turkey', 'code' => 'TUR', 'alpha2' => 'TR', 'demonym' => 'Turkish'],
        ];

        $payload = collect($rows)
            ->values()
            ->map(fn (array $row, int $index) => [
                'name' => $row['name'],
                'code' => $row['code'],
                'alpha2' => $row['alpha2'],
                'demonym' => $row['demonym'],
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        Nationality::query()->upsert(
            $payload,
            ['code'],
            ['name', 'alpha2', 'demonym', 'is_active', 'sort_order', 'updated_at']
        );
    }
}