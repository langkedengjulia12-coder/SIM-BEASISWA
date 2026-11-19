<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Mitra;
use App\Models\Pengguna;
use App\Services\NotifikasiService;
use App\Support\Kode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** P-02 Kelola Mitra dan Donor (UC-02). */
class MitraController extends Controller
{
    public function index(Request $request)
    {
        $q = Mitra::withCount('kontrak')->orderByDesc('id_mitra');

        if ($cari = $request->input('q')) {
            $q->where(fn ($w) => $w->where('nama_mitra', 'like', "%{$cari}%")->orWhere('nama_pic', 'like', "%{$cari}%"));
        }
        if ($status = $request->input('status')) {
            $q->where('status_verifikasi', $status);
        }

        return view('mitra.index', ['mitra' => $q->get(), 'jenis' => Mitra::JENIS]);
    }

    private function aturan(): array
    {
        return [
            'nama_mitra' => ['required', 'string', 'max:120'],
            'jenis_mitra' => ['required', Rule::in(array_keys(Mitra::JENIS))],
            'alamat' => ['nullable', 'string'],
            'nama_pic' => ['required', 'string', 'max:100'],
            'email_pic' => ['required', 'email', 'max:100'],
            'telepon_pic' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function store(Request $request)
    {
        // 1. Validasi seluruh field wajib
        $data = $request->validate($this->aturan());

        // 2-3. Cek duplikasi nama_mitra + email_pic
        if (Mitra::where('nama_mitra', $data['nama_mitra'])->where('email_pic', $data['email_pic'])->exists()) {
            return back()->withInput()->with('error', 'Mitra dengan nama dan email PIC tersebut sudah terdaftar.');
        }

        // 4. Bangkitkan id_mitra, simpan dengan status menunggu
        $mitra = DB::transaction(fn () => Mitra::create($data + [
            'id_mitra' => Kode::berikutnya('t_mitra', 'id_mitra', 'MT', 4),
            'status_verifikasi' => 'menunggu',
            'tgl_daftar' => now()->toDateString(),
        ]));

        LogAktivitas::catat('tambah', 'mitra', "Mitra {$mitra->id_mitra} - {$mitra->nama_mitra}");

        return back()->with('success', 'Data mitra berhasil disimpan.');
    }

    public function update(Request $request, Mitra $mitra)
    {
        $data = $request->validate($this->aturan());

        $duplikat = Mitra::where('nama_mitra', $data['nama_mitra'])
            ->where('email_pic', $data['email_pic'])
            ->where('id_mitra', '!=', $mitra->id_mitra)->exists();
        if ($duplikat) {
            return back()->withInput()->with('error', 'Mitra dengan nama dan email PIC tersebut sudah terdaftar.');
        }

        $mitra->update($data);
        LogAktivitas::catat('ubah', 'mitra', "Mitra {$mitra->id_mitra}");

        return back()->with('success', 'Data mitra berhasil disimpan.');
    }

    /** ALGO-02b: verifikasi; bila terverifikasi buat akun mitra dan kirim kredensial ke email PIC. */
    public function verifikasi(Request $request, Mitra $mitra, NotifikasiService $notif)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['terverifikasi', 'ditolak'])]]);

        if ($mitra->status_verifikasi === 'terverifikasi') {
            return back()->with('error', 'Mitra ini sudah terverifikasi.');
        }

        if ($data['status'] === 'ditolak') {
            $mitra->update(['status_verifikasi' => 'ditolak']);
            LogAktivitas::catat('verifikasi', 'mitra', "Mitra {$mitra->id_mitra} ditolak");

            return back()->with('success', 'Mitra ditandai ditolak.');
        }

        if (Pengguna::where('email', $mitra->email_pic)->exists()) {
            return back()->with('error', 'Email PIC sudah dipakai akun lain. Ubah email PIC terlebih dahulu.');
        }

        $username = Str::lower(str_replace('.', '', $mitra->id_mitra));   // mt260001
        $passwordAwal = Str::random(10);

        DB::transaction(function () use ($mitra, $username, $passwordAwal) {
            $akun = Pengguna::create([
                'username' => $username,
                'password' => $passwordAwal,
                'nama' => $mitra->nama_pic,
                'email' => $mitra->email_pic,
                'role' => Pengguna::ROLE_MITRA,
                'status_aktif' => true,
            ]);
            $mitra->update(['status_verifikasi' => 'terverifikasi', 'id_pengguna' => $akun->id_pengguna]);
        });

        $notif->kirimEmail($mitra->email_pic, 'Akun Portal Mitra',
            "Yth. {$mitra->nama_pic},\n\nKemitraan {$mitra->nama_mitra} telah diverifikasi oleh Universitas Kebangsaan Republik Indonesia.\n"
            ."Username : {$username}\nPassword sementara : {$passwordAwal}\n\nSilakan login dan segera ganti password Anda.");

        LogAktivitas::catat('verifikasi', 'mitra', "Mitra {$mitra->id_mitra} terverifikasi; akun {$username} dibuat");

        return back()->with('success', "Mitra terverifikasi. Akun dibuat - username: {$username}, password sementara: {$passwordAwal} (juga dikirim ke email PIC).");
    }
}
