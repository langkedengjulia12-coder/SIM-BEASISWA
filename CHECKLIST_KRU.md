# Checklist Implementasi CRUD Gaji & Absensi Kru

## ✅ Models & Relationships
- [x] Model `Crew` dengan relasi ke `CrewSalary` dan `CrewAbsence`
- [x] Model `CrewSalary` dengan relasi ke `Crew`
- [x] Model `CrewAbsence` dengan relasi ke `Crew`
- [x] Casts untuk decimal dan date

## ✅ Migrations
- [x] Migration `create_crews_table`
  - Kolom: id_crew, nama, no_ktp, telepon, alamat, status_aktif, tanggal_gabung, foto, gaji_pokok, catatan
  - Index: no_ktp (unique)
  
- [x] Migration `create_crew_salaries_table`
  - Kolom: id_crew_salary, id_crew, bulan, gaji_pokok, tunjangan, bonus, potongan_alfa, potongan_lainnya, gaji_bersih, status, tanggal_bayar, catatan
  - Index: bulan, foreign key ke crews
  - Unique: (id_crew, bulan)
  
- [x] Migration `create_crew_absences_table`
  - Kolom: id_crew_absence, id_crew, tanggal, tipe, deduction_amount, keterangan
  - Index: foreign key ke crews
  - Unique: (id_crew, tanggal)

## ✅ Controllers
- [x] `CrewController` dengan methods: index, create, store, show, edit, update, destroy
  - Validasi for create & update
  - File upload untuk foto
  - Notification untuk setiap aksi
  
- [x] `CrewSalaryController` dengan methods: index, create, store, show, edit, update, destroy
  - Validasi for create & update
  - Otomatis hitung gaji_bersih
  - Unique validation untuk (id_crew, bulan)
  
- [x] `CrewAbsenceController` dengan methods: index, create, store, show, edit, update, destroy
  - Validasi for create & update
  - Unique validation untuk (id_crew, tanggal)

## ✅ Views - Data Kru
- [x] `crew/index.blade.php`
  - Tabel dengan kolom: No, Foto, Nama, No. KTP, Telepon, Status, Gaji Pokok, Aksi
  - Tombol Tambah Kru
  - Aksi: View, Edit, Delete
  
- [x] `crew/create.blade.php`
  - Form dengan field: Nama, No. KTP, Telepon, Gaji Pokok, Alamat, Tanggal Gabung, Status, Foto
  - Preview foto
  - Validasi error display
  
- [x] `crew/edit.blade.php`
  - Form edit dengan pre-fill data
  - Foto display dan upload baru
  - Delete tombol untuk kru
  
- [x] `crew/show.blade.php`
  - Profil kru dengan foto dan data
  - Tab untuk Gaji dan Absensi
  - Tabel riwayat gaji dan absensi

## ✅ Views - Gaji Kru
- [x] `crew/salary/index.blade.php`
  - Tabel dengan kolom: No, Nama Kru, Bulan, Gaji Pokok, Tunjangan, Potongan Alfa, Gaji Bersih, Status
  - Tombol Tambah Gaji
  - Aksi: View, Edit, Delete
  
- [x] `crew/salary/create.blade.php`
  - Form dengan field: Kru, Bulan (month input)
  - Komponen gaji: Gaji Pokok, Tunjangan, Bonus, Potongan Alfa, Potongan Lainnya
  - Display Gaji Bersih (readonly, otomatis hitung)
  - Status dan Tanggal Bayar
  - JavaScript untuk kalkulasi otomatis
  
- [x] `crew/salary/edit.blade.php`
  - Form edit dengan pre-fill data
  - Sama dengan create tapi dengan update logic
  - Kalkulasi otomatis saat edit
  
- [x] `crew/salary/show.blade.php`
  - Display detail gaji dalam format rapi
  - Breakdown komponen gaji
  - Tombol Edit dan Delete

