<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;

/** Profil & rekening mahasiswa (prasyarat penyaluran dana, ALGO-11). */
class ProfilMahasiswaController extends Controller
{
    public function edit(Request $request)
    {
        return view('profil.edit', ['mhs' => $request->user()->mahasiswa]);
    }

    public function update(Request $request)
    {
        $mhs = $request->user()->mahasiswa;
        abort_unless($mhs, 404);

        $data = $request->validate([
            'nama_bank' => ['required', 'string', 'max:40'],
            'no_rekening' => ['required', 'regex:/^[0-9]{6,20}$/'],
        ], ['no_rekening.regex' => 'Nomor rekening harus berupa 6-20 digit angka.']);

        $mhs->update($data);   // no_rekening dienkripsi otomatis oleh cast
        LogAktivitas::catat('ubah', 'profil', "Rekening {$mhs->npm} diperbarui");

        return back()->with('success', 'Data rekening berhasil disimpan.');
    }
}
