<?php

namespace App\Http\Controllers;

use App\Models\DokumenPendaftaran;
use App\Models\LogAktivitas;
use App\Models\Pendaftaran;
use App\Models\SkemaBeasiswa;
use App\Services\DokumenService;
use App\Services\NotifikasiService;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** P-06 Mendaftar Beasiswa (UC-06) dan P-10 Lihat Status Seleksi (UC-10) - sisi Mahasiswa. */
class PendaftaranController extends Controller
{
    private function mahasiswa(Request $request)
    {
        return $request->user()->mahasiswa;
    }

    /** L-06 Formulir Pendaftaran Beasiswa. */
    public function form(Request $request)
    {
        $mhs = $this->mahasiswa($request);

        $skemaDibuka = SkemaBeasiswa::where('status', 'dibuka')
            ->whereDate('tgl_buka', '<=', now()->toDateString())
            ->whereDate('tgl_tutup', '>=', now()->toDateString())
            ->orderBy('tgl_tutup')->get();

        $sudah = $mhs ? Pendaftaran::where('npm', $mhs->npm)->pluck('id_skema')->all() : [];

        return view('pendaftaran.form', [
            'mhs' => $mhs,
            'skemaDibuka' => $skemaDibuka,
            'sudah' => $sudah,
            'dipilih' => $skemaDibuka->firstWhere('id_skema', $request->input('skema')),
            'label' => config('beasiswa.dokumen_label'),
        ]);
    }

    /** ALGO-06 Mendaftar Beasiswa. */
    public function kirim(Request $request, DokumenService $dok, NotifikasiService $notif)
    {
        $mhs = $this->mahasiswa($request);
        if (! $mhs) {
            return back()->with('error', 'Profil mahasiswa Anda belum terdaftar. Hubungi admin.');
        }

        $request->validate([
            'id_skema' => ['required', 'exists:t_skema_beasiswa,id_skema'],
            'motivasi' => ['nullable', 'string', 'max:3000'],
            'dokumen.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.config('beasiswa.maks_dokumen_kb')],
        ], [
            'dokumen.*.mimes' => 'Berkas harus berformat PDF, JPG, atau PNG.',
            'dokumen.*.max' => 'Ukuran berkas maksimal 5 MB.',
        ]);

        $skema = SkemaBeasiswa::findOrFail($request->input('id_skema'));

        // 1. Skema dibuka dan masih dalam periode
        if ($skema->status !== 'dibuka' || ! $skema->dalamPeriode()) {
            return back()->withInput()->with('error', 'Skema ini tidak sedang membuka pendaftaran.');
        }
        if ($mhs->status_akademik !== 'aktif') {
            return back()->withInput()->with('error', 'Hanya mahasiswa berstatus aktif yang dapat mendaftar.');
        }
        // 2. IPK
        if ($mhs->ipk < $skema->ipk_minimum) {
            return back()->withInput()->with('error', 'IPK Anda belum memenuhi syarat minimum skema.');
        }
        // 3. Sudah mendaftar
        if (Pendaftaran::where('npm', $mhs->npm)->where('id_skema', $skema->id_skema)->exists()) {
            return back()->withInput()->with('error', 'Anda sudah mendaftar pada skema ini.');
        }
        // 4. Dokumen wajib
        $kurang = collect($skema->dokumen_wajib)->filter(fn ($j) => ! $request->hasFile("dokumen.{$j}"));
        if ($kurang->isNotEmpty()) {
            $label = config('beasiswa.dokumen_label');

            return back()->withInput()->with('error', 'Dokumen wajib belum lengkap: '
                .$kurang->map(fn ($j) => $label[$j] ?? $j)->implode(', ').'.');
        }

        // 5. Simpan pendaftaran dan dokumen (terenkripsi), kirim notifikasi konfirmasi
        $pendaftaran = DB::transaction(function () use ($request, $mhs, $skema, $dok) {
            $p = Pendaftaran::create([
                'id_pendaftaran' => Kode::berikutnya('t_pendaftaran', 'id_pendaftaran', 'PD', 5),
                'npm' => $mhs->npm,
                'id_skema' => $skema->id_skema,
                'tgl_daftar' => now(),
                'ipk_saat_daftar' => $mhs->ipk,
                'motivasi' => $request->input('motivasi'),
                'status' => 'diajukan',
            ]);

            foreach ($skema->dokumen_wajib as $jenis) {
                $s = $dok->simpan($request->file("dokumen.{$jenis}"), 'pendaftaran');
                DokumenPendaftaran::create([
                    'id_dokumen' => Kode::berikutnya('t_dokumen_pendaftaran', 'id_dokumen', 'DK', 5),
                    'id_pendaftaran' => $p->id_pendaftaran,
                    'jenis_dokumen' => $jenis,
                    'nama_file' => $s['nama'],
                    'path_file' => $s['path'],
                    'ukuran_kb' => $s['ukuran_kb'],
                    'status_verifikasi' => 'menunggu',
                    'tgl_unggah' => now(),
                ]);
            }

            return $p;
        });

        $notif->kirim($request->user(), 'seleksi', 'Pendaftaran beasiswa diterima',
            "Pendaftaran Anda pada skema {$skema->nama_skema} telah kami terima dengan nomor {$pendaftaran->id_pendaftaran}. Berkas akan segera diverifikasi.");
        LogAktivitas::catat('tambah', 'pendaftaran', "Pendaftaran {$pendaftaran->id_pendaftaran} pada {$skema->id_skema}");

        return redirect()->route('pendaftaran.status')
            ->with('success', "Pendaftaran berhasil dikirim. Nomor: {$pendaftaran->id_pendaftaran}.");
    }

