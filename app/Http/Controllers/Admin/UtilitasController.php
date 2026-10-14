<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Penyewa;
use App\Models\TarifUtilitas;
use App\Models\Transaksi;
use App\Models\Utilitas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UtilitasController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Utilitas::with(['kamar', 'penyewa.user', 'tarif']);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('kamar_id')) {
            $query->where('kamar_id', $request->kamar_id);
        }
        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->where('periode_bulan', $request->bulan)->where('periode_tahun', $request->tahun);
        }
        if ($request->filled('status_bayar')) {
            $query->where('status_bayar', $request->status_bayar);
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $utilitas = $query->orderByDesc('periode_tahun')->orderByDesc('periode_bulan')->paginate($request->get('per_page', 15));
            return $this->paginate($utilitas, 'Data utilitas berhasil diambil');
        }

        $utilitas = $query->orderByDesc('periode_tahun')->orderByDesc('periode_bulan')->paginate(10);
        return view('admin.simk.utilitas.index', compact('utilitas'));
    }

    public function create(): View
    {
        $kamars = Kamar::orderBy('nomor_kamar')->get();
        $penyewas = Penyewa::with('user')->orderBy('created_at', 'desc')->get();
        $tarifs = TarifUtilitas::orderBy('created_at', 'desc')->get();

        return view('admin.simk.utilitas.create', compact('kamars', 'penyewas', 'tarifs'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $request->validate([
                'kamar_id' => 'required|exists:kamar,id',
                'penyewa_id' => 'nullable|exists:penyewa,id',
                'jenis' => 'required|in:listrik,air',
                'periode_bulan' => 'required|integer|between:1,12',
                'periode_tahun' => 'required|integer|min:2020',
                'meteran_awal' => 'required|numeric|min:0',
                'meteran_akhir' => 'required|numeric|gte:meteran_awal',
                'tanggal_catat' => 'required|date',
                'jatuh_tempo' => 'required|date|after_or_equal:tanggal_catat',
                'catatan' => 'nullable|string',
            ]);

            $exists = Utilitas::where('kamar_id', $request->kamar_id)
                ->where('jenis', $request->jenis)
                ->where('periode_bulan', $request->periode_bulan)
                ->where('periode_tahun', $request->periode_tahun)
                ->exists();

            if ($exists) {
                return $this->error("Data utilitas {$request->jenis} kamar ini untuk periode {$request->periode_bulan}/{$request->periode_tahun} sudah ada.", 422);
            }

            $tarif = TarifUtilitas::getAktif($request->jenis);
            if (!$tarif) {
                return $this->error("Tarif {$request->jenis} belum dikonfigurasi. Hubungi admin.", 422);
            }

            $utilitas = Utilitas::create(array_merge($request->only(['kamar_id', 'penyewa_id', 'jenis', 'periode_bulan', 'periode_tahun', 'meteran_awal', 'meteran_akhir', 'tanggal_catat', 'catatan']), [
                'tarif_id' => $tarif->id,
                'harga_per_unit' => $tarif->harga_per_unit,
                'status_bayar' => 'belum',
            ]));

            if ($request->filled('penyewa_id')) {
                $jumlah = $utilitas->total_tagihan ?? $utilitas->total_biaya ?? 0;
                $transaksi = Transaksi::create([
                    'penyewa_id' => $request->penyewa_id,
                    'kamar_id' => $request->kamar_id,
                    'jenis' => 'utilitas',
                    'keterangan' => "Tagihan utilitas {$utilitas->jenis} periode {$utilitas->periode_bulan}/{$utilitas->periode_tahun}",
                    'jumlah' => $jumlah,
                    'tanggal_tagihan' => now()->toDateString(),
                    'jatuh_tempo' => $request->jatuh_tempo,
                    'periode_bulan' => $utilitas->periode_bulan,
                    'periode_tahun' => $utilitas->periode_tahun,
                    'dibuat_oleh' => auth()->id(),
                    'status' => 'belum_bayar',
                ]);

                $utilitas->update(['transaksi_id' => $transaksi->id]);
            }

            return $this->created($utilitas->load(['kamar:id,nomor_kamar', 'tarif']), 'Data utilitas berhasil dicatat');
        }

        $request->validate([
            'kamar_id' => 'required|exists:kamar,id',
            'penyewa_id' => 'nullable|exists:penyewa,id',
            'jenis' => 'required|in:listrik,air',
            'periode_bulan' => 'required|integer|between:1,12',
            'periode_tahun' => 'required|integer|min:2020',
            'meteran_awal' => 'required|numeric|min:0',
            'meteran_akhir' => 'required|numeric|gte:meteran_awal',
            'tanggal_catat' => 'required|date',
            'jatuh_tempo' => 'required|date|after_or_equal:tanggal_catat',
            'catatan' => 'nullable|string',
        ]);

        $exists = Utilitas::where('kamar_id', $request->kamar_id)
            ->where('jenis', $request->jenis)
            ->where('periode_bulan', $request->periode_bulan)
            ->where('periode_tahun', $request->periode_tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Data utilitas untuk periode tersebut sudah ada.');
        }

        $tarif = TarifUtilitas::getAktif($request->jenis);
        if (!$tarif) {
            return redirect()->back()->with('error', 'Tarif utilitas belum dikonfigurasi.');
        }

        $utilitas = Utilitas::create(array_merge($request->only(['kamar_id', 'penyewa_id', 'jenis', 'periode_bulan', 'periode_tahun', 'tanggal_catat', 'catatan']), [
            'meter_awal' => $request->meteran_awal,
            'meter_akhir' => $request->meteran_akhir,
            'meteran_awal' => $request->meteran_awal,
            'meteran_akhir' => $request->meteran_akhir,
            'penggunaan' => max(0, $request->meteran_akhir - $request->meteran_awal),
            'harga_satuan' => $tarif->harga_per_unit,
            'total_biaya' => max(0, $request->meteran_akhir - $request->meteran_awal) * $tarif->harga_per_unit,
            'tarif_id' => $tarif->id,
            'harga_per_unit' => $tarif->harga_per_unit,
            'status_bayar' => 'belum',
        ]));

        if ($request->filled('penyewa_id')) {
            $jumlah = $utilitas->total_tagihan ?? $utilitas->total_biaya ?? 0;
            $transaksi = Transaksi::create([
                'penyewa_id' => $request->penyewa_id,
                'kamar_id' => $request->kamar_id,
                'jenis' => 'utilitas',
                'keterangan' => "Tagihan utilitas {$utilitas->jenis} periode {$utilitas->periode_bulan}/{$utilitas->periode_tahun}",
                'jumlah' => $jumlah,
                'tanggal_tagihan' => now()->toDateString(),
                'jatuh_tempo' => $request->jatuh_tempo,
                'periode_bulan' => $utilitas->periode_bulan,
                'periode_tahun' => $utilitas->periode_tahun,
                'dibuat_oleh' => auth()->id(),
                'status' => 'belum_bayar',
            ]);

            $utilitas->update(['transaksi_id' => $transaksi->id]);
        }

        return redirect()->route('simk.utilitas.index')->with('success', 'Data utilitas berhasil dicatat.');
    }

    public function show(Request $request, Utilitas $utilitas): View|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $utilitas->load(['kamar', 'penyewa', 'tarif', 'transaksi']);
            return $this->success($utilitas);
        }

        $utilitas->load(['kamar', 'penyewa.user', 'tarif', 'transaksi']);
        return view('admin.simk.utilitas.show', compact('utilitas'));
    }

    public function edit(Utilitas $utilitas): View
    {
        $kamars = Kamar::orderBy('nomor_kamar')->get();
        $penyewas = Penyewa::with('user')->orderBy('created_at', 'desc')->get();
        $tarifs = TarifUtilitas::orderBy('created_at', 'desc')->get();

        return view('admin.simk.utilitas.edit', compact('utilitas', 'kamars', 'penyewas', 'tarifs'));
    }

    public function update(Request $request, Utilitas $utilitas): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            if ($utilitas->status_bayar === 'sudah') {
                return $this->error('Data utilitas yang sudah dibayar tidak dapat diubah', 422);
            }

            $request->validate([
                'meteran_awal' => 'sometimes|numeric|min:0',
                'meteran_akhir' => 'sometimes|numeric',
                'tanggal_catat' => 'sometimes|date',
                'catatan' => 'nullable|string',
            ]);

            if ($request->filled('meteran_akhir') && $request->meteran_akhir < ($request->meteran_awal ?? $utilitas->meteran_awal)) {
                return $this->error('Meteran akhir tidak boleh lebih kecil dari meteran awal', 422);
            }

            $utilitas->update($request->only(['meteran_awal', 'meteran_akhir', 'tanggal_catat', 'catatan']));
            return $this->success($utilitas->fresh(), 'Data utilitas berhasil diperbarui');
        }

        if ($utilitas->status_bayar === 'sudah') {
            return redirect()->back()->with('error', 'Data utilitas yang sudah dibayar tidak dapat diubah.');
        }

        $request->validate([
            'kamar_id' => 'required|exists:kamar,id',
            'penyewa_id' => 'nullable|exists:penyewa,id',
            'jenis' => 'required|in:listrik,air',
            'periode_bulan' => 'required|integer|between:1,12',
            'periode_tahun' => 'required|integer|min:2020',
            'meteran_awal' => 'required|numeric|min:0',
            'meteran_akhir' => 'required|numeric|gte:meteran_awal',
            'tanggal_catat' => 'required|date',
            'status_bayar' => 'required|in:belum,sudah',
            'catatan' => 'nullable|string',
        ]);

        $utilitas->update($request->only(['kamar_id', 'penyewa_id', 'jenis', 'periode_bulan', 'periode_tahun', 'meteran_awal', 'meteran_akhir', 'tanggal_catat', 'status_bayar', 'catatan']));
        return redirect()->route('simk.utilitas.index')->with('success', 'Data utilitas berhasil diperbarui.');
    }

    public function destroy(Request $request, Utilitas $utilitas): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            if ($utilitas->status_bayar === 'sudah') {
                return $this->error('Data utilitas yang sudah dibayar tidak dapat dihapus', 422);
            }

            $utilitas->delete();
            return $this->success(null, 'Data utilitas berhasil dihapus');
        }

        if ($utilitas->status_bayar === 'sudah') {
            return redirect()->back()->with('error', 'Data utilitas yang sudah dibayar tidak dapat dihapus.');
        }

        $utilitas->delete();
        return redirect()->route('simk.utilitas.index')->with('success', 'Data utilitas berhasil dihapus.');
    }

    public function byKamar(Request $request, int $kamarId): JsonResponse
    {
        $utilitas = Utilitas::where('kamar_id', $kamarId)
            ->with('tarif')
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->paginate($request->get('per_page', 12));

        return $this->paginate($utilitas);
    }

    public function hitungTagihan(Request $request): JsonResponse
    {
        $request->validate([
            'utilitas_ids' => 'required|array|min:1',
            'utilitas_ids.*' => 'exists:utilitas,id',
            'penyewa_id' => 'required|exists:penyewa,id',
            'jatuh_tempo' => 'required|date',
        ]);

        $utilitasList = Utilitas::whereIn('id', $request->utilitas_ids)
            ->where('status_bayar', 'belum')
            ->get();

        if ($utilitasList->isEmpty()) {
            return $this->error('Tidak ada data utilitas yang belum dibayar', 422);
        }

        DB::beginTransaction();
        try {
            $totalTagihan = $utilitasList->sum('total_tagihan');
            $keterangan = 'Tagihan utilitas: ' . $utilitasList->pluck('jenis')->unique()->implode(' & ');

            $transaksi = Transaksi::create([
                'penyewa_id' => $request->penyewa_id,
                'kamar_id' => $utilitasList->first()->kamar_id,
                'jenis' => 'utilitas',
                'keterangan' => $keterangan,
                'jumlah' => $totalTagihan,
                'tanggal_tagihan' => now()->toDateString(),
                'jatuh_tempo' => $request->jatuh_tempo,
                'periode_bulan' => $utilitasList->first()->periode_bulan,
                'periode_tahun' => $utilitasList->first()->periode_tahun,
                'dibuat_oleh' => auth()->id(),
            ]);

            Utilitas::whereIn('id', $request->utilitas_ids)->update([
                'transaksi_id' => $transaksi->id,
            ]);

            DB::commit();
            return $this->created([
                'transaksi' => $transaksi->load('penyewa:id,nama_lengkap'),
                'total_tagihan' => $totalTagihan,
                'jumlah_item' => $utilitasList->count(),
            ], 'Tagihan utilitas berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Gagal membuat tagihan: ' . $e->getMessage(), 500);
        }
    }
}