<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Notifikasi::with('penyewa:id,nama_lengkap')->where('user_id', auth()->id());

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('is_read')) {
            $query->where('is_read', (bool) $request->is_read);
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $notifikasi = $query->orderByDesc('created_at')->paginate($request->get('per_page', 20));
            return $this->paginate($notifikasi, 'Data notifikasi berhasil diambil');
        }

        $notifikasi = $query->orderByDesc('created_at')->paginate(10);
        return view('admin.simk.notifikasi.index', compact('notifikasi'));
    }

    public function unread(Request $request): JsonResponse
    {
        $notifikasi = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->with('penyewa:id,nama_lengkap')
            ->orderByDesc('created_at')
            ->get();

        return $this->success([
            'total_unread' => $notifikasi->count(),
            'data' => $notifikasi,
        ]);
    }

    public function markRead(int $id): JsonResponse
    {
        $notifikasi = Notifikasi::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$notifikasi) {
            return $this->notFound('Notifikasi tidak ditemukan');
        }

        $notifikasi->markAsRead();
        return $this->success($notifikasi->fresh(), 'Notifikasi ditandai dibaca');
    }

    public function markAllRead(): JsonResponse
    {
        $updated = Notifikasi::where('user_id', auth()->id())->where('is_read', false)->update([
            'is_read' => true,
            'dibaca_pada' => now(),
        ]);

        return $this->success(['updated' => $updated], 'Semua notifikasi ditandai dibaca');
    }

    public function destroy(int $id): JsonResponse
    {
        $notifikasi = Notifikasi::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$notifikasi) {
            return $this->notFound('Notifikasi tidak ditemukan');
        }

        $notifikasi->delete();
        return $this->success(null, 'Notifikasi berhasil dihapus');
    }

    public function sendPaymentReminder(Request $request): JsonResponse
    {
        $request->validate([
            'hari_sebelum_jatuh_tempo' => 'nullable|integer|min:1|max:30',
        ]);

        $hari = $request->hari_sebelum_jatuh_tempo ?? 3;
        $batas = now()->addDays($hari)->toDateString();

        $transaksi = Transaksi::with('penyewa:id,nama_lengkap,no_hp,user_id')
            ->whereIn('status', ['belum_bayar', 'terlambat'])
            ->where('jatuh_tempo', '<=', $batas)
            ->get();

        $kirim = 0;
        foreach ($transaksi as $trx) {
            $sudahAda = Notifikasi::where('penyewa_id', $trx->penyewa_id)
                ->where('tipe', 'jatuh_tempo')
                ->whereDate('created_at', today())
                ->exists();

            if (!$sudahAda && $trx->penyewa) {
                Notifikasi::kirim([
                    'user_id' => $trx->penyewa->user_id,
                    'penyewa_id' => $trx->penyewa_id,
                    'judul' => $trx->status === 'terlambat' ? '⚠️ Tagihan Terlambat!' : '🔔 Pengingat Tagihan',
                    'pesan' => $trx->status === 'terlambat'
                        ? "Tagihan {$trx->jenis} sebesar {$trx->jumlah_format} Anda sudah melewati jatuh tempo ({$trx->jatuh_tempo->format('d/m/Y')}). Segera lakukan pembayaran."
                        : "Tagihan {$trx->jenis} sebesar {$trx->jumlah_format} akan jatuh tempo pada {$trx->jatuh_tempo->format('d/m/Y')}. Jangan lupa bayar ya!",
                    'tipe' => 'jatuh_tempo',
                    'prioritas' => $trx->status === 'terlambat' ? 'tinggi' : 'sedang',
                    'data_tambahan' => [
                        'transaksi_id' => $trx->id,
                        'jatuh_tempo' => $trx->jatuh_tempo,
                    ],
                ]);
                $kirim++;
            }
        }

        return $this->success([
            'total_tagihan_ditemukan' => $transaksi->count(),
            'reminder_terkirim' => $kirim,
        ], "Berhasil mengirim {$kirim} reminder pembayaran");
    }
}