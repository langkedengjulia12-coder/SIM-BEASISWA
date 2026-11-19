<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\LogAktivitas;
use App\Models\Mitra;
use App\Models\Pendaftaran;
use App\Models\Penerima;
use App\Models\Penyaluran;
use App\Models\SkemaBeasiswa;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Mengarahkan pengguna ke halaman awal sesuai role. */
    public function arahkan()
    {
        $u = auth()->user();

        return match (true) {
            $u->isAdmin() => redirect()->route('dashboard.admin'),
            $u->isStaf() => redirect()->route('beranda'),
            $u->isMitra() => redirect()->route('laporan.index'),
            default => redirect()->route('pendaftaran.status'),
        };
    }

    /** P-16 Dashboard Admin Wakil Rektor 3. */
    public function ringkasan(Request $request)
    {
        $tahun = (int) $request->input('tahun', now()->year);

        // 1. Hitung penerima aktif, total dana tersalurkan, dan kontrak aktif
        $penyaluranTahun = Penyaluran::whereIn('status', ['tersalurkan', 'dikonfirmasi'])
            ->whereBetween('tgl_penyaluran', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->get();

        $kartu = [
            'penerima_aktif' => Penerima::where('status_penerima', 'aktif')->count(),
            'dana_tersalur' => (float) $penyaluranTahun->sum('nominal'),
            'kontrak_aktif' => Kontrak::berlaku()->count(),
            'mitra_terverifikasi' => Mitra::where('status_verifikasi', 'terverifikasi')->count(),
            'menunggu_verifikasi' => Pendaftaran::where('status', 'diajukan')->count(),
            'skema_dibuka' => SkemaBeasiswa::where('status', 'dibuka')->count(),
        ];

        // 2. Susun data per bulan untuk grafik
        $perBulan = array_fill(1, 12, 0.0);
        foreach ($penyaluranTahun as $p) {
            $perBulan[(int) $p->tgl_penyaluran->format('n')] += $p->nominal;
        }
        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $perJenis = Penerima::with('skema')->where('status_penerima', 'aktif')->get()
            ->groupBy(fn ($p) => $p->skema->label_jenis)->map->count();

        $realisasiKomitmen = [
            'komitmen' => (float) \App\Models\KomitmenDana::sum('nominal_komitmen'),
            'diterima' => (float) \App\Models\KomitmenDana::sum('nominal_diterima'),
        ];

        $kontrakHampirBerakhir = Kontrak::with('mitra')->berlaku()
            ->whereDate('tgl_berakhir', '<=', now()->addDays((int) config('beasiswa.kontrak_peringatan_hari'))->toDateString())
            ->orderBy('tgl_berakhir')->get();

        $logTerbaru = LogAktivitas::with('pengguna')->orderByDesc('id_log')->limit(8)->get();

        // 3. Tampilkan pada kartu dan grafik ("Kosong" bila tidak ada data tahun terpilih)
        $kosong = $penyaluranTahun->isEmpty();

        return view('dashboard.index', compact(
            'tahun', 'kartu', 'perBulan', 'namaBulan', 'perJenis', 'realisasiKomitmen',
            'kontrakHampirBerakhir', 'logTerbaru', 'kosong'
        ));
    }

    /** Beranda Staf Unit Beasiswa: ringkasan pekerjaan yang menunggu. */
    public function beranda()
    {
        $tugas = [
            ['label' => 'Mitra menunggu verifikasi', 'jumlah' => Mitra::where('status_verifikasi', 'menunggu')->count(), 'route' => 'mitra.index'],
            ['label' => 'Pendaftar menunggu verifikasi berkas', 'jumlah' => Pendaftaran::where('status', 'diajukan')->count(), 'route' => 'verifikasi.index'],
            ['label' => 'Pendaftar siap dinilai', 'jumlah' => Pendaftaran::where('status', 'lolos_administrasi')->count(), 'route' => 'penilaian.index'],
            ['label' => 'Skema dibuka', 'jumlah' => SkemaBeasiswa::where('status', 'dibuka')->count(), 'route' => 'skema.index'],
            ['label' => 'Penyaluran gagal (perlu diulang)', 'jumlah' => Penyaluran::where('status', 'gagal')->count(), 'route' => 'penyaluran.index'],
            ['label' => 'Kontrak berakhir ≤ 30 hari', 'jumlah' => Kontrak::berlaku()->whereDate('tgl_berakhir', '<=', now()->addDays(30)->toDateString())->count(), 'route' => 'kontrak.index'],
        ];

        return view('beranda', compact('tugas'));
    }
}
