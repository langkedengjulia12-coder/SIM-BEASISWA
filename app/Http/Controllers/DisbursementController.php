<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Disbursement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DisbursementController extends Controller
{
    public function index(): View
    {
        $records = Disbursement::query()
            ->with('application.scholarshipScheme')
            ->latest('disbursement_date')
            ->get();

        $summary = [
            [
                'label' => 'Total Dana Dicairkan',
                'value' => 'Rp '.number_format($records->where('status', 'success')->sum('amount'), 0, ',', '.'),
                'tone' => 'emerald',
            ],
            [
                'label' => 'Menunggu Rekonsiliasi',
                'value' => 'Rp '.number_format($records->where('status', 'pending')->sum('amount'), 0, ',', '.'),
                'tone' => 'amber',
            ],
            [
                'label' => 'Selisih / Retur Transaksi',
                'value' => 'Rp '.number_format($records->where('status', 'failed')->sum('amount'), 0, ',', '.'),
                'tone' => 'rose',
            ],
        ];

        $disbursements = $records->map(static function (Disbursement $disbursement): array {
            return [
                'batch' => $disbursement->batch_number ?? $disbursement->reference_number ?? 'DSB-'.$disbursement->id,
                'tanggal' => Carbon::parse($disbursement->disbursement_date)->translatedFormat('d M Y'),
                'skema' => $disbursement->application->scholarshipScheme->name,
                'penerima' => 1,
                'nominal' => (float) $disbursement->amount,
                'bank' => $disbursement->bank_name,
                'status' => match ($disbursement->status) {
                    'success' => 'Terekonsiliasi',
                    'failed' => 'Selisih',
                    default => 'Menunggu',
                },
            ];
        });

        return view('beasiswa.disbursements', compact('summary', 'disbursements'));
    }

    public function createBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);
        $path = $validated['file']->getRealPath();
        if (! is_string($path)) {
            throw ValidationException::withMessages(['file' => 'File batch tidak dapat dibaca.']);
        }
        $rows = $this->readCsv($path, [
            'registration_number',
            'bank_name',
            'account_number',
        ]);

        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'CSV tidak berisi data penerima.']);
        }

        $batchNumber = 'DSB-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));

        DB::transaction(function () use ($rows, $batchNumber, $request): void {
            $seen = [];
            $validatedRows = [];
            $errors = [];

            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $validator = Validator::make($row, [
                    'registration_number' => ['required', 'string', 'max:255'],
                    'bank_name' => ['required', 'string', 'max:255'],
                    'account_number' => ['required', 'regex:/^[0-9]{6,34}$/'],
                ]);

                if ($validator->fails()) {
                    $errors["file.{$line}"] = $validator->errors()->all();

                    continue;
                }

                $registrationNumber = $row['registration_number'];
                if (isset($seen[$registrationNumber])) {
                    $errors["file.{$line}"] = ['Nomor registrasi tidak boleh duplikat dalam satu batch.'];

                    continue;
                }
                $seen[$registrationNumber] = true;

                $application = Application::query()
                    ->with('scholarshipScheme')
                    ->where('registration_number', $registrationNumber)
                    ->lockForUpdate()
                    ->first();

                if (! $application || $application->status !== 'approved') {
                    $errors["file.{$line}"] = ['Pendaftar tidak ditemukan atau belum berstatus Lolos.'];

                    continue;
                }
                if (Disbursement::query()->where('application_id', $application->id)->exists()) {
                    $errors["file.{$line}"] = ['Pendaftar sudah memiliki transaksi penyaluran.'];

                    continue;
                }

                $validatedRows[] = [
                    'application' => $application,
                    'bank_name' => $row['bank_name'],
                    'account_number' => $row['account_number'],
                ];
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            foreach ($validatedRows as $row) {
                Disbursement::create([
                    'application_id' => $row['application']->id,
                    'amount' => $row['application']->scholarshipScheme->funding_per_student,
                    'disbursement_date' => now()->toDateString(),
                    'bank_name' => $row['bank_name'],
                    'account_number' => $row['account_number'],
                    'batch_number' => $batchNumber,
                    'status' => 'pending',
                ]);
            }

            AuditLog::create([
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name,
                'action' => "Membuat batch {$batchNumber} dengan ".count($validatedRows).' penerima.',
                'module' => 'Penyaluran',
            ]);
        });

        return back()->with('success', "Batch {$batchNumber} berhasil dibuat.");
    }

    public function importMutations(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);
        $path = $validated['file']->getRealPath();
        if (! is_string($path)) {
            throw ValidationException::withMessages(['file' => 'File mutasi tidak dapat dibaca.']);
        }
        $rows = $this->readCsv($path, [
            'registration_number',
            'amount',
            'reference_number',
            'status',
        ]);

        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'CSV tidak berisi transaksi.']);
        }

        DB::transaction(function () use ($rows, $request): void {
            $seen = [];
            $seenReferences = [];
            $updates = [];
            $errors = [];

            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $validator = Validator::make($row, [
                    'registration_number' => ['required', 'string', 'max:255'],
                    'amount' => ['required', 'numeric', 'gt:0'],
                    'reference_number' => ['required', 'string', 'max:255'],
                    'status' => ['required', 'in:success,failed'],
                ]);

                if ($validator->fails()) {
                    $errors["file.{$line}"] = $validator->errors()->all();

                    continue;
                }

                $registrationNumber = $row['registration_number'];
                if (isset($seen[$registrationNumber])) {
                    $errors["file.{$line}"] = ['Nomor registrasi tidak boleh duplikat dalam satu file.'];

                    continue;
                }
                if (isset($seenReferences[$row['reference_number']])) {
                    $errors["file.{$line}"] = ['Nomor referensi bank tidak boleh duplikat dalam satu file.'];

                    continue;
                }
                $seen[$registrationNumber] = true;
                $seenReferences[$row['reference_number']] = true;

                $pending = Disbursement::query()
                    ->whereHas('application', static fn ($query) => $query->where('registration_number', $registrationNumber))
                    ->where('status', 'pending')
                    ->where('amount', $row['amount'])
                    ->lockForUpdate()
                    ->get();

                if ($pending->count() !== 1) {
                    $errors["file.{$line}"] = [
                        $pending->isEmpty()
                            ? 'Transaksi tertunda dengan nomor registrasi dan nominal tersebut tidak ditemukan.'
                            : 'Transaksi tidak dapat dicocokkan secara unik.',
                    ];

                    continue;
                }
                if (Disbursement::query()
                    ->where('reference_number', $row['reference_number'])
                    ->where('id', '!=', $pending->first()->id)
                    ->exists()) {
                    $errors["file.{$line}"] = ['Nomor referensi bank sudah digunakan oleh transaksi lain.'];

                    continue;
                }

                $updates[] = [
                    'disbursement' => $pending->first(),
                    'reference_number' => $row['reference_number'],
                    'status' => $row['status'],
                ];
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            foreach ($updates as $update) {
                $update['disbursement']->update([
                    'reference_number' => $update['reference_number'],
                    'status' => $update['status'],
                ]);
            }

            AuditLog::create([
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name,
                'action' => 'Merekonsiliasi '.count($updates).' transaksi mutasi bank.',
                'module' => 'Penyaluran',
            ]);
        });

        return back()->with('success', 'Mutasi bank berhasil direkonsiliasi.');
    }

    public function batchTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(static function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['registration_number', 'bank_name', 'account_number']);
            fclose($output);
        }, 'template-batch-penyaluran.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function mutationTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(static function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['registration_number', 'amount', 'reference_number', 'status']);
            fclose($output);
        }, 'template-mutasi-bank.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function readCsv(string $path, array $expectedHeaders): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['file' => 'File CSV tidak dapat dibaca.']);
        }

        try {
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw ValidationException::withMessages(['file' => 'File CSV kosong.']);
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $headers = array_map(static fn ($header): string => trim((string) $header), $headers);
            if ($headers !== $expectedHeaders) {
                throw ValidationException::withMessages([
                    'file' => 'Kolom CSV harus berurutan: '.implode(', ', $expectedHeaders).'.',
                ]);
            }

            $rows = [];
            while (($values = fgetcsv($handle)) !== false) {
                if ($values === [null]) {
                    continue;
                }
                if (count($values) !== count($headers)) {
                    throw ValidationException::withMessages(['file' => 'Jumlah kolom pada CSV tidak konsisten.']);
                }
                $rows[] = array_combine($headers, array_map(static fn ($value): string => trim((string) $value), $values));
                if (count($rows) > 2000) {
                    throw ValidationException::withMessages(['file' => 'CSV tidak boleh berisi lebih dari 2.000 baris.']);
                }
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }
}
