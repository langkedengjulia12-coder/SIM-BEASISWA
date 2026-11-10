<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Disbursement;
use App\Models\Partner;
use App\Support\ReportFileBuilder;
use Illuminate\Http\Response;

class ReportExportController extends Controller
{
    public function export(string $report, string $format, ReportFileBuilder $builder): Response
    {
        [$title, $headers, $rows] = match ($report) {
            'realization' => $this->realization(),
            'partners' => $this->partners(),
            'recipients' => $this->recipients(),
            'reconciliation' => $this->reconciliation(),
            'audit' => $this->auditTrail(),
        };

        $extension = $format === 'xlsx' ? 'xlsx' : 'pdf';
        $content = $extension === 'xlsx'
            ? $builder->xlsx($title, $headers, $rows)
            : $builder->pdf($title, $headers, $rows);

        $user = auth()->user();
        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => "Mengunduh laporan {$report} dalam format ".strtoupper($format).'.',
            'module' => 'Pelaporan',
        ]);

        return response($content, 200, [
            'Content-Type' => $extension === 'xlsx'
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$report.'-'.now()->format('Ymd').'.'.$extension.'"',
        ]);
    }

    private function realization(): array
    {
        $records = Disbursement::query()->with('application.scholarshipScheme')->latest('disbursement_date')->get();
        $rows = $records->map(static fn (Disbursement $disbursement): array => [
            $disbursement->batch_number ?? '',
            $disbursement->application->registration_number,
            $disbursement->application->scholarshipScheme->name,
            $disbursement->disbursement_date->format('Y-m-d'),
            number_format((float) $disbursement->amount, 2, '.', ''),
            $disbursement->status,
        ])->all();

        return ['Laporan Realisasi Dana per Skema', ['Batch', 'No. Registrasi', 'Skema', 'Tanggal', 'Nominal', 'Status'], $rows];
    }

    private function partners(): array
    {
        $records = Partner::query()->with('scholarshipSchemes')->orderBy('name')->get();
        $rows = $records->map(static fn (Partner $partner): array => [
            $partner->name,
            $partner->type,
            $partner->mou_number,
            $partner->scholarshipSchemes->count(),
            number_format((float) $partner->funding_amount, 2, '.', ''),
            $partner->mou_end?->format('Y-m-d') ?? '',
            $partner->status,
        ])->all();

        return ['Laporan Pertanggungjawaban Mitra', ['Mitra', 'Jenis', 'Nomor MoU', 'Jumlah Skema', 'Dana', 'Akhir MoU', 'Status'], $rows];
    }

    private function recipients(): array
    {
        $records = Application::query()
            ->with(['user', 'scholarshipScheme'])
            ->where('status', 'approved')
            ->latest('submitted_at')
            ->get();
        $rows = $records->map(static fn (Application $application): array => [
            $application->registration_number,
            $application->user->name,
            $application->study_program,
            number_format((float) $application->gpa, 2, '.', ''),
            $application->scholarshipScheme->name,
            number_format((float) $application->scholarshipScheme->funding_per_student, 2, '.', ''),
            $application->status,
        ])->all();

        return ['Rekapitulasi Penerima Beasiswa', ['No. Registrasi', 'Nama', 'Program Studi', 'IPK', 'Skema', 'Nominal', 'Status'], $rows];
    }

    private function reconciliation(): array
    {
        $records = Disbursement::query()->with('application')->latest('disbursement_date')->get();
        $rows = $records->map(static fn (Disbursement $disbursement): array => [
            $disbursement->batch_number ?? '',
            $disbursement->application->registration_number,
            $disbursement->disbursement_date->format('Y-m-d'),
            number_format((float) $disbursement->amount, 2, '.', ''),
            $disbursement->reference_number ?? '',
            $disbursement->status,
        ])->all();

        return ['Laporan Rekonsiliasi Bank', ['Batch', 'No. Registrasi', 'Tanggal', 'Nominal', 'Referensi Bank', 'Status'], $rows];
    }

    private function auditTrail(): array
    {
        $records = AuditLog::query()->latest()->get();
        $rows = $records->map(static fn (AuditLog $log): array => [
            $log->created_at->format('Y-m-d H:i:s'),
            $log->user_name,
            $log->action,
            $log->module,
        ])->all();

        return ['Laporan Audit Trail', ['Waktu', 'Pengguna', 'Aktivitas', 'Modul'], $rows];
    }
}
