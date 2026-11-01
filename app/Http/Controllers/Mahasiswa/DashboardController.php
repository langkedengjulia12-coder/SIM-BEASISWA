<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ScholarshipScheme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $student = [
            'name' => $request->user()->name,
            'nim' => '20241320084',
            'program' => 'S1 Sistem Informasi',
            'faculty' => 'Fakultas Ilmu Komunikasi & Informatika (FIKONSI)',
            'semester' => 'Semester 4 (T.A. 2026/2027 Genap)',
            'gpa' => '3.82',
            'baak' => 'Lolos',
        ];

        $scholarships = [
            [
                'name' => 'Beasiswa Prestasi Akademik BUMN 2026',
                'category' => 'Mitra Konsorsium BUMN',
                'quota' => 'Sisa 16 Slot Kuota',
                'deadline' => '15 Mar 2026',
                'requirement' => 'Syarat IPK Min 3.25 Terpenuhi',
                'benefit' => 'Bebas SPP + Uang Saku',
            ],
            [
                'name' => 'Beasiswa Riset & TA PT Len',
                'category' => 'Riset dan Tugas Akhir',
                'quota' => 'Program Riset',
                'deadline' => '30 Apr 2026',
                'requirement' => 'Proposal riset terverifikasi',
                'benefit' => 'Hibah Rp 15 Juta',
            ],
            [
                'name' => 'Beasiswa Talenta Pemprov Jabar',
                'category' => 'Talenta dan Kepemimpinan',
                'quota' => 'Talenta & kepemimpinan',
                'deadline' => '20 Mar 2026',
                'requirement' => 'Rekomendasi fakultas',
                'benefit' => 'Uang pembinaan & pelatihan',
            ],
        ];

        $activeSchemes = ScholarshipScheme::query()
            ->with('partner')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        if ($activeSchemes->isNotEmpty()) {
            $scholarships = $activeSchemes->map(static function (ScholarshipScheme $scheme): array {
                return [
                    'id' => $scheme->id,
                    'name' => $scheme->name,
                    'partner' => $scheme->partner?->name ?? 'Internal UKRI',
                    'quota' => $scheme->quota.' Slot Kuota',
                    'deadline' => $scheme->academic_year,
                    'requirement' => $scheme->minimum_gpa ? 'Syarat IPK Min '.$scheme->minimum_gpa : 'Persyaratan sesuai regulasi',
                    'benefit' => 'Rp '.number_format((float) $scheme->funding_per_student, 0, ',', '.').' / semester',
                ];
            })->all();
        }

        return view('mahasiswa.dashboard', compact('student', 'scholarships'));
    }

    public function downloadGuide(): BinaryFileResponse
    {
        $disk = Storage::disk('public');
        $relativePath = 'documents/panduan-sk-beasiswa.pdf';

        if (! $disk->exists($relativePath)) {
            $disk->put($relativePath, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n");
        }

        $path = $disk->path($relativePath);

        return response()->download($path, 'panduan-sk-beasiswa.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function storeConfirmation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mutation_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'confirmation' => ['accepted'],
        ]);

        $application = Application::query()
            ->where('user_id', $request->user()->id)
            ->latest('submitted_at')
            ->firstOrFail();

        $path = $request->file('mutation_file')->store('mutasi', 'public');

        if ($application->mutation_file) {
            Storage::disk('public')->delete($application->mutation_file);
        }

        $application->update([
            'mutation_file' => $path,
            'confirmation_status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Bukti mutasi berhasil dikirim untuk diverifikasi.');
    }

    public function applyScheme(ScholarshipScheme $scheme): RedirectResponse
    {
        abort_unless($scheme->status === 'active', 404, 'Skema beasiswa sudah tidak dibuka.');

        $user = request()->user();
        $alreadyApplied = Application::query()
            ->where('user_id', $user->id)
            ->where('scholarship_scheme_id', $scheme->id)
            ->exists();

        if ($alreadyApplied) {
            return back()->with('error', 'Anda sudah terdaftar pada skema beasiswa ini.');
        }

        DB::transaction(function () use ($user, $scheme): void {
            $lastNumber = Application::query()
                ->whereYear('created_at', now()->year)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('registration_number');
            $sequence = $lastNumber && preg_match('/-(\d{4})$/', $lastNumber, $matches)
                ? ((int) $matches[1]) + 1
                : 1;

            Application::create([
                'user_id' => $user->id,
                'scholarship_scheme_id' => $scheme->id,
                'registration_number' => sprintf('REG-%s-%04d', now()->year, $sequence),
                'study_program' => $user->study_program ?? 'Belum diisi',
                'gpa' => $user->gpa ?? 0,
                'status' => 'Verifikasi Berkas',
                'submitted_at' => now(),
            ]);
        });

        return back()->with('success', 'Pendaftaran beasiswa berhasil dikirim.');
    }
}
