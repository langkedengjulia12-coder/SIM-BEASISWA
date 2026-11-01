<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\ScholarshipScheme;
use Illuminate\Database\Seeder;

class ScholarshipSchemeSeeder extends Seeder
{
    public function run(): void
    {
        $partners = Partner::query()->pluck('id', 'name');

        $schemes = [
            [
                'code' => 'BSW-PRS',
                'name' => 'Beasiswa Prestasi Akademik',
                'category' => 'Prestasi',
                'quota' => 200,
                'funding_per_student' => 6000000,
                'academic_year' => '2026/2027 Genap',
                'status' => 'active',
                'partner_id' => null,
            ],
            [
                'code' => 'BSW-MIT',
                'name' => 'Beasiswa Mitra Industri',
                'category' => 'Bantuan',
                'quota' => 80,
                'funding_per_student' => 7500000,
                'academic_year' => '2026/2027 Genap',
                'status' => 'active',
                'partner_id' => $partners['PT Nusantara Energi'] ?? null,
            ],
            [
                'code' => 'BSW-YTM',
                'name' => 'Beasiswa Yatim Piatu',
                'category' => 'Bantuan',
                'quota' => 60,
                'funding_per_student' => 3000000,
                'academic_year' => '2026/2027 Genap',
                'status' => 'active',
                'partner_id' => $partners['Yayasan Cahaya Bangsa'] ?? null,
            ],
        ];

        foreach ($schemes as $scheme) {
            ScholarshipScheme::updateOrCreate(
                ['code' => $scheme['code']],
                $scheme
            );
        }
    }
}