    /** ALGO-10 Lihat Status Seleksi (L-10). */
    public function statusSaya(Request $request)
    {
        $mhs = $this->mahasiswa($request);

        $daftar = $mhs
            ? Pendaftaran::with('skema', 'dokumen', 'penilaian', 'penerima')
                ->where('npm', $mhs->npm)->orderByDesc('tgl_daftar')->get()
            : collect();

        return view('pendaftaran.status', ['mhs' => $mhs, 'daftar' => $daftar]);
    }

    /** Perbaikan berkas yang ditandai tidak valid oleh verifikator (status tidak_lengkap -> diajukan). */
    public function perbaiki(Request $request, Pendaftaran $pendaftaran, DokumenService $dok)
    {
        $mhs = $this->mahasiswa($request);
        abort_unless($mhs && $pendaftaran->npm === $mhs->npm, 403);

        if ($pendaftaran->status !== 'tidak_lengkap' || $pendaftaran->skema->sudahDitetapkan()) {
            return back()->with('error', 'Pendaftaran ini tidak dapat diperbaiki.');
        }

        $salah = $pendaftaran->dokumen()->where('status_verifikasi', 'tidak_valid')->get();

        $request->validate([
            'perbaikan.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.config('beasiswa.maks_dokumen_kb')],
        ], [
            'perbaikan.*.mimes' => 'Berkas harus berformat PDF, JPG, atau PNG.',
            'perbaikan.*.max' => 'Ukuran berkas maksimal 5 MB.',
        ]);

        foreach ($salah as $d) {
            if (! $request->hasFile('perbaikan.'.Kode::kunci($d->id_dokumen))) {
                return back()->with('error', 'Dokumen wajib belum lengkap: unggah ulang '.$d->label.'.');
            }
        }

        DB::transaction(function () use ($request, $salah, $pendaftaran, $dok) {
            foreach ($salah as $d) {
                $s = $dok->simpan($request->file('perbaikan.'.Kode::kunci($d->id_dokumen)), 'pendaftaran');
                $dok->hapus($d->path_file);
                $d->update([
                    'nama_file' => $s['nama'], 'path_file' => $s['path'], 'ukuran_kb' => $s['ukuran_kb'],
                    'status_verifikasi' => 'menunggu', 'tgl_unggah' => now(),
                ]);
            }
            $pendaftaran->update(['status' => 'diajukan']);
        });

        LogAktivitas::catat('ubah', 'pendaftaran', "Perbaikan berkas {$pendaftaran->id_pendaftaran}");

        return back()->with('success', 'Berkas perbaikan berhasil dikirim dan akan diverifikasi ulang.');
    }
}
