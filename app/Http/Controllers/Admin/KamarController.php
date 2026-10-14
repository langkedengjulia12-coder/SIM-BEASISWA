<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class KamarController extends Controller
{
    /**
     * Display a listing of kamar
     */
    public function index(Request $request): View
    {
        $query = Kamar::query();
        
        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan tipe
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kamar', 'like', "%{$search}%")
                  ->orWhere('nama_kamar', 'like', "%{$search}%");
            });
        }
        
        $kamars = $query->orderBy('nomor_kamar')->paginate(10);
        
        return view('admin.simk.kamar.index', compact('kamars'));
    }

    /**
     * Show the form for creating a new kamar
     */
    public function create(): View
    {
        return view('admin.simk.kamar.create');
    }

    /**
     * Store a newly created kamar in storage
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|unique:kamar,nomor_kamar|max:20',
            'nama_kamar'  => 'nullable|string|max:100',
            'tipe'        => 'required|in:standar,deluxe,premium',
            'harga_sewa'  => 'required|numeric|min:0',
            'lantai'      => 'nullable|integer|min:1',
            'fasilitas'   => 'nullable|string',
            'deskripsi'   => 'nullable|string',
            'status'      => 'required|in:tersedia,terisi,maintenance',
        ]);

        foreach (['lantai'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        // Pastikan `lantai` memiliki nilai default jika tidak diberikan (kolom DB tidak nullable)
        if (!isset($validated['lantai']) || $validated['lantai'] === null) {
            $validated['lantai'] = 1;
        }

        Kamar::create($validated);

        return redirect()->route('simk.kamar.index')
            ->with('success', 'Kamar berhasil ditambahkan!');
    }

    /**
     * Display the specified kamar
     */
    public function show(Kamar $kamar): View
    {
        $kamar->load('penyewa');
        return view('admin.simk.kamar.show', compact('kamar'));
    }

    /**
     * Show the form for editing the specified kamar
     */
    public function edit(Kamar $kamar): View
    {
        return view('admin.simk.kamar.edit', compact('kamar'));
    }

    /**
     * Update the specified kamar in storage
     */
    public function update(Request $request, Kamar $kamar): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_kamar' => "required|string|unique:kamar,nomor_kamar,{$kamar->id}|max:20",
            'nama_kamar'  => 'nullable|string|max:100',
            'tipe'        => 'required|in:standar,deluxe,premium',
            'harga_sewa'  => 'required|numeric|min:0',
            'lantai'      => 'nullable|integer|min:1',
            'fasilitas'   => 'nullable|string',
            'deskripsi'   => 'nullable|string',
            'status'      => 'required|in:tersedia,terisi,maintenance',
        ]);

        foreach (['lantai'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        // Pastikan `lantai` memiliki nilai jika tidak diberikan
        if (!isset($validated['lantai']) || $validated['lantai'] === null) {
            $validated['lantai'] = $kamar->lantai ?? 1;
        }

        $kamar->update($validated);

        return redirect()->route('simk.kamar.index')
            ->with('success', 'Kamar berhasil diperbarui!');
    }

    /**
     * Remove the specified kamar from storage
     */
    public function destroy(Kamar $kamar): RedirectResponse
    {
        if ($kamar->status === 'terisi') {
            return redirect()->route('simk.kamar.index')
                ->with('error', 'Tidak dapat menghapus kamar yang sedang terisi!');
        }

        $kamar->delete();

        return redirect()->route('simk.kamar.index')
            ->with('success', 'Kamar berhasil dihapus!');
    }
}