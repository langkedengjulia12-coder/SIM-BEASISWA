<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\LogAktivitas;
use App\Models\Mitra;
use App\Services\DokumenService;
use App\Services\KontrakService;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** P-03 Kelola Kontrak Kerja Sama (UC-03). */
class KontrakController extends Controller
{
    public function index(Request $request)
    {
        $q = Kontrak::with('mitra')->orderByDesc('id_kontrak');
        if ($status = $request->input('status')) {
            $q->where('status', $status);
        }

        return view('kontrak.index', [
            'kontrak' => $q->get(),
            'mitra' => Mitra::where('status_verifikasi', 'terverifikasi')->orderBy('nama_mitra')->get(),
        ]);
    }

    public function store(Request $request, DokumenService $dok, KontrakService $svc)
    {
        // 1. Validasi field wajib dan rentang tanggal
        $data = $request->validate([
            'id_mitra' => ['required', Rule::exists('t_mitra', 'id_mitra')->where('status_verifikasi', 'terverifikasi')],
            'no_dokumen' => ['required', 'string', 'max:50', 'unique:t_kontrak,no_dokumen'],
            'judul' => ['required', 'string', 'max:150'],
            'jenis_dokumen' => ['required', Rule::in(['MoU', 'MoA'])],
            'tgl_mulai' => ['required', 'date'],
            'tgl_berakhir' => ['required', 'date', 'after:tgl_mulai'],
            'nilai_komitmen' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draf', 'aktif'])],
            'file_dokumen' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:'.config('beasiswa.maks_dokumen_kb')],
        ], [
            'tgl_berakhir.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
            'no_dokumen.unique' => 'Nomor dokumen sudah digunakan.',
        ]);

        // 2. Simpan berkas ke penyimpanan terenkripsi
        $path = null;
        if ($request->hasFile('file_dokumen')) {
            $s = $dok->simpan($request->file('file_dokumen'), 'kontrak');
            $path = $s['path'].'|'.$s['nama'];
        }

        // 3. Simpan record t_kontrak (draf atau aktif)
        $kontrak = DB::transaction(fn () => Kontrak::create([
            'id_kontrak' => Kode::berikutnya('t_kontrak', 'id_kontrak', 'KT', 4),
            'id_mitra' => $data['id_mitra'],
            'no_dokumen' => $data['no_dokumen'],
            'judul' => $data['judul'],
            'jenis_dokumen' => $data['jenis_dokumen'],
            'tgl_mulai' => $data['tgl_mulai'],
            'tgl_berakhir' => $data['tgl_berakhir'],
            'nilai_komitmen' => $data['nilai_komitmen'],
            'status' => $data['status'],
            'file_dokumen' => $path,
        ]));

        LogAktivitas::catat('tambah', 'kontrak', "Kontrak {$kontrak->id_kontrak} ({$kontrak->no_dokumen})");
        $svc->periksa($kontrak);

        return back()->with('success', 'Kontrak berhasil disimpan.');
    }

    public function aktifkan(Kontrak $kontrak, KontrakService $svc)
    {
        if ($kontrak->status !== 'draf') {
            return back()->with('error', 'Hanya kontrak berstatus draf yang dapat diaktifkan.');
        }

        $kontrak->update(['status' => 'aktif']);
        LogAktivitas::catat('ubah', 'kontrak', "Kontrak {$kontrak->id_kontrak} diaktifkan");
        $svc->periksa($kontrak);

        return back()->with('success', 'Kontrak berhasil disimpan.');
    }

    public function perpanjang(Request $request, Kontrak $kontrak, KontrakService $svc)
    {
        $data = $request->validate([
            'tgl_berakhir' => ['required', 'date', 'after:'.$kontrak->tgl_berakhir->toDateString()],
        ], [
            'tgl_berakhir.after' => 'Tanggal berakhir baru harus setelah tanggal berakhir saat ini.',
        ]);

        if ($kontrak->status === 'draf') {
            return back()->with('error', 'Aktifkan kontrak terlebih dahulu sebelum diperpanjang.');
        }

        $kontrak->update(['tgl_berakhir' => $data['tgl_berakhir'], 'status' => 'diperpanjang']);
        LogAktivitas::catat('ubah', 'kontrak', "Kontrak {$kontrak->id_kontrak} diperpanjang s.d. {$data['tgl_berakhir']}");
        $svc->periksa($kontrak->fresh());

        return back()->with('success', 'Kontrak berhasil disimpan.');
    }

    public function berkas(Kontrak $kontrak, DokumenService $dok)
    {
        abort_if(! $kontrak->file_dokumen, 404, 'Kontrak ini belum memiliki berkas.');
        [$path, $nama] = array_pad(explode('|', $kontrak->file_dokumen, 2), 2, 'kontrak.pdf');

        return $dok->respons($path, $nama);
    }
}