## ✅ Views - Absensi Kru
- [x] `crew/absence/index.blade.php`
  - Tabel dengan kolom: No, Nama Kru, Tanggal, Tipe, Potongan, Keterangan, Aksi
  - Tombol Tambah Absensi
  - Badge untuk tipe (Hadir=success, Alfa=danger, Sakit=warning)
  - Aksi: View, Edit, Delete
  
- [x] `crew/absence/create.blade.php`
  - Form dengan field: Kru, Tanggal, Tipe Absensi
  - Deduction Amount (otomatis 0 untuk Hadir/Cuti)
  - Keterangan
  - JavaScript untuk update deduction
  
- [x] `crew/absence/edit.blade.php`
  - Form edit dengan pre-fill data
  - Sama dengan create tapi dengan update logic
  
- [x] `crew/absence/show.blade.php`
  - Display detail absensi
  - Tombol Edit dan Delete

## ✅ Routes
- [x] Routes untuk Crew: `admin.crew.*` (index, create, store, show, edit, update, destroy)
- [x] Nested routes untuk Salary: `admin.crew.salary.*`
- [x] Nested routes untuk Absence: `admin.crew.absence.*`
- [x] Routes di dalam middleware 'auth' dan 'role:Super Admin|Staff'

## ✅ Navigation/Menu
- [x] Menu "Manajemen Kru" di sidebar admin.blade.php
- [x] Submenu: Data Kru, Gaji Kru, Absensi Kru
- [x] Active state berdasarkan current route

## ✅ Features
- [x] Foto upload dengan preview
- [x] Otomatis kalkulasi gaji bersih
- [x] Status tracking untuk gaji (pending, approved, paid)
- [x] Type tracking untuk absensi (hadir, alfa, sakit, cuti, lainnya)
- [x] Potongan gaji untuk absensi
- [x] Unique constraints pada database
- [x] Validasi pada form
- [x] Notification untuk create/update/delete
- [x] Soft delete ready (jika diperlukan)
- [x] Format currency untuk display gaji
- [x] Format date untuk display tanggal

## 📋 Next Steps untuk Menjalankan

1. **Jalankan Migration:**
   ```bash
   php artisan migrate
   ```

2. **Test di Browser:**
   - Login sebagai Super Admin atau Staff
   - Navigasi ke "Manajemen Kru"
   - Test CRUD untuk Data Kru
   - Test CRUD untuk Gaji Kru
   - Test CRUD untuk Absensi Kru

3. **Verifikasi Database:**
   - Check tabel `crews` sudah ada
   - Check tabel `crew_salaries` sudah ada
   - Check tabel `crew_absences` sudah ada

4. **File Storage:**
   - Pastikan folder `storage/app/public/crew/` ada
   - Jika belum: `php artisan storage:link`

## 🔍 Testing Scenarios

### Test Crew Create
1. Klik "Data Kru" → "Tambah Kru"
2. Isi semua field (nama, KTP, telepon, alamat, tanggal, gaji pokok, status)
3. Upload foto
4. Simpan → Should redirect ke index dengan success message

### Test Gaji Create
1. Klik "Gaji Kru" → "Tambah Gaji"
2. Pilih kru, pilih bulan, isi komponen gaji
3. Verifikasi gaji bersih otomatis terhitung
4. Simpan → Should calculate gaji_bersih = pokok + tunjangan + bonus - alfa - lainnya

### Test Absensi Create
1. Klik "Absensi Kru" → "Tambah Absensi"
2. Pilih kru, pilih tanggal, pilih tipe (alfa)
3. Masukkan potongan gaji
4. Simpan → Should save dengan deduction

### Test Validasi
1. Coba tambah gaji untuk kru yang sama di bulan yang sama → Should error "duplicate"
2. Coba tambah absensi untuk kru yang sama di tanggal yang sama → Should error "duplicate"
3. Coba tambah kru dengan KTP yang sama → Should error "KTP sudah terdaftar"

---

**Dokumentasi:** Lihat `DOKUMENTASI_KRU.md` untuk detail lengkap
