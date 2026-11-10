<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                    'id' => $application->id,
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

    public function review(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        DB::transaction(function () use ($application, $validated, $request): void {
            $application->update([
                'status' => $validated['decision'],
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name,
                'action' => "Mengubah status pendaftar {$application->registration_number} menjadi {$validated['decision']}.",
                'module' => 'Seleksi',
            ]);
        });

        return back()->with('success', 'Status pendaftar berhasil diperbarui.');
    }
}
