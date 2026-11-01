<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

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
                'batch' => $disbursement->reference_number ?? 'DSB-'.$disbursement->id,
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
}
