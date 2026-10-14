<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Notifikasi;
use App\Models\Penyewa;
use App\Models\Transaksi;
use App\Models\Utilitas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Transaksi::with(['penyewa.user', 'kamar']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('penyewa_id')) {
            $query->where('penyewa_id', $request->penyewa_id);
        }
        if ($request->filled('kamar_id')) {
            $query->where('kamar_id', $request->kamar_id);
        }
        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->where('periode_bulan', $request->bulan)->where('periode_tahun', $request->tahun);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_transaksi', 'like', "%{$request->search}%")
                    ->orWhereHas('penyewa', fn($p) => $p->where('nama_lengkap', 'like', "%{$request->search}%"));
            });
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $transaksi = $query->orderByDesc('tanggal_tagihan')->paginate($request->get('per_page', 15));
            return $this->paginate($transaksi, 'Data transaksi berhasil diambil');
        }

        $transaksis = $query->orderByDesc('tanggal_tagihan')->paginate(10);
        return view('admin.simk.transaksi.index', compact('transaksis'));
    }

    public function create(): View
    {
        $penyewas = Penyewa::with('user')->orderBy('created_at', 'desc')->get();
        $kamars = Kamar::orderBy('nomor_kamar')->get();

        return view('admin.simk.transaksi.create', compact('penyewas', 'kamars'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $request->validate([
                'penyewa_id' => 'required|exists:penyewa,id',
                'kamar_id' => 'required|exists:kamar,id',
                'jenis' => 'required|in:sewa,utilitas,deposit,denda,perawatan,lainnya',
                'keterangan' => 'nullable|string|max:255',
                'jumlah' => 'required|numeric|min:1',
                'tanggal_tagihan' => 'required|date',
                'jatuh_tempo' => 'required|date|after_or_equal:tanggal_tagihan',
                'periode_bulan' => 'nullable|integer|between:1,12',
                'periode_tahun' => 'nullable|integer|min:2020',
                'catatan' => 'nullable|string',
            ]);

            $data = $request->only([
                'penyewa_id', 'kamar_id', 'jenis', 'keterangan', 'jumlah',
                'tanggal_tagihan', 'jatuh_tempo', 'periode_bulan', 'periode_tahun', 'catatan',
            ]);
            $data['dibuat_oleh'] = auth()->id();
            $data['status'] = 'belum_bayar';

            $transaksi = Transaksi::create($data);

            Notifikasi::kirim([
                'penyewa_id' => $transaksi->penyewa_id,
                'judul' => 'Tagihan Baru',
                'pesan' => "Tagihan {$transaksi->jenis} sebesar {$transaksi->jumlah_format} telah dibuat. Jatuh tempo: " . $transaksi->jatuh_tempo->format('d/m/Y'),
                'tipe' => 'pembayaran',
                'prioritas' => 'sedang',
                'data_tambahan' => ['transaksi_id' => $transaksi->id],
            ]);

            return $this->created($transaksi->load(['penyewa:id,nama_lengkap', 'kamar:id,nomor_kamar']), 'Transaksi berhasil dibuat');
        }

        $request->validate([
            'penyewa_id' => 'required|exists:penyewa,id',
            'kamar_id' => 'required|exists:kamar,id',
            'jenis' => 'required|in:sewa,utilitas,deposit,denda,perawatan,lainnya',
            'keterangan' => 'nullable|string|max:255',
            'jumlah' => 'required|numeric|min:1',
            'tanggal_tagihan' => 'required|date',
            'jatuh_tempo' => 'required|date|after_or_equal:tanggal_tagihan',
            'periode_bulan' => 'nullable|integer|between:1,12',
            'periode_tahun' => 'nullable|integer|min:2020',
            'catatan' => 'nullable|string',
        ]);

        $data = $request->only([
            'penyewa_id', 'kamar_id', 'jenis', 'keterangan', 'jumlah',
            'tanggal_tagihan', 'jatuh_tempo', 'periode_bulan', 'periode_tahun', 'catatan',
        ]);
        $data['dibuat_oleh'] = auth()->id();
        $data['status'] = 'belum_bayar';

        Transaksi::create($data);

        return redirect()->route('simk.transaksi.index')->with('success', 'Transaksi berhasil dibuat.');
    }

    public function show(Request $request, Transaksi $transaksi): View|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $transaksi->load(['penyewa', 'kamar', 'dibuatOleh:id,name']);
            return $this->success($transaksi);
        }

        $transaksi->load(['penyewa.user', 'kamar']);
        return view('admin.simk.transaksi.show', compact('transaksi'));
    }

    public function edit(Transaksi $transaksi): View
    {
        $penyewas = Penyewa::with('user')->orderBy('created_at', 'desc')->get();
        $kamars = Kamar::orderBy('nomor_kamar')->get();

        return view('admin.simk.transaksi.edit', compact('transaksi', 'penyewas', 'kamars'));
    }

    public function update(Request $request, Transaksi $transaksi): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            if ($transaksi->status === 'lunas') {
                return $this->error('Transaksi yang sudah lunas tidak dapat diubah', 422);
            }

            $request->validate([
                'jenis' => 'sometimes|in:sewa,utilitas,deposit,denda,perawatan,lainnya',
                'keterangan' => 'nullable|string|max:255',
                'jumlah' => 'sometimes|numeric|min:1',
                'tanggal_tagihan' => 'sometimes|date',
                'jatuh_tempo' => 'sometimes|date',
                'catatan' => 'nullable|string',
            ]);

            $transaksi->update($request->only(['jenis', 'keterangan', 'jumlah', 'tanggal_tagihan', 'jatuh_tempo', 'catatan']));
            return $this->success($transaksi->fresh(), 'Transaksi berhasil diperbarui');
        }

        if ($transaksi->status === 'lunas') {
            return redirect()->back()->with('error', 'Transaksi yang sudah lunas tidak dapat diubah.');
        }

        $request->validate([
            'penyewa_id' => 'required|exists:penyewa,id',
            'kamar_id' => 'required|exists:kamar,id',
            'jenis' => 'required|in:sewa,utilitas,deposit,denda,perawatan,lainnya',
            'keterangan' => 'nullable|string|max:255',
            'jumlah' => 'required|numeric|min:1',
            'tanggal_tagihan' => 'required|date',
            'jatuh_tempo' => 'required|date|after_or_equal:tanggal_tagihan',
            'status' => 'required|in:belum_bayar,lunas,terlambat',
            'catatan' => 'nullable|string',
        ]);

        $transaksi->update($request->only(['penyewa_id', 'kamar_id', 'jenis', 'keterangan', 'jumlah', 'tanggal_tagihan', 'jatuh_tempo', 'status', 'catatan']));
        return redirect()->route('simk.transaksi.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Request $request, Transaksi $transaksi): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            if ($transaksi->status === 'lunas') {
                return $this->error('Transaksi yang sudah lunas tidak dapat dihapus', 422);
            }

            $transaksi->delete();
            return $this->success(null, 'Transaksi berhasil dihapus');
        }

        if ($transaksi->status === 'lunas') {
            return redirect()->back()->with('error', 'Transaksi yang sudah lunas tidak dapat dihapus.');
        }

        $transaksi->delete();
        return redirect()->route('simk.transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    public function markLunas(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'metode_bayar' => 'required|in:tunai,transfer,qris,lainnya',
            'tanggal_bayar' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        $transaksi = Transaksi::find($id);
        if (!$transaksi) {
            return $this->notFound('Transaksi tidak ditemukan');
        }

        if ($transaksi->status === 'lunas') {
            return $this->error('Transaksi sudah lunas', 422);
        }

        $transaksi->update([
            'status' => 'lunas',
            'metode_bayar' => $request->metode_bayar,
            'tanggal_bayar' => $request->tanggal_bayar ?? now()->toDateString(),
            'catatan' => $request->catatan ?? $transaksi->catatan,
        ]);

        if ($transaksi->jenis === 'utilitas') {
            $updated = Utilitas::where('transaksi_id', $transaksi->id)
                ->update(['status_bayar' => 'sudah']);

            if ($updated === 0) {
                Utilitas::where('penyewa_id', $transaksi->penyewa_id)
                    ->where('kamar_id', $transaksi->kamar_id)
                    ->where('periode_bulan', $transaksi->periode_bulan)
                    ->where('periode_tahun', $transaksi->periode_tahun)
                    ->where('status_bayar', 'belum')
                    ->update(['status_bayar' => 'sudah', 'transaksi_id' => $transaksi->id]);
            }
        }

        Notifikasi::kirim([
            'penyewa_id' => $transaksi->penyewa_id,
            'judul' => 'Pembayaran Dikonfirmasi',
            'pesan' => "Pembayaran {$transaksi->jenis} sebesar {$transaksi->jumlah_format} telah dikonfirmasi. Terima kasih!",
            'tipe' => 'pembayaran',
            'prioritas' => 'rendah',
            'data_tambahan' => ['transaksi_id' => $transaksi->id],
        ]);

        Notifikasi::kirimKeAdmin([
            'penyewa_id' => $transaksi->penyewa_id,
            'judul' => 'Pembayaran Dikonfirmasi',
            'pesan' => "Pembayaran {$transaksi->jenis} sebesar {$transaksi->jumlah_format} telah dikonfirmasi.",
            'tipe' => 'pembayaran',
            'prioritas' => 'rendah',
            'data_tambahan' => ['transaksi_id' => $transaksi->id],
        ]);

        return $this->success($transaksi->fresh()->load('penyewa:id,nama_lengkap'), 'Pembayaran berhasil dikonfirmasi');
    }

    public function rekapBulanan(Request $request): JsonResponse
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $rekap = Transaksi::where('periode_bulan', $request->bulan)
            ->where('periode_tahun', $request->tahun)
            ->selectRaw('
                jenis,
                status,
                COUNT(*) as jumlah_transaksi,
                SUM(jumlah) as total_tagihan,
                SUM(CASE WHEN status = "lunas" THEN jumlah ELSE 0 END) as total_terbayar,
                SUM(CASE WHEN status IN ("belum_bayar","terlambat") THEN jumlah ELSE 0 END) as total_tunggakan
            ')
            ->groupBy('jenis', 'status')
            ->get();

        $summary = Transaksi::where('periode_bulan', $request->bulan)
            ->where('periode_tahun', $request->tahun)
            ->selectRaw('
                COUNT(*) as total_transaksi,
                SUM(jumlah) as total_tagihan,
                SUM(CASE WHEN status = "lunas" THEN jumlah ELSE 0 END) as total_terbayar,
                SUM(CASE WHEN status IN ("belum_bayar","terlambat") THEN jumlah ELSE 0 END) as total_tunggakan,
                COUNT(CASE WHEN status = "lunas" THEN 1 END) as jumlah_lunas,
                COUNT(CASE WHEN status IN ("belum_bayar","terlambat") THEN 1 END) as jumlah_belum_bayar
            ')
            ->first();

        return $this->success([
            'periode' => ['bulan' => $request->bulan, 'tahun' => $request->tahun],
            'summary' => $summary,
            'per_jenis' => $rekap,
        ]);
    }

    public function rekapTahunan(Request $request): JsonResponse
    {
        $request->validate(['tahun' => 'required|integer|min:2020']);

        $rekap = Transaksi::where('periode_tahun', $request->tahun)
            ->where('status', 'lunas')
            ->selectRaw('
                periode_bulan,
                COUNT(*) as jumlah_transaksi,
                SUM(jumlah) as total_pendapatan
            ')
            ->groupBy('periode_bulan')
            ->orderBy('periode_bulan')
            ->get()
            ->keyBy('periode_bulan');

        $months = collect(range(1, 12))->map(fn($m) => [
            'bulan' => $m,
            'jumlah_transaksi' => $rekap->get($m)?->jumlah_transaksi ?? 0,
            'total_pendapatan' => $rekap->get($m)?->total_pendapatan ?? 0,
        ]);

        return $this->success([
            'tahun' => $request->tahun,
            'per_bulan' => $months,
            'total_pendapatan' => $months->sum('total_pendapatan'),
        ]);
    }
}