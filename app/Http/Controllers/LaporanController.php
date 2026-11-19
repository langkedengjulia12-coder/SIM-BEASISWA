<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\LogAktivitas;
use App\Models\Mitra;
use App\Services\DokumenService;
use App\Services\ExportService;
use App\Services\LaporanService;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** P-13 Laporan Realisasi Dana dan Laporan Mitra (UC-13, ALGO-13). */
class LaporanController extends Controller
{
    private function mitraId(Request $request): ?string
    {
        $u = $request->user();

        return $u->isMitra() ? optional($u->mitra)->id_mitra : null;
    }

    private function arsip(Request $request)
    {
        $q = Laporan::with('mitra', 'pembuat')->orderByDesc('id_laporan')->limit(10);
        if ($request->user()->isMitra()) {
            $q->where('id_mitra', $this->mitraId($request) ?? '-');
        }

        return $q->get();
    }

    private function viewName(Request $request): string
    {
        return $request->user()->isMitra() ? 'laporan.mitra' : 'laporan.index';
    }

    public function index(Request $request)
    {
        return view($this->viewName($request), [
            'hasil' => null,
            'arsip' => $this->arsip($request),
            'mitraList' => Mitra::where('status_verifikasi', 'terverifikasi')->orderBy('nama_mitra')->get(),
            'jenisList' => ['realisasi' => LaporanService::JENIS['realisasi'], 'mitra' => LaporanService::JENIS['mitra']],
        ]);
    }

    public function tampil(Request $request, LaporanService $svc, ExportService $exp, DokumenService $dok)
    {
        $mitraRole = $request->user()->isMitra();

        $data = $request->validate([
            'jenis' => [$mitraRole ? 'nullable' : 'required', 'in:realisasi,mitra'],
            'id_mitra' => ['nullable', 'exists:t_mitra,id_mitra'],
            'periode_awal' => ['required', 'date'],
            'periode_akhir' => ['required', 'date', 'after_or_equal:periode_awal'],
        ], ['periode_akhir.after_or_equal' => 'Periode akhir harus setelah periode awal.']);

        // 2. Pengguna role mitra dibatasi pada skema yang didanai mitra tersebut
        $idMitra = $this->mitraId($request);
        if ($mitraRole && ! $idMitra) {
            return back()->with('error', 'Akun Anda belum terhubung dengan data mitra.');
        }
        if (! $mitraRole && ($data['jenis'] ?? '') === 'mitra') {
            if (blank($data['id_mitra'] ?? null)) {
                return back()->withInput()->with('error', 'Pilih mitra untuk laporan mitra.');
            }
            $idMitra = $data['id_mitra'];
        }

        $awal = Carbon::parse($data['periode_awal']);
        $akhir = Carbon::parse($data['periode_akhir']);

        // 3. Agregasi nominal tersalurkan per skema/mitra (QRY_REALISASI)
        $dataset = $svc->realisasi($awal, $akhir, $idMitra);

        // 4. Kosong -> pesan; selain itu tampilkan dan simpan arsip laporan
        if ($dataset['kosong']) {
            return back()->withInput()->with('error', 'Tidak ada data pada periode terpilih.');
        }

        $file = $exp->buat('CSV', $dataset, $awal, $akhir);
        $simpan = $dok->simpanKonten($file['isi'], 'laporan_'.($idMitra ? 'mitra' : 'realisasi').'_'.now()->format('Ymd_His').'.csv', 'laporan');

        DB::transaction(fn () => Laporan::create([
            'id_laporan' => Kode::berikutnya('t_laporan', 'id_laporan', 'LP', 5),
            'jenis_laporan' => $idMitra ? 'mitra' : 'realisasi',
            'id_mitra' => $idMitra,
            'periode_awal' => $awal->toDateString(),
            'periode_akhir' => $akhir->toDateString(),
            'format_file' => 'CSV',
            'path_file' => $simpan['path'].'|'.$simpan['nama'],
            'id_pembuat' => $request->user()->id_pengguna,
            'tgl_generate' => now(),
        ]));
        LogAktivitas::catat('tambah', 'laporan', 'Laporan '.($idMitra ? 'mitra '.$idMitra : 'realisasi')." {$data['periode_awal']} s.d. {$data['periode_akhir']}");

        return view($this->viewName($request), [
            'hasil' => $dataset + ['awal' => $awal, 'akhir' => $akhir],
            'arsip' => $this->arsip($request),
            'mitraList' => Mitra::where('status_verifikasi', 'terverifikasi')->orderBy('nama_mitra')->get(),
            'jenisList' => ['realisasi' => LaporanService::JENIS['realisasi'], 'mitra' => LaporanService::JENIS['mitra']],
        ]);
    }

    public function unduh(Request $request, Laporan $laporan, DokumenService $dok)
    {
        if ($request->user()->isMitra()) {
            abort_unless($laporan->id_mitra && $laporan->id_mitra === $this->mitraId($request), 403);
        }
        [$path, $nama] = array_pad(explode('|', $laporan->path_file, 2), 2, 'laporan');

        return $dok->respons($path, $nama, false);
    }
}
