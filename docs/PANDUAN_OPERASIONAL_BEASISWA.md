# Panduan Operasional Beasiswa

## Seleksi pendaftar

1. Masuk menggunakan akun admin, lalu buka **Seleksi & Pendaftaran**.
2. Tinjau berkas pendaftar dan pilih **Lolos** atau **Tolak**. Status tersimpan dan aktivitasnya masuk ke audit trail.
3. Keputusan yang salah dapat diperbarui dengan memilih keputusan yang benar kembali.

## Membuat batch penyaluran

1. Buka **Penyaluran & Rekonsiliasi** dan unduh template batch CSV.
2. Isi satu baris untuk setiap pendaftar yang sudah berstatus Lolos. Kolom wajib dan urutannya:

   `registration_number,bank_name,account_number`

3. Nomor rekening wajib berisi 6–34 digit. Nominal penyaluran tidak diambil dari CSV; sistem menggunakan nilai pendanaan pada skema beasiswa.
4. Unggah CSV pada bagian **Buat batch**. Seluruh baris harus valid. Sistem menolak batch duplikat, pendaftar yang belum lolos, dan pendaftar yang sudah memiliki transaksi.
5. Nomor rekening disimpan terenkripsi. Jangan membagikan file CSV melalui kanal publik dan hapus salinan kerja setelah pemrosesan sesuai kebijakan UKRI.

## Rekonsiliasi mutasi bank

1. Unduh template mutasi CSV pada halaman **Penyaluran & Rekonsiliasi**.
2. Isi kolom sesuai urutan berikut:

   `registration_number,amount,reference_number,status`

3. Gunakan status `success` untuk transaksi berhasil atau `failed` untuk transaksi gagal. Nomor registrasi dan nominal harus cocok dengan satu transaksi yang masih menunggu.
4. Unggah file. Jika satu baris tidak valid atau tidak cocok, seluruh file ditolak dan tidak ada status transaksi yang diubah.

## Mengunduh laporan dan audit trail

1. Buka **Pelaporan & Audit**.
2. Pilih **PDF** atau **Excel** pada laporan yang diperlukan. Berkas Excel menggunakan format `.xlsx`.
3. Pilih modul dan tekan **Filter** untuk melihat aktivitas terbaru. Laporan **Audit Trail** mengekspor riwayat tindakan yang tercatat oleh aplikasi.

## Persiapan penerapan

### Backup dan pemulihan

- Perintah `php artisan beasiswa:backup` membuat dump MySQL dan arsip unggahan, kemudian mengenkripsinya dengan enkripsi terautentikasi AES-256-GCM berbasis `APP_KEY`. File plaintext sementara dihapus setelah selesai.
- Atur `BEASISWA_BACKUP_PATH` ke volume privat yang tidak berada di web root; atur `BEASISWA_BACKUP_RETENTION_DAYS` sesuai kebijakan penyimpanan. Default masa simpan adalah 7 hari. Aplikasi menolak lokasi backup di bawah web root.
- Perintah dijadwalkan setiap hari pukul 02.00. Di Linux, pasang cron Laravel yang menjalankan `php artisan schedule:run` setiap menit. Di Windows Server, buat Scheduled Task dengan pemicu setiap menit yang menjalankan PHP dan `artisan schedule:run` dari direktori aplikasi.
- Backup berisi data pribadi. Batasi akses direktori dan salin file `.zip.enc` ke lokasi terpisah yang terenkripsi/terkendali. Jangan mengunggah backup ke repository.
- Untuk memulihkan, dekripsi file terenkripsi: `php artisan beasiswa:backup --decrypt="PATH_BACKUP.zip.enc" --output="PATH_PEMULIHAN.zip"`. Setelah itu operator dapat memulihkan `database.sql` dan folder `storage/public/` dari ZIP menggunakan prosedur pemulihan server yang disetujui. Uji pemulihan pada lingkungan terisolasi sebelum produksi.
- Simpan salinan `APP_KEY` dengan aman dan terpisah dari backup. Tanpa kunci yang sama, backup maupun nomor rekening terenkripsi tidak dapat dipulihkan.
- Pastikan ekstensi PHP `openssl`, `zip`, dan `iconv`, serta executable `mysqldump` tersedia. Konfigurasikan `MYSQLDUMP_BINARY` bila executable tidak ada di PATH.

### Migrasi dan deployment

- Ambil dan verifikasi backup sebelum deployment.
- Pasang versi kode dan dependency yang telah diuji, atur `.env` produksi, permission folder `storage`/`bootstrap/cache`, dan pastikan `APP_KEY` produksi tetap sama.
- Jalankan `php artisan migrate --force`, `php artisan optimize:clear`, lalu `php artisan optimize`. Uji login admin, halaman seleksi, batch, rekonsiliasi, ekspor laporan, dan backup sebelum membuka layanan.
- Periksa status scheduler, log aplikasi, health check, dan hasil backup; lakukan prosedur rollback yang disetujui jika verifikasi gagal.

### Uji responsif lintas browser

Lakukan uji manual sebelum serah terima; jangan tandai checklist ini selesai hanya berdasarkan build asset.

| Halaman | Desktop (≥1280 px) | Tablet (768 px) | Mobile (375 px) |
|---|---|---|---|
| Seleksi & Pendaftaran | Pipeline dan tabel terbaca | Tabel dapat digulir horizontal | Form aksi tidak menutupi data |
| Penyaluran & Rekonsiliasi | Dua form unggah terlihat | Form tersusun tanpa overflow | Tombol dan pesan validasi tetap terlihat |
| Pelaporan & Audit | Kartu laporan dan tabel terbaca | Kartu menyesuaikan dua kolom | Aksi PDF/Excel dan filter dapat digunakan |

Ulangi pada versi browser Chrome, Edge, dan Firefox yang didukung UKRI serta perangkat mobile yang tersedia.

### Pelatihan dan BAST

Gunakan agenda dan template serah terima pada [BAST_PELATIHAN_TEMPLATE.md](./BAST_PELATIHAN_TEMPLATE.md). Pelaksanaan pelatihan, pengisian bukti, dan tanda tangan tetap membutuhkan perwakilan UKRI; hal tersebut tidak dapat diselesaikan hanya dengan perubahan kode.
