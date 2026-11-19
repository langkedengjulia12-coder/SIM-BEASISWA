# Overlay SIM-BEASISWA UKRI — penyelarasan dengan KAK & SKPL OO

ZIP ini adalah **paket timpa (overlay)**. Struktur foldernya sama dengan proyek (`SIM-BEASISWA/...`), sehingga
cukup di-extract ke tempat proyek berada dan pilih **Replace All / Timpa Semua**.
File `.env`, folder `vendor`, `node_modules`, `.git`, dan `Document/` **tidak** disentuh.

## 1. Cara memasang

1. **Backup** dulu folder proyek dan database Anda (overlay mengganti skema database).
2. Extract `SIM-BEASISWA_OVERLAY_SKPL.zip` ke folder tempat `SIM-BEASISWA` berada → **Replace All**.
3. Buka terminal di folder proyek, lalu:

```bash
composer dump-autoload
```

4. Periksa `.env` (file ini tidak ditimpa). Minimal:

```
DB_CONNECTION=mysql
DB_DATABASE=sim_beasiswa        # buat database kosong ini di phpMyAdmin
MAIL_MAILER=log                 # agar email tidak error bila SMTP belum ada (isi email masuk ke storage/logs)
```

   Opsional (lihat `.env.example`): `KEUANGAN_API_URL`, `KEUANGAN_API_TOKEN`, `MYSQLDUMP_PATH`.

5. Bangun ulang database (**menghapus semua data lama**, skema lama diganti 15 tabel `t_*` sesuai SKPL):

```bash
php artisan migrate:fresh --seed
php artisan serve
```

6. Buka http://127.0.0.1:8000 dan login dengan akun demo di bawah.
7. *(Opsional)* Hapus file sisa proyek lama: `sh bersihkan_file_lama.sh` (Windows: `bersihkan_file_lama.bat`),
   lalu `composer dump-autoload`. Aplikasi tetap berjalan tanpa langkah ini.
8. *(Opsional)* Jalankan uji otomatis: `php artisan test` (memakai SQLite in-memory; butuh ekstensi `pdo_sqlite`).

CSS/JS sudah dibangun ulang di `public/build`, jadi **tidak perlu** `npm run build`
(kecuali Anda mengubah tampilan: `npm install && npm run build`).

## 2. Akun demo (password semuanya: `Ukri@2026` — wajib diganti di produksi)

| Peran | Username |
|---|---|
| Admin WR3 | `admin.wr3` |
| Staf Unit Beasiswa | `staf.beasiswa` |
| Mahasiswa | `mhs1` … `mhs7` |
| Mitra/Donor | `mt260001`, `mt260002` |

## 3. Alur yang dapat dicoba (data demo sudah menyiapkan tiap tahap)

1. **Kemitraan** — Data Mitra (verifikasi → akun mitra dibuat otomatis) → Kontrak → Komitmen Dana.
2. **Skema** — buat skema (bobot kriteria harus 100%); *Buka Pendaftaran* hanya bila komitmen dana terkait cukup.
3. **Mahasiswa** — login `mhs6`, Daftar Beasiswa → pilih skema → unggah dokumen wajib.
4. **Seleksi (Staf)** — Verifikasi Berkas → Penilaian & Ranking → *Tutup Pendaftaran* → Penetapan & Pengumuman.
5. **Penyaluran** — mahasiswa mengisi rekening (Profil & Rekening) → Staf *Salurkan Dana* → Mahasiswa *Konfirmasi*.
6. **Monitoring & Pelaporan** — input IPS/IPK, Laporan Realisasi, Ekspor PDF/XLSX/CSV, Dashboard Admin WR3.

Penjadwal harian (`php artisan schedule:work` untuk pengembangan, atau cron `* * * * * php artisan schedule:run`):
cek masa berlaku kontrak (00:05), buka/tutup skema otomatis (00:10), backup harian (01:00) & mingguan (Minggu 02:00).

## 4. Backup & pemulihan (KAK 7.1.B)

```bash
php artisan beasiswa:backup harian       # simpan 7 berkas terakhir
php artisan beasiswa:backup mingguan     # simpan 4 berkas terakhir
```

Hasil: `storage/app/backup/{harian|mingguan}/sikb_YYYYMMDD_HHMMSS.sql.gz`.
Pemulihan: `gunzip -c sikb_xxx.sql.gz | mysql -u root -p sim_beasiswa` (Windows: ekstrak .gz lalu impor lewat phpMyAdmin).
Rencana DR sederhana: salin folder `storage/app/backup` **dan** `storage/app/{pendaftaran,kontrak,laporan}` (berkas terenkripsi)
ke media luar server; simpan juga `APP_KEY` — tanpa `APP_KEY` data terenkripsi tidak dapat dibuka.

## 5. Peringatan keamanan

* `.env` berisi `APP_KEY` dan kata sandi database — **jangan ikut dikumpulkan/dibagikan** dan jangan di-commit.
* Set `APP_ENV=production` dan `APP_DEBUG=false` saat produksi.
* Ganti seluruh password akun demo.
