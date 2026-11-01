<?php

namespace App\Http\Controllers;

use App\Models\ScholarshipScheme;
use Illuminate\Contracts\View\View;

class ScholarshipSchemeController extends Controller
{
    public function index(): View
    {
        $schemes = ScholarshipScheme::query()
            ->with('partner')
            ->orderBy('name')
            ->get()
            ->map(static function (ScholarshipScheme $scheme): array {
                return [
                    'kode' => $scheme->code,
                    'nama' => $scheme->name,
                    'mitra' => $scheme->partner?->name ?? 'Internal UKRI',
                    'nominal' => (float) $scheme->funding_per_student,
                    'kuota' => $scheme->quota,
                    'terisi' => 0,
                    'cakupan' => $scheme->category,
                    'status' => match ($scheme->status) {
                        'active' => 'Dibuka',
                        'inactive' => 'Ditutup',
                        default => ucfirst($scheme->status),
                    },
                ];
            });

        return view('beasiswa.schemes', compact('schemes'));
    }
}
