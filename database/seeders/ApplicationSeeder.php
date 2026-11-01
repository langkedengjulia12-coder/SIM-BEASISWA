<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ScholarshipScheme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $students = collect([
            ['name' => 'Aulia Rahmawati', 'email' => 'aulia.rahmawati@student.ukri.ac.id'],
            ['name' => 'Bagas Pratama', 'email' => 'bagas.pratama@student.ukri.ac.id'],
            ['name' => 'Citra Lestari', 'email' => 'citra.lestari@student.ukri.ac.id'],
            ['name' => 'Dimas Nugraha', 'email' => 'dimas.nugraha@student.ukri.ac.id'],
        ])->mapWithKeys(function (array $student): array {
            $user = User::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                ]
            );

            return [$student['email'] => $user->id];
        });

        $schemes = ScholarshipScheme::query()->pluck('id', 'code');
        $applications = [
            ['registration_number' => 'REG-2026-0001', 'email' => 'aulia.rahmawati@student.ukri.ac.id', 'scheme' => 'BSW-PRS', 'study_program' => 'Teknik Informatika', 'gpa' => 3.82, 'status' => 'pending', 'submitted_at' => '2026-10-01 09:15:00'],
            ['registration_number' => 'REG-2026-0002', 'email' => 'bagas.pratama@student.ukri.ac.id', 'scheme' => 'BSW-MIT', 'study_program' => 'Manajemen', 'gpa' => 3.45, 'status' => 'pending', 'submitted_at' => '2026-10-02 10:30:00'],
            ['registration_number' => 'REG-2026-0003', 'email' => 'citra.lestari@student.ukri.ac.id', 'scheme' => 'BSW-PRS', 'study_program' => 'Akuntansi', 'gpa' => 3.91, 'status' => 'approved', 'submitted_at' => '2026-09-28 08:00:00'],
            ['registration_number' => 'REG-2026-0004', 'email' => 'dimas.nugraha@student.ukri.ac.id', 'scheme' => 'BSW-YTM', 'study_program' => 'Teknik Sipil', 'gpa' => 2.98, 'status' => 'rejected', 'submitted_at' => '2026-09-29 13:45:00'],
        ];

        foreach ($applications as $application) {
            Application::updateOrCreate(
                ['registration_number' => $application['registration_number']],
                [
                    'user_id' => $students[$application['email']],
                    'scholarship_scheme_id' => $schemes[$application['scheme']],
                    'study_program' => $application['study_program'],
                    'gpa' => $application['gpa'],
                    'status' => $application['status'],
                    'submitted_at' => $application['submitted_at'],
                ]
            );
        }
    }
}
