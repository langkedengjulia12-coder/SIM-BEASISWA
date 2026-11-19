# Pemetaan SKPL OO & KAK → Kode

## A. Proses SKPL (P-xx / UC-xx)

| P / UC | Kebutuhan | Controller / Service | Tampilan | Aktor |
|---|---|---|---|---|
| P-01 / UC-01 | Login & autentikasi (ALGO-01, kunci 5× gagal/15 mnt, log) | `AuthController` | `auth/login` | semua |
| P-02 / UC-02 | Kelola mitra & donor, verifikasi → akun mitra | `MitraController` | `mitra/index` | Staf/Admin |
| P-03 / UC-03 | Kontrak MoU/MoA, perpanjang, notifikasi ≤30 hari | `KontrakController`, `KontrakService`, cmd `beasiswa:cek-kontrak` | `kontrak/index` | Staf/Admin |
| P-04 / UC-04 | Komitmen dana & catat penerimaan | `KomitmenController` | `komitmen/index` | Staf/Admin, Mitra (lihat) |
| P-05 / UC-05 | Skema beasiswa, bobot 100%, cek dana, buka/tutup | `SkemaController`, cmd `beasiswa:perbarui-skema` | `skema/index` | Staf/Admin |
| P-06 / UC-06 | Daftar beasiswa (IPK, dokumen wajib, duplikat) | `PendaftaranController@form,kirim` | `pendaftaran/form` | Mahasiswa |
| P-07 / UC-07 | Verifikasi berkas | `VerifikasiController` | `seleksi/verifikasi*` | Staf/Admin |
| P-08 / UC-08 | Penilaian & ranking terbobot | `PenilaianController` | `seleksi/penilaian` | Staf/Admin |
| P-09 / UC-09 | Penetapan (kuota, cadangan 20%) & pengumuman | `PenetapanController` | `seleksi/penetapan` | Staf/Admin |
| P-10 / UC-10 | Lihat status seleksi | `PendaftaranController@statusSaya,perbaiki` | `pendaftaran/status` | Mahasiswa |
| P-11 / UC-11 | Verifikasi penerima & salurkan dana (API keuangan) | `PenyaluranController`, `KeuanganApiService` | `penyaluran/index` | Staf/Admin, Sistem Keuangan |
| P-12 / UC-12 | Konfirmasi penerimaan dana | `PenyaluranController@saya,konfirmasi` | `penyaluran/konfirmasi` | Mahasiswa |
| P-13 / UC-13 | Laporan realisasi & laporan mitra (+arsip `t_laporan`) | `LaporanController`, `LaporanService` | `laporan/index`, `laporan/mitra` | Staf/Admin, Mitra |
| P-14 / UC-14 | Ekspor PDF / XLSX / CSV | `EksporController`, `ExportService`, `SimplePdf`, `XlsxWriter` | `ekspor/index` | Staf/Admin |
| P-15 / UC-15 | Monitoring status akademik | `MonitoringController` | `monitoring/index` | Staf/Admin |
| P-16 / UC-16 | Dashboard Admin WR3 | `DashboardController@ringkasan` | `dashboard/index` | Admin |
| P-17 / UC-17 | Kelola pengguna | `PenggunaController` | `pengguna/index` | Admin |
| P-18 / UC-18 | Kirim notifikasi (DB + email, retry 3×) | `NotifikasiService`, `NotifikasiController` | `notifikasi/index` | sistem |

Database: 15 tabel `t_*` (`database/migrations/2026_11_20_*`), model di `app/Models`, kode ID otomatis `prefix.yy.nnnn` (`App\Support\Kode`).

## B. KAK

| KAK | Status |
|---|---|
| 6 modul (Kemitraan, Skema, Pendaftaran & Seleksi, Penyaluran, Pelaporan, Dashboard WR3) | Tercakup |
| Database + backup harian/mingguan | Tercakup (`beasiswa:backup`) |
| Enkripsi data sensitif (rekening, dokumen, kontrak) | Tercakup (AES-256 via `Crypt`; password bcrypt) |
| Pengujian fungsional | `php artisan test` — 32 test; sudah dijalankan pada SQLite dan MariaDB |
| Load test, penetration test, UAT | **Belum** — dilakukan di luar kode |
| SOP, manual pengguna, dokumentasi teknis | **Belum** (dokumen, bukan kode); dokumen ini hanya pemetaan |
| Migrasi data lama, operasional paralel | **Belum** (tergantung data sistem lama) |
| Integrasi sistem keuangan nyata | Disiapkan (`KEUANGAN_API_URL`); default **mode simulasi** |

## C. Keputusan & asumsi yang perlu Anda ketahui

1. **`id_pengguna` CHAR(11)**, bukan CHAR(10) seperti tabel SKPL, karena format kode `US.yy.nnnnn` memang 11 karakter (MySQL menolak data 11 karakter di CHAR(10)). Sebaiknya SKPL direvisi.
2. **Peran disimpan di kolom `t_pengguna.role`** (sesuai SKPL), bukan Spatie Permission (ID string tidak cocok dengan tabel Spatie). Paket & tabel Spatie dibiarkan tak terpakai.
3. **Tanpa "ingat saya"** dan tanpa pendaftaran mandiri: akun dibuat Admin (UC-17) atau otomatis saat mitra diverifikasi.
4. **Cek dana skema (ALGO-05):** kebutuhan = kuota × nominal × jumlah termin (sekaligus 1, per_semester 2, bulanan 6; ubah di `config/beasiswa.php`). Pengecekan wajib saat *Buka Pendaftaran* dan saat simpan bila sudah ada komitmen terkait; skema draf baru boleh disimpan tanpa komitmen.
5. **Nominal skema = per termin** (mengikuti rumus SKPL).
6. **Dokumen wajib per jenis** (`config/beasiswa.php`): prestasi = transkrip+KTM+sertifikat; bantuan biaya = transkrip+KTM+surat keterangan+slip penghasilan; riset = transkrip+KTM+proposal.
7. **Penetapan** hanya setelah skema *ditutup* dan semua pendaftar sudah dinilai; pendaftar yang tetap `tidak_lengkap` ditolak saat penetapan.
8. **Grafik dashboard** memakai SVG server-side (bukan Chart.js) agar tidak butuh internet/CDN.
9. **PDF & XLSX dibuat oleh kode sendiri** (tanpa dompdf/PhpSpreadsheet karena tidak terpasang). XLSX membutuhkan ekstensi PHP `zip`.
10. **Penghalusan alur** yang tidak ada di SKPL tetapi dibutuhkan agar alur tidak buntu: *Profil & Rekening* mahasiswa, *perbaikan berkas* (tidak_lengkap → diajukan), input IPS/IPK pada Monitoring (sebagai pengganti sinkronisasi SIAKAD).
11. Zona waktu `Asia/Jakarta`, bahasa `id`.
