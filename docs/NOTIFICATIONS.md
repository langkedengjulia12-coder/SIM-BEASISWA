# Notifikasi & Perubahan Role/Permission

Perubahan yang saya terapkan:

- Menambahkan permissions baru pada `RoleSeeder`:
  - `manage users`, `manage system settings`, `view audit log`, `view finance`, `manage finance`
- Memastikan `Super Admin` memiliki semua permission; `Staff` mendapat akses terbatas (`view finance`, CRUD operasional tanpa `delete`)
- Menambahkan migrasi `notifications` (tabel database untuk notifikasi in-app)
- Menambahkan tampilan notifikasi di navbar (mengambil unread notifications)
- Menambahkan `NotificationController` (index, show, markAllRead) dan route admin untuk notifikasi
- Menambahkan `GenericNotice` notification class dan contoh pengiriman notifikasi ketika menambah `Armada`
- Menambahkan test `NotificationsTest` untuk memastikan pembuatan armada mengirim notifikasi ke Super Admin

Langkah selanjutnya untuk menjalankan:

1. Jalankan migrasi: `php artisan migrate`
2. Jalankan seeder: `php artisan db:seed` (untuk membuat roles & users contoh)
3. Periksa navbar admin — notifikasi baru akan muncul setelah aksi yang mengirim notifikasi (mis. tambah armada)

Catatan & opsi tambahan:
- Jika Anda ingin notifikasi dikirim ke Staff juga, beri peran Staff permission yang sesuai atau kirim ke semua user dengan role tertentu.
- Untuk Audit Log, saat ini hanya ada permission `view audit log`. Jika ingin, saya bisa menambahkan paket seperti `spatie/laravel-activitylog` untuk menyimpan dan menampilkan audit aktivitas.

Jika setuju, saya akan:
- Menambahkan notifikasi pada controller lain (Rute, Driver, Tracking, dll.) sesuai kebutuhan, dan
- Mengimplementasikan Audit Log (jika Anda ingin fitur tersebut sekarang).
