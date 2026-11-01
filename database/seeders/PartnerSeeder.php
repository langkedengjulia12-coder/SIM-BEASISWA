<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            [
                'name' => 'PT Nusantara Energi',
                'type' => 'Perusahaan',
                'mou_number' => 'MoU/UKRI/012/2026',
                'mou_start' => '2026-01-01',
                'mou_end' => '2028-12-31',
                'funding_amount' => 1500000000,
                'status' => 'active',
            ],
            [
                'name' => 'Yayasan Cahaya Bangsa',
                'type' => 'Yayasan',
                'mou_number' => 'MoU/UKRI/033/2026',
                'mou_start' => '2026-07-01',
                'mou_end' => '2027-06-30',
                'funding_amount' => 600000000,
                'status' => 'active',
            ],
            [
                'name' => 'Pemerintah Kota Bandung',
                'type' => 'Pemerintah',
                'mou_number' => 'MoU/UKRI/045/2026',
                'mou_start' => '2026-02-02',
                'mou_end' => '2027-02-01',
                'funding_amount' => 1800000000,
                'status' => 'active',
            ],
        ];

        foreach ($partners as $partner) {
            Partner::updateOrCreate(
                ['mou_number' => $partner['mou_number']],
                $partner
            );
        }
    }
}
