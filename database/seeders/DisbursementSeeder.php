<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Disbursement;
use Illuminate\Database\Seeder;

class DisbursementSeeder extends Seeder
{
    public function run(): void
    {
        $applications = Application::query()->pluck('id', 'registration_number');
        $disbursements = [
            ['registration_number' => 'REG-2026-0003', 'amount' => 6000000, 'disbursement_date' => '2026-10-05', 'bank_name' => 'BNI', 'account_number' => '0123456789', 'reference_number' => 'DSB-2026-0001', 'status' => 'success'],
            ['registration_number' => 'REG-2026-0001', 'amount' => 6000000, 'disbursement_date' => '2026-10-12', 'bank_name' => 'Bank Mandiri', 'account_number' => '1400012345678', 'reference_number' => 'DSB-2026-0002', 'status' => 'pending'],
            ['registration_number' => 'REG-2026-0004', 'amount' => 3000000, 'disbursement_date' => '2026-10-12', 'bank_name' => 'BRI', 'account_number' => '020101234567890', 'reference_number' => 'DSB-2026-0003', 'status' => 'failed'],
        ];

        foreach ($disbursements as $disbursement) {
            Disbursement::updateOrCreate(
                ['reference_number' => $disbursement['reference_number']],
                [
                    'application_id' => $applications[$disbursement['registration_number']],
                    'amount' => $disbursement['amount'],
                    'disbursement_date' => $disbursement['disbursement_date'],
                    'bank_name' => $disbursement['bank_name'],
                    'account_number' => $disbursement['account_number'],
                    'status' => $disbursement['status'],
                ]
            );
        }
    }
}
