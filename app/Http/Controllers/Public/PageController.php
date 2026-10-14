<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Notifikasi;
use App\Models\Penyewa;
use App\Models\Transaksi;
use App\Models\Utilitas;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $userRole = mb_strtolower(trim($user->role ?? ''));

            if ($user->hasAnyRole(['Super Admin', 'Staff']) || in_array($userRole, ['super admin', 'staff'], true)) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasAnyRole(['admin', 'pengelola']) || in_array($userRole, ['admin', 'pengelola'], true)) {
                return redirect()->route('simk.dashboard');
            }

            if ($user->hasAnyRole(['penyewa']) || $userRole === 'penyewa') {
                return $this->penyewaDashboard();
            }
        }

        return view('public.wellcome');
    }

    protected function getPenyewaDashboardData(): array
    {
        $user = auth()->user();
        $penyewa = $user?->penyewa()->with('kamar')->first();

        $kamarKosong = Kamar::where('status', 'tersedia')
            ->latest()
            ->take(6)
            ->get();

        $penyewaId = optional($penyewa)->id;

        $tagihanBulanIni = Transaksi::query()
            ->with('utilitas')
            ->tagihanBulanIni($penyewaId)
            ->orderByDesc('tanggal_tagihan')
            ->get();

        $tagihanBelumBayar = Transaksi::query()
            ->with('utilitas')
            ->where('penyewa_id', $penyewaId)
            ->belumBayar()
            ->orderBy('jatuh_tempo')
            ->get();

        $tunggakan = Transaksi::query()
            ->with('utilitas')
            ->tunggakan($penyewaId)
            ->orderBy('jatuh_tempo')
            ->get();

        $notifikasi = Notifikasi::query()
            ->where('user_id', optional($user)->id)
            ->orWhere('penyewa_id', optional($penyewa)->id)
            ->latest('created_at')
            ->take(5)
            ->get();

        return compact('penyewa', 'kamarKosong', 'tagihanBulanIni', 'tagihanBelumBayar', 'tunggakan', 'notifikasi');
    }

    public function penyewaDashboard()
    {
        return view('public.wellcome', $this->getPenyewaDashboardData());
    }

    public function profilShow()
    {
        $data = $this->getPenyewaDashboardData();
        // show editable profile form for penyewa
        return view('public.penyewa.profil', $data);
    }

    public function profilUpdate(Request $request)
    {
        $user = auth()->user();
        $penyewa = $user?->penyewa;

        if (!$penyewa) {
            return redirect()->back()->with('error', 'Profil penyewa tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'nik' => "required|string|max:30|unique:penyewa,nik,{$penyewa->id}",
            'no_hp' => "required|string|max:20|unique:penyewa,no_hp,{$penyewa->id}",
            'alamat_asal' => 'nullable|string|max:255',
            'pekerjaan' => 'nullable|string|max:150',
            'tanggal_lahir' => 'nullable|date',
        ]);

        // update user
        $user->update([
            'name' => $validated['name'],
            'no_hp' => $validated['no_hp'],
        ]);

        // update penyewa
        $penyewa->update([
            'nama_lengkap' => $validated['name'],
            'nik' => $validated['nik'],
            'no_hp' => $validated['no_hp'],
            'alamat_asal' => $validated['alamat_asal'] ?? $penyewa->alamat_asal,
            'pekerjaan' => $validated['pekerjaan'] ?? $penyewa->pekerjaan,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? $penyewa->tanggal_lahir,
        ]);

        return redirect()->route('penyewa.profil.show')->with('success', 'Profil berhasil diperbarui.');
    }

    public function tagihanIndex()
    {
        $data = $this->getPenyewaDashboardData();
        return view('public.wellcome', $data);
    }

    public function tagihanShow($transaksi)
    {
        $data = $this->getPenyewaDashboardData();
        $data['transaksi'] = $transaksi;
        return view('public.wellcome', $data);
    }

    public function pilihKamar(Request $request, Kamar $kamar)
    {
        $user = auth()->user();
        $penyewa = $user?->penyewa;

        if (!$penyewa) {
            return redirect()->back()->with('error', 'Profil penyewa tidak ditemukan. Hubungi admin untuk setup akun Anda.');
        }

        if ($kamar->status !== 'tersedia') {
            return redirect()->back()->with('error', 'Kamar ini sudah tidak tersedia. Silakan pilih kamar lain.');
        }

        if ($penyewa->kamar_id) {
            return redirect()->back()->with('info', 'Anda sudah memiliki kamar yang dipilih. Hubungi admin jika ingin mengubahnya.');
        }

        try {
            $penyewa->update(['kamar_id' => $kamar->id]);
            $kamar->forceFill(['status' => 'terisi'])->save();

            Notifikasi::kirim([
                'user_id' => $user->id,
                'penyewa_id' => $penyewa->id,
                'judul' => 'Kamar Berhasil Dipilih',
                'pesan' => "Anda berhasil memilih kamar {$kamar->nomor_kamar}.",
                'tipe' => 'kamar',
                'prioritas' => 'tinggi',
            ]);

            return redirect()->back()->with('success', "Kamar {$kamar->nomor_kamar} berhasil dipilih.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memilih kamar. Silakan coba lagi.');
        }
    }

    public function bayarTagihan(Request $request, Transaksi $transaksi)
    {
        $user = auth()->user();
        $penyewa = $user?->penyewa;

        if (!$penyewa) {
            return redirect()->back()->with('error', 'Profil penyewa tidak ditemukan.');
        }

        if ($transaksi->penyewa_id !== $penyewa->id) {
            abort(403, 'Anda tidak berhak mengakses tagihan ini.');
        }

        $validated = $request->validate([
            'metode_bayar' => 'required|in:tunai,transfer,qris,lainnya',
        ], [
            'metode_bayar.required' => 'Silakan pilih metode pembayaran.',
            'metode_bayar.in' => 'Metode pembayaran tidak valid.',
        ]);

        if ($transaksi->status === 'lunas') {
            return redirect()->back()->with('info', 'Tagihan ini sudah terbayar.');
        }

        try {
            $transaksi->update([
                'status' => 'lunas',
                'metode_bayar' => $validated['metode_bayar'],
                'tanggal_bayar' => now()->toDateString(),
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
                'user_id' => $user->id,
                'penyewa_id' => $penyewa->id,
                'judul' => 'Pembayaran Berhasil',
                'pesan' => "Pembayaran Rp " . number_format($transaksi->jumlah, 0, ',', '.') . " berhasil dikonfirmasi.",
                'tipe' => 'pembayaran',
                'prioritas' => 'tinggi',
            ]);

            Notifikasi::kirimKeAdmin([
                'penyewa_id' => $penyewa->id,
                'judul' => 'Pembayaran Dikonfirmasi',
                'pesan' => "Pembayaran {$transaksi->jenis} sebesar {$transaksi->jumlah_format} telah dikonfirmasi oleh {$user->name}.",
                'tipe' => 'pembayaran',
                'prioritas' => 'tinggi',
                'data_tambahan' => ['transaksi_id' => $transaksi->id],
            ]);

            return redirect()->back()->with('success', 'Pembayaran berhasil dicatat. Terima kasih!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
        }
    }
}
