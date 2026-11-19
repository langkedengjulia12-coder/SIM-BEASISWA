<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** P-17 Kelola Pengguna (UC-17, ALGO-17) - khusus Admin WR3. */
class PenggunaController extends Controller
{
    private const ROLE_BARU = [Pengguna::ROLE_ADMIN, Pengguna::ROLE_STAF, Pengguna::ROLE_MAHASISWA];

    public function index(Request $request)
    {
        $q = Pengguna::with('mahasiswa')->orderBy('role')->orderBy('nama');

        if ($role = $request->input('role')) {
            $q->where('role', $role);
        }
        if ($cari = $request->input('q')) {
            $q->where(fn ($w) => $w->where('nama', 'like', "%{$cari}%")->orWhere('username', 'like', "%{$cari}%")->orWhere('email', 'like', "%{$cari}%"));
        }

        return view('pengguna.index', [
            'pengguna' => $q->get(),
            'roleBaru' => self::ROLE_BARU,
            'labelRole' => Pengguna::LABEL_ROLE,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi field
        $data = $request->validate([
            'username' => ['required', 'regex:/^[A-Za-z0-9._-]{3,50}$/'],
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'role' => ['required', Rule::in(self::ROLE_BARU)],
            'password' => ['required', 'string', 'min:8'],
            'npm' => ['required_if:role,mahasiswa', 'nullable', 'digits:11', 'unique:t_mahasiswa,npm'],
            'program_studi' => ['required_if:role,mahasiswa', 'nullable', 'string', 'max:60'],
            'fakultas' => ['required_if:role,mahasiswa', 'nullable', 'string', 'max:60'],
            'angkatan' => ['required_if:role,mahasiswa', 'nullable', 'integer', 'between:2000,'.now()->year],
            'ipk' => ['nullable', 'numeric', 'between:0,4'],
        ], [
            'username.regex' => 'Username 3-50 karakter: huruf, angka, titik, garis bawah, atau strip.',
            'npm.digits' => 'NPM harus 11 digit.',
            'npm.unique' => 'NPM sudah terdaftar.',
        ]);

        // 2-3. Keunikan username dan email
        if (Pengguna::where('username', $data['username'])->orWhere('email', $data['email'])->exists()) {
            return back()->withInput()->with('error', 'Username atau email sudah digunakan.');
        }

        // 4. Simpan (password di-hash bcrypt oleh model) dan catat log
        DB::transaction(function () use ($data) {
            $akun = Pengguna::create([
                'username' => $data['username'], 'password' => $data['password'], 'nama' => $data['nama'],
                'email' => $data['email'], 'role' => $data['role'], 'status_aktif' => true,
            ]);

            if ($data['role'] === Pengguna::ROLE_MAHASISWA) {
                Mahasiswa::create([
                    'npm' => $data['npm'], 'id_pengguna' => $akun->id_pengguna, 'nama_lengkap' => $data['nama'],
                    'program_studi' => $data['program_studi'], 'fakultas' => $data['fakultas'],
                    'angkatan' => $data['angkatan'], 'ipk' => $data['ipk'] ?? 0, 'status_akademik' => 'aktif',
                ]);
            }
            LogAktivitas::catat('tambah', 'pengguna', "Pengguna {$akun->id_pengguna} ({$akun->username}) role {$akun->role}");
        });

        return back()->with('success', 'Data pengguna berhasil disimpan.');
    }

    public function update(Request $request, Pengguna $pengguna)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'password' => ['nullable', 'string', 'min:8'],
            'status_aktif' => ['required', 'boolean'],
        ]);

        if (Pengguna::where('email', $data['email'])->where('id_pengguna', '!=', $pengguna->id_pengguna)->exists()) {
            return back()->with('error', 'Username atau email sudah digunakan.');
        }
        if ($pengguna->id_pengguna === $request->user()->id_pengguna && ! (bool) $data['status_aktif']) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $isi = ['nama' => $data['nama'], 'email' => $data['email'], 'status_aktif' => (bool) $data['status_aktif']];
        if (filled($data['password'] ?? null)) {
            $isi['password'] = $data['password'];
        }

        DB::transaction(function () use ($pengguna, $isi, $data) {
            $pengguna->update($isi);
            if ($pengguna->mahasiswa) {
                $pengguna->mahasiswa->update(['nama_lengkap' => $data['nama']]);
            }
        });

        LogAktivitas::catat('ubah', 'pengguna', "Pengguna {$pengguna->id_pengguna}".(filled($data['password'] ?? null) ? ' (reset password)' : ''));

        return back()->with('success', 'Data pengguna berhasil disimpan.');
    }
}
