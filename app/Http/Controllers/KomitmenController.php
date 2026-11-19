<?php

namespace App\Http\Controllers;

use App\Models\KomitmenDana;
use App\Models\Kontrak;
use App\Models\LogAktivitas;
use App\Models\SkemaBeasiswa;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** P-04 Pantau Komitmen Dana (UC-04) - Staf/Admin mengelola, Mitra hanya memantau. */
class KomitmenController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q = KomitmenDana::with('kontrak.mitra', 'skema')->orderByDesc('id_komitmen');

        if ($user->isMitra()) {
            $idMitra = optional($user->mitra)->id_mitra;
            $q->whereHas('kontrak', fn ($k) => $k->where('id_mitra', $idMitra ?? '-'));
        }

        $komitmen = $q->get();

        return view('komitmen.index', [
            'komitmen' => $komitmen,
            'kontrak' => $user->isStaf() ? Kontrak::with('mitra')->berlaku()->orderBy('id_kontrak')->get() : collect(),
            'skema' => $user->isStaf() ? SkemaBeasiswa::whereIn('status', ['draf', 'dibuka', 'ditutup'])->orderBy('id_skema')->get() : collect(),
            'total' => [
                'komitmen' => $komitmen->sum('nominal_komitmen'),
                'diterima' => $komitmen->sum('nominal_diterima'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_kontrak' => ['required', Rule::exists('t_kontrak', 'id_kontrak')->whereIn('status', ['aktif', 'diperpanjang'])],
            'id_skema' => ['nullable', 'exists:t_skema_beasiswa,id_skema'],
            'tgl_komitmen' => ['required', 'date'],
            'nominal_komitmen' => ['required', 'numeric', 'gt:0'],
            'keterangan' => ['nullable', 'string', 'max:200'],
        ], ['id_kontrak.exists' => 'Pilih kontrak yang berstatus aktif / diperpanjang.']);

        $k = DB::transaction(fn () => KomitmenDana::create($data + [
            'id_komitmen' => Kode::berikutnya('t_komitmen_dana', 'id_komitmen', 'KD', 4),
            'nominal_diterima' => 0,
        ]));

        LogAktivitas::catat('tambah', 'komitmen', "Komitmen {$k->id_komitmen} senilai ".Kode::rupiah($k->nominal_komitmen));

        return back()->with('success', 'Komitmen dana berhasil disimpan.');
    }

    /** ALGO-04 Catat Penerimaan. */
    public function catatPenerimaan(Request $request, KomitmenDana $komitmen)
    {
        $data = $request->validate([
            'jumlah' => ['required', 'numeric', 'gt:0'],
            'tgl_terima' => ['nullable', 'date'],
        ]);

        // 2. Hitung nominal_diterima baru = lama + input
        $baru = round($komitmen->nominal_diterima + (float) $data['jumlah'], 2);

        // 3. IF hasil > nominal_komitmen
        if ($baru > round($komitmen->nominal_komitmen, 2)) {
            return back()->with('error', 'Nominal diterima melebihi nominal komitmen.');
        }

        // 4. ELSE perbarui dan catat log
        $komitmen->update([
            'nominal_diterima' => $baru,
            'tgl_terima_terakhir' => $data['tgl_terima'] ?? now()->toDateString(),
        ]);
        LogAktivitas::catat('ubah', 'komitmen', "Penerimaan {$komitmen->id_komitmen} +".Kode::rupiah($data['jumlah']));

        return back()->with('success', 'Penerimaan dana berhasil dicatat.');
    }
}
