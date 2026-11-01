<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Contracts\View\View;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $applications = Application::query()
            ->with(['user', 'scholarshipScheme'])
            ->latest('submitted_at')
            ->get()
            ->map(static function (Application $application): array {
                return [
                    'no' => $application->registration_number,
                    'nama' => $application->user->name,
                    'nim' => $application->user->email,
                    'prodi' => $application->study_program,
                    'ipk' => (float) $application->gpa,
                    'skema' => $application->scholarshipScheme->name,
                    'tahap' => match ($application->status) {
                        'approved' => 'Penetapan',
                        'rejected' => 'Verifikasi Berkas',
                        default => 'Verifikasi Berkas',
                    },
                    'status' => match ($application->status) {
                        'approved' => 'Lolos',
                        'rejected' => 'Ditolak',
                        default => 'Proses',
                    },
                ];
            });

        $pipeline = [
            ['tahap' => 'Pendaftaran', 'jumlah' => Application::count()],
            ['tahap' => 'Verifikasi Berkas', 'jumlah' => Application::where('status', 'pending')->count()],
            ['tahap' => 'Wawancara', 'jumlah' => 0],
            ['tahap' => 'Penetapan', 'jumlah' => Application::where('status', 'approved')->count()],
        ];

        return view('beasiswa.applications', compact('pipeline', 'applications'));
    }
}
