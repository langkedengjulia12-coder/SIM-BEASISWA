<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class PartnerController extends Controller
{
    public function index(): View
    {
        $partners = Partner::query()
            ->orderBy('name')
            ->get()
            ->map(static function (Partner $partner): array {
                return [
                    'nama' => $partner->name,
                    'jenis' => $partner->type,
                    'mou' => $partner->mou_number,
                    'berlaku' => Carbon::parse($partner->mou_start)->translatedFormat('d M Y').' - '.Carbon::parse($partner->mou_end)->translatedFormat('d M Y'),
                    'dana' => (float) $partner->funding_amount,
                    'status' => match ($partner->status) {
                        'active' => 'Aktif',
                        'expired' => 'Kedaluwarsa',
                        default => ucfirst($partner->status),
                    },
                ];
            });

        return view('beasiswa.partners', compact('partners'));
    }
}
