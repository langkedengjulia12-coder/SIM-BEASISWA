<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

/** Kotak masuk notifikasi pengguna (hasil UC-18). */
class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $daftar = Notifikasi::where('id_pengguna', $request->user()->id_pengguna)
            ->orderByDesc('id_notifikasi')->paginate(15);

        return view('notifikasi.index', compact('daftar'));
    }

    public function baca(Request $request, int $id)
    {
        Notifikasi::where('id_pengguna', $request->user()->id_pengguna)
            ->where('id_notifikasi', $id)->update(['dibaca' => true]);

        return back();
    }

    public function bacaSemua(Request $request)
    {
        Notifikasi::where('id_pengguna', $request->user()->id_pengguna)->update(['dibaca' => true]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
