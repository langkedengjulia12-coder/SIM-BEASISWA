<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Penyewa;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenyewaController extends Controller
{
    /**
     * Display a listing of penyewa.
     */
    public function index(Request $request): View
    {
        $query = User::select([
                'users.id as user_id',
                'users.name',
                'users.email',
                'penyewa.id as penyewa_id',
                'penyewa.no_hp',
                'penyewa.nik',
                'penyewa.status',
                'penyewa.durasi_kontrak_bulan',
                'penyewa.kamar_id',
                'kamar.nomor_kamar',
                'penyewa.created_at as penyewa_created_at',
                'users.created_at as user_created_at',
            ])
            ->leftJoin('penyewa', 'users.id', '=', 'penyewa.user_id')
            ->leftJoin('kamar', 'penyewa.kamar_id', '=', 'kamar.id')
            ->where('users.role', 'penyewa');

        if ($request->filled('status')) {
            $query->where('penyewa.status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('penyewa.no_hp', 'like', "%{$search}%")
                    ->orWhere('penyewa.nik', 'like', "%{$search}%");
            });
        }

        $penyewas = $query->orderBy('users.created_at', 'DESC')->paginate(10);

        return view('admin.simk.penyewa.index', compact('penyewas'));
    }

    /**
     * Show the form for creating a new penyewa.
     */
    public function create(): View
    {
        $kamars = Kamar::where('status', '!=', 'maintenance')
            ->orderBy('nomor_kamar')
            ->get();

        return view('admin.simk.penyewa.create', compact('kamars'));
    }

    /**
     * Store a newly created penyewa in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'no_hp' => 'required|string|max:20|unique:penyewa,no_hp',
            'nik' => 'required|string|max:20|unique:penyewa,nik',
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_masuk' => 'required|date',
            'durasi_kontrak' => 'required|integer|min:1',
            'harga_sewa_kontrak' => 'required|numeric|min:0',
            'alamat_asal' => 'required|string',
        ]);


        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'is_active' => true,
        ]);

        $penyewa = Penyewa::create([
            'user_id' => $user->id,
            'kamar_id' => $validated['kamar_id'],
            'nama_lengkap' => $validated['name'],
            'jenis_kelamin' => 'L',
            'no_hp' => $validated['no_hp'],
            'email' => $validated['email'],
            'nik' => $validated['nik'],
            'tanggal_masuk' => $validated['tanggal_masuk'],
            'kontrak_mulai' => $validated['tanggal_masuk'],
            'kontrak_selesai' => now()->addMonths($validated['durasi_kontrak'])->toDateString(),
            'durasi_kontrak_bulan' => $validated['durasi_kontrak'],
            'harga_sewa_kontrak' => $validated['harga_sewa_kontrak'],
            'alamat_asal' => $validated['alamat_asal'],
            'status' => 'aktif',
        ]);



        Kamar::find($validated['kamar_id'])->update(['status' => 'terisi']);

        return redirect()->route('simk.penyewa.index')
            ->with('success', 'Penyewa berhasil ditambahkan!');
    }

    /**
     * Display the specified penyewa.
     */
    public function show(Penyewa $penyewa): View
    {
        $penyewa->load('user', 'kamar');

        return view('admin.simk.penyewa.show', compact('penyewa'));
    }

    /**
     * Show the form for editing the specified penyewa.
     */
    public function edit(Penyewa $penyewa): View
    {
        $kamars = Kamar::where('status', '!=', 'maintenance')
            ->orderBy('nomor_kamar')
            ->get();

        $penyewa->load('user', 'kamar');

        return view('admin.simk.penyewa.edit', compact('penyewa', 'kamars'));
    }

    /**
     * Update the specified penyewa in storage.
     */
    public function update(Request $request, Penyewa $penyewa): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => "required|email|unique:users,email,{$penyewa->user_id}",
            'no_hp' => "required|string|max:20|unique:penyewa,no_hp,{$penyewa->id}",
            'nik' => "required|string|max:20|unique:penyewa,nik,{$penyewa->id}",
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_masuk' => 'required|date',
            'durasi_kontrak' => 'required|integer|min:1',
            'harga_sewa_kontrak' => 'required|numeric|min:0',
            'alamat_asal' => 'required|string',
            'status' => 'required|in:aktif,keluar',
        ]);


        $penyewa->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $kontrakSelesai = now()->parse($validated['tanggal_masuk'])->addMonths($validated['durasi_kontrak'])->toDateString();

        $oldKamarId = $penyewa->kamar_id;
        $newKamarId = $validated['kamar_id'];

        if ($oldKamarId && $oldKamarId !== $newKamarId) {
            Kamar::find($oldKamarId)?->update(['status' => 'tersedia']);
            Kamar::find($newKamarId)?->update(['status' => 'terisi']);
        }

        $penyewa->update([
            'kamar_id' => $newKamarId,
            'nama_lengkap' => $validated['name'],
            'jenis_kelamin' => $penyewa->jenis_kelamin,
            'no_hp' => $validated['no_hp'],
            'email' => $validated['email'],
            'nik' => $validated['nik'],
            'tanggal_masuk' => $validated['tanggal_masuk'],
            'kontrak_mulai' => $validated['tanggal_masuk'],
            'kontrak_selesai' => $kontrakSelesai,
            'durasi_kontrak_bulan' => $validated['durasi_kontrak'],
            'harga_sewa_kontrak' => $validated['harga_sewa_kontrak'],
            'alamat_asal' => $validated['alamat_asal'],
            'status' => $validated['status'],
        ]);



        return redirect()->route('simk.penyewa.index')
            ->with('success', 'Penyewa berhasil diperbarui!');
    }

    /**
     * Remove the specified penyewa from storage.
     */
    public function destroy(Penyewa $penyewa): RedirectResponse
    {
        $kamarId = $penyewa->kamar_id;

        $penyewa->user->delete();
        $penyewa->delete();

        if ($kamarId) {
            Kamar::find($kamarId)->update(['status' => 'tersedia']);
        }

        return redirect()->route('simk.penyewa.index')
            ->with('success', 'Penyewa berhasil dihapus!');
    }
}